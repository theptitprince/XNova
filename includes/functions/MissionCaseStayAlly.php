<?php

/**
 * MissionCaseStayAlly.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */


// ----------------------------------------------------------------------------------------------------------------
// Mission Case 5: -> Stationner chez un allié
//
// XNova Renaissance : etats de la flotte (fleet_mess) : 0 en route, 2 en stationnement (arrivee traitee), 1 retour.
// L'original n'avait pas d'etat « en stationnement » : apres l'arrivee, les deux messages repartaient a chaque
// passage et la fin du stationnement n'etait jamais atteinte (la flotte ne rentrait que rappelee). Les ressources
// annoncees comme livrees par ces messages ne l'etaient pas : elles sont livrees a l'arrivee. Un passage en retard
// enchaine les etapes (arrivee, fin du stationnement, retour).
function MissionCaseStayAlly ( $FleetRow ) {
	global $lang;

	$QryStartPlanet   = "SELECT * FROM {{table}} ";
	$QryStartPlanet  .= "WHERE ";
	$QryStartPlanet  .= "`galaxy` = '". $FleetRow['fleet_start_galaxy'] ."' AND ";
	$QryStartPlanet  .= "`system` = '". $FleetRow['fleet_start_system'] ."' AND ";
	$QryStartPlanet  .= "`planet` = '". $FleetRow['fleet_start_planet'] ."' AND ";
	$QryStartPlanet  .= "`planet_type` = '". $FleetRow['fleet_start_type'] ."';"; // (sans le type : la planete au lieu de sa lune)
	$StartPlanet      = doquery( $QryStartPlanet, 'planets', true);
	$StartName        = $StartPlanet['name'] ?? ''; // planete disparue (colonie abandonnee, compte supprime) : vide
	$StartOwner       = $StartPlanet['id_owner'] ?? 0;

	$State = intval($FleetRow['fleet_mess']);
	if ($State == 0 && $FleetRow['fleet_start_time'] <= time()) {
		// Planete cible lue seulement a l'arrivee, ou elle sert (0.9k, performances)
		$QryTargetPlanet  = "SELECT * FROM {{table}} ";
		$QryTargetPlanet .= "WHERE ";
		$QryTargetPlanet .= "`galaxy` = '". $FleetRow['fleet_end_galaxy'] ."' AND ";
		$QryTargetPlanet .= "`system` = '". $FleetRow['fleet_end_system'] ."' AND ";
		$QryTargetPlanet .= "`planet` = '". $FleetRow['fleet_end_planet'] ."' AND ";
		$QryTargetPlanet .= "`planet_type` = '". $FleetRow['fleet_end_type'] ."';";
		$TargetPlanet     = doquery( $QryTargetPlanet, 'planets', true);
		$TargetName       = $TargetPlanet['name'] ?? ''; // planete disparue (colonie abandonnee, compte supprime) : vide
		$TargetOwner      = $TargetPlanet['id_owner'] ?? 0;

		// Arrivee : livraison et messages, une seule fois. Cible disparue : pas de livraison, la flotte rentre
		// avec son chargement.
		if ($TargetPlanet) {
			StoreGoodsToPlanet ( $FleetRow, false );

			$Message         = sprintf( $lang['sys_tran_mess_owner'],
									$TargetName, GetTargetAdressLink($FleetRow, ''),
									$FleetRow['fleet_resource_metal'], $lang['metal_label'],
									$FleetRow['fleet_resource_crystal'], $lang['crystal_label'],
									$FleetRow['fleet_resource_deuterium'], $lang['deuterium_label'] );

			SendSimpleMessage ( $StartOwner, '', $FleetRow['fleet_start_time'], 5, $lang['sys_mess_tower'], $lang['sys_mess_transport'], $Message);

			$Message         = sprintf( $lang['sys_tran_mess_user'],
									$StartName, GetStartAdressLink($FleetRow, ''),
									$TargetName, GetTargetAdressLink($FleetRow, ''),
									$FleetRow['fleet_resource_metal'], $lang['metal_label'],
									$FleetRow['fleet_resource_crystal'], $lang['crystal_label'],
									$FleetRow['fleet_resource_deuterium'], $lang['deuterium_label'] );
			SendSimpleMessage ( $TargetOwner, '', $FleetRow['fleet_start_time'], 5, $lang['sys_mess_tower'], $lang['sys_mess_transport'], $Message);

			$FleetRow['fleet_resource_metal']     = 0;
			$FleetRow['fleet_resource_crystal']   = 0;
			$FleetRow['fleet_resource_deuterium'] = 0;
			$State = 2;
		} else {
			$State = 1;
		}
		$QryUpdateFleet  = "UPDATE {{table}} SET ";
		$QryUpdateFleet .= "`fleet_resource_metal` = '". $FleetRow['fleet_resource_metal'] ."', ";
		$QryUpdateFleet .= "`fleet_resource_crystal` = '". $FleetRow['fleet_resource_crystal'] ."', ";
		$QryUpdateFleet .= "`fleet_resource_deuterium` = '". $FleetRow['fleet_resource_deuterium'] ."', ";
		$QryUpdateFleet .= "`fleet_mess` = '". $State ."' ";
		$QryUpdateFleet .= "WHERE `fleet_id` = '". $FleetRow['fleet_id'] ."' ";
		$QryUpdateFleet .= "LIMIT 1 ;";
		doquery( $QryUpdateFleet, 'fleets');
	}
	if ($State == 2 && $FleetRow['fleet_end_stay'] <= time()) {
		// Fin du stationnement : la flotte repart
		$QryUpdateFleet  = "UPDATE {{table}} SET ";
		$QryUpdateFleet .= "`fleet_mess` = '1' ";
		$QryUpdateFleet .= "WHERE `fleet_id` = '". $FleetRow['fleet_id'] ."' ";
		$QryUpdateFleet .= "LIMIT 1 ;";
		doquery( $QryUpdateFleet, 'fleets');
		$State = 1;
	}
	if ($State == 1 && $FleetRow['fleet_end_time'] <= time()) {
		$Message         = sprintf ($lang['sys_tran_mess_back'],
								$StartName, GetStartAdressLink($FleetRow, ''));
		SendSimpleMessage ( $StartOwner, '', $FleetRow['fleet_end_time'], 5, $lang['sys_mess_tower'], $lang['sys_mess_fleetback'], $Message);
		RestoreFleetToPlanet ( $FleetRow, true );
		doquery("DELETE FROM {{table}} WHERE fleet_id=" . $FleetRow["fleet_id"], 'fleets');
	}
}

?>
