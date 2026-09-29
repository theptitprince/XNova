<?php

/**
 * FlyingFleetHandler.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// XNova Renaissance (0.9k, performances) : vrai si la mission de cette flotte ferait quelque chose a l'heure $Now
// (ecriture ou tirage au sort), faux si elle passerait sans rien changer (retour ou stationnement en cours...).
// Memes conditions que chaque MissionCase*, dans le meme ordre ; a tenir a jour avec elles (verifie par le banc,
// test_09k_pflottes).
function FleetRowIsDue ( $FleetRow, $Now ) {
	switch ($FleetRow["fleet_mission"]) {
		case 1:
		case 2:
			// Attaquer, attaque groupee : arrivee, puis retour
			return ($FleetRow['fleet_start_time'] <= $Now && ($FleetRow['fleet_mess'] == 0 || $FleetRow['fleet_end_time'] <= $Now));

		case 3:
			// Transporter
			return ($FleetRow['fleet_mess'] == 0) ? ($FleetRow['fleet_start_time'] < $Now) : ($FleetRow['fleet_end_time'] < $Now);

		case 4:
			// Stationner
			return ($FleetRow['fleet_mess'] == 0) ? ($FleetRow['fleet_start_time'] <= $Now) : ($FleetRow['fleet_end_time'] <= $Now);

		case 5:
			// Stationner chez un allie : arrivee, fin du stationnement, retour
			$State = intval($FleetRow['fleet_mess']);
			return (($State == 0 && $FleetRow['fleet_start_time'] <= $Now) || ($State == 2 && $FleetRow['fleet_end_stay'] <= $Now) ||
			        ($State == 1 && $FleetRow['fleet_end_time'] <= $Now));

		case 6:
			// Espionner
			return ($FleetRow['fleet_start_time'] <= $Now && ($FleetRow["fleet_mess"] != "1" || $FleetRow['fleet_end_time'] <= $Now));

		case 7:
			// Coloniser
			return ($FleetRow['fleet_mess'] == 0 || $FleetRow['fleet_end_time'] <= $Now);

		case 8:
			// Recyclage
			return ($FleetRow["fleet_mess"] == "0") ? ($FleetRow['fleet_start_time'] <= $Now) : ($FleetRow['fleet_end_time'] <= $Now);

		case 9:
			// Detruire
			return ($FleetRow['fleet_start_time'] <= $Now && ($FleetRow['fleet_mess'] == 0 || $FleetRow['fleet_end_time'] <= $Now));

		case 10:
			// Missiles : rien
			return false;

		case 15:
			// Expeditions
			return ($FleetRow['fleet_mess'] == 0) ? ($FleetRow['fleet_end_stay'] < $Now) : ($FleetRow['fleet_end_time'] < $Now);

		default:
			// Mission inconnue : supprimee
			return true;
	}
}

function FlyingFleetHandler (&$planet) {
	global $resource, $FleetHandlerLocked;

	$QryWhere   = "WHERE (";
	$QryWhere  .= "( ";
	$QryWhere  .= "`fleet_start_galaxy` = ". $planet['galaxy']      ." AND ";
	$QryWhere  .= "`fleet_start_system` = ". $planet['system']      ." AND ";
	$QryWhere  .= "`fleet_start_planet` = ". $planet['planet']      ." AND ";
	$QryWhere  .= "`fleet_start_type` = ".   $planet['planet_type'] ." ";
	$QryWhere  .= ") OR ( ";
	$QryWhere  .= "`fleet_end_galaxy` = ".   $planet['galaxy']      ." AND ";
	$QryWhere  .= "`fleet_end_system` = ".   $planet['system']      ." AND ";
	$QryWhere  .= "`fleet_end_planet` = ".   $planet['planet']      ." ) AND ";
	$QryWhere  .= "`fleet_end_type`= ".      $planet['planet_type'] ." ) AND ";

	// XNova Renaissance (0.9k, performances) : flottes de la position lues d'abord sans verrou (index des coordonnees).
	// Aucune n'a rien a faire : ni verrou des 9 tables ni traitement, qui n'auraient rien change. Sinon, traitement
	// d'origine, avec la liste relue sous verrou.
	$Now        = time();
	$DueQuery   = doquery("SELECT `fleet_mission`, `fleet_mess`, `fleet_start_time`, `fleet_end_stay`, `fleet_end_time` FROM {{table}} ". $QryWhere ."( `fleet_start_time` < '". $Now ."' OR `fleet_end_time` < '". $Now ."' );", 'fleets');
	$Due        = false;
	while (!$Due && ($DueRow = mysqli_fetch_assoc($DueQuery))) {
		$Due = FleetRowIsDue($DueRow, $Now);
	}
	if (!$Due) {
		return;
	}

	// (aks : attaques groupees, 0.9i)
	doquery("LOCK TABLE {{table}}lunas WRITE, {{table}}rw WRITE, {{table}}errors WRITE, {{table}}messages WRITE, {{table}}fleets WRITE, {{table}}planets WRITE, {{table}}galaxy WRITE ,{{table}}users WRITE, {{table}}aks WRITE", "");
	// Tables verrouillees (0.9k) : un LOCK TABLE pose pendant le traitement liberait tout le verrou des flottes
	// (voir PlanetResourceUpdate)
	$FleetHandlerLocked = true;

	// Parcours de toute la table (USE INDEX ()) : lignes dans l'ordre physique, qui decide de l'ordre de traitement
	$QryFleet   = "SELECT * FROM {{table}} USE INDEX () ". $QryWhere;
	$QryFleet  .= "( `fleet_start_time` < '". time() ."' OR `fleet_end_time` < '". time() ."' );";
	$fleetquery = doquery( $QryFleet, 'fleets' );

	// XNova Renaissance (0.9k) : chaque flotte est relue juste avant d'etre traitee, dans le meme ordre. La liste etait
	// lue d'un coup : une flotte deja traitee par une ligne precedente gardait son ancien etat. Une attaque groupee
	// etait jouee deux fois (deux flottes du groupe parties de la meme planete, ou une flotte partie de la cible), une
	// flotte detruite en defense groupee livrait encore son chargement, une flotte renvoyee par la destruction d'une
	// planete ou d'une lune etait traitee avec ses anciennes coordonnees.
	$FleetRows = array();
	while ($Row = mysqli_fetch_array($fleetquery)) {
		$FleetRows[] = $Row;
	}

	foreach ($FleetRows as $FleetIndex => $CurrentFleet) {
		if ($FleetIndex > 0) {
			$CurrentFleet = doquery("SELECT * FROM {{table}} WHERE `fleet_id` = '". intval($CurrentFleet['fleet_id']) ."';", 'fleets', true);
			if (!$CurrentFleet) {
				// Supprimee entre-temps (combat, defense groupee, retour deja traite)
				continue;
			}
		}
		// Rien a faire pour cette flotte maintenant : sa mission ne ferait que lire la base (0.9k, performances)
		if (!FleetRowIsDue($CurrentFleet, time())) {
			continue;
		}
		switch ($CurrentFleet["fleet_mission"]) {
			case 1:
				// Attaquer
				MissionCaseAttack ( $CurrentFleet );
				break;

			case 2:
				// Attaque groupee (0.9i) : un seul combat pour tout le groupe, a l'arrivee de sa premiere flotte traitee
				// (jamais programmee dans l'original : la flotte etait supprimee a l'arrivee, vaisseaux compris)
				MissionCaseAttack ( $CurrentFleet );
				break;

			case 3:
				// Transporter
				MissionCaseTransport ( $CurrentFleet );
				break;

			case 4:
				// Stationner
				MissionCaseStay ( $CurrentFleet );
				break;

			case 5:
				// Stationner chez un Allié
			MissionCaseStayAlly ( $CurrentFleet );
				break;

			case 6:
				// Flotte d'espionnage
				MissionCaseSpy ( $CurrentFleet );
				break;

			case 7:
				// Coloniser
				MissionCaseColonisation ( $CurrentFleet );
				break;

			case 8:
				// Recyclage
				MissionCaseRecycling ( $CurrentFleet );
				break;

			case 9:
				// Detruire ??? dans le code ogame c'est 9 !!
				MissionCaseDestruction ( $CurrentFleet );
				break;

			case 10:
				// Missiles !!
				
				break;

			case 15:
				// Expeditions
				MissionCaseExpedition ( $CurrentFleet );
				break;

			default: {
				doquery("DELETE FROM {{table}} WHERE `fleet_id` = '". $CurrentFleet['fleet_id'] ."';", 'fleets');
			}
		}
	}

	$FleetHandlerLocked = false;
	doquery("UNLOCK TABLES", "");
}

?>