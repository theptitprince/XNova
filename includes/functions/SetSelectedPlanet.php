<?php

/**
 * SetSelectedPlanet.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function SetSelectedPlanet ( &$CurrentUser ) {
	global $_GET;

	$SelectPlanet  = isset($_GET['cp']) ? intval($_GET['cp']) : null;
	$RestorePlanet = isset($_GET['re']) ? intval($_GET['re']) : null;

	if (isset($SelectPlanet)      &&
		is_numeric($SelectPlanet) &&
		isset($RestorePlanet)     &&
		$RestorePlanet == 0) {
		$IsPlanetMine   = doquery("SELECT `id` FROM {{table}} WHERE `id` = '". $SelectPlanet ."' AND `id_owner` = '". $CurrentUser['id'] ."';", 'planets', true);
		if ($IsPlanetMine) {
			// Ouaip elle est a moi ... Donc ... on met la met comme planete courrante
			$CurrentUser['current_planet'] = $SelectPlanet;
			// Puis tant qu'a faire ... On l'enregistre aussi sait on jamais
			doquery("UPDATE {{table}} SET `current_planet` = '". $SelectPlanet ."' WHERE `id` = '".$CurrentUser['id']."';", 'users');
		}
	}
}

?>
