<?php

/**
 * AcsGroup.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Attaque groupee (0.9i) : fonctions communes a la page du groupe (verband.php), a l'envoi et au rappel des flottes
// et au combat. Un groupe est une ligne de la table aks ; ses flottes portent son numero (fleets.fleet_group).

// Numeros des joueurs invites (colonne eingeladen : numeros separes par des virgules)
function AcsInvitedIds ( $Group ) {
	$Ids = array();
	foreach (explode(',', (string) ($Group['eingeladen'] ?? '')) as $Id) {
		if (intval($Id) > 0) {
			$Ids[] = intval($Id);
		}
	}
	return array_values(array_unique($Ids));
}

// Groupes qu'un joueur peut rejoindre : il en est le chef ou y est invite, et le groupe n'est pas encore arrive
function AcsJoinableGroups ( $UserId ) {
	$UserId = intval($UserId);
	$Groups = array();
	$Query  = doquery("SELECT * FROM {{table}} WHERE `ankunft` > '". time() ."' AND (`owner` = '". $UserId ."' OR FIND_IN_SET('". $UserId ."', `eingeladen`)) ORDER BY `ankunft`;", 'aks');
	while ($Row = mysqli_fetch_assoc($Query)) {
		$Groups[] = $Row;
	}
	return $Groups;
}

// Groupe que le joueur rejoint en envoyant une flotte vers cette cible (mission « Attaque groupee ») : la ligne du
// groupe, ou la cle du message d'erreur (groupe introuvable, arrive, pas invite, autre cible, 16 flottes deja)
function AcsGroupToJoin ( $GroupId, $UserId, $Galaxy, $System, $Planet, $PlanetType ) {
	$Group = (intval($GroupId) > 0) ? doquery("SELECT * FROM {{table}} WHERE `id` = '". intval($GroupId) ."';", 'aks', true) : false;
	if (!$Group || $Group['ankunft'] <= time()) {
		return 'fl_acs_not_found';
	}
	if ($Group['owner'] != $UserId && !in_array(intval($UserId), AcsInvitedIds($Group))) {
		return 'fl_acs_not_found';
	}
	if ($Group['galaxy'] != $Galaxy || $Group['system'] != $System || $Group['planet'] != $Planet || $Group['planet_type'] != $PlanetType) {
		return 'fl_acs_other_target';
	}
	$Fleets = doquery("SELECT COUNT(*) AS `n` FROM {{table}} WHERE `fleet_group` = '". intval($Group['id']) ."';", 'fleets', true);
	if (intval($Fleets['n']) >= 16) {
		return 'fl_acs_fleets_full';
	}
	return $Group;
}

// Defense groupee : flottes alliees qui stationnent sur une planete ou une lune a l'heure d'une attaque (arrivees,
// pendant leur duree ; un stationnement arrive mais pas encore traite compte aussi), dans l'ordre d'envoi
function AcsHoldingFleets ( $Galaxy, $System, $Planet, $PlanetType, $Time ) {
	$Rows  = array();
	$Qry   = "SELECT * FROM {{table}} WHERE `fleet_mission` = '5' AND `fleet_mess` IN ('0', '2') ";
	$Qry  .= "AND `fleet_end_galaxy` = '". intval($Galaxy) ."' AND `fleet_end_system` = '". intval($System) ."' ";
	$Qry  .= "AND `fleet_end_planet` = '". intval($Planet) ."' AND `fleet_end_type` = '". intval($PlanetType) ."' ";
	$Qry  .= "AND `fleet_start_time` <= '". intval($Time) ."' AND `fleet_end_stay` > '". intval($Time) ."' ORDER BY `fleet_id` ASC;";
	$Query = doquery($Qry, 'fleets');
	while ($Row = mysqli_fetch_assoc($Query)) {
		$Rows[] = $Row;
	}
	return $Rows;
}

// Defense groupee : vaisseaux restants des flottes en stationnement apres le combat ($Fleets : resultat du moteur pour
// les defenseurs, memes numeros que $HoldRows) ; une flotte detruite disparait, les autres continuent de stationner
function AcsUpdateHoldingFleets ( $HoldRows, $Fleets ) {
	foreach ($HoldRows as $i => $Row) {
		$Array  = '';
		$Amount = 0;
		foreach ($Fleets[$i] as $Ship => $Count) {
			$Array  .= $Ship .",". $Count .";";
			$Amount += $Count;
		}
		if ($Amount <= 0) {
			doquery("DELETE FROM {{table}} WHERE `fleet_id` = '". intval($Row['fleet_id']) ."';", 'fleets');
		} else {
			doquery("UPDATE {{table}} SET `fleet_array` = '". $Array ."', `fleet_amount` = '". $Amount ."' WHERE `fleet_id` = '". intval($Row['fleet_id']) ."';", 'fleets');
		}
	}
}

// Groupe sans flotte (toutes rappelees, joueurs supprimes) : supprime
function AcsDeleteIfEmpty ( $GroupId ) {
	$GroupId = intval($GroupId);
	if ($GroupId < 1) {
		return;
	}
	$Left = doquery("SELECT COUNT(*) AS `n` FROM {{table}} WHERE `fleet_group` = '". $GroupId ."';", 'fleets', true);
	if (intval($Left['n']) == 0) {
		doquery("DELETE FROM {{table}} WHERE `id` = '". $GroupId ."';", 'aks');
	}
}

?>
