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

function FlyingFleetHandler (&$planet) {
	global $resource, $FleetHandlerLocked;

	// (aks : attaques groupees, 0.9i)
	doquery("LOCK TABLE {{table}}lunas WRITE, {{table}}rw WRITE, {{table}}errors WRITE, {{table}}messages WRITE, {{table}}fleets WRITE, {{table}}planets WRITE, {{table}}galaxy WRITE ,{{table}}users WRITE, {{table}}aks WRITE", "");
	// Tables verrouillees (0.9k) : un LOCK TABLE pose pendant le traitement liberait tout le verrou des flottes
	// (voir PlanetResourceUpdate)
	$FleetHandlerLocked = true;

	$QryFleet   = "SELECT * FROM {{table}} ";
	$QryFleet  .= "WHERE (";
	$QryFleet  .= "( ";
	$QryFleet  .= "`fleet_start_galaxy` = ". $planet['galaxy']      ." AND ";
	$QryFleet  .= "`fleet_start_system` = ". $planet['system']      ." AND ";
	$QryFleet  .= "`fleet_start_planet` = ". $planet['planet']      ." AND ";
	$QryFleet  .= "`fleet_start_type` = ".   $planet['planet_type'] ." ";
	$QryFleet  .= ") OR ( ";
	$QryFleet  .= "`fleet_end_galaxy` = ".   $planet['galaxy']      ." AND ";
	$QryFleet  .= "`fleet_end_system` = ".   $planet['system']      ." AND ";
	$QryFleet  .= "`fleet_end_planet` = ".   $planet['planet']      ." ) AND ";
	$QryFleet  .= "`fleet_end_type`= ".      $planet['planet_type'] ." ) AND ";
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