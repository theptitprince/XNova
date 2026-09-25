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



	
function check_urlaubmodus ($user) {
	if ($user['urlaubs_modus'] == 1) {
		message("Vous êtes en mode vacances!", $title = $user['username'], $dest = "", $time = "3");
	}
}

function check_urlaubmodus_time () {
	global $user, $game_config;
	if ($game_config['urlaubs_modus_erz'] == 1) {
		$begrenzung             = 86400; //24x60x60= 24h
		$urlaub_modus_time      = $user['urlaubs_modus_time'];
		$urlaub_modus_time_soll = $urlaub_modus_time + $begrenzung;
		$time_jetzt             = time();
		if ($user['urlaubs_modus'] == 1 && $urlaub_modus_time_soll > $time_jetzt) {
			$soll_datum = date("d.m.Y", $urlaub_modus_time_soll);
			$soll_uhrzeit = date("H:i:s", $urlaub_modus_time_soll);
			message("Vous êtes en mode vacances!<br>Le mode vacance dure jusque $soll_datum $soll_uhrzeit<br>	Ce n'est qu'après cette période que vous pouvez changer vos options.", "Mode vacance");
		}
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

// Jeton du cookie : signature HMAC de l'id et du hash du mot de passe (change si le mot de passe change)
function AuthCookieToken ( $UserRow ) {
	global $xnova_root_path;
	include($xnova_root_path . 'config.php');
	return hash_hmac('sha256', intval($UserRow['id']) .'|'. $UserRow['password'], $dbsettings['secretword']);
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
	$String = preg_replace('/[<>"\'`\\\\\x00-\x1F\x7F]/u', '', (string) $String);
	$String = trim(preg_replace('/\s+/u', ' ', $String));
	return mb_substr($String, 0, $MaxLength, 'UTF-8');
}

// Texte libre (message, texte d'alliance, note...) : echappe pour l'affichage HTML, rien n'est perdu
function SafeText ( $String ) {
	return htmlspecialchars(trim((string) $String), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Adresse web : uniquement http(s), sans espace ni guillemet (interdit javascript:, data:...). Sinon chaine vide.
function SafeUrl ( $Url ) {
	$Url = trim((string) $Url);
	if ($Url == '' || !preg_match('#^https?://[A-Za-z0-9.\-]+(:[0-9]+)?(/[A-Za-z0-9._~:/?\#\[\]@!$&()*+,;=%\-]*)?$#', $Url)) {
		return '';
	}
	return $Url;
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
		'annonce2.php'           => array('action'),           // supprimer une annonce
		'buddy.php'              => array('bid'),              // accepter / supprimer un ami
		'alliance.php'           => array('kick', 'd', 'yes'), // exclure un membre, supprimer un rang, quitter
		'quickfleet.php'         => array('mode'),             // envoi rapide de recycleurs
		'phalanx.php'            => array('galaxy'),           // scan de phalange (coute du deuterium)
		'admin/userlist.php'     => array('cmd'),              // supprimer un joueur
		'admin/chat.php'         => array('delete', 'deleteall'),
		'admin/errors.php'       => array('delete', 'deleteall'),
		'admin/paneladmina.php'  => array('authlvl'),          // changer le niveau d'un compte
		'admin/contactlist.php'  => array('read', 'unread', 'delete'), // messages de contact
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
	// Liens internes vers une page .php avec parametres (ou "?..."), hors adresses externes
	$Html  = preg_replace_callback('/(href\s*=\s*)(["\']?)((?!https?:|\/\/|javascript:|mailto:|#)[A-Za-z0-9_\-.\/]*\.php\?[^"\'\s>]*|\?[^"\'\s>]*)\2/i', function ($m) use ($Token) {
		if (strpos($m[3], 'csrf_token=') !== false) {
			return $m[0];
		}
		return $m[1] . $m[2] . $m[3] . '&amp;csrf_token=' . $Token . $m[2];
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
// Routine Test de validité d'une adresse email
//
function is_email($email) {
	return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i", $email));
}

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
	// Affichage du Debug si necessaire
	if (is_array($user) && isset($user['authlevel']) && ($user['authlevel'] == 1 || $user['authlevel'] == 3)) {
		if (!empty($game_config['debug'])) $debug->echo_log();
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
	global $user, $dpath, $langInfos;

	$parse             = $langInfos;
	$parse['title']    = $title;
	if ( defined('LOGIN') ) {
		$parse['dpath']    = "skins/xnova/";
		$parse['-style-']  = "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/styles.css\">\n";
		$parse['-style-'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/about.css\">\n";
	} else {
		$parse['dpath']    = $dpath;
		$parse['-style-']  = "<link rel=\"stylesheet\" type=\"text/css\" href=\"". $dpath ."default.css\" />";
		$parse['-style-'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"". $dpath ."formate.css\" />";
	}

	$parse['-meta-']  = ($metatags) ? $metatags : "";
	$parse['-body-']  = "<body>"; //  class=\"style\" topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">";
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
	$parse['-meta-'] = ($metatags) ? $metatags : "";
	$parse['-body-'] = "<body>"; //  class=\"style\" topmargin=\"0\" leftmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">";
	return parsetemplate(gettemplate('admin/simple_header'), $parse);
}

// ----------------------------------------------------------------------------------------------------------------
//
// Pied de page
//
function StdFooter() {
	global $game_config, $lang;
	$parse['copyright']     = $game_config['copyright'] ?? '';
	$parse['TranslationBy'] = $lang['TranslationBy'] ?? '';
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
