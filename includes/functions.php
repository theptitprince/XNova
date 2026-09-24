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
	$parse['color'] = $color;
	$parse['title'] = $title;
	$parse['mes']   = $mes;

	$page .= parsetemplate(gettemplate('admin/message_body'), $parse);

	display ($page, $title, false, (($dest != "") ? "<meta http-equiv=\"refresh\" content=\"$time;URL=javascript:self.location='$dest';\">" : ""), true);
}

// ----------------------------------------------------------------------------------------------------------------
//
// Routine Affichage d'un message avec saut vers une autre page si souhaité
//
function message ($mes, $title = 'Error', $dest = "", $time = "3") {
	$parse['color'] = $color;
	$parse['title'] = $title;
	$parse['mes']   = $mes;

	$page .= parsetemplate(gettemplate('message_body'), $parse);

	display ($page, $title, false, (($dest != "") ? "<meta http-equiv=\"refresh\" content=\"$time;URL=javascript:self.location='$dest';\">" : ""), false);
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
	if (is_array($user) && ($user['authlevel'] == 1 || $user['authlevel'] == 3)) {
		if ($game_config['debug'] == 1) $debug->echo_log();
	}

	$DisplayPage .= StdFooter();
	if ($link instanceof mysqli) {
		mysqli_close($link);
	}

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
	$parse['copyright']     = $game_config['copyright'];
	$parse['TranslationBy'] = $lang['TranslationBy'];
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
