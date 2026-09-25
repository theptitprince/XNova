<?php

/**
 * add_fleet.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Tom1991 for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Ajout de vaisseaux sur une planete (outil d'administration).
// L'original n'a jamais fonctionne : modele absent (page vide) et requete invalide
// (`light_hunter` = '5+light_hunter' entre apostrophes, virgule en trop avant WHERE).

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

	if ($user['authlevel'] >= 1) {
		includeLang('admin/add_fleet');

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$PlanetID = max(0, intval($_POST['id'] ?? 0));
			$Planet   = doquery("SELECT `id` FROM {{table}} WHERE `id` = '". $PlanetID ."' LIMIT 1;", 'planets', true);
			if (!$Planet) {
				AdminMessage ( $lang['adm_af_noplanet'], $lang['adm_af_title'], 'add_fleet.php', 3 );
			}
			$Set = array();
			foreach ($reslist['fleet'] as $ShipID) {
				$Count = max(0, intval($_POST['ship'. $ShipID] ?? 0));
				if ($Count > 0) {
					$Set[] = "`". $resource[$ShipID] ."` = `". $resource[$ShipID] ."` + ". $Count;
				}
			}
			if ($Set) {
				doquery("UPDATE {{table}} SET ". implode(', ', $Set) ." WHERE `id` = '". $PlanetID ."' LIMIT 1;", 'planets');
			}
			AdminMessage ( $lang['adm_af_done'], $lang['adm_af_title'], 'add_fleet.php', 3 );
		}

		$parse          = $lang;
		$parse['rows']  = '';
		foreach ($reslist['fleet'] as $ShipID) {
			$parse['rows'] .= "<tr><th>". $lang['tech'][$ShipID] ."</th><th><input name=\"ship". $ShipID ."\" type=\"text\" value=\"0\" size=\"8\" /></th></tr>";
		}
		$page = parsetemplate(gettemplate('admin/add_fleet'), $parse);
		display( $page, $lang['adm_af_title'], false, '', true );

	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>
