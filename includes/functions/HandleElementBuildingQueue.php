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
				// (numeros entiers : « 0204 » passait la liste sans exister dans les tables, tout etait construit
				// d'un coup et l'enregistrement de la planete echouait)
				$BuildArray[$Node] = array(intval($Item[0]), intval($Item[1]), GetBuildingTime ($CurrentUser, $CurrentPlanet, intval($Item[0])));
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
				// Unites terminees comptees d'un coup (0.9k) : l'original retirait le temps d'une unite a la fois
				// (jusqu'a 1 000 000 de tours). Memes nombres : temps entiers, soustraction exacte ; un temps nul ou
				// negatif termine toute la ligne, comme la boucle
				if ( $Count > 0 && $CurrentPlanet['b_hangar'] >= $BuildTime ) {
					$Done = ($BuildTime <= 0) ? $Count : min($Count, intdiv((int) $CurrentPlanet['b_hangar'], (int) $BuildTime));
					$CurrentPlanet['b_hangar'] -= $Done * $BuildTime;
					$Made = ($Element == 214 && $Destroyer) ? 2 : 1;
					$Builded[$Element] = ($Builded[$Element] ?? 0) + $Made * $Done;
					$CurrentPlanet[$resource[$Element]] += $Made * $Done;
					$Count -= $Done;
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
		// File vide : « 0 » (ecrit par admin/ElementQueueFixer.php) remis a vide, il etait affiche comme un element
		$CurrentPlanet['b_hangar_id'] = '';
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
	// File vide, ou « 0 » (admin/ElementQueueFixer.php) : la commande la remplace au lieu de s'ajouter derriere le « 0 »
	$Empty            = in_array(strval($CurrentPlanet['b_hangar_id'] ?? ''), array('', '0'), true);
	$QryUpdatePlanet  = "UPDATE {{table}} SET ";
	$QryUpdatePlanet .= "`metal` = `metal` - '".         floatval($Ressource['metal'])     ."', ";
	$QryUpdatePlanet .= "`crystal` = `crystal` - '".     floatval($Ressource['crystal'])   ."', ";
	$QryUpdatePlanet .= "`deuterium` = `deuterium` - '". floatval($Ressource['deuterium']) ."', ";
	if ($Empty) {
		$QryUpdatePlanet .= "`b_hangar_id` = '". $Item ."' ";
	} else {
		$QryUpdatePlanet .= "`b_hangar_id` = CONCAT(`b_hangar_id`, '". $Item ."') ";
	}
	$QryUpdatePlanet .= "WHERE ";
	$QryUpdatePlanet .= "`id` = '".                      intval($CurrentPlanet['id'])      ."' AND ";
	if ($Empty) {
		$QryUpdatePlanet .= "`b_hangar_id` IN ('', '0') AND ";
	} else {
		$QryUpdatePlanet .= "`b_hangar_id` = '".         SqlEscape($CurrentPlanet['b_hangar_id']) ."' AND ";
	}
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
	$CurrentPlanet['b_hangar_id']  = ($Empty) ? $Item : $CurrentPlanet['b_hangar_id'] . $Item;
	return true;
}

// Relit dans la base les ressources et la file du chantier de la planete (0.9k), apres une commande refusee parce
// qu'une autre requete est passee avant : la barre du haut reecrivait sinon l'ancien stock et l'ancienne file (valeurs
// absolues), ce qui annulait le debit et la commande de l'autre requete
function ShipyardQueueReload ( &$CurrentPlanet ) {
	// Compteurs des flottes (*_fleets) relus avec les ressources (voir PlanetResourceUpdate)
	$Fields = array('metal', 'crystal', 'deuterium', 'b_hangar_id', 'b_hangar');
	foreach (array('metal_fleets', 'crystal_fleets', 'deuterium_fleets') as $Field) {
		if (isset($CurrentPlanet[$Field])) {
			$Fields[] = $Field;
		}
	}
	$Fresh = doquery("SELECT `". implode("`, `", $Fields) ."` FROM {{table}} WHERE `id` = '". intval($CurrentPlanet['id']) ."';", 'planets', true);
	if (!$Fresh) {
		return false;
	}
	foreach ($Fields as $Field) {
		$CurrentPlanet[$Field] = $Fresh[$Field];
	}
	return true;
}
?>
