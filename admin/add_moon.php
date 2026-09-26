<?php

/**
 * add_moon.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	if ($user['authlevel'] >= 2) {
		includeLang('admin/addmoon');

		$mode      = ($_POST['mode'] ?? null);

		$PageTpl   = gettemplate("admin/add_moon");
		$parse     = $lang;

		if ($mode == 'addit') {
			$PlanetID  = intval(($_POST['user'] ?? null));
			$MoonName  = SqlEscape(SafeName(($_POST['name'] ?? null), 32));

			$QrySelectPlanet  = "SELECT * FROM {{table}} ";
			$QrySelectPlanet .= "WHERE ";
			$QrySelectPlanet .= "`id` = '". $PlanetID ."';";
			$PlanetSelected = doquery ( $QrySelectPlanet, 'planets', true);
			// XNova Renaissance : planete inconnue ou lune donnee a la place de la planete mere (etait « Ajout OK » sans rien creer)
			if (!$PlanetSelected || $PlanetSelected['planet_type'] != 1) {
				AdminMessage ( $lang['addm_noplanet'], $lang['addm_title'] );
			}

			$Galaxy    = $PlanetSelected['galaxy'];
			$System    = $PlanetSelected['system'];
			$Planet    = $PlanetSelected['planet'];
            $Owner     = $PlanetSelected['id_owner'];
			$MoonID    = time();

			// Nom de la planete mere si la lune est creee, vide si la planete en a deja une
			if (CreateOneMoonRecord ( $Galaxy, $System, $Planet, $Owner, $MoonID, $MoonName, 20 ) == '') {
				AdminMessage ( $lang['addm_hasmoon'], $lang['addm_title'] );
			}

			AdminMessage ( $lang['addm_done'], $lang['addm_title'] );
		}
		$Page = parsetemplate($PageTpl, $parse);

		display ($Page, $lang['addm_title'], false, '', true);
	} else {
		AdminMessage ( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}
?>