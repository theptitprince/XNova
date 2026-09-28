<?php

/**
 * PlanetDestruction.php
 *
 * XNova Renaissance
 * Écrit par theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Destruction d'une colonie par le Destructeur planetaire (0.9j, unite de l'officier Empereur), sur le modele de la
// destruction de lune par l'etoile de la mort : mission « Detruire » vers une planete, combat d'abord ; si l'attaquant
// gagne, chance de destruction = racine(nombre de Destructeurs planetaires) x max(10, 150 - racine(diametre)) / 10 % ; si la
// planete resiste, les Destructeurs risquent d'etre detruits (racine(diametre) / 2 %, comme pour la lune). Jamais la
// planete mere d'un joueur. La planete detruite devient une « planete detruite » sans proprietaire pendant 24 heures
// (comme une colonie abandonnee), sa lune disparait, les flottes qui s'y rendaient font demi-tour et celles qui en
// venaient rentrent sur la planete mere de leur proprietaire.

// Planete mere de son proprietaire ?
function PlanetIsHome ( $PlanetId, $OwnerId ) {
	$Owner = doquery("SELECT `id_planet` FROM {{table}} WHERE `id` = '". intval($OwnerId) ."' LIMIT 1;", 'users', true);
	return ($Owner && intval($Owner['id_planet']) == intval($PlanetId));
}

// Chance de destruction (en %, arrondie, entre 0 et 100). Plancher de 10 pour le second facteur : sans lui, une planete
// de plus de 22 500 km (racine au-dela de 150) aurait ete indestructible ; rien ne change jusqu'a 19 600 km
function PlanetDestructionChance ( $Count, $Diameter ) {
	$Chance = sqrt(max(0, $Count)) * max(10, 150 - sqrt(max(0, $Diameter))) / 10;
	return (int) max(0, min(100, round($Chance)));
}

// Destruction de la planete $TargetPlanet (ligne complete) et de sa lune
function PlanetDestroy ( $TargetPlanet ) {
	$Galaxy = intval($TargetPlanet['galaxy']);
	$System = intval($TargetPlanet['system']);
	$Planet = intval($TargetPlanet['planet']);
	$Owner  = doquery("SELECT `id`, `id_planet`, `current_planet` FROM {{table}} WHERE `id` = '". intval($TargetPlanet['id_owner']) ."' LIMIT 1;", 'users', true);
	$Home   = $Owner ? doquery("SELECT `id`, `galaxy`, `system`, `planet` FROM {{table}} WHERE `id` = '". intval($Owner['id_planet']) ."' LIMIT 1;", 'planets', true) : false;
	$Moon   = doquery("SELECT `id` FROM {{table}} WHERE `galaxy` = '". $Galaxy ."' AND `system` = '". $System ."' AND `planet` = '". $Planet ."' AND `planet_type` = '3' LIMIT 1;", 'planets', true);

	// Planete detruite : sans proprietaire pendant 24 heures, puis effacee (comme une colonie abandonnee)
	doquery("UPDATE {{table}} SET `id_owner` = '0', `destruyed` = '". (time() + 86400) ."' WHERE `id` = '". intval($TargetPlanet['id']) ."' LIMIT 1;", 'planets');
	// Sa lune disparait
	if ($Moon) {
		doquery("DELETE FROM {{table}} WHERE `id` = '". intval($Moon['id']) ."' LIMIT 1;", 'planets');
	}
	doquery("DELETE FROM {{table}} WHERE `galaxy` = '". $Galaxy ."' AND `system` = '". $System ."' AND `lunapos` = '". $Planet ."';", 'lunas');
	doquery("UPDATE {{table}} SET `id_luna` = '0' WHERE `galaxy` = '". $Galaxy ."' AND `system` = '". $System ."' AND `planet` = '". $Planet ."' LIMIT 1;", 'galaxy');

	// Flottes qui s'y rendaient (planete ou lune) : demi-tour ; flottes qui en venaient : retour sur la planete mere
	doquery("UPDATE {{table}} SET `fleet_mess` = '1' WHERE `fleet_end_galaxy` = '". $Galaxy ."' AND `fleet_end_system` = '". $System ."' AND `fleet_end_planet` = '". $Planet ."' AND `fleet_end_type` IN (1, 3);", 'fleets');
	if ($Home) {
		doquery("UPDATE {{table}} SET `fleet_start_galaxy` = '". intval($Home['galaxy']) ."', `fleet_start_system` = '". intval($Home['system']) ."', `fleet_start_planet` = '". intval($Home['planet']) ."', `fleet_start_type` = '1' " .
		        "WHERE `fleet_start_galaxy` = '". $Galaxy ."' AND `fleet_start_system` = '". $System ."' AND `fleet_start_planet` = '". $Planet ."' AND `fleet_start_type` IN (1, 3);", 'fleets');
		// Vue du joueur calee sur la planete detruite ou sa lune : retour sur la planete mere
		if (intval($Owner['current_planet']) == intval($TargetPlanet['id']) || ($Moon && intval($Owner['current_planet']) == intval($Moon['id']))) {
			doquery("UPDATE {{table}} SET `current_planet` = '". intval($Home['id']) ."' WHERE `id` = '". intval($Owner['id']) ."' LIMIT 1;", 'users');
		}
	}
}

?>
