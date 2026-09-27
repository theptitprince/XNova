<?php

/**
 * RecalculateRunningQueues.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Changement des vitesses du serveur (Configuration) : ce qui est deja en cours suit la nouvelle vitesse (l'original
 * ne recalculait rien, seules les nouvelles actions en profitaient). Temps restant multiplie par ancienne vitesse /
 * nouvelle vitesse : constructions (file comprise), recherches et missiles (vitesse du jeu), trajets des flottes en vol
 * (vitesse des flottes ; stationnement et expedition gardent leur duree en heures). Le chantier spatial suit deja la
 * vitesse en continu.
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Retourne le nombre d'elements recalcules : buildings (planetes), research, fleets, missiles
function RecalculateRunningQueues ( $OldGameSpeed, $NewGameSpeed, $OldFleetSpeed, $NewFleetSpeed ) {
	global $link;

	$Now    = time();
	$Result = array('buildings' => 0, 'research' => 0, 'fleets' => 0, 'missiles' => 0);

	if ($OldGameSpeed > 0 && $NewGameSpeed > 0 && $OldGameSpeed != $NewGameSpeed) {
		$Ratio = $OldGameSpeed / $NewGameSpeed;

		// Batiments : l'element en cours (fin = b_building) puis la file « element,niveau,duree,fin,mode », chaque fin a
		// la suite de la precedente
		$Planets = doquery("SELECT `id`, `b_building`, `b_building_id` FROM {{table}} WHERE `b_building` > '". $Now ."' AND `b_building_id` NOT IN ('', '0');", 'planets');
		while ($Planet = mysqli_fetch_assoc($Planets)) {
			$Queue   = explode(';', $Planet['b_building_id']);
			$HeadEnd = intval($Planet['b_building']);
			$PrevEnd = 0;
			foreach ($Queue as $Pos => $Entry) {
				$Item = explode(',', $Entry);
				if (count($Item) < 5) {
					continue;
				}
				$Item[2] = max(1, (int) round($Item[2] * $Ratio));
				$Item[3] = ($Pos == 0) ? $Now + (int) round(($HeadEnd - $Now) * $Ratio) : $PrevEnd + $Item[2];
				if ($Pos == 0) {
					$HeadEnd = $Item[3];
				}
				$PrevEnd     = $Item[3];
				$Queue[$Pos] = implode(',', $Item);
			}
			doquery("UPDATE {{table}} SET `b_building` = '". $HeadEnd ."', `b_building_id` = '". SqlEscape(implode(';', $Queue)) ."' WHERE `id` = '". intval($Planet['id']) ."';", 'planets');
			$Result['buildings']++;
		}

		// Recherches (fin sur la planete du laboratoire) et missiles interplanetaires en vol (vol calcule sur la vitesse
		// du jeu, comme dans l'original)
		doquery("UPDATE {{table}} SET `b_tech` = ". $Now ." + ROUND((`b_tech` - ". $Now .") * ". $Ratio .") WHERE `b_tech` > ". $Now .";", 'planets');
		$Result['research'] = mysqli_affected_rows($link);
		doquery("UPDATE {{table}} SET `zeit` = ". $Now ." + ROUND((`zeit` - ". $Now .") * ". $Ratio .") WHERE `zeit` > ". $Now .";", 'iraks');
		$Result['missiles'] = mysqli_affected_rows($link);
	}

	if ($OldFleetSpeed > 0 && $NewFleetSpeed > 0 && $OldFleetSpeed != $NewFleetSpeed) {
		$Ratio  = $OldFleetSpeed / $NewFleetSpeed;
		$Fleets = doquery("SELECT `fleet_id`, `fleet_start_time`, `fleet_end_stay`, `fleet_end_time` FROM {{table}} WHERE `fleet_end_time` > '". $Now ."';", 'fleets');
		while ($Fleet = mysqli_fetch_assoc($Fleets)) {
			$Arrival = intval($Fleet['fleet_start_time']);
			$StayEnd = intval($Fleet['fleet_end_stay']);
			$Return  = intval($Fleet['fleet_end_time']);
			$Stay    = ($StayEnd > 0) ? $StayEnd - $Arrival : 0;
			$Back    = $Return - (($StayEnd > 0) ? $StayEnd : $Arrival);
			if ($Now < $Arrival) {
				// Aller en cours : reste de l'aller et retour entier a la nouvelle vitesse, stationnement inchange
				$Arrival = $Now + (int) round(($Arrival - $Now) * $Ratio);
				$StayEnd = ($StayEnd > 0) ? $Arrival + $Stay : 0;
				$Return  = (($StayEnd > 0) ? $StayEnd : $Arrival) + (int) round($Back * $Ratio);
			} elseif ($StayEnd > 0 && $Now < $StayEnd) {
				// Stationnement ou expedition en cours : seul le retour change
				$Return  = $StayEnd + (int) round($Back * $Ratio);
			} else {
				// Retour en cours
				$Return  = $Now + (int) round(($Return - $Now) * $Ratio);
			}
			doquery("UPDATE {{table}} SET `fleet_start_time` = '". $Arrival ."', `fleet_end_stay` = '". $StayEnd ."', `fleet_end_time` = '". $Return ."' WHERE `fleet_id` = '". intval($Fleet['fleet_id']) ."';", 'fleets');
			$Result['fleets']++;
		}
	}

	return $Result;
}

?>
