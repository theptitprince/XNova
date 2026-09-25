<?php

/**
 * common.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('VERSION'     ,'0.9f');        // Version d'XNova utilisée...
define('VERSION_NAME','Renaissance'); // Nom de la version (0.9 et suivantes)

$phpEx = "php";

$game_config   = array();
$user          = array();
$lang          = array();
$link          = "";            // Lien pour liaison MySQL :)
$IsUserChecked = false;

define('DEFAULT_SKINPATH' , 'skins/xnova/');
define('TEMPLATE_DIR'     , 'templates/');
define('TEMPLATE_NAME'    , 'OpenGame');
define('DEFAULT_LANG'     , 'fr');

$HTTP_ACCEPT_LANGUAGE = DEFAULT_LANG;

include($xnova_root_path . 'includes/debug.class.'.$phpEx);
$debug = new debug();

include($xnova_root_path . 'includes/constants.'.$phpEx);
include($xnova_root_path . 'includes/functions.'.$phpEx);
include($xnova_root_path . 'includes/unlocalised.'.$phpEx);
include($xnova_root_path . 'includes/todofleetcontrol.'.$phpEx);
include($xnova_root_path . 'language/'. DEFAULT_LANG .'/lang_info.cfg');

// Jeu pas encore installe (config.php vide) : quelle que soit la page demandee, direction l'installeur
if (INSTALL != true && (!file_exists($xnova_root_path . 'config.php') || filesize($xnova_root_path . 'config.php') == 0)) {
	header('Location: ' . $xnova_root_path . 'install/');
	exit();
}

if (INSTALL != true) {
    include($xnova_root_path . 'includes/vars.'.$phpEx);
    include($xnova_root_path . 'includes/db.'.$phpEx);
    include($xnova_root_path . 'includes/strings.'.$phpEx);

    // Lecture de la table de configuration
    $query = doquery("SELECT * FROM {{table}}",'config');
    while ( $row = mysqli_fetch_assoc($query) ) {
	    $game_config[$row['config_name']] = $row['config_value'];
    }

	if (empty($InLogin)) {
		$Result        = CheckTheUser ( $IsUserChecked );
		$IsUserChecked = $Result['state'];
		$user          = $Result['record'];
	}

	includeLang ("system");
	includeLang ('tech');

	// Jeu en mode 'clos' (reglage de l'admin) : seuls les administrateurs peuvent jouer.
	// Dans l'original, ce test etait dans une branche jamais atteinte : le jeu restait toujours ouvert.
	if (!empty($game_config['game_disable']) && empty($InLogin) && is_array($user) && !empty($user['id']) &&
	    $user['authlevel'] < 1 && basename($_SERVER['SCRIPT_NAME']) != 'logout.php') {
		message ( stripslashes ( $game_config['close_reason'] ), $game_config['game_name'] );
	}

	// Visiteur non connecte : seules les pages publiques s'affichent, les autres renvoient vers la connexion.
	// (Dans l'original, n'importe qui pouvait ouvrir frames.php, overview.php, l'admin... avec un joueur vide.)
	$PublicPages = array('index.php', 'login.php', 'reg.php', 'lostpassword.php', 'contact.php', 'credit.php',
	                     'rules.php', 'changelog.php', 'banned.php', 'logout.php');
	if (empty($user['id']) && !defined('LOGIN') &&
	    (defined('IN_ADMIN') || !in_array(basename($_SERVER['SCRIPT_NAME']), $PublicPages))) {
		$LoginUrl = $xnova_root_path . 'login.php';
		// top.location : on sort des frames (sinon la page de connexion s'afficherait dans le cadre du jeu)
		echo "<html><head><meta http-equiv=\"refresh\" content=\"0;URL=". $LoginUrl ."\">".
		     "<script type=\"text/javascript\">top.location.href = '". $LoginUrl ."';</script></head><body></body></html>";
		exit();
	}

	// Protection CSRF : tout formulaire envoye par un joueur connecte, et toute action declenchee par un lien,
	// doivent porter le jeton de ce joueur (un site exterieur ne peut pas le connaitre)
	if (is_array($user) && !empty($user['id']) && !defined('LOGIN')) {
		if (($_SERVER['REQUEST_METHOD'] == 'POST' || CsrfGetAction()) && !CsrfValid()) {
			message($lang['sys_csrf_error'], $lang['sys_noaccess']);
		}
	}

	if ( isset ($user) ) {
		$_fleets = doquery("SELECT * FROM {{table}} WHERE `fleet_start_time` <= '".time()."';", 'fleets'); //  OR fleet_end_time <= ".time()
		while ($row = mysqli_fetch_array($_fleets)) {
			$array                = array();
			$array['galaxy']      = $row['fleet_start_galaxy'];
			$array['system']      = $row['fleet_start_system'];
			$array['planet']      = $row['fleet_start_planet'];
			$array['planet_type'] = $row['fleet_start_type'];

			$temp = FlyingFleetHandler ($array);
		}

		$_fleets = doquery("SELECT * FROM {{table}} WHERE `fleet_end_time` <= '".time()."';", 'fleets'); //  OR fleet_end_time <= ".time()
		while ($row = mysqli_fetch_array($_fleets)) {
			$array                = array();
			$array['galaxy']      = $row['fleet_end_galaxy'];
			$array['system']      = $row['fleet_end_system'];
			$array['planet']      = $row['fleet_end_planet'];
			$array['planet_type'] = $row['fleet_end_type'];

			$temp = FlyingFleetHandler ($array);
		}

		unset($_fleets);

		include($xnova_root_path . 'rak.'.$phpEx);
		if ( defined('IN_ADMIN') ) {
			$UserSkin  = $user['dpath'] ?? '';
			$local     = stristr ( $UserSkin, "http:");
			if ($local === false) {
				if (!$UserSkin) {
					$dpath     = "../". DEFAULT_SKINPATH  ;
				} else {
					$dpath     = "../". $UserSkin;
				}
			} else {
				$dpath     = $UserSkin;
			}
		} else {
			$dpath     = empty($user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
		}

		// Planete courante : seulement pour un joueur connecte ($user vaut un tableau vide pour un visiteur)
		if (!empty($user['id'])) {
			SetSelectedPlanet ( $user );

			$planetrow = doquery("SELECT * FROM {{table}} WHERE `id` = '".$user['current_planet']."';", 'planets', true);
			$galaxyrow = doquery("SELECT * FROM {{table}} WHERE `id_planet` = '".$planetrow['id']."';", 'galaxy', true);

			CheckPlanetUsedFields($planetrow);
		} else {
			$planetrow = null;
			$galaxyrow = null;
		}
	} else {
		// Bah si déjà y a quelqu'un qui passe par là et qu'a rien a faire de pressé ...
		// On se sert de lui pour mettre a jour tout les retardataires !!

	}
} else {
	$dpath     = "../" . DEFAULT_SKINPATH;
}

?>
