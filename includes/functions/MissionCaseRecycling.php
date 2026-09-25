<?php

/**
 * MissionCaseRecycling.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function MissionCaseRecycling ($FleetRow) {
	global $pricelist, $lang;

	if ($FleetRow["fleet_mess"] == "0") {
		if ($FleetRow['fleet_start_time'] <= time()) {
			$QrySelectGalaxy  = "SELECT * FROM {{table}} ";
			$QrySelectGalaxy .= "WHERE ";
			$QrySelectGalaxy .= "`galaxy` = '".$FleetRow['fleet_end_galaxy']."' AND ";
			$QrySelectGalaxy .= "`system` = '".$FleetRow['fleet_end_system']."' AND ";
			$QrySelectGalaxy .= "`planet` = '".$FleetRow['fleet_end_planet']."' ";
			$QrySelectGalaxy .= "LIMIT 1;";
			$TargetGalaxy     = doquery( $QrySelectGalaxy, 'galaxy', true);

			$FleetRecord         = explode(";", $FleetRow['fleet_array']);
			$RecyclerCapacity    = 0;
			$OtherFleetCapacity  = 0;
			foreach ($FleetRecord as $Item => $Group) {
				if ($Group != '') {
					$Class        = explode (",", $Group);
					if ($Class[0] == 209) {
						$RecyclerCapacity   += $pricelist[$Class[0]]["capacity"] * $Class[1];
					} else {
						$OtherFleetCapacity += $pricelist[$Class[0]]["capacity"] * $Class[1];
					}
				}
			}

			$IncomingFleetGoods = $FleetRow["fleet_resource_metal"] + $FleetRow["fleet_resource_crystal"] + $FleetRow["fleet_resource_deuterium"];
			if ($IncomingFleetGoods > $OtherFleetCapacity) {
				$RecyclerCapacity -= ($IncomingFleetGoods - $OtherFleetCapacity);
			}

			if (($TargetGalaxy["metal"] + $TargetGalaxy["crystal"]) <= $RecyclerCapacity) {
				$RecycledGoods["metal"]   = $TargetGalaxy["metal"];
				$RecycledGoods["crystal"] = $TargetGalaxy["crystal"];
			} else {
				if (($TargetGalaxy["metal"]   > $RecyclerCapacity / 2) AND
					($TargetGalaxy["crystal"] > $RecyclerCapacity / 2)) {
					$RecycledGoods["metal"]   = $RecyclerCapacity / 2;
					$RecycledGoods["crystal"] = $RecyclerCapacity / 2;
				} else {
					if ($TargetGalaxy["metal"] > $TargetGalaxy["crystal"]) {
						$RecycledGoods["crystal"] = $TargetGalaxy["crystal"];
						if ($TargetGalaxy["metal"] > ($RecyclerCapacity - $RecycledGoods["crystal"])) {
							$RecycledGoods["metal"] = $RecyclerCapacity - $RecycledGoods["crystal"];
						} else {
							$RecycledGoods["metal"] = $TargetGalaxy["metal"];
						}
					} else {
						$RecycledGoods["metal"] = $TargetGalaxy["metal"];
						if ($TargetGalaxy["crystal"] > ($RecyclerCapacity - $RecycledGoods["metal"])) {
							$RecycledGoods["crystal"] = $RecyclerCapacity - $RecycledGoods["metal"];
						} else {
							$RecycledGoods["crystal"] = $TargetGalaxy["crystal"];
						}
					}
				}
			}
			$NewCargo['Metal']     = $FleetRow["fleet_resource_metal"]   + $RecycledGoods["metal"];
			$NewCargo['Crystal']   = $FleetRow["fleet_resource_crystal"] + $RecycledGoods["crystal"];
			$NewCargo['Deuterium'] = $FleetRow["fleet_resource_deuterium"];

			$QryUpdateGalaxy  = "UPDATE {{table}} SET ";
			$QryUpdateGalaxy .= "`metal` = `metal` - '".$RecycledGoods["metal"]."', ";
			$QryUpdateGalaxy .= "`crystal` = `crystal` - '".$RecycledGoods["crystal"]."' ";
			$QryUpdateGalaxy .= "WHERE ";
			$QryUpdateGalaxy .= "`galaxy` = '".$FleetRow['fleet_end_galaxy']."' AND ";
			$QryUpdateGalaxy .= "`system` = '".$FleetRow['fleet_end_system']."' AND ";
			$QryUpdateGalaxy .= "`planet` = '".$FleetRow['fleet_end_planet']."' ";
			$QryUpdateGalaxy .= "LIMIT 1;";
			doquery( $QryUpdateGalaxy, 'galaxy');

			$Message = sprintf($lang['sys_recy_gotten'], pretty_number($RecycledGoods["metal"]), $lang['metal_label'], pretty_number($RecycledGoods["crystal"]), $lang['crystal_label']);
			SendSimpleMessage ( $FleetRow['fleet_owner'], '', $FleetRow['fleet_start_time'], 4, $lang['sys_mess_spy_control'], $lang['sys_recy_report'], $Message);
			doquery("UPDATE {{table}} SET `mnl_exploit` = `mnl_exploit` + '1' WHERE `id` = '".$FleetRow['fleet_owner']."'", 'users');

			$QryUpdateFleet  = "UPDATE {{table}} SET ";
            $QryUpdateFleet .= "`fleet_resource_metal` = '".$NewCargo['Metal']."', ";
			$QryUpdateFleet .= "`fleet_resource_crystal` = '".$NewCargo['Crystal']."', ";
			$QryUpdateFleet .= "`fleet_resource_deuterium` = '".$NewCargo['Deuterium']."', ";
			$QryUpdateFleet .= "`fleet_mess` = '1' ";
            $QryUpdateFleet .= "WHERE ";
			$QryUpdateFleet .= "`fleet_id` = '".$FleetRow['fleet_id']."' ";
            $QryUpdateFleet .= "LIMIT 1;";
			doquery( $QryUpdateFleet, 'fleets');
		}
	} else {
		if ($FleetRow['fleet_end_time'] <= time()) {
			// Mettre le message de retour de flotte : planete de depart (avant : nom vide et coordonnees de la cible)
			$StartPlanet     = doquery("SELECT `name` FROM {{table}} WHERE `galaxy` = '". intval($FleetRow['fleet_start_galaxy']) ."' AND `system` = '". intval($FleetRow['fleet_start_system']) ."' AND `planet` = '". intval($FleetRow['fleet_start_planet']) ."' AND `planet_type` = '". intval($FleetRow['fleet_start_type']) ."';", 'planets', true);
			$Message         = sprintf( $lang['sys_tran_mess_owner'],
						($StartPlanet ? $StartPlanet['name'] : ''), GetStartAdressLink($FleetRow, ''),
						pretty_number($FleetRow['fleet_resource_metal']), $lang['metal_label'],
						pretty_number($FleetRow['fleet_resource_crystal']), $lang['crystal_label'],
						pretty_number($FleetRow['fleet_resource_deuterium']), $lang['deuterium_label'] );
			SendSimpleMessage ( $FleetRow['fleet_owner'], '', $FleetRow['fleet_end_time'], 4, $lang['sys_mess_spy_control'], $lang['sys_mess_fleetback'], $Message);

			RestoreFleetToPlanet ( $FleetRow, true );
			doquery("DELETE FROM {{table}} WHERE `fleet_id` = '". $FleetRow["fleet_id"] ."';", 'fleets');
		}
	}
}

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 Mise en module initiale

?>