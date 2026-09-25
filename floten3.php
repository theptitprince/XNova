<?php

/**
 * floten3.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	$dpath     = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

	includeLang('fleet');

	// PHP 8 : un champ vide ("") ne peut plus entrer dans un calcul ; tous les champs numeriques deviennent des entiers
	SanitizeNumericInput ( array('mission', 'galaxy', 'system', 'planet', 'planettype', 'planet_type', 'thisgalaxy', 'thissystem', 'thisplanet',
	                           'thisplanettype', 'resource1', 'resource2', 'resource3', 'holdingtime', 'expeditiontime',
	                           'speed', 'speedfactor', 'speedallsmin', 'maxepedition', 'curepedition', 'target_mission', 'fleetid'), '/^ship[0-9]+$/' );

	$CurrentPlanet = doquery("SELECT * FROM {{table}} WHERE `id` = '". $user['current_planet'] ."'", 'planets', true);
	$TargetPlanet  = doquery("SELECT * FROM {{table}} WHERE `galaxy` = '". ($_POST['galaxy'] ?? null) ."' AND `system` = '". ($_POST['system'] ?? null) ."' AND `planet` = '". ($_POST['planet'] ?? null) ."' AND `planet_type` = '". ($_POST['planettype'] ?? null) ."';", 'planets', true);
	$MyDBRec       = doquery("SELECT * FROM {{table}} WHERE `id` = '". $user['id']."';", 'users', true);

	$protection      = $game_config['noobprotection'];
	$protectiontime  = $game_config['noobprotectiontime'];
	$protectionmulti = $game_config['noobprotectionmulti'];
	if ($protectiontime < 1) {
		$protectiontime = 9999999999999999;
	}

	$fleetarray  = unserialize(base64_decode(str_rot13((string) ($_POST["usedfleet"] ?? ''))), array('allowed_classes' => false));

	// La liste vient du navigateur : on ne garde que des vaisseaux existants en quantites positives
	// (une quantite negative ajoutait des vaisseaux a la planete au depart de la flotte)
	$CleanFleet = array();
	if (is_array($fleetarray)) {
		foreach ($fleetarray as $Ship => $Count) {
			$Ship  = intval($Ship);
			$Count = intval($Count);
			if ($Ship > 200 && $Ship < 300 && isset($resource[$Ship]) && $Count > 0) {
				$CleanFleet[$Ship] = $Count;
			}
		}
	}
	$fleetarray = $CleanFleet;

	if (count($fleetarray) == 0) {
		message ("<font color=\"red\"><b>". $lang['fl_fleet_err'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	// On verifie s'il y a assez de vaisseaux sur la planete !
	foreach ($fleetarray as $Ship => $Count) {
		if ($Count > $CurrentPlanet[$resource[$Ship]]) {
			message ("<font color=\"red\"><b>". $lang['fl_fleet_err'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
	}

	$error              = 0;
	$galaxy             = intval(($_POST['galaxy'] ?? null));
	$system             = intval(($_POST['system'] ?? null));
	$planet             = intval(($_POST['planet'] ?? null));
    $planettype         = intval(($_POST['planettype'] ?? null));
    $fleetmission       = ($_POST['mission'] ?? null);

	if ($planettype != 1 && $planettype != 2 && $planettype != 3) {
		message ("<font color=\"red\"><b>". $lang['fl_fleet_err_pl'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	if ($fleetmission == 8) {
		$YourPlanet = false;
		$UsedPlanet = false;
		$select     = doquery("SELECT * FROM {{table}} WHERE galaxy = '". $galaxy ."' AND system = '". $system ."' AND planet = '". $planet ."'", "planets");
	} else {
		$YourPlanet = false;
		$UsedPlanet = false;
		$select     = doquery("SELECT * FROM {{table}} WHERE galaxy = '". $galaxy ."' AND system = '". $system ."' AND planet = '". $planet ."' AND planet_type = '". $planettype ."'", "planets");
	}

	if ($CurrentPlanet['galaxy'] == $galaxy &&
		$CurrentPlanet['system'] == $system &&
		$CurrentPlanet['planet'] == $planet &&
		$CurrentPlanet['planet_type'] == $planettype) {
		message ("<font color=\"red\"><b>". $lang['fl_ownpl_err'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	// Test d'existance de l'enregistrement dans la gaalxie !
	if (($_POST['mission'] ?? null) != 15) {
		if (mysqli_num_rows($select) < 1 && $fleetmission != 7) {
			message ("<font color=\"red\"><b>". $lang['fl_unknow_target'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		} elseif ($fleetmission == 9 && mysqli_num_rows($select) < 1) {
			message ("<font color=\"red\"><b>". $lang['fl_used_target'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
	} else {
	    // Limite recalculee cote serveur (avant : lue dans le formulaire, donc modifiable par le joueur)
	    $ExpeTech           = intval($user[$resource[124]] ?? 0);
	    $EnvoiMaxExpedition = ($ExpeTech >= 1) ? 1 + floor( $ExpeTech / 3 ) : 0;
	    $ExpeCount          = doquery("SELECT COUNT(fleet_owner) AS `expedi` FROM {{table}} WHERE `fleet_owner` = '". intval($user['id']) ."' AND `fleet_mission` = '15';", 'fleets', true);
	    $Expedition         = intval($ExpeCount['expedi']);

	    if       ($EnvoiMaxExpedition == 0 ) {
			message ("<font color=\"red\"><b>". $lang['fl_expe_notech'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		} elseif ($Expedition >= $EnvoiMaxExpedition ) {
			message ("<font color=\"red\"><b>". $lang['fl_expe_max'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
	}

	$select = mysqli_fetch_array($select);

	if (($select['id_owner'] ?? 0) == $user['id']) {
		$YourPlanet = true;
		$UsedPlanet = true;
	} elseif (!empty($select['id_owner'])) {
		$YourPlanet = false;
		$UsedPlanet = true;
	} else {
		$YourPlanet = false;
		$UsedPlanet = false;
	}

	// Determinons les type de missions possibles par rapport a la planete cible
	if ($fleetmission == 15) {
		// Gestion des Expéditions
		$missiontype = array(15 => $lang['type_mission'][15]);
	} else {
		if (($_POST['planettype'] ?? null) == "2") {
			if (($_POST['ship209'] ?? null) >= 1) {
				$missiontype = array(8 => $lang['type_mission'][8]);
			} else {
				$missiontype = array();
			}
		} elseif (($_POST['planettype'] ?? null) == "1" || ($_POST['planettype'] ?? null) == "3") {
			if (($_POST['ship208'] ?? null) >= 1 && !$UsedPlanet) {
				$missiontype = array(7 => $lang['type_mission'][7]);
			} elseif (($_POST['ship210'] ?? null) >= 1 && !$YourPlanet) {
				$missiontype = array(6 => $lang['type_mission'][6]);
			}

			if (($_POST['ship202'] ?? null) >= 1 ||
				($_POST['ship203'] ?? null) >= 1 ||
				($_POST['ship204'] ?? null) >= 1 ||
				($_POST['ship205'] ?? null) >= 1 ||
				($_POST['ship206'] ?? null) >= 1 ||
				($_POST['ship207'] ?? null) >= 1 ||
				($_POST['ship210'] ?? null) >= 1 ||
				($_POST['ship211'] ?? null) >= 1 ||
				($_POST['ship213'] ?? null) >= 1 ||
				($_POST['ship214'] ?? null) >= 1 ||
				($_POST['ship215'] ?? null) >= 1) {
				if (!$YourPlanet) {
					$missiontype[1] = $lang['type_mission'][1];
				}
				$missiontype[3] = $lang['type_mission'][3];
				$missiontype[5] = $lang['type_mission'][5];
			}
			// Recycleurs et vaisseaux de colonisation peuvent aussi transporter (regle d'origine jamais atteinte : elle etait hors de ce bloc)
			if (($_POST['ship208'] ?? null) >= 1 || ($_POST['ship209'] ?? null) >= 1) {
				$missiontype[3] = $lang['type_mission'][3];
			}
		}
		if ($YourPlanet)
			$missiontype[4] = $lang['type_mission'][4];

		if ( ($_POST['planettype'] ?? null) == 3 &&
			(($_POST['ship214'] ?? null)         ||
			 ($_POST['ship213'] ?? null))        &&
			 !$YourPlanet              &&
			 $UsedPlanet) {
			$missiontype[2] = $lang['type_mission'][2];
		}
        if ( ($_POST['planettype'] ?? null) == 3 &&
	     ($_POST['ship214'] ?? null) >= 1    &&
           !$YourPlanet            &&
           $UsedPlanet) {
          $missiontype[9] = $lang['type_mission'][9];
        }
	}

	if (empty($missiontype[$fleetmission])) {
		message ("<font color=\"red\"><b>". $lang['fl_bad_mission'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	CheckPlanetUsedFields($CurrentPlanet);

	if (empty($TargetPlanet['id_owner'])) {
		$HeDBRec = $MyDBRec;
	} elseif ($TargetPlanet['id_owner'] != '') {
		$HeDBRec = doquery("SELECT * FROM {{table}} WHERE `id` = '". $TargetPlanet['id_owner'] ."';", 'users', true);
	}

	$UserPoints    = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '". $MyDBRec['id'] ."';", 'statpoints', true);
	$User2Points   = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '". $HeDBRec['id'] ."';", 'statpoints', true);

	$MyGameLevel  = $UserPoints['total_points'] ?? 0;
	$HeGameLevel  = $User2Points['total_points'] ?? 0;
	$VacationMode = $HeDBRec['urlaubs_modus'];

	if ($MyGameLevel > ($HeGameLevel * $protectionmulti) AND
		$TargetPlanet['id_owner'] != '' AND
		($_POST['mission'] ?? null)     == 1  AND
		$protection           == 1  AND
		$HeGameLevel < ($protectiontime * 1000)) {
		message("<font color=\"lime\"><b>".$lang['fl_noob_mess_n']."</b></font>", $lang['fl_noob_title'], "fleet." . $phpEx, 2);
	}

	if ($MyGameLevel > ($HeGameLevel * $protectionmulti) AND
		$TargetPlanet['id_owner'] != '' AND
		($_POST['mission'] ?? null)     == 5  AND
		$protection           == 1  AND
		$HeGameLevel < ($protectiontime * 1000)) {
		message("<font color=\"lime\"><b>".$lang['fl_noob_mess_n']."</b></font>", $lang['fl_noob_title'], "fleet." . $phpEx, 2);
	}

	if ($MyGameLevel > ($HeGameLevel * $protectionmulti) AND
		$TargetPlanet['id_owner'] != '' AND
		($_POST['mission'] ?? null)     == 6  AND
		$protection           == 1  AND
		$HeGameLevel < ($protectiontime * 1000)) {
		message("<font color=\"lime\"><b>".$lang['fl_noob_mess_n']."</b></font>", $lang['fl_noob_title'], "fleet." . $phpEx, 2);
	}

	if (($MyGameLevel * $protectionmulti) < $HeGameLevel AND
		$TargetPlanet['id_owner'] != '' AND
		($_POST['mission'] ?? null)     == 1  AND
		$protection           == 1  AND
		$MyGameLevel < ($protectiontime * 1000)) {
		message("<font color=\"lime\"><b>".$lang['fl_noob_mess_n']."</b></font>", $lang['fl_noob_title'], "fleet." . $phpEx, 2);
	}

	if (($MyGameLevel * $protectionmulti) < $HeGameLevel AND
		$TargetPlanet['id_owner'] != '' AND
		($_POST['mission'] ?? null)     == 5  AND
		$protection           == 1  AND
		$MyGameLevel < ($protectiontime * 1000)) {
		message("<font color=\"lime\"><b>".$lang['fl_noob_mess_n']."</b></font>", $lang['fl_noob_title'], "fleet." . $phpEx, 2);
	}

	if (($MyGameLevel * $protectionmulti) < $HeGameLevel AND
		$TargetPlanet['id_owner'] != '' AND
		($_POST['mission'] ?? null)     == 6  AND
		$protection           == 1  AND
		$MyGameLevel < ($protectiontime * 1000)) {
		message("<font color=\"lime\"><b>".$lang['fl_noob_mess_n']."</b></font>", $lang['fl_noob_title'], "fleet." . $phpEx, 2);
	}

	if ($VacationMode AND ($_POST['mission'] ?? null) != 8) {
		message("<font color=\"lime\"><b>".$lang['fl_vacation_pla']."</b></font>", $lang['fl_vacation_ttl'], "fleet." . $phpEx, 2);
	}

	$FlyingFleets = mysqli_fetch_assoc(doquery("SELECT COUNT(fleet_id) as Number FROM {{table}} WHERE `fleet_owner`='{$user['id']}'", 'fleets'));
	$ActualFleets = $FlyingFleets["Number"];
	if (($user[$resource[108]] + 1) <= $ActualFleets) {
		message("Pas de slot disponible", "Erreur", "fleet." . $phpEx, 1);
	}

	if (($_POST['resource1'] ?? null) + ($_POST['resource2'] ?? null) + ($_POST['resource3'] ?? null) < 1 AND ($_POST['mission'] ?? null) == 3) {
		message("<font color=\"lime\"><b>".$lang['fl_noenoughtgoods']."</b></font>", $lang['type_mission'][3], "fleet." . $phpEx, 1);
	}
	if (($_POST['mission'] ?? null) != 15) {
		if ($TargetPlanet['id_owner'] == '' AND ($_POST['mission'] ?? null) < 7) {
			message ("<font color=\"red\"><b>". $lang['fl_bad_planet01'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
		if ($TargetPlanet['id_owner'] != '' AND ($_POST['mission'] ?? null) == 7) {
			message ("<font color=\"red\"><b>". $lang['fl_bad_planet02'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
		if ($HeDBRec['ally_id'] != $MyDBRec['ally_id'] AND ($_POST['mission'] ?? null) == 4) {
			message ("<font color=\"red\"><b>". $lang['fl_dont_stay_here'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
		if ($TargetPlanet['ally_deposit'] < 1 AND $HeDBRec != $MyDBRec AND ($_POST['mission'] ?? null) == 5) {
			message ("<font color=\"red\"><b>". $lang['fl_no_allydeposit'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
		if (($TargetPlanet["id_owner"] == $CurrentPlanet["id_owner"]) AND (($_POST["mission"] ?? null) == 1)) {
			message ("<font color=\"red\"><b>". $lang['fl_no_self_attack'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
		if (($TargetPlanet["id_owner"] == $CurrentPlanet["id_owner"]) AND (($_POST["mission"] ?? null) == 6)) {
			message ("<font color=\"red\"><b>". $lang['fl_no_self_spy'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
		if (($TargetPlanet["id_owner"] != $CurrentPlanet["id_owner"]) AND (($_POST["mission"] ?? null) == 4)) {
			message ("<font color=\"red\"><b>". $lang['fl_only_stay_at_home'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		}
	}

	$missiontype = array(
		1 => $lang['type_mission'][1],
		2 => $lang['type_mission'][2],
		3 => $lang['type_mission'][3],
		4 => $lang['type_mission'][4],
		5 => $lang['type_mission'][5],
		6 => $lang['type_mission'][6],
		7 => $lang['type_mission'][7],
		8 => $lang['type_mission'][8],
		9 => $lang['type_mission'][9],
		15 => $lang['type_mission'][15],
		);

	$speed_possible = array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1);

	$AllFleetSpeed  = GetFleetMaxSpeed ($fleetarray, 0, $user);
	$GenFleetSpeed  = ($_POST['speed'] ?? null);
	$SpeedFactor    = GetGameSpeedFactor (); // valeur du serveur (avant : lue dans le formulaire, falsifiable)
	$MaxFleetSpeed  = min($AllFleetSpeed);

	if (!in_array($GenFleetSpeed, $speed_possible)) {
		message ("<font color=\"red\"><b>". $lang['fl_cheat_speed'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	$CurrentPlanet = doquery("SELECT * FROM {{table}} WHERE `id` = '".$user['current_planet']."';", 'planets', true);

	if ($MaxFleetSpeed != ($_POST['speedallsmin'] ?? null)) {
		message ("<font color=\"red\"><b>". $lang['fl_cheat_speed'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	if (!($_POST['planettype'] ?? null)) {
		message ("<font color=\"red\"><b>". $lang['fl_no_planet_type'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	// Test de coherance de la destination (voir si elle se trouve dans les limites de l'univers connu
	$error     = 0;
	$errorlist = "";
	if (!($_POST['galaxy'] ?? null) || !is_numeric(($_POST['galaxy'] ?? null)) || ($_POST['galaxy'] ?? null) > 9 || ($_POST['galaxy'] ?? null) < 1) {
		$error++;
		$errorlist .= $lang['fl_limit_galaxy'];
	}
	if (!($_POST['system'] ?? null) || !is_numeric(($_POST['system'] ?? null)) || ($_POST['system'] ?? null) > 499 || ($_POST['system'] ?? null) < 1) {
		$error++;
		$errorlist .= $lang['fl_limit_system'];
	}
	if (!($_POST['planet'] ?? null) || !is_numeric(($_POST['planet'] ?? null)) || ($_POST['planet'] ?? null) > 16 || ($_POST['planet'] ?? null) < 1) {
		$error++;
		$errorlist .= $lang['fl_limit_planet'];
	}

	if ($error > 0) {
		message ("<font color=\"red\"><ul>" . $errorlist . "</ul></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	// La flotte part bien de la planete courrante ??
	if (($_POST['thisgalaxy'] ?? null) != $CurrentPlanet['galaxy'] |
		($_POST['thissystem'] ?? null) != $CurrentPlanet['system'] |
		($_POST['thisplanet'] ?? null) != $CurrentPlanet['planet'] |
		($_POST['thisplanettype'] ?? null) != $CurrentPlanet['planet_type']) {
		message ("<font color=\"red\"><b>". $lang['fl_cheat_origine'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	if (!isset($fleetarray)) {
		message ("<font color=\"red\"><b>". $lang['fl_no_fleetarray'] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	$distance      = GetTargetDistance ( ($_POST['thisgalaxy'] ?? null), ($_POST['galaxy'] ?? null), ($_POST['thissystem'] ?? null), ($_POST['system'] ?? null), ($_POST['thisplanet'] ?? null), ($_POST['planet'] ?? null) );
	$duration      = GetMissionDuration ( $GenFleetSpeed, $MaxFleetSpeed, $distance, $SpeedFactor );
	$consumption   = GetFleetConsumption ( $fleetarray, $SpeedFactor, $duration, $distance, $MaxFleetSpeed, $user );

	$fleet['start_time'] = $duration + time();
	// Durees limitees aux choix du formulaire (avant : n'importe quelle valeur, meme negative)
	$ExpeHours = in_array(intval($_POST['expeditiontime'] ?? 0), array(1, 2)) ? intval($_POST['expeditiontime'] ?? 0) : 1;
	$HoldHours = in_array(intval($_POST['holdingtime'] ?? 0), array(0, 1, 2, 4, 8, 16, 32)) ? intval($_POST['holdingtime'] ?? 0) : 0;
	if (($_POST['mission'] ?? null) == 15) {
		$StayDuration    = $ExpeHours * 3600;
		$StayTime        = $fleet['start_time'] + $ExpeHours * 3600;
	} elseif (($_POST['mission'] ?? null) == 5) {
		$StayDuration    = $HoldHours * 3600;
		$StayTime        = $fleet['start_time'] + $HoldHours * 3600;
	} else {
		$StayDuration    = 0;
		$StayTime        = 0;
	}
	$fleet['end_time']   = $StayDuration + (2 * $duration) + time();
	$FleetStorage        = 0;
	$FleetShipCount      = 0;
	$fleet_array         = "";
	$FleetSubQRY         = "";

	foreach ($fleetarray as $Ship => $Count) {
		$FleetStorage    += $pricelist[$Ship]["capacity"] * $Count;
		$FleetShipCount  += $Count;
		$fleet_array     .= $Ship .",". $Count .";";
		$FleetSubQRY     .= "`".$resource[$Ship] . "` = `" . $resource[$Ship] . "` - " . $Count . " , ";
	}

	$FleetStorage        -= $consumption;
	$StorageNeeded        = 0;
	if (($_POST['resource1'] ?? null) < 1) {
		$TransMetal      = 0;
	} else {
		$TransMetal      = ($_POST['resource1'] ?? null);
		$StorageNeeded  += $TransMetal;
	}
	if (($_POST['resource2'] ?? null) < 1) {
		$TransCrystal    = 0;
	} else {
		$TransCrystal    = ($_POST['resource2'] ?? null);
		$StorageNeeded  += $TransCrystal;
	}
	if (($_POST['resource3'] ?? null) < 1) {
		$TransDeuterium  = 0;
	} else {
		$TransDeuterium  = ($_POST['resource3'] ?? null);
		$StorageNeeded  += $TransDeuterium;
	}

	$StockMetal      = $CurrentPlanet['metal'];
	$StockCrystal    = $CurrentPlanet['crystal'];
	$StockDeuterium  = $CurrentPlanet['deuterium'];
	$StockDeuterium -= $consumption;

	$StockOk         = false;
	if ($StockMetal >= $TransMetal) {
		if ($StockCrystal >= $TransCrystal) {
			if ($StockDeuterium >= $TransDeuterium) {
				$StockOk         = true;
			}
		}
	}
	if ( !$StockOk ) {
		message ("<font color=\"red\"><b>". $lang['fl_noressources'] . pretty_number($consumption) ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	if ( $StorageNeeded > $FleetStorage) {
		message ("<font color=\"red\"><b>". $lang['fl_nostoragespa'] . pretty_number($StorageNeeded - $FleetStorage) ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
	}

	if (($TargetPlanet['id_level'] ?? 0) > $user['authlevel']) {
		$Allowed = true;
		switch (($_POST['mission'] ?? null)){
			case 1:
			case 2:
			case 6:
			case 9:
				$Allowed = false;
				break;
			case 3:
			case 4:
			case 5:
			case 7:
			case 8:
			case 15:
				break;
			default:
		}
		if ($Allowed == false) {
			message ("<font color=\"red\"><b>". $lang['fl_adm_attak'] ."</b></font>", $lang['fl_warning'], "fleet." . $phpEx, 2);
		}
	}

	// ecriture de l'enregistrement de flotte (a partir de là, y a quelque chose qui vole et c'est toujours sur la planete d'origine)
	$QryInsertFleet  = "INSERT INTO {{table}} SET ";
	$QryInsertFleet .= "`fleet_owner` = '". $user['id'] ."', ";
	$QryInsertFleet .= "`fleet_mission` = '". ($_POST['mission'] ?? null) ."', ";
	$QryInsertFleet .= "`fleet_amount` = '". $FleetShipCount ."', ";
	$QryInsertFleet .= "`fleet_array` = '". $fleet_array ."', ";
	$QryInsertFleet .= "`fleet_start_time` = '". $fleet['start_time'] ."', ";
	$QryInsertFleet .= "`fleet_start_galaxy` = '". intval(($_POST['thisgalaxy'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_start_system` = '". intval(($_POST['thissystem'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_start_planet` = '". intval(($_POST['thisplanet'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_start_type` = '". intval(($_POST['thisplanettype'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_end_time` = '". $fleet['end_time'] ."', ";
	$QryInsertFleet .= "`fleet_end_stay` = '". $StayTime ."', ";
	$QryInsertFleet .= "`fleet_end_galaxy` = '". intval(($_POST['galaxy'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_end_system` = '". intval(($_POST['system'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_end_planet` = '". intval(($_POST['planet'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_end_type` = '". intval(($_POST['planettype'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_resource_metal` = '". $TransMetal ."', ";
	$QryInsertFleet .= "`fleet_resource_crystal` = '". $TransCrystal ."', ";
	$QryInsertFleet .= "`fleet_resource_deuterium` = '". $TransDeuterium ."', ";
	$QryInsertFleet .= "`fleet_target_owner` = '". intval($TargetPlanet['id_owner'] ?? 0) ."', ";
	$QryInsertFleet .= "`start_time` = '". time() ."';";
	doquery( $QryInsertFleet, 'fleets');


	$CurrentPlanet["metal"]     = $CurrentPlanet["metal"] - $TransMetal;
	$CurrentPlanet["crystal"]   = $CurrentPlanet["crystal"] - $TransCrystal;
	$CurrentPlanet["deuterium"] = $CurrentPlanet["deuterium"] - $TransDeuterium;
	$CurrentPlanet["deuterium"] = $CurrentPlanet["deuterium"] - $consumption;

	$QryUpdatePlanet  = "UPDATE {{table}} SET ";
	$QryUpdatePlanet .= $FleetSubQRY;
	$QryUpdatePlanet .= "`metal` = '". $CurrentPlanet["metal"] ."', ";
	$QryUpdatePlanet .= "`crystal` = '". $CurrentPlanet["crystal"] ."', ";
	$QryUpdatePlanet .= "`deuterium` = '". $CurrentPlanet["deuterium"] ."' ";
	$QryUpdatePlanet .= "WHERE ";
	$QryUpdatePlanet .= "`id` = '". $CurrentPlanet['id'] ."'";

	// Mise a jours de l'enregistrement de la planete de depart (a partir de là, y a quelque chose qui vole et ce n'est plus sur la planete de depart)
	doquery("LOCK TABLE {{table}} WRITE", 'planets');
	doquery ($QryUpdatePlanet, "planets");
	doquery("UNLOCK TABLES", '');
//	doquery("FLUSH TABLES", '');

	// Un peu de blabla pour l'utilisateur, affichage d'un joli tableau de la flotte expédiée
	$page  = "<br><div><center>";
	$page .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"519\">";
	$page .= "<tr height=\"20\">";
	$page .= "<td class=\"c\" colspan=\"2\"><span class=\"success\">". $lang['fl_fleet_send'] ."</span></td>";
	$page .= "</tr><tr height=\"20\">";
	$page .= "<th>". $lang['fl_mission'] ."</th>";
	$page .= "<th>". $missiontype[($_POST['mission'] ?? null)] ."</th>";
	$page .= "</tr><tr height=\"20\">";
	$page .= "<th>". $lang['fl_dist'] ."</th>";
	$page .= "<th>". pretty_number($distance) ."</th>";
	$page .= "</tr><tr height=\"20\">";
	$page .= "<th>". $lang['fl_speed'] ."</th>";
	$page .= "<th>". pretty_number(($_POST['speedallsmin'] ?? null)) ."</th>";
	$page .= "</tr><tr height=\"20\">";
	$page .= "<th>". $lang['fl_deute_need'] ."</th>";
	$page .= "<th>". pretty_number($consumption) ."</th>";
	$page .= "</tr><tr height=\"20\">";
	$page .= "<th>". $lang['fl_from'] ."</th>";
	$page .= "<th>". ($_POST['thisgalaxy'] ?? null) .":". ($_POST['thissystem'] ?? null). ":". ($_POST['thisplanet'] ?? null) ."</th>";
	$page .= "</tr><tr height=\"20\">";
	$page .= "<th>". $lang['fl_dest'] ."</th>";
	$page .= "<th>". ($_POST['galaxy'] ?? null) .":". ($_POST['system'] ?? null) .":". ($_POST['planet'] ?? null) ."</th>";
	$page .= "</tr><tr height=\"20\">";
	$page .= "<th>". $lang['fl_time_go'] ."</th>";
	$page .= "<th>". date("d/m/Y H:i:s", $fleet['start_time']) ."</th>";
	$page .= "</tr><tr height=\"20\">";
	$page .= "<th>". $lang['fl_time_back'] ."</th>";
	$page .= "<th>". date("d/m/Y H:i:s", $fleet['end_time']) ."</th>";
	$page .= "</tr><tr height=\"20\">";
	$page .= "<td class=\"c\" colspan=\"2\">". $lang['fl_title'] ."</td>";

	foreach ($fleetarray as $Ship => $Count) {
		$page .= "</tr><tr height=\"20\">";
		$page .= "<th>". $lang['tech'][$Ship] ."</th>";
		$page .= "<th>". pretty_number($Count) ."</th>";
	}
	$page .= "</tr></table></div></center>";

	// Provisoire
	sleep (1);

	$planetrow = doquery ("SELECT * FROM {{table}} WHERE `id` = '". $CurrentPlanet['id'] ."';", 'planets', true);

	display($page, $lang['fl_title']);

// Updated by Chlorel. 16 Jan 2008 (String extraction, bug corrections, code uniformisation
// Updated by -= MoF =- for Deutsches Ugamela Forum
// 06.12.2007 - 08:39
// Open Source
// (c) by MoF

?>