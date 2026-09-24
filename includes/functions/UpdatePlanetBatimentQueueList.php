<?php

/**
 * UpdatePlanetBatimentQueueList.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function UpdatePlanetBatimentQueueList ( &$CurrentPlanet, &$CurrentUser ) {
	$RetValue = false;
	if ( !empty($CurrentPlanet['b_building_id']) ) {
		while ( !empty($CurrentPlanet['b_building_id']) ) {
			if ( $CurrentPlanet['b_building'] <= time() ) {
				PlanetResourceUpdate ( $CurrentUser, $CurrentPlanet, $CurrentPlanet['b_building'], false );
				$IsDone = CheckPlanetBuildingQueue( $CurrentPlanet, $CurrentUser );
				if ( $IsDone == true ) {
					SetNextQueueElementOnTop ( $CurrentPlanet, $CurrentUser );
				}
			} else {
				$RetValue = true;
				break;
			}
		}
	}
	return $RetValue;
}

// Revision History
// - 1.0 Mise en module initiale
// - 1.1 Mise a jour des ressources sur la planete verifiée (pour prendre en compte les ressources produites
//       pendant la construction et avant l'evolution evantuel d'une mine ou d'en batiment

?>