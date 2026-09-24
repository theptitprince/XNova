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

define('VERSION'     ,'0.9d');        // Version d'XNova utilisée...
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

	if ($InLogin != true) {
		$Result        = CheckTheUser ( $IsUserChecked );
		$IsUserChecked = $Result['state'];
		$user          = $Result['record'];
	} elseif ($InLogin == false) {
		// Jeux en mode 'clos' ???
		if( $game_config['game_disable']) {
			if ($user['authlevel'] < 1) {
				message ( stripslashes ( $game_config['close_reason'] ), $game_config['game_name'] );
			}
		}
	}

	includeLang ("system");
	includeLang ('tech');

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
			$UserSkin  = $user['dpath'];
			$local     = stristr ( $UserSkin, "http:");
			if ($local === false) {
				if (!$user['dpath']) {
					$dpath     = "../". DEFAULT_SKINPATH  ;
				} else {
					$dpath     = "../". $user["dpath"];
				}
			} else {
				$dpath     = $UserSkin;
			}
		} else {
			$dpath     = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
		}

		SetSelectedPlanet ( $user );

		$planetrow = doquery("SELECT * FROM {{table}} WHERE `id` = '".$user['current_planet']."';", 'planets', true);
		$galaxyrow = doquery("SELECT * FROM {{table}} WHERE `id_planet` = '".$planetrow['id']."';", 'galaxy', true);

		CheckPlanetUsedFields($planetrow);
	} else {
		// Bah si d�ja y a quelqu'un qui passe par l� et qu'a rien a faire de press� ...
		// On se sert de lui pour mettre a jour tout les retardataires !!

	}
} else {
	$dpath     = "../" . DEFAULT_SKINPATH;
}

?>
