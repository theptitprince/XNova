<?php

/**
 * HandleElementBuildingQueue.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function HandleElementBuildingQueue ( $CurrentUser, &$CurrentPlanet, $ProductionTime ) {
	global $resource;
	// Pendant qu'on y est, si on verifiait ce qui se passe dans la queue de construction du chantier ?
	if (!empty($CurrentPlanet['b_hangar_id'])) {
		$Builded                    = array ();
		$CurrentPlanet['b_hangar'] += $ProductionTime;

		$BuildQueue                 = explode(';', $CurrentPlanet['b_hangar_id']);

		foreach ($BuildQueue as $Node => $Array) {
			if ($Array != '') {
				$Item              = explode(',', $Array);
				// On stocke sous forme Element, Nombre, Duree de fab
				$BuildArray[$Node] = array($Item[0], $Item[1], GetBuildingTime ($CurrentUser, $CurrentPlanet, $Item[0]));
			}
		}

		$CurrentPlanet['b_hangar_id'] = '';

		// File traitee dans l'ordre : on ne passe a l'element suivant que lorsque le precedent est termine.
		// L'original ne s'arretait jamais : un element rapide place derriere un long (missiles derriere une
		// etoile de la mort) etait construit tout de suite avec le temps accumule pour le premier, qui reculait.
		$UnFinished = false;
		foreach ( $BuildArray as $Node => $Item ) {
			$Element   = $Item[0];
			$Count     = $Item[1];
			$BuildTime = $Item[2];
			if (!$UnFinished) {
				while ( $Count > 0 && $CurrentPlanet['b_hangar'] >= $BuildTime ) {
					$CurrentPlanet['b_hangar'] -= $BuildTime;
					$Builded[$Element] = ($Builded[$Element] ?? 0) + 1;
					$CurrentPlanet[$resource[$Element]]++;
					$Count--;
				}
				if ( $Count > 0 ) {
					$UnFinished = true;
				}
			}
			if ( $Count > 0 ) {
				$CurrentPlanet['b_hangar_id'] .= $Element.",".$Count.";";
			}
		}
	} else {
		$Builded                   = '';
		$CurrentPlanet['b_hangar'] = 0;
	}

	return $Builded;
}
?>