<?php

/**
 * AllyDeposit.php
 *
 * XNova Renaissance
 * Écrit par theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Depot de ravitaillement (0.9j), comme dans OGame : le proprietaire d'une planete dotee d'un depot livre du
// deuterium aux flottes alliees qui y stationnent (mission « Stationner chez un allie », etat 2) pour prolonger leur
// sejour. Chaque vaisseau coute au stationnement sa consommation / 10 par heure ; une livraison porte au plus 10 000 de
// deuterium par niveau du depot (valeur de la fiche du batiment) ; le stationnement restant ne depasse jamais 32 heures
// (la plus longue duree proposee a l'envoi, qui reste gratuite comme dans l'original). Avant, le depot ne servait qu'a
// autoriser le stationnement.

define('ALLY_DEPOSIT_PER_LEVEL', 10000);
define('ALLY_DEPOSIT_MAX_STAY' , 32 * 3600);

// Flottes en stationnement sur la planete, avec le pseudo de leur proprietaire
function AllyDepositFleets ( $Planet ) {
	$Qry  = "SELECT f.*, u.`username` FROM {{table}}fleets f LEFT JOIN {{table}}users u ON u.`id` = f.`fleet_owner` ";
	$Qry .= "WHERE f.`fleet_mission` = '5' AND f.`fleet_mess` = '2' AND f.`fleet_end_stay` > '". time() ."' AND ";
	$Qry .= "f.`fleet_end_galaxy` = '". intval($Planet['galaxy']) ."' AND f.`fleet_end_system` = '". intval($Planet['system']) ."' AND ";
	$Qry .= "f.`fleet_end_planet` = '". intval($Planet['planet']) ."' AND f.`fleet_end_type` = '". intval($Planet['planet_type']) ."' ";
	$Qry .= "ORDER BY f.`fleet_end_stay`, f.`fleet_id`;";
	$Result = doquery($Qry, '');
	$Fleets = array();
	while ($Row = mysqli_fetch_assoc($Result)) {
		$Fleets[] = $Row;
	}
	return $Fleets;
}

// Vaisseaux d'une flotte (fleet_array « type,nombre;type,nombre; »)
function AllyDepositShips ( $FleetArray ) {
	$Ships = array();
	foreach (explode(';', (string) $FleetArray) as $Item) {
		$Pair = explode(',', $Item);
		if (count($Pair) == 2 && intval($Pair[1]) > 0) {
			$Ships[intval($Pair[0])] = intval($Pair[1]);
		}
	}
	return $Ships;
}

// Cout du stationnement d'une flotte, en deuterium par heure (au moins 1)
function AllyDepositHourCost ( $FleetArray ) {
	global $pricelist;

	$Cost = 0;
	foreach (AllyDepositShips($FleetArray) as $Ship => $Count) {
		$Cost += $Count * ($pricelist[$Ship]['consumption'] ?? 0) / 10;
	}
	return max(1, (int) ceil($Cost));
}

// Nombre d'heures qu'une livraison peut ajouter : capacite du depot, stationnement de 32 h au plus, deuterium disponible
function AllyDepositMaxHours ( $Fleet, $Level, $Deuterium ) {
	$Cost  = AllyDepositHourCost($Fleet['fleet_array']);
	$Hours = min(floor(ALLY_DEPOSIT_PER_LEVEL * $Level / $Cost),
	             floor((time() + ALLY_DEPOSIT_MAX_STAY - $Fleet['fleet_end_stay']) / 3600),
	             floor($Deuterium / $Cost));
	return max(0, (int) $Hours);
}

// Livraison : $Hours heures de plus pour la flotte $FleetId, payees en deuterium de la planete courante. Tables
// verrouillees comme pendant le traitement des flottes (la flotte ne peut pas partir pendant la livraison). Retourne
// array('error' => cle de langue) ou le detail de la livraison.
function AllyDepositSupply ( $CurrentUser, $CurrentPlanet, $FleetId, $Hours ) {
	global $resource, $lang;

	$Level = intval($CurrentPlanet[$resource[34]] ?? 0);
	if ($CurrentPlanet['id_owner'] != $CurrentUser['id'] || $Level < 1) {
		return array('error' => 'depot_err_level');
	}
	if ($Hours < 1) {
		return array('error' => 'depot_err_hours');
	}

	doquery("LOCK TABLE {{table}}fleets WRITE, {{table}}planets WRITE", '');
	$Qry  = "SELECT * FROM {{table}} WHERE `fleet_id` = '". intval($FleetId) ."' AND `fleet_mission` = '5' AND `fleet_mess` = '2' ";
	$Qry .= "AND `fleet_end_stay` > '". time() ."' AND `fleet_end_galaxy` = '". intval($CurrentPlanet['galaxy']) ."' ";
	$Qry .= "AND `fleet_end_system` = '". intval($CurrentPlanet['system']) ."' AND `fleet_end_planet` = '". intval($CurrentPlanet['planet']) ."' ";
	$Qry .= "AND `fleet_end_type` = '". intval($CurrentPlanet['planet_type']) ."' LIMIT 1;";
	$Fleet = doquery($Qry, 'fleets', true);
	$Error = '';
	if (!$Fleet) {
		$Error = 'depot_err_fleet';
	} else {
		$Cost      = AllyDepositHourCost($Fleet['fleet_array']);
		$Total     = $Cost * $Hours;
		$Planet    = doquery("SELECT `deuterium` FROM {{table}} WHERE `id` = '". intval($CurrentPlanet['id']) ."' LIMIT 1;", 'planets', true);
		if ($Total > ALLY_DEPOSIT_PER_LEVEL * $Level) {
			$Error = 'depot_err_capacity';
		} elseif ($Fleet['fleet_end_stay'] + $Hours * 3600 > time() + ALLY_DEPOSIT_MAX_STAY) {
			$Error = 'depot_err_stay';
		} elseif (!$Planet || $Planet['deuterium'] < $Total) {
			$Error = 'depot_err_deut';
		} else {
			doquery("UPDATE {{table}} SET `deuterium` = `deuterium` - '". $Total ."' WHERE `id` = '". intval($CurrentPlanet['id']) ."' LIMIT 1;", 'planets');
			doquery("UPDATE {{table}} SET `fleet_end_stay` = `fleet_end_stay` + '". ($Hours * 3600) ."', `fleet_end_time` = `fleet_end_time` + '". ($Hours * 3600) ."' WHERE `fleet_id` = '". intval($Fleet['fleet_id']) ."' LIMIT 1;", 'fleets');
		}
	}
	doquery("UNLOCK TABLES", '');
	if ($Error != '') {
		return array('error' => $Error, 'max' => ALLY_DEPOSIT_PER_LEVEL * $Level);
	}

	$NewEnd = $Fleet['fleet_end_stay'] + $Hours * 3600;
	$Owner  = doquery("SELECT `username` FROM {{table}} WHERE `id` = '". intval($Fleet['fleet_owner']) ."' LIMIT 1;", 'users', true);
	$Message = sprintf($lang['depot_msg'], $CurrentPlanet['name'], GetTargetAdressLink($Fleet, ''), $CurrentUser['username'],
	                   $Hours, date("d/m/Y H:i:s", $NewEnd));
	SendSimpleMessage($Fleet['fleet_owner'], '', time(), 5, $lang['sys_mess_tower'], $lang['depot_msg_title'], $Message);

	return array('error' => '', 'owner' => ($Owner['username'] ?? ''), 'hours' => $Hours, 'cost' => $Total, 'end' => $NewEnd);
}

?>
