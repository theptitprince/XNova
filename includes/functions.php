<?php

/**
 * functions.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// ----------------------------------------------------------------------------------------------------------------
//
// Routine pour la gestion du mode vacance
//



	
// Joueur en mode vacances : il ne peut pas etre attaque, donc il ne peut pas non plus envoyer de flotte ni de missiles
// (rien ne l'en empechait : il attaquait en restant intouchable). Arrete la page avec un message.
function check_urlaubmodus ($user) {
	global $lang;
	if (($user['urlaubs_modus'] ?? 0) == 1) {
		message($lang['sys_vacation_active'], $lang['sys_vacation_title'], "fleet.php", 3);
	}
}

// ----------------------------------------------------------------------------------------------------------------
// XNova Renaissance : mots de passe et cookie de connexion
//
// Hachage moderne (bcrypt / Argon2 selon PHP), remplace le md5 d'origine
function PasswordHash ( $Password ) {
	return password_hash($Password, PASSWORD_DEFAULT);
}

// Verifie le mot de passe d'un joueur. Les anciens hash md5 (XNova 0.8e / 0.9d) sont convertis
// automatiquement a la premiere connexion reussie. $UserRow['password'] est mis a jour si besoin.
function PasswordCheck ( $Password, &$UserRow ) {
	$Stored = $UserRow['password'];
	if (preg_match('/^[a-f0-9]{32}$/i', $Stored)) {
		if (!hash_equals(strtolower($Stored), md5($Password))) {
			return false;
		}
		$NeedUpdate = true;
	} else {
		if (!password_verify($Password, $Stored)) {
			return false;
		}
		$NeedUpdate = password_needs_rehash($Stored, PASSWORD_DEFAULT);
	}
	if ($NeedUpdate) {
		$UserRow['password'] = PasswordHash($Password);
		doquery("UPDATE {{table}} SET `password` = '". SqlEscape($UserRow['password']) ."' WHERE `id` = '". intval($UserRow['id']) ."' LIMIT 1;", 'users');
	}
	return true;
}

// Duree de validite du cookie de connexion, verifiee par le serveur grace a la date de connexion signee : 365 jours
// avec « se souvenir de moi » (comme l'original), 1 jour sinon. Avant, une copie du cookie restait valable pour
// toujours, meme apres la deconnexion (seul un changement de mot de passe l'annulait).
define('AUTH_COOKIE_REMEMBER', 31536000);
define('AUTH_COOKIE_SESSION', 86400);

// Jeton du cookie : signature HMAC de l'id, du hash du mot de passe (change si le mot de passe change), de
// « se souvenir de moi » et de la date de connexion
function AuthCookieToken ( $UserRow, $RememberMe, $IssueTime ) {
	global $xnova_root_path;
	include($xnova_root_path . 'config.php');
	return hash_hmac('sha256', intval($UserRow['id']) .'|'. $UserRow['password'] .'|'. intval($RememberMe) .'|'. intval($IssueTime), $dbsettings['secretword']);
}

// Valeur du cookie de connexion : id/%/pseudo/%/jeton/%/se souvenir de moi/%/date de connexion
function AuthCookieValue ( $UserRow, $RememberMe, $IssueTime ) {
	return intval($UserRow['id']) .'/%/'. $UserRow['username'] .'/%/'. AuthCookieToken($UserRow, $RememberMe, $IssueTime) .'/%/'. intval($RememberMe) .'/%/'. intval($IssueTime);
}

// Pose (ou efface avec $Value = '') le cookie de connexion : inaccessible au JavaScript, envoye seulement par ce site
function SetAuthCookie ( $Value, $Expire ) {
	global $game_config;
	setcookie($game_config['COOKIE_NAME'], $Value, array(
		'expires'  => $Expire,
		'path'     => '/',
		'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off',
		'httponly' => true,
		'samesite' => 'Lax',
	));
}

// ----------------------------------------------------------------------------------------------------------------
// XNova Renaissance : nettoyage des textes saisis par les joueurs (protection XSS).
// Le moteur de templates insere les valeurs telles quelles : on nettoie donc a la saisie, une seule fois.
//
// Nom (planete, alliance, tag, rang, raccourci...) : ces noms sont aussi places dans des infobulles JavaScript,
// ou l'echappement HTML ne suffit pas. On retire donc les caracteres dangereux < > " ' ` \ et les controles.
function SafeName ( $String, $MaxLength = 64 ) {
	// Apostrophe droite -> apostrophe typographique, sans danger dans le HTML et le JavaScript : elle etait
	// supprimee (« Officier d'etat-major » devenait « Officier detat-major »)
	$String = str_replace("'", "\u{2019}", (string) $String);
	$String = preg_replace('/[<>"\'`\\\\\x00-\x1F\x7F]/u', '', (string) $String);
	$String = trim(preg_replace('/\s+/u', ' ', $String));
	return mb_substr($String, 0, $MaxLength, 'UTF-8');
}

// Texte libre (message, texte d'alliance, note...) : echappe pour l'affichage HTML, rien n'est perdu
function SafeText ( $String ) {
	return htmlspecialchars(trim((string) $String), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Taille de la file de construction des batiments et unites par commande au chantier spatial et a la defense :
// reglages de la Configuration (5 et 1 000 par defaut, les valeurs de l'original), dans les bornes de constants.php
function BuildingQueueSize () {
	global $game_config;
	return max(1, min(MAX_BUILDING_QUEUE_LIMIT, intval($game_config['max_building_queue'] ?? MAX_BUILDING_QUEUE_SIZE)));
}

function OrderUnitsMax () {
	global $game_config;
	return max(1, min(MAX_ORDER_UNITS_LIMIT, intval($game_config['max_order_units'] ?? MAX_FLEET_OR_DEFS_PER_ROW)));
}

// Adresse web : uniquement http(s), sans espace ni guillemet (interdit javascript:, data:...). Sinon chaine vide.
function SafeUrl ( $Url ) {
	$Url = trim((string) $Url);
	if ($Url == '' || !preg_match('#^https?://[A-Za-z0-9.\-]+(:[0-9]+)?(/[A-Za-z0-9._~:/?\#\[\]@!$&()*+,;=%\-]*)?$#', $Url)) {
		return '';
	}
	return $Url;
}

// Adresse du jeu pour les liens des mails (mot de passe oublie, bienvenue) : reglage game_url, rempli a
// l'installation ou a la mise a jour avec l'adresse utilisee par l'administrateur. Vide : adresse de la page en
// cours, d'apres l'en-tete Host envoye par le visiteur (ancien comportement : un visiteur pouvait y mettre le
// domaine de son choix, et le mail authentique du jeu contenait alors un lien vers ce domaine).
// L'administrateur doit garder game_url egale a l'adresse publique du jeu (changement de domaine, passage en https,
// demenagement) : ligne game_url de la table config, en attendant son champ dans les parametres de l'administration.
function GameUrl () {
	global $game_config;
	$Url = SafeUrl($game_config['game_url'] ?? '');
	if ($Url != '') {
		return rtrim($Url, '/') . '/';
	}
	return RequestGameUrl(1);
}

// Adresse du jeu d'apres la page en cours (en-tete Host), $Up dossiers au-dessus de la page : 1 pour une page du jeu,
// 2 pour install/index.php. Dossier encode : un jeu installe dans « XNova Renaissance/ » (espace, accents) donnait
// une adresse refusee par SafeUrl.
function RequestGameUrl ( $Up = 1 ) {
	$Scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
	$Host   = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
	$Dir    = (string) ($_SERVER['SCRIPT_NAME'] ?? '/');
	for ($i = 0; $i < $Up; $i++) {
		$Dir = dirname($Dir);
	}
	$Dir    = rtrim(str_replace('\\', '/', $Dir), '/');
	return $Scheme . $Host . implode('/', array_map('rawurlencode', explode('/', $Dir))) . '/';
}

// Chemin de skin : vide, chemin relatif simple ou adresse http(s)
function SafePath ( $Path ) {
	$Path = trim((string) $Path);
	if (preg_match('#^https?://#i', $Path)) {
		return SafeUrl($Path);
	}
	return preg_match('#^[A-Za-z0-9_\-./]*$#', $Path) && strpos($Path, '..') === false ? $Path : '';
}

// ----------------------------------------------------------------------------------------------------------------
// XNova Renaissance : protection contre les requetes forgees (CSRF).
// Jeton propre a chaque joueur (signe avec le mot secret, change avec le mot de passe), sans session PHP.
function CsrfToken () {
	global $user, $xnova_root_path;
	if (!is_array($user) || empty($user['id'])) {
		return '';
	}
	include($xnova_root_path . 'config.php');
	return hash_hmac('sha256', 'csrf|' . intval($user['id']) . '|' . $user['password'], $dbsettings['secretword']);
}

// Le jeton recu (formulaire ou lien) est-il valide ?
function CsrfValid () {
	$Token = CsrfToken();
	$Given = isset($_POST['csrf_token']) ? ($_POST['csrf_token'] ?? null) : (isset($_GET['csrf_token']) ? ($_GET['csrf_token'] ?? null) : '');
	return ($Token != '' && is_string($Given) && hash_equals($Token, $Given));
}

// Actions declenchees par un simple lien (GET) qui modifient le jeu : elles exigent aussi le jeton.
// Page => parametres qui declenchent l'action. Les formulaires (POST) sont tous verifies, sans liste.
function CsrfGetAction () {
	$Actions = array(
		'buildings.php'          => array('cmd'),              // construire, detruire, annuler (batiments et recherche)
		'officier.php'           => array('offi'),             // recruter un officier
		'annonce.php'            => array('action'),           // supprimer une de ses annonces
		'buddy.php'              => array('bid'),              // accepter / supprimer un ami
		'alliance.php'           => array('kick', 'd', 'yes'), // exclure un membre, supprimer un rang, quitter
		'quickfleet.php'         => array('mode'),             // envoi rapide de recycleurs
		'phalanx.php'            => array('galaxy'),           // scan de phalange (coute du deuterium)
		'admin/userlist.php'     => array('cmd'),              // supprimer un joueur
		'admin/chat.php'         => array('delete', 'deleteall'),
		'admin/errors.php'       => array('delete', 'deleteall'),
		'admin/paneladmina.php'  => array('authlvl'),          // changer le niveau d'un compte
		'admin/contactlist.php'  => array('read', 'unread', 'delete'), // messages de contact
		'admin/reports.php'      => array('done', 'undone', 'delete'), // messages signales
	);
	$Script = (defined('IN_ADMIN') ? 'admin/' : '') . basename($_SERVER['SCRIPT_NAME']);
	if (!isset($Actions[$Script])) {
		return false;
	}
	foreach ($Actions[$Script] as $Param) {
		if (isset($_GET[$Param])) {
			return true;
		}
	}
	return false;
}

// Ajoute le jeton a tous les formulaires et a tous les liens internes d'une page (appele par display())
function CsrfInject ( $Html ) {
	$Token = CsrfToken();
	if ($Token == '') {
		return $Html;
	}
	$Field = '<input type="hidden" name="csrf_token" value="' . $Token . '" />';
	$Html  = preg_replace('/(<form\b[^>]*>)/i', '$1' . $Field, $Html);
	// Jeton aussi disponible en JavaScript, pour les liens fabriques par les comptes a rebours (annulation)
	$Script = '<script type="text/javascript">var xnova_csrf = "' . $Token . '";</script>';
	$Html   = (stripos($Html, '</head>') !== false) ? preg_replace('#</head>#i', $Script . '</head>', $Html, 1) : $Script . $Html;
	// Liens internes vers une page .php avec parametres (ou "?..."), hors adresses externes. Seulement le vrai
	// attribut href des balises a, area et link : avant, tout « href= » de la page etait pris, meme au milieu de
	// l'adresse d'une image exterieure, qui recevait alors le jeton du joueur
	$Html  = preg_replace_callback('/<(?:a|area|link)\b[^>]*>/i', function ($Tag) use ($Token) {
		return preg_replace_callback('/(\s)([^\s=>"\'\/]+)(\s*=\s*)("[^"]*"|\'[^\']*\'|[^\s>"\']+)/', function ($m) use ($Token) {
			if (strtolower($m[2]) != 'href') {
				return $m[0];
			}
			$Quote = ($m[4][0] == '"' || $m[4][0] == "'") ? $m[4][0] : '';
			$Value = ($Quote != '') ? substr($m[4], 1, -1) : $m[4];
			if (strpos($Value, 'csrf_token=') !== false ||
			    !preg_match('/^(?:[A-Za-z0-9_\-.\/]*\.php\?[^"\'\s>]*|\?[^"\'\s>]*)$/', $Value) || strpos($Value, '//') === 0) {
				return $m[0];
			}
			return $m[1] . $m[2] . $m[3] . $Quote . $Value . '&amp;csrf_token=' . $Token . $Quote;
		}, $Tag[0]);
	}, $Html);
	return $Html;
}

// ----------------------------------------------------------------------------------------------------------------
// XNova Renaissance : convertit en entiers les champs numeriques recus (GET et POST).
// $Fields : liste des noms de champs ; $Pattern : expression reguliere optionnelle (ex. '/^ship[0-9]+$/')
function SanitizeNumericInput ( $Fields, $Pattern = '' ) {
	foreach (array('_GET', '_POST') as $Source) {
		foreach ($GLOBALS[$Source] as $Field => $Value) {
			if (in_array($Field, $Fields, true) || ($Pattern != '' && preg_match($Pattern, $Field))) {
				$GLOBALS[$Source][$Field] = is_array($Value) ? 0 : intval($Value);
			}
		}
	}
}

// ----------------------------------------------------------------------------------------------------------------
//
// Adresse e-mail valide : syntaxe verifiee par PHP (FILTER_VALIDATE_EMAIL) et domaine avec au moins un point.
// Remplace l'expression de 2008 et sa liste figee d'extensions (.app, .dev, .paris... etaient refusees).
// 64 caracteres au plus : taille des colonnes email / email_2. Caracteres usuels seulement : la norme accepte
// guillemets et espaces entre guillemets, dangereux une fois l'adresse affichee (administration, options).
//
function is_email($email) {
	$email = trim((string) $email);
	if ($email == '' || strlen($email) > 64 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
		return false;
	}
	if (!preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+$/', $email)) {
		return false;
	}
	return (strpos(substr(strrchr($email, '@'), 1), '.') !== false);
}

// Pseudo : 64 caracteres au plus, taille de la colonne username. Au-dela, MySQL tronquait sans erreur a
// l'inscription : compte cree sans planete mere et planete sans proprietaire.
define('USERNAME_MAX_LENGTH', 64);

// ----------------------------------------------------------------------------------------------------------------
//
// Routine Affichage d'un message administrateur avec saut vers une autre page si souhaité
//
function AdminMessage ($mes, $title = 'Error', $dest = "", $time = "3") {
	$page = '';
	$parse['color'] = '';
	$parse['title'] = $title;
	$parse['mes']   = $mes;

	$page .= parsetemplate(gettemplate('admin/message_body'), $parse);

	display ($page, $title, false, (($dest != "") ? "<meta http-equiv=\"refresh\" content=\"". intval($time) .";URL=". htmlspecialchars($dest) ."\">" : ""), true);
}

// ----------------------------------------------------------------------------------------------------------------
//
// Routine Affichage d'un message avec saut vers une autre page si souhaité
//
function message ($mes, $title = 'Error', $dest = "", $time = "3") {
	$page = '';
	$parse['color'] = '';
	$parse['title'] = $title;
	$parse['mes']   = $mes;

	$page .= parsetemplate(gettemplate('message_body'), $parse);

	display ($page, $title, false, (($dest != "") ? "<meta http-equiv=\"refresh\" content=\"". intval($time) .";URL=". htmlspecialchars($dest) ."\">" : ""), false);
}

// ----------------------------------------------------------------------------------------------------------------
//
// Routine d'affichage d'une page dans un cadre donné
//
// $page      -> la page
// $title     -> le titre de la page
// $topnav    -> Affichage des ressources ? oui ou non ??
// $metatags  -> S'il y a quelques actions particulieres a faire ...
// $AdminPage -> Si on est dans la section admin ... faut le dire ...
function display ($page, $title = '', $topnav = true, $metatags = '', $AdminPage = false) {
	global $link, $game_config, $debug, $user, $planetrow;

	if (!$AdminPage) {
		$DisplayPage  = StdUserHeader ($title, $metatags);
	} else {
		$DisplayPage  = AdminUserHeader ($title, $metatags);
	}

	if ($topnav) {
		$DisplayPage .= ShowTopNavigationBar( $user, $planetrow );
	}
	$DisplayPage .= "<center>\n". $page ."\n</center>\n";
	// Affichage du Debug si necessaire : outil technique, administrateurs seulement (0.9k, comme les erreurs SQL de
	// debug->error() : le journal contient les requetes des autres joueurs), sous la page au lieu de la remplacer
	if (is_array($user) && isset($user['authlevel']) && $user['authlevel'] >= 3) {
		if (!empty($game_config['debug'])) $DisplayPage .= $debug->echo_log();
	}

	$DisplayPage .= StdFooter();
	if ($link instanceof mysqli) {
		mysqli_close($link);
	}

	// Protection CSRF : jeton ajoute a tous les formulaires et liens internes de la page
	$DisplayPage = CsrfInject($DisplayPage);

	echo $DisplayPage;

	die();
}

// ----------------------------------------------------------------------------------------------------------------
//
// Entete de page
//
function StdUserHeader ($title = '', $metatags = '') {
	global $user, $dpath, $langInfos, $xnova_root_path;

	$parse             = $langInfos;
	$parse['title']    = $title;
	// Racine du jeu pour le script et l'icone : message() est aussi appele depuis admin/ (404 sur admin/scripts/...)
	$parse['root']     = $xnova_root_path ?? './';
	if ( defined('LOGIN') ) {
		$parse['dpath']    = "skins/xnova/";
		$parse['style_tags']  = "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/styles.css\">\n";
		$parse['style_tags'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/about.css\">\n";
	} else {
		$parse['dpath']    = $dpath;
		$parse['style_tags']  = "<link rel=\"stylesheet\" type=\"text/css\" href=\"". $dpath ."default.css\" />";
		$parse['style_tags'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"". $dpath ."formate.css\" />";
	}

	$parse['meta_tags']  = ($metatags) ? $metatags : "";
	$parse['body_tag']  = "<body>"; //  class=\"style\" topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">";
	return parsetemplate(gettemplate('simple_header'), $parse);
}

// ----------------------------------------------------------------------------------------------------------------
//
// Entete de page administration
//
function AdminUserHeader ($title = '', $metatags = '') {
	global $user, $dpath, $langInfos;

	$parse           = $langInfos;
	$parse['dpath']  = $dpath;
	$parse['title']  = $title;
	$parse['meta_tags'] = ($metatags) ? $metatags : "";
	$parse['body_tag'] = "<body>"; //  class=\"style\" topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">";
	return parsetemplate(gettemplate('admin/simple_header'), $parse);
}

// ----------------------------------------------------------------------------------------------------------------
//
// Pied de page
//
function StdFooter() {
	global $game_config, $lang;
	$parse['copyright']     = $game_config['copyright'] ?? '';
	$parse['translation_by'] = $lang['translation_by'] ?? '';
	return parsetemplate(gettemplate('overall_footer'), $parse);
}

// ----------------------------------------------------------------------------------------------------------------
//
// Calcul de la place disponible sur une planete
//
function CalculateMaxPlanetFields (&$planet) {
	global $resource;

	return $planet["field_max"] + ($planet[ $resource[33] ] * 5);
}

?>
