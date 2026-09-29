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
	global $resource, $reslist;
	// Pendant qu'on y est, si on verifiait ce qui se passe dans la queue de construction du chantier ?
	if (!empty($CurrentPlanet['b_hangar_id'])) {
		$Builded                    = array ();
		$BuildArray                 = array ();
		$CurrentPlanet['b_hangar'] += $ProductionTime;

		$BuildQueue                 = explode(';', $CurrentPlanet['b_hangar_id']);

		foreach ($BuildQueue as $Node => $Array) {
			if ($Array != '') {
				$Item              = explode(',', $Array);
				// Vaisseaux et defenses seulement : une file piegee par un identifiant de batiment (commande forgee
				// avant la 0.9k) n'augmente plus le niveau du batiment
				if (count($Item) < 2 || (!in_array(intval($Item[0]), $reslist['fleet']) && !in_array(intval($Item[0]), $reslist['defense']))) {
					continue;
				}
				// On stocke sous forme Element, Nombre, Duree de fab
				$BuildArray[$Node] = array($Item[0], $Item[1], GetBuildingTime ($CurrentUser, $CurrentPlanet, $Item[0]));
			}
		}

		$CurrentPlanet['b_hangar_id'] = '';

		// File traitee dans l'ordre : on ne passe a l'element suivant que lorsque le precedent est termine.
		// L'original ne s'arretait jamais : un element rapide place derriere un long (missiles derriere une
		// etoile de la mort) etait construit tout de suite avec le temps accumule pour le premier, qui reculait.
		$UnFinished = false;
		// Officier Destructeur : 2 etoiles de la mort construites pour une commandee (sans effet dans l'original)
		$Destroyer  = (intval($CurrentUser['rpg_destructeur'] ?? 0) >= 1);
		foreach ( $BuildArray as $Node => $Item ) {
			$Element   = $Item[0];
			$Count     = $Item[1];
			$BuildTime = $Item[2];
			if (!$UnFinished) {
				while ( $Count > 0 && $CurrentPlanet['b_hangar'] >= $BuildTime ) {
					$CurrentPlanet['b_hangar'] -= $BuildTime;
					$Made = ($Element == 214 && $Destroyer) ? 2 : 1;
					$Builded[$Element] = ($Builded[$Element] ?? 0) + $Made;
					$CurrentPlanet[$resource[$Element]] += $Made;
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

// Commande du chantier spatial ou de la defense (0.9k) : ressources debitees et file allongee en une seule requete,
// a condition que la file n'ait pas change depuis la lecture de la planete et que les ressources suffisent encore.
// Avant, la commande etait seulement retranchee en memoire puis ecrite en valeurs absolues a la fin de la page : deux
// requetes simultanees etaient servies pour le prix d'une (et deux boucliers, ou un silo deborde, passaient).
// Retourne vrai si la commande est enregistree ($CurrentPlanet est alors mis a jour comme avant).
function ShipyardQueueAdd ( &$CurrentPlanet, $Element, $Count, $Ressource ) {
	$Item             = intval($Element) .",". intval($Count) .";";
	$QryUpdatePlanet  = "UPDATE {{table}} SET ";
	$QryUpdatePlanet .= "`metal` = `metal` - '".         floatval($Ressource['metal'])     ."', ";
	$QryUpdatePlanet .= "`crystal` = `crystal` - '".     floatval($Ressource['crystal'])   ."', ";
	$QryUpdatePlanet .= "`deuterium` = `deuterium` - '". floatval($Ressource['deuterium']) ."', ";
	$QryUpdatePlanet .= "`b_hangar_id` = CONCAT(`b_hangar_id`, '". $Item ."') ";
	$QryUpdatePlanet .= "WHERE ";
	$QryUpdatePlanet .= "`id` = '".                      intval($CurrentPlanet['id'])      ."' AND ";
	$QryUpdatePlanet .= "`b_hangar_id` = '".             SqlEscape($CurrentPlanet['b_hangar_id']) ."' AND ";
	$QryUpdatePlanet .= "`metal` >= '".                  floatval($Ressource['metal'])     ."' AND ";
	$QryUpdatePlanet .= "`crystal` >= '".                floatval($Ressource['crystal'])   ."' AND ";
	$QryUpdatePlanet .= "`deuterium` >= '".              floatval($Ressource['deuterium']) ."';";
	doquery($QryUpdatePlanet, 'planets');
	if (mysqli_affected_rows(DbConnect()) != 1) {
		return false;
	}
	$CurrentPlanet['metal']       -= $Ressource['metal'];
	$CurrentPlanet['crystal']     -= $Ressource['crystal'];
	$CurrentPlanet['deuterium']   -= $Ressource['deuterium'];
	$CurrentPlanet['b_hangar_id'] .= $Item;
	return true;
}
?>
