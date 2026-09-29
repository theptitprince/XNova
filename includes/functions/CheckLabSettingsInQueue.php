<?php

/**
 * CheckLabSettingsInQueue.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Teste si la queue de construction eventuelle a le labo en premiere position ....
function CheckLabSettingsInQueue ( $CurrentPlanet ) {
	global $lang, $game_config;

	if ($CurrentPlanet['b_building_id'] != "0") {
		$BuildQueue = $CurrentPlanet['b_building_id'];
		if (strpos ($BuildQueue, ";")) {
			$Queue = explode (";", $BuildQueue);
			$CurrentBuilding = $Queue[0];
		} else {
			// Y a pas de queue de construction la liste n'a qu'un seul element
			$CurrentBuilding = $BuildQueue;
		}

		// Numero de l'element seul (0.9k) : sous PHP 8, « 31,4,600,...,build » == 31 est faux, le laboratoire en
		// travaux ne bloquait plus aucune recherche
		if (intval(explode(",", $CurrentBuilding)[0]) == 31 && $game_config['BuildLabWhileRun'] != 1) {
			$return = false;
		} else {
			$return = true;
		}

	} else {
		$return = true;
	}

	return $return;
}

?>