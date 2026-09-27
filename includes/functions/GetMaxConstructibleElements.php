<?php

/**
 * GetMaxConstructibleElements.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.2
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */
// Retourne un entier du nombre maximum d'elements constructible
// par rapport aux ressources disponibles
// $Element    -> L'element visé
// $Ressources -> Un table contenant metal, crystal, deuterium, energy de la planete
//                sur laquelle on veut construire l'Element
function GetMaxConstructibleElements ($Element, $Ressources) {
	global $pricelist;
	// On test les 4 Type de ressource pour voir si au moins on sait en construire 1
	if ($pricelist[$Element]['metal'] != 0) {
		$ResType_1_Needed = $pricelist[$Element]['metal'];
		$Buildable        = floor($Ressources["metal"] / $ResType_1_Needed);
		$MaxElements      = $Buildable;
	}

	if ($pricelist[$Element]['crystal'] != 0) {
		$ResType_2_Needed = $pricelist[$Element]['crystal'];
		$Buildable        = floor($Ressources["crystal"] / $ResType_2_Needed);
	}
	if (!isset($MaxElements)) {
		$MaxElements      = $Buildable;
	} elseif ($MaxElements > $Buildable) {
		$MaxElements      = $Buildable;
	}

	if ($pricelist[$Element]['deuterium'] != 0) {
		$ResType_3_Needed = $pricelist[$Element]['deuterium'];
		$Buildable        = floor($Ressources["deuterium"] / $ResType_3_Needed);
	}
	if (!isset($MaxElements)) {
		$MaxElements      = $Buildable;
	} elseif ($MaxElements > $Buildable) {
		$MaxElements      = $Buildable;
	}

	if ($pricelist[$Element]['energy'] != 0) {
		$ResType_4_Needed = $pricelist[$Element]['energy'];
		$Buildable        = floor($Ressources["energy_max"] / $ResType_4_Needed);
	}
	if ($Buildable < 1) {
		$MaxElements      = 0;
	}

	return $MaxElements;
}

// XNova Renaissance : lien « max. N » sous la quantite (chantier spatial, defense) ; le maximum est calcule par la page
// avec les memes regles que la commande (ressources, MAX_FLEET_OR_DEFS_PER_ROW, boucliers, silo)
function ElementMaxLink ( $Element, $Max ) {
	global $lang;
	if ($Max < 1) {
		return '';
	}
	return "<br><a href=\"#\" style=\"white-space:nowrap\" onclick=\"document.getElementsByName('fmenge[". intval($Element) ."]')[0].value = '". intval($Max) ."'; return false;\">"
	     . sprintf($lang['bd_max'], pretty_number($Max)) ."</a>";
}
// Verion History
// - 1.0 Version initiale (creation)
// - 1.1 Correction bug ressources négatives ...
// - 1.2 Correction bug quand pas de métal
?>