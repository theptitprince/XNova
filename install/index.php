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

// Ecrit config.php : valeurs exportees proprement (plus d'injection de code possible par le formulaire)
// et mot secret aleatoire (il signe les cookies de connexion)
function InstallWriteConfig ( $Host, $User, $Pass, $Db, $Prefix ) {
	$Settings = array(
		'server'     => $Host,
		'user'       => $User,
		'pass'       => $Pass,
		'name'       => $Db,
		'prefix'     => $Prefix,
		'secretword' => bin2hex(random_bytes(32)),
	);
	$Content  = "<?php\n";
	$Content .= "if(!defined(\"INSIDE\")){ die(\"attemp hacking\"); }\n";
	$Content .= "\$dbsettings = ". var_export($Settings, true) .";\n";
	$Content .= "?>";
	return (@file_put_contents("../config.php", $Content) !== false);
}

// Prefixe des tables : lettres, chiffres et _ uniquement (il entre dans le nom des tables)
function InstallValidPrefix ( $Prefix ) {
	return (preg_match('/^[A-Za-z0-9_]*$/', $Prefix) == 1);
}


$Mode     = $_GET['mode'];
$Page     = $_GET['page'];
$phpself  = $_SERVER['PHP_SELF'];
$nextpage = $Page + 1;

	if (empty($Mode)) { $Mode = 'intro'; }
	if (empty($Page)) { $Page = 1;       }

	$MainTPL = gettemplate('install/ins_body');
	includeLang('install/install');
	switch ($Mode) {
		case 'intro':
				$SubTPL = gettemplate ('install/ins_intro');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
		 	break;
		case 'ins':
			if ($Page == 1) {
				if ($_GET['error'] == 1) {
				adminMessage ($lang['ins_error1'], $lang['ins_error']);
				}
				elseif ($_GET['error'] == 2) {
				adminMessage ($lang['ins_error2'], $lang['ins_error']);
				}

				$SubTPL = gettemplate ('install/ins_form');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 2) {
				$host   = $_POST['host'];
				$user   = $_POST['user'];
				$pass   = $_POST['passwort'];
				$prefix = $_POST['prefix'];
				$db     = $_POST['db'];

				$connection = InstallValidPrefix($prefix) ? InstallConnect($host, $user, $pass, $db) : false;
				if (!$connection) {
					header("Location: ?mode=ins&page=1&error=1");
					exit();
				}

				if (!InstallWriteConfig($host, $user, $pass, $db, $prefix)) {
					header("Location: ?mode=ins&page=1&error=2");
					exit();
				}

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
				doquery ( $QryTableMulti      , 'multi'      );
				// Table du formulaire de contact : meme definition que la mise a jour 0.9f (includes/migrations.php)
				mysqli_query($connection, str_replace('{{prefix}}', $prefix, $RenaissanceMigrations['0.9f'][0])) or die("MySQL Error: <b>". mysqli_error($connection) ."</b>");

				// Nouvelle base : directement a la version courante du schema
				RenaissanceSetSchemaVersion($connection, $prefix, RENAISSANCE_DB_VERSION);

				$SubTPL = gettemplate ('install/ins_form_done');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 3) {
				if ($_GET['error'] == 3) {
				adminMessage ($lang['ins_error3'], $lang['ins_error']);
				}

				$SubTPL = gettemplate ('install/ins_acc');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 4) {
				$adm_user   = $_POST['adm_user'];
				$adm_pass   = $_POST['adm_pass'];
				$adm_email  = $_POST['adm_email'];
				$adm_planet = $_POST['adm_planet'];
				$adm_sex    = $_POST['adm_sex'];
				$md5pass    = PasswordHash($adm_pass);

				if (!$_POST['adm_user']) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}
				if (!$_POST['adm_pass']) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}
				if (!$_POST['adm_email']) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}
				if (!$_POST['adm_planet']) {
					header("Location: ?mode=ins&page=3&error=3");
					exit();
				}

				// Pseudo : meme regle qu'a l'inscription
				if (preg_match("/[^A-Za-z0-9_\-]/", $adm_user) == 1) {
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
				if ($_GET['error'] == 1) {
				adminMessage ($lang['ins_error1'], $lang['ins_error']);
				}
				elseif ($_GET['error'] == 2) {
				adminMessage ($lang['ins_error2'], $lang['ins_error']);
				}
				elseif ($_GET['error'] == 4) {
				adminMessage ($lang['ins_goto_err_version'], $lang['ins_error']);
				}

				$SubTPL = gettemplate ('install/ins_goto_form');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 3) {
				// Transfere : reprise d'une base XNova Renaissance existante (0.9d ou plus recente) sur un nouveau serveur
				$host   = $_POST['host'];
				$user   = $_POST['user'];
				$pass   = $_POST['passwort'];
				$prefix = $_POST['prefix'];
				$db     = $_POST['db'];

				$connection = InstallValidPrefix($prefix) ? InstallConnect($host, $user, $pass, $db) : false;
				if (!$connection) {
					header("Location: ?mode=goto&page=2&error=1");
					exit();
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
				$SubTPL = gettemplate ('install/ins_upg_intro');
				$bloc   = $lang;
				$frame  = parsetemplate ( $SubTPL, $bloc );
			}
			elseif ($Page == 2) {
				if (!file_exists($xnova_root_path.'config.php') || filesize($xnova_root_path.'config.php') == 0) {
					adminMessage ($lang['ins_upg_noconfig'], $lang['ins_error']);
				}
				include($xnova_root_path.'config.php');
				$connection = InstallConnect($dbsettings['server'], $dbsettings['user'], $dbsettings['pass'], $dbsettings['name']);
				if (!$connection) {
					adminMessage ($lang['ins_error1'], $lang['ins_error']);
				}
				$FromVersion = RenaissanceSchemaVersion($connection, $dbsettings['prefix']);
				if ($FromVersion === false) {
					adminMessage ($lang['ins_goto_err_version'], $lang['ins_error']);
				}
				$Applied = RenaissanceRunMigrations($connection, $dbsettings['prefix'], $FromVersion);

				$bloc                     = $lang;
				$bloc['ins_upg_from']     = str_replace('%s', RenaissanceVersionLabel($FromVersion), $lang['ins_upg_from_version']);
				$bloc['ins_upg_result']   = (count($Applied) > 0) ? str_replace('%s', implode(', ', $Applied), $lang['ins_upg_applied']) : $lang['ins_upg_uptodate'];
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
	$parse['ins_page']     = $frame;
	$parse['dis_ins_btn']  = "?mode=$Mode&page=$nextpage";
	$Displ                 = parsetemplate ($MainTPL, $parse);

	display ($Displ, "Installeur", false, '', true);

?>