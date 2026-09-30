<?php

/**
 * index.php (Installeur)
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By e-Zobar for XNova
 * Based on first Chlorel's code
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);
include($xnova_root_path . 'includes/databaseinfos.'.$phpEx);
include($xnova_root_path . 'includes/migrations.'.$phpEx);

// Connexion MySQL de l'installeur (meme reglages que le jeu). Retourne la connexion ou false.
function InstallConnect ( $Host, $User, $Pass, $Db ) {
	mysqli_report(MYSQLI_REPORT_OFF);
	$Connection = @mysqli_connect($Host, $User, $Pass);
	if (!$Connection || !@mysqli_select_db($Connection, $Db)) {
		return false;
	}
	mysqli_set_charset($Connection, 'utf8mb4');
	mysqli_query($Connection, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
	return $Connection;
}

// Cookie de la cle d'une installation neuve (etape 2, voir InstallKeyValid)
define('INSTALL_KEY_COOKIE', 'xnova_install');

// Ecrit config.php avec les reglages donnes (valeurs exportees proprement). OPcache : la copie compilee de
// config.php est invalidee aussitot, sinon l'ancien fichier (vide a l'installation) pouvait rester en cache
function InstallSaveConfig ( $Settings ) {
	$Content  = "<?php\n";
	$Content .= "if(!defined(\"INSIDE\")){ die(\"attemp hacking\"); }\n";
	$Content .= "\$dbsettings = ". var_export($Settings, true) .";\n";
	$Content .= "?>";
	$Written  = (@file_put_contents("../config.php", $Content) !== false);
	if ($Written && function_exists('opcache_invalidate')) {
		@opcache_invalidate((realpath("../config.php") ?: "../config.php"), true);
	}
	return $Written;
}

// Ecrit config.php : valeurs exportees proprement (plus d'injection de code possible par le formulaire)
// et mot secret aleatoire (il signe les cookies de connexion). $InstallKey : cle d'une installation neuve, dont
// l'empreinte reste dans config.php jusqu'a la creation du compte administrateur
function InstallWriteConfig ( $Host, $User, $Pass, $Db, $Prefix, $InstallKey = '' ) {
	$Settings = array(
		'server'     => $Host,
		'user'       => $User,
		'pass'       => $Pass,
		'name'       => $Db,
		'prefix'     => $Prefix,
		'secretword' => bin2hex(random_bytes(32)),
	);
	if ($InstallKey != '') {
		$Settings['install_key'] = hash('sha256', $InstallKey);
	}
	return InstallSaveConfig($Settings);
}

// Pose (ou efface) le cookie de la cle d'installation : dossier install seulement, inaccessible au JavaScript
function InstallKeyCookie ( $Value, $Expire ) {
	setcookie(INSTALL_KEY_COOKIE, $Value, array(
		'expires'  => $Expire,
		'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off',
		'httponly' => true,
		'samesite' => 'Strict',
	));
}

// Prefixe des tables : lettres, chiffres et _ uniquement (il entre dans le nom des tables)
function InstallValidPrefix ( $Prefix ) {
	return (preg_match('/^[A-Za-z0-9_]*$/', $Prefix) == 1);
}

// Verrou de l'installeur. Sans lui, sur un serveur ou le dossier install est reste, n'importe qui pouvait
// reecrire config.php (Installer, Transfere) et brancher le jeu sur sa propre base.
// config.php deja rempli : le jeu est installe (ou une installation est en cours)
function InstallConfigWritten () {
	global $xnova_root_path;
	$File = $xnova_root_path . 'config.php';
	return (is_file($File) && trim((string) file_get_contents($File)) != '');
}

// Un compte administrateur existe deja dans la base de config.php : l'installation est terminee. Au moindre doute
// (prefixe invalide, connexion ou requete en echec), la reponse est oui : le verrou reste ferme (il s'ouvrait)
function InstallHasAdmin () {
	global $xnova_root_path;
	if (!InstallConfigWritten()) {
		return false;
	}
	$dbsettings = array();
	include($xnova_root_path . 'config.php');
	if (!InstallValidPrefix($dbsettings['prefix'] ?? '')) {
		return true;
	}
	$Connection = InstallConnect($dbsettings['server'] ?? '', $dbsettings['user'] ?? '', $dbsettings['pass'] ?? '', $dbsettings['name'] ?? '');
	if (!$Connection) {
		return true;
	}
	$Result = @mysqli_query($Connection, "SELECT COUNT(*) FROM `" . $dbsettings['prefix'] . "users` WHERE `authlevel` >= 3");
	$Row    = $Result ? mysqli_fetch_row($Result) : null;
	return (!$Row || $Row[0] > 0);
}

// Installation neuve en cours dans ce navigateur : cle posee en cookie a l'etape 2 (ecriture de config.php), son
// empreinte gardee dans config.php jusqu'a la creation du compte administrateur. Sans elle, n'importe quel visiteur
// pouvait creer l'administrateur pendant l'installation, ou plus tard des que la base n'en montrait plus aucun
function InstallKeyValid () {
	global $xnova_root_path;
	if (!InstallConfigWritten() || !isset($_COOKIE[INSTALL_KEY_COOKIE]) || !is_string($_COOKIE[INSTALL_KEY_COOKIE])) {
		return false;
	}
	$dbsettings = array();
	include($xnova_root_path . 'config.php');
	$Hash = (string) ($dbsettings['install_key'] ?? '');
	return ($Hash != '' && hash_equals($Hash, hash('sha256', $_COOKIE[INSTALL_KEY_COOKIE])));
}

// Essais de mot de passe de l'installeur : meme regle que login.php (partie Securite), dans la meme table. Apres
// 5 essais rates pour un meme pseudo depuis une meme adresse, refus pendant 15 minutes
define('INSTALL_LOGIN_MAX_FAILURES', 5);
define('INSTALL_LOGIN_BLOCK_TIME', 900);

// Adresse comptee pour la limite : meme regle que LoginAttemptIp (login.php), IPv6 regroupee par /64 et adresse
// IPv4 vue en IPv6 ramenee a l'adresse IPv4
function InstallAttemptIp ( $Ip ) {
	$Ip = (string) $Ip;
	if (filter_var($Ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
		$Bin = inet_pton($Ip);
		if (substr($Bin, 0, 12) == str_repeat(chr(0), 10) . chr(255) . chr(255)) {
			return inet_ntop(substr($Bin, 12));
		}
		return inet_ntop(substr($Bin, 0, 8) . str_repeat(chr(0), 8)) . '/64';
	}
	return substr($Ip, 0, 45);
}

// Essais rates dans les 15 minutes qui precedent le dernier essai rate ; 0 si ce dernier a plus de 15 minutes (comme
// LoginFailures de login.php). false si la table ne repond pas
function InstallLoginFailures ( $Connection, $Prefix, $Name, $Ip ) {
	$Where  = "`username` = '". mysqli_real_escape_string($Connection, $Name) ."' AND `ip` = '". mysqli_real_escape_string($Connection, $Ip) ."'";
	$Result = @mysqli_query($Connection, "SELECT MAX(`time`) FROM `". $Prefix ."login_attempts` WHERE ". $Where .";");
	$Last   = $Result ? mysqli_fetch_row($Result) : null;
	if (!$Last) {
		return false;
	}
	if (empty($Last[0]) || $Last[0] <= time() - INSTALL_LOGIN_BLOCK_TIME) {
		return 0;
	}
	$Result = @mysqli_query($Connection, "SELECT COUNT(*) FROM `". $Prefix ."login_attempts` WHERE ". $Where ." AND `time` > '". (intval($Last[0]) - INSTALL_LOGIN_BLOCK_TIME) ."';");
	$Count  = $Result ? mysqli_fetch_row($Result) : null;
	return $Count ? intval($Count[0]) : false;
}

// Administrateur du jeu installe, identifie dans l'installeur par son pseudo et son mot de passe (champs adm_login et
// adm_password, envoyes en POST) : sa fiche, sinon la raison du refus ('get' : page ouverte sans le formulaire,
// 'fail' : identifiants incorrects, 'blocked' : trop d'essais rates, 'db' : base de config.php injoignable). Une fois le
// jeu installe, lui seul peut lancer la mise a jour ou le transfert (ils etaient ouverts a tous). Aucun code du jeu ne
// tourne : la base peut encore etre celle d'une version plus ancienne (passer par login.php lancait les flottes et les
// suppressions de comptes du nouveau code sur l'ancienne base), et le format du cookie de connexion peut changer
function InstallAdminLogin () {
	global $xnova_root_path;
	if ($_SERVER['REQUEST_METHOD'] != 'POST' || !InstallConfigWritten()) {
		return 'get';
	}
	$Login    = $_POST['adm_login'] ?? '';
	$Password = $_POST['adm_password'] ?? '';
	if (!is_string($Login) || !is_string($Password) || $Login == '' || $Password == '') {
		return 'fail';
	}
	$dbsettings = array();
	include($xnova_root_path . 'config.php');
	$Prefix = $dbsettings['prefix'] ?? '';
	if (!InstallValidPrefix($Prefix)) {
		return 'db';
	}
	$Connection = InstallConnect($dbsettings['server'] ?? '', $dbsettings['user'] ?? '', $dbsettings['pass'] ?? '', $dbsettings['name'] ?? '');
	if (!$Connection) {
		return 'db';
	}
	// Table des essais de login.php, creee ici si la base n'est pas encore a la 0.9k (meme definition que
	// RenaissanceMigration09kSecurite, includes/migrations.php) : la limite vaut aussitot pour l'installeur et login.php.
	// Avant : un essai toutes les 3 s pour tout le serveur, sans blocage, et un verrou garde pendant l'attente
	// (quelques requetes simultanees occupaient les processus PHP et bloquaient l'administrateur)
	$Table = @mysqli_query($Connection, "CREATE TABLE IF NOT EXISTS `". $Prefix ."login_attempts` (
			`id` int(11) unsigned NOT NULL auto_increment,
			`username` varchar(64) NOT NULL default '',
			`ip` varchar(45) NOT NULL default '',
			`time` int(11) NOT NULL default '0',
			PRIMARY KEY (`id`),
			KEY `username_ip` (`username`, `ip`, `time`),
			KEY `time` (`time`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
	$Name     = mb_substr($Login, 0, 64, 'UTF-8');
	$Ip       = InstallAttemptIp($_SERVER['REMOTE_ADDR'] ?? '');
	$Failures = $Table ? InstallLoginFailures($Connection, $Prefix, $Name, $Ip) : false;
	if ($Failures === false) {
		return 'db';
	}
	if ($Failures >= INSTALL_LOGIN_MAX_FAILURES) {
		return 'blocked';
	}
	// Essai enregistre avant de verifier le mot de passe : des requetes simultanees ne depassent pas la limite
	@mysqli_query($Connection, "DELETE FROM `". $Prefix ."login_attempts` WHERE `time` < '". (time() - 2 * INSTALL_LOGIN_BLOCK_TIME) ."';");
	$Saved = @mysqli_query($Connection, "INSERT INTO `". $Prefix ."login_attempts` SET `username` = '". mysqli_real_escape_string($Connection, $Name) ."', `ip` = '". mysqli_real_escape_string($Connection, $Ip) ."', `time` = '". time() ."';");
	if (!$Saved) {
		return 'db';
	}
	$AttemptId = mysqli_insert_id($Connection);
	if (InstallLoginFailures($Connection, $Prefix, $Name, $Ip) > INSTALL_LOGIN_MAX_FAILURES) {
		@mysqli_query($Connection, "DELETE FROM `". $Prefix ."login_attempts` WHERE `id` = '". intval($AttemptId) ."';");
		return 'blocked';
	}
	$Result  = @mysqli_query($Connection, "SELECT `id`, `username`, `password`, `authlevel` FROM `". $Prefix ."users` WHERE `username` = '". mysqli_real_escape_string($Connection, $Login) ."' LIMIT 1;");
	if (!$Result) {
		return 'db';
	}
	$UserRow = mysqli_fetch_assoc($Result);
	// Meme controle que PasswordCheck (hash md5 de la 0.8e / 0.9d ou password_hash), sans reecrire le hash : le jeu le
	// convertit a la connexion suivante. Pseudo inconnu : meme temps de calcul qu'un vrai essai
	$Stored  = (string) ($UserRow['password'] ?? '');
	if (preg_match('/^[a-f0-9]{32}$/i', $Stored)) {
		$Valid = hash_equals(strtolower($Stored), md5($Password));
		// Meme temps qu'un essai sur un mot de passe moderne (compte en md5 non reperable, comme PasswordCheck)
		if (!$Valid) {
			PasswordHash($Password);
		}
	} elseif ($Stored != '') {
		$Valid = password_verify($Password, $Stored);
	} else {
		PasswordHash($Password);
		$Valid = false;
	}
	if (!$Valid || intval($UserRow['authlevel']) < 3) {
		return 'fail';
	}
	// Reussite : les essais rates de ce pseudo depuis cette adresse sont oublies
	@mysqli_query($Connection, "DELETE FROM `". $Prefix ."login_attempts` WHERE `username` = '". mysqli_real_escape_string($Connection, $Name) ."' AND `ip` = '". mysqli_real_escape_string($Connection, $Ip) ."';");
	return $UserRow;
}

// Champs d'identification de l'administrateur (jeu installe), places en tete du formulaire de l'etape
function InstallAdminRows () {
	global $lang;
	return "<tr><td class=\"c\" colspan=\"2\">". $lang['ins_adm_auth'] ."</td></tr>"
	     . "<tr><th colspan=\"2\"><br>". $lang['ins_adm_auth_txt'] ."<br><br>"
	     . "<table width=\"270\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">"
	     . "<tr><td>". $lang['ins_acc_user'] .":</td><td><input type=\"text\" name=\"adm_login\" value=\"\" size=\"20\"></td></tr>"
	     . "<tr><td>". $lang['ins_acc_pass'] .":</td><td><input type=\"password\" name=\"adm_password\" value=\"\" size=\"20\"></td></tr>"
	     . "</table><br></th></tr>";
}

// Ligne d'erreur placee au-dessus du contenu de l'etape (le formulaire reste affiche en dessous)
function InstallErrorRow ($Text) {
	global $lang;
	return "<tr><td class=\"c\" colspan=\"2\">". $lang['ins_error'] ."</td></tr>"
	     . "<tr><th colspan=\"2\"><br><font color=\"#FF6666\">". $Text ."</font><br><br></th></tr>";
}

// Message bloquant, dans le cadre de l'installeur (bandeau et menu compris) : AdminMessage affichait une page
// nue, sans menu pour revenir
function InstallMessage ($Text) {
	global $lang, $Page;
	$parse                = $lang;
	$parse['ins_state']   = $Page;
	$parse['ins_page']    = InstallErrorRow($Text);
	$parse['dis_ins_btn'] = 'index.php';
	display (parsetemplate(gettemplate('install/ins_body'), $parse), $lang['ins_page_title'], false, '', true);
}


$Mode     = ($_GET['mode'] ?? null);
$Page     = intval($_GET['page'] ?? 1);
$phpself  = $_SERVER['PHP_SELF'];

	// Modes connus seulement : la valeur etait recopiee telle quelle dans l'action du formulaire (injection de code)
	if (!in_array($Mode, array('intro', 'ins', 'goto', 'upg', 'bye'), true)) { $Mode = 'intro'; $Page = 1; }
	if ($Page < 1)    { $Page = 1;       }
	// Page suivante calculee apres la page par defaut (avant : depuis « Mise a jour » sans numero de page,
	// « Suivant » ramenait a l'etape 1)
	$nextpage = $Page + 1;

	$MainTPL = gettemplate('install/ins_body');
	includeLang('install/install');

	// Verrou : Installer (pages 1-2) reecrit config.php et cree les tables, donc seulement s'il est vide
	$Installed = InstallConfigWritten();
	if ($Mode == 'ins' && $Page <= 2 && $Installed) {
		InstallMessage ($lang['ins_locked']);
	}
	// Compte administrateur (pages 3-4) : seulement pendant une installation neuve, dans le navigateur qui a ecrit
	// config.php (cle de l'etape 2), et jamais s'il existe deja un administrateur
	if ($Mode == 'ins' && $Page >= 3 && (!InstallKeyValid() || InstallHasAdmin())) {
		InstallMessage ($lang['ins_locked']);
	}
	// Jeu installe : mise a jour et transfert reserves a un administrateur du jeu. L'etape qui modifie la base ou
	// config.php exige son pseudo et son mot de passe, saisis dans le formulaire de l'etape precedente. Refus : retour
	// au formulaire, avec la raison (rien pour une page ouverte sans le formulaire, un ancien favori par exemple)
	if ((($Mode == 'upg' && $Page >= 2) || ($Mode == 'goto' && $Page >= 3)) && $Installed) {
		$AdminLogin = InstallAdminLogin();
		if (!is_array($AdminLogin)) {
			$Errors = array('get' => '', 'fail' => '&error=5', 'db' => '&error=6', 'blocked' => '&error=7');
			header("Location: ?mode=". $Mode ."&page=". (($Mode == 'upg') ? 1 : 2) . $Errors[$AdminLogin]);
			exit();
		}
	}

	switch ($Mode) {
		case 'intro':
				$SubTPL = gettemplate ('install/ins_intro');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
		 	break;
		case 'ins':
			if ($Page == 1) {
				$ErrorRow = '';
				if (($_GET['error'] ?? null) == 1) {
					$ErrorRow = InstallErrorRow($lang['ins_error1']);
				}
				elseif (($_GET['error'] ?? null) == 2) {
					$ErrorRow = InstallErrorRow($lang['ins_error2']);
				}

				$SubTPL = gettemplate ('install/ins_form');
				$bloc   = $lang;
				$frame  = $ErrorRow . parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 2) {
				$host   = ($_POST['host'] ?? null);
				$user   = ($_POST['user'] ?? null);
				$pass   = ($_POST['passwort'] ?? null);
				$prefix = ($_POST['prefix'] ?? null);
				$db     = ($_POST['db'] ?? null);

				$connection = InstallValidPrefix($prefix) ? InstallConnect($host, $user, $pass, $db) : false;
				if (!$connection) {
					header("Location: ?mode=ins&page=1&error=1");
					exit();
				}

				// Cle de cette installation : seul ce navigateur pourra creer le compte administrateur (pages 3-4)
				$InstallKey = bin2hex(random_bytes(32));
				if (!InstallWriteConfig($host, $user, $pass, $db, $prefix, $InstallKey)) {
					header("Location: ?mode=ins&page=1&error=2");
					exit();
				}
				InstallKeyCookie($InstallKey, time() + 86400);

				function doquery ($InQry, $TblName) {
					global $prefix, $connection;
					$Table  = $prefix.$TblName;
					$DoQry  = str_replace("{{table}}", $Table, $InQry);
					$return = mysqli_query($connection, $DoQry) or die("MySQL Error: <b>".mysqli_error($connection)."</b>");
				return $return;
				}

				doquery ( $QryTableAks        , 'aks'        );
				doquery ( $QryTableAnnonce    , 'annonce'    );
				doquery ( $QryTableAlliance   , 'alliance'   );
				doquery ( $QryTableBanned     , 'banned'     );
				doquery ( $QryTableBuddy      , 'buddy'      );
				doquery ( $QryTableChat       , 'chat'       );
				doquery ( $QryTableConfig     , 'config'     );
				doquery ( $QryInsertConfig    , 'config'     );
				doquery ( $QryTabledeclared        , 'declared'        );
				doquery ( $QryTableErrors     , 'errors'     );
				doquery ( $QryTableFleets     , 'fleets'     );
				doquery ( $QryTableGalaxy     , 'galaxy'     );
				doquery ( $QryTableIraks      , 'iraks'      );
				doquery ( $QryTableLunas      , 'lunas'      );
				doquery ( $QryTableMessages   , 'messages'   );
				doquery ( $QryTableNotes      , 'notes'      );
				doquery ( $QryTablePlanets    , 'planets'    );
				doquery ( $QryTableRw         , 'rw'         );
				doquery ( $QryTableStatPoints , 'statpoints' );
				doquery ( $QryTableUsers      , 'users'      );
				// Table du formulaire de contact : meme definition que la mise a jour 0.9f (includes/migrations.php)
				mysqli_query($connection, str_replace('{{prefix}}', $prefix, $RenaissanceMigrations['0.9f'][0])) or die("MySQL Error: <b>". mysqli_error($connection) ."</b>");
				// Messages signales (bouton « Signaler ») : meme definition que la mise a jour 0.9h
				mysqli_query($connection, str_replace('{{prefix}}', $prefix, $RenaissanceMigrations['0.9h'][0])) or die("MySQL Error: <b>". mysqli_error($connection) ."</b>");
				// 0.9k : tables, index et reglages ajoutes par les fonctions de la mise a jour (relancables), memes definitions
				foreach ($RenaissanceMigrations['0.9k'] as $Step) {
					$Step($connection, $prefix);
				}

				// Nouvelle base : directement a la version courante du schema
				RenaissanceSetSchemaVersion($connection, $prefix, RENAISSANCE_DB_VERSION);

				$SubTPL = gettemplate ('install/ins_form_done');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 3) {
				$ErrorRow = (($_GET['error'] ?? null) == 3) ? InstallErrorRow($lang['ins_error3']) : '';

				$SubTPL = gettemplate ('install/ins_acc');
				$bloc   = $lang;
				$frame  = $ErrorRow . parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 4) {
				$adm_user   = ($_POST['adm_user'] ?? null);
				$adm_pass   = ($_POST['adm_pass'] ?? null);
				$adm_email  = ($_POST['adm_email'] ?? null);
				$adm_planet = ($_POST['adm_planet'] ?? null);
				$adm_sex    = ($_POST['adm_sex'] ?? null);
				$md5pass    = PasswordHash($adm_pass);

				if (!($_POST['adm_user'] ?? null)) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}
				if (!($_POST['adm_pass'] ?? null)) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}
				if (!($_POST['adm_email'] ?? null)) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}
				if (!($_POST['adm_planet'] ?? null)) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}

				// Pseudo : meme regle qu'a l'inscription
				if (preg_match("/[^A-Za-z0-9_\-]/", $adm_user) == 1) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}
				// Mot de passe et e-mail : memes regles qu'a l'inscription (un seul caractere suffisait pour l'administrateur)
				if (mb_strlen((string) $adm_pass) < 8 || !is_email($adm_email)) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}

				include($xnova_root_path.'config.php');
				$db_prefix = $dbsettings['prefix'];

				$connection = InstallConnect($dbsettings['server'], $dbsettings['user'], $dbsettings['pass'], $dbsettings['name']);
				if (!$connection) {
					header("Location: ?mode=ins&page=1&error=1");
					exit();
				}

				// Valeurs saisies echappees avant d'entrer dans les requetes
				$adm_user   = mysqli_real_escape_string($connection, $adm_user);
				$adm_email  = mysqli_real_escape_string($connection, $adm_email);
				$adm_planet = mysqli_real_escape_string($connection, strip_tags($adm_planet));
				$adm_sex    = ($adm_sex == 'F' || $adm_sex == 'M') ? $adm_sex : '';
				$md5pass    = mysqli_real_escape_string($connection, $md5pass);

				function doquery ($InQry, $TblName) {
					global $db_prefix, $connection;
					$Table  = $db_prefix.$TblName;
					$DoQry  = str_replace("{{table}}", $Table, $InQry);
					$return = mysqli_query($connection, $DoQry) or die("MySQL Error: <b>".mysqli_error($connection)."</b>");
				return $return;
				}

				$QryInsertAdm  = "INSERT INTO {{table}} SET ";
				$QryInsertAdm .= "`id`                = '1', ";
				$QryInsertAdm .= "`username`          = '". $adm_user ."', ";
				$QryInsertAdm .= "`email`             = '". $adm_email ."', ";
				$QryInsertAdm .= "`email_2`           = '". $adm_email ."', ";
				$QryInsertAdm .= "`authlevel`         = '3', ";
				$QryInsertAdm .= "`sex`               = '". $adm_sex ."', ";
				$QryInsertAdm .= "`id_planet`         = '1', ";
				$QryInsertAdm .= "`galaxy`            = '1', ";
				$QryInsertAdm .= "`system`            = '1', ";
				$QryInsertAdm .= "`planet`            = '1', ";
				$QryInsertAdm .= "`current_planet`    = '1', ";
				$QryInsertAdm .= "`register_time`     = '". time() ."', ";
				$QryInsertAdm .= "`password`          = '". $md5pass ."';";
				doquery($QryInsertAdm, 'users');

				$QryAddAdmPlt  = "INSERT INTO {{table}} SET ";
				$QryAddAdmPlt .= "`name`              = '". $adm_planet ."', ";
				$QryAddAdmPlt .= "`id_owner`          = '1', ";
				$QryAddAdmPlt .= "`galaxy`            = '1', ";
				$QryAddAdmPlt .= "`system`            = '1', ";
				$QryAddAdmPlt .= "`planet`            = '1', ";
				$QryAddAdmPlt .= "`last_update`       = '". time() ."', ";
				$QryAddAdmPlt .= "`planet_type`       = '1', ";
				$QryAddAdmPlt .= "`image`             = 'normaltempplanet02', ";
				$QryAddAdmPlt .= "`diameter`          = '12750', ";
				$QryAddAdmPlt .= "`field_max`         = '163', ";
				$QryAddAdmPlt .= "`temp_min`          = '47', ";
				$QryAddAdmPlt .= "`temp_max`          = '87', ";
				$QryAddAdmPlt .= "`metal`             = '500', ";
				$QryAddAdmPlt .= "`metal_perhour`     = '0', ";
				$QryAddAdmPlt .= "`metal_max`         = '1000000', ";
				$QryAddAdmPlt .= "`crystal`           = '500', ";
				$QryAddAdmPlt .= "`crystal_perhour`   = '0', ";
				$QryAddAdmPlt .= "`crystal_max`       = '1000000', ";
				$QryAddAdmPlt .= "`deuterium`         = '500', ";
				$QryAddAdmPlt .= "`deuterium_perhour` = '0', ";
				$QryAddAdmPlt .= "`deuterium_max`     = '1000000';";
				doquery($QryAddAdmPlt, 'planets');

				$QryAddAdmGlx  = "INSERT INTO {{table}} SET ";
				$QryAddAdmGlx .= "`galaxy`            = '1', ";
				$QryAddAdmGlx .= "`system`            = '1', ";
				$QryAddAdmGlx .= "`planet`            = '1', ";
				$QryAddAdmGlx .= "`id_planet`         = '1'; ";
				doquery($QryAddAdmGlx, 'galaxy');

				doquery("UPDATE {{table}} SET `config_value` = '1' WHERE `config_name` = 'LastSettedGalaxyPos';", 'config');
				doquery("UPDATE {{table}} SET `config_value` = '1' WHERE `config_name` = 'LastSettedSystemPos';", 'config');
				doquery("UPDATE {{table}} SET `config_value` = '1' WHERE `config_name` = 'LastSettedPlanetPos';", 'config');
				doquery("UPDATE {{table}} SET `config_value` = `config_value` + '1' WHERE `config_name` = 'users_amount' LIMIT 1;", 'config');

				// Installation terminee : cle retiree de config.php (l'installeur ne creera plus jamais de compte
				// administrateur) et cookie efface
				unset($dbsettings['install_key']);
				InstallSaveConfig($dbsettings);
				InstallKeyCookie('', time() - 3600);

				$SubTPL = gettemplate ('install/ins_acc_done');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
			break;
		case 'goto':
			if ($Page == 1) {
				$SubTPL = gettemplate ('install/ins_goto_intro');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 2) {
				$ErrorRow = '';
				if (($_GET['error'] ?? null) == 1) {
					$ErrorRow = InstallErrorRow($lang['ins_error1']);
				}
				elseif (($_GET['error'] ?? null) == 2) {
					$ErrorRow = InstallErrorRow($lang['ins_error2']);
				}
				elseif (($_GET['error'] ?? null) == 4) {
					$ErrorRow = InstallErrorRow($lang['ins_goto_err_version']);
				}
				elseif (($_GET['error'] ?? null) == 5) {
					$ErrorRow = InstallErrorRow($lang['ins_admin_only']);
				}
				elseif (($_GET['error'] ?? null) == 6) {
					$ErrorRow = InstallErrorRow($lang['ins_admin_nodb']);
				}
				elseif (($_GET['error'] ?? null) == 7) {
					$ErrorRow = InstallErrorRow($lang['ins_admin_blocked']);
				}

				$SubTPL = gettemplate ('install/ins_goto_form');
				$bloc   = $lang;
				$frame  = $ErrorRow . ($Installed ? InstallAdminRows() : '') . parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 3) {
				// Transfere : reprise d'une base XNova Renaissance existante (0.9d ou plus recente) sur un nouveau serveur
				$host   = ($_POST['host'] ?? null);
				$user   = ($_POST['user'] ?? null);
				$pass   = ($_POST['passwort'] ?? null);
				$prefix = ($_POST['prefix'] ?? null);
				$db     = ($_POST['db'] ?? null);

				$connection = InstallValidPrefix($prefix) ? InstallConnect($host, $user, $pass, $db) : false;
				if (!$connection) {
					header("Location: ?mode=goto&page=2&error=1");
					exit();
				}

				// Une seule operation a la fois sur cette base (double clic, deux onglets, mise a jour lancee en meme temps) :
				// meme verrou nomme que la mise a jour, pris sans attendre
				$Lock = @mysqli_query($connection, "SELECT GET_LOCK(LEFT(CONCAT(DATABASE(), '.". $prefix ."upgrade'), 64), 0);");
				$Lock = $Lock ? mysqli_fetch_row($Lock) : null;
				if (!$Lock || $Lock[0] != 1) {
					InstallMessage ($lang['ins_upg_running']);
				}
				$FromVersion = RenaissanceSchemaVersion($connection, $prefix);
				if ($FromVersion === false) {
					header("Location: ?mode=goto&page=2&error=4");
					exit();
				}

				if (!InstallWriteConfig($host, $user, $pass, $db, $prefix)) {
					header("Location: ?mode=goto&page=2&error=2");
					exit();
				}

				// La base est mise a niveau dans la foulee si elle vient d'une version plus ancienne
				$Applied = RenaissanceRunMigrations($connection, $prefix, $FromVersion);

				$bloc                = $lang;
				$bloc['ins_tx_done4'] = str_replace('%s', RenaissanceVersionLabel($FromVersion), $lang['ins_goto_done_version']);
				$bloc['ins_tx_done3'] = (count($Applied) > 0) ? str_replace('%s', implode(', ', $Applied), $lang['ins_upg_applied']) : $lang['ins_upg_uptodate'];
				$SubTPL = gettemplate ('install/ins_goto_done');
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
		 	break;
		case 'upg':
			// Mise a jour : applique a la base du jeu installe les modifications des versions plus recentes
			if ($Page == 1) {
				$AdminErrors = array(5 => 'ins_admin_only', 6 => 'ins_admin_nodb', 7 => 'ins_admin_blocked');
				$Error       = intval($_GET['error'] ?? 0);
				$ErrorRow    = isset($AdminErrors[$Error]) ? InstallErrorRow($lang[$AdminErrors[$Error]]) : '';
				$SubTPL   = gettemplate ('install/ins_upg_intro');
				$bloc     = $lang;
				$frame    = $ErrorRow . ($Installed ? InstallAdminRows() : '') . parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 2) {
				if (!file_exists($xnova_root_path.'config.php') || filesize($xnova_root_path.'config.php') == 0) {
					InstallMessage ($lang['ins_upg_noconfig']);
				}
				include($xnova_root_path.'config.php');
				$connection = InstallConnect($dbsettings['server'], $dbsettings['user'], $dbsettings['pass'], $dbsettings['name']);
				if (!$connection) {
					InstallMessage ($lang['ins_error1']);
				}
				// Une seule mise a jour a la fois (double clic, deux onglets) : verrou nomme de MySQL, pris sans attendre
				$Lock = @mysqli_query($connection, "SELECT GET_LOCK(LEFT(CONCAT(DATABASE(), '.". $dbsettings['prefix'] ."upgrade'), 64), 0);");
				$Lock = $Lock ? mysqli_fetch_row($Lock) : null;
				if (!$Lock || $Lock[0] != 1) {
					InstallMessage ($lang['ins_upg_running']);
				}
				$FromVersion = RenaissanceSchemaVersion($connection, $dbsettings['prefix']);
				if ($FromVersion === false) {
					InstallMessage ($lang['ins_goto_err_version']);
				}
				$Applied = RenaissanceRunMigrations($connection, $dbsettings['prefix'], $FromVersion);

				$bloc                     = $lang;
				$bloc['ins_upg_from']     = str_replace('%s', RenaissanceVersionLabel($FromVersion), $lang['ins_upg_from_version']);
				$bloc['ins_upg_result']   = (count($Applied) > 0) ? str_replace('%s', implode(', ', $Applied), $lang['ins_upg_applied']) : $lang['ins_upg_uptodate'];
				// Mot secret faible herite de la 0.8e / 0.9d ("XNova" suivi d'un nombre, retrouvable hors ligne a partir de
				// la signature du formulaire de contact) : remplace par un mot secret aleatoire, comme a l'installation.
				// Cookies et jetons en dependent : chacun se reconnecte une fois
				if (!preg_match('/^[0-9a-f]{64}$/', (string) ($dbsettings['secretword'] ?? ''))) {
					$dbsettings['secretword']  = bin2hex(random_bytes(32));
					$bloc['ins_upg_result']   .= '<br>' . (InstallSaveConfig($dbsettings) ? $lang['ins_upg_secret'] : $lang['ins_upg_secret_fail']);
				}
				$SubTPL = gettemplate ('install/ins_upg_done');
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
		 	break;
		case 'bye':
				header("Location: ../");
		 	break;
		default:
	}

	$parse                 = $lang;
	$parse['ins_state']    = $Page;
	$parse['ins_page']     = $frame ?? '';
	$parse['dis_ins_btn']  = "?mode=$Mode&page=$nextpage";
	$Displ                 = parsetemplate ($MainTPL, $parse);

	display ($Displ, $lang['ins_page_title'], false, '', true);

?>