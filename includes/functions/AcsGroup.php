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
