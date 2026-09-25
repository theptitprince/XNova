<?php

/**
 * flotenajax.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

// Champs numeriques convertis en entiers (securite + PHP 8)
SanitizeNumericInput ( array('mission', 'galaxy', 'system', 'planet', 'planettype', 'planet_type', 'thisgalaxy', 'thissystem', 'thisplanet',
	                           'thisplanettype', 'resource1', 'resource2', 'resource3', 'holdingtime', 'expeditiontime',
	                           'speed', 'speedfactor', 'speedallsmin', 'maxepedition', 'curepedition', 'target_mission', 'fleetid'), '/^ship[0-9]+$/' );

	includeLang('galaxy');
	includeLang('fleet');

	$UserSpyProbes  = $planetrow['spy_sonde'];
	$UserRecycles   = $planetrow['recycler'];
	$UserDeuterium  = $planetrow['deuterium'];
	$UserMissiles   = $planetrow['interplanetary_misil'];

	// Compte des flottes en vol : calcule avant les premiers messages d'erreur, qui le renvoient aussi
	// (sinon la galaxie affichait « /1 Slots » au lieu de « 0/1 »)
	$CurrentFlyingFleets = doquery("SELECT COUNT(fleet_id) AS `Nbre` FROM {{table}} WHERE `fleet_owner` = '".$user['id']."';", 'fleets', true);
	$CurrentFlyingFleets = intval($CurrentFlyingFleets["Nbre"]);

	// Pas d'envoi en mode vacances (espionnage, recyclage depuis la galaxie)
	if ($user['urlaubs_modus'] == 1) {
		die ( "620;".$lang['gs_c620']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles );
	}

	$fleet          = array('fleetarray' => array(), 'fleetlist' => '', 'amount' => 0);
	$speedalls      = array();
	$PartialFleet   = false; // 610
	$PartialCount   = 0;

	foreach ($reslist['fleet'] as $Node => $ShipID) {
		$TName = "ship".$ShipID;
		if ($ShipID > 200 && $ShipID < 300 && ($_POST[$TName] ?? null) > 0) {
			if (($_POST[$TName] ?? null) > $planetrow[$resource[$ShipID]]) {
				$fleet['fleetarray'][$ShipID]   = $planetrow[$resource[$ShipID]];
				$fleet['fleetlist']            .= $ShipID .",". $planetrow[$resource[$ShipID]] .";";
				$fleet['amount']               += $planetrow[$resource[$ShipID]];
				$PartialCount                  += $planetrow[$resource[$ShipID]];
				$PartialFleet                   = true;
			} else {
				$fleet['fleetarray'][$ShipID]   = ($_POST[$TName] ?? null);
				$fleet['fleetlist']            .= $ShipID .",". ($_POST[$TName] ?? null) .";";
				$fleet['amount']               += ($_POST[$TName] ?? null);
				$speedalls[$ShipID]             = ($_POST[$TName] ?? null);
			}
		}
	}

	if ($PartialFleet == true) {
		if ( $PartialCount < 1 ) {
			$ResultMessage = "610;".$lang['gs_c610a']. $PartialCount .$lang['gs_c610b']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
			die ( $ResultMessage );
		}
	}

	$PrNoob      = $game_config['noobprotection'];
	$PrNoobTime  = $game_config['noobprotectiontime'];
	$PrNoobMulti = $game_config['noobprotectionmulti'];

	// Petit Test de coherance
	$galaxy          = intval(($_POST['galaxy'] ?? null));
	if ($galaxy > 9 || $galaxy < 1) {
		$ResultMessage = "602;".$lang['gs_c602']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	$system = intval(($_POST['system'] ?? null));
	if ($system > 499 || $system < 1) {
		$ResultMessage = "602;".$lang['gs_c602']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	$planet = intval(($_POST['planet'] ?? null));
	if ($planet > 15 || $planet < 1) {
		$ResultMessage = "602;".$lang['gs_c602']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	$FleetArray = $fleet['fleetarray'];

	$QrySelectEnemy  = "SELECT * FROM {{table}} ";
	$QrySelectEnemy .= "WHERE ";
	$QrySelectEnemy .= "`galaxy` = '". ($_POST['galaxy'] ?? null) ."' AND ";
	$QrySelectEnemy .= "`system` = '". ($_POST['system'] ?? null) ."' AND ";
	$QrySelectEnemy .= "`planet` = '". ($_POST['planet'] ?? null) ."' AND ";
	$QrySelectEnemy .= "`planet_type` = '". ($_POST['planettype'] ?? null) ."';";
	$TargetRow = doquery( $QrySelectEnemy, 'planets', true);

	if       ($TargetRow['id_owner'] == '') {
		$TargetUser = $user;
	} elseif ($TargetRow['id_owner'] != '') {
		$TargetUser = doquery("SELECT * FROM {{table}} WHERE `id` = '". $TargetRow['id_owner'] ."';", 'users', true);
	}
	$UserPoints    = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '". $user['id'] ."';", 'statpoints', true);
	$User2Points   = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '". $TargetUser['id'] ."';", 'statpoints', true);

	$CurrentPoints = $UserPoints['total_points'];
	$TargetPoints  = $User2Points['total_points'];
	$TargetVacat   = $TargetUser['urlaubs_modus'];

	// Test s'il y a un slot de libre au moins !
	if (($user[$resource[108]] + 1) <= $CurrentFlyingFleets) {
		$ResultMessage = "612;".$lang['gs_c612']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	// Y a une flotte dans la variable ??
	if (!is_array($FleetArray)) {
		$ResultMessage = "618;".$lang['gs_c618']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	// Faut pas deconner non plus ... c'est Espionnage OU Recyclage .... Pour le café vous repasserez !!
	if (! ((($_POST["mission"] ?? null) == 6) OR
		   (($_POST["mission"] ?? null) == 8)) ) {
		$ResultMessage = "618;".$lang['gs_c618']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	// On teste une derniere fois s'il nous reste des billes ...
	foreach ($FleetArray as $Ships => $Count) {
		if ($Count > $planetrow[$resource[$Ships]]) {
			$ResultMessage = "611;".$lang['gs_c611']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
			die ( $ResultMessage );
		}
	}

	if ($PrNoobTime < 1) {
		$PrNoobTime = 9999999999999999;
	}

	if ($TargetVacat && ($_POST['mission'] ?? null) != 8) {
		$ResultMessage = "605;".$lang['gs_c605']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	if ($CurrentPoints          > ($TargetPoints * $PrNoobMulti) AND
		$TargetRow['id_owner'] != '' AND
		($_POST['mission'] ?? null)      == 6  AND
		$PrNoob                == 1  AND
		$TargetPoints           < ($PrNoobTime * 1000)) {
		$ResultMessage = "603;".$lang['gs_c603']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	if ($TargetPoints           > ($CurrentPoints * $PrNoobMulti) AND
		$TargetRow['id_owner'] != '' AND
		($_POST['mission'] ?? null)      == 6  AND
		$PrNoob                == 1  AND
		$CurrentPoints          < ($PrNoobTime * 1000)) {
		$ResultMessage = "604;".$lang['gs_c604']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	if ($TargetRow['id_owner'] == '' AND
		($_POST['mission'] ?? null)      != 8 ) {
		$ResultMessage = "601;".$lang['gs_c601']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	if (($TargetRow["id_owner"] == $planetrow["id_owner"]) AND
		(($_POST["mission"] ?? null) == 6)) {
		$ResultMessage = "618;".$lang['gs_c618']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	if (($_POST['thisgalaxy'] ?? null) != $planetrow['galaxy'] |
		($_POST['thissystem'] ?? null) != $planetrow['system'] |
		($_POST['thisplanet'] ?? null) != $planetrow['planet'] |
		($_POST['thisplanettype'] ?? null) != $planetrow['planet_type']) {
		$ResultMessage = "618;".$lang['gs_c618']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
		die ( $ResultMessage );
	}

	$Distance    = GetTargetDistance (($_POST['thisgalaxy'] ?? null), ($_POST['galaxy'] ?? null), ($_POST['thissystem'] ?? null), ($_POST['system'] ?? null), ($_POST['thisplanet'] ?? null), ($_POST['planet'] ?? null));
	$speedall    = GetFleetMaxSpeed ($FleetArray, 0, $user);
	$SpeedAllMin = min($speedall);
	$Duration    = GetMissionDuration ( 10, $SpeedAllMin, $Distance, GetGameSpeedFactor ());

	$fleet['fly_time']   = $Duration;
	$fleet['start_time'] = $Duration + time();
	$fleet['end_time']   = ($Duration * 2) + time();

	$FleetShipCount      = 0;
	$FleetDBArray        = "";
	$FleetSubQRY         = "";
	$consumption         = 0;
	$SpeedFactor         = GetGameSpeedFactor ();
	foreach ($FleetArray as $Ship => $Count) {
		$ShipSpeed        = $pricelist[$Ship]["speed"];
		$spd              = 35000 / ($Duration * $SpeedFactor - 10) * sqrt($Distance * 10 / $ShipSpeed);
		$basicConsumption = $pricelist[$Ship]["consumption"] * $Count ;
		$consumption     += $basicConsumption * $Distance / 35000 * (($spd / 10) + 1) * (($spd / 10) + 1);
		$FleetShipCount  += $Count;
		$FleetDBArray    .= $Ship .",". $Count .";";
		$FleetSubQRY     .= "`".$resource[$Ship] . "` = `" . $resource[$Ship] . "` - " . $Count . " , ";
	}
	$consumption = round($consumption) + 1;

	if ($TargetRow['id_level'] > $user['authlevel']) {
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
			$ResultMessage = "619;".$lang['gs_c619']."|".$CurrentFlyingFleets." ".$UserSpyProbes." ".$UserRecycles." ".$UserMissiles;
			die ( $ResultMessage );
		}
	}

	$QryInsertFleet  = "INSERT INTO {{table}} SET ";
	$QryInsertFleet .= "`fleet_owner` = '". $user['id'] ."', ";
	$QryInsertFleet .= "`fleet_mission` = '". intval(($_POST['mission'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_amount` = '". $FleetShipCount ."', ";
	$QryInsertFleet .= "`fleet_array` = '". $FleetDBArray ."', ";
	$QryInsertFleet .= "`fleet_start_time` = '". $fleet['start_time']. "', ";
	$QryInsertFleet .= "`fleet_start_galaxy` = '". intval(($_POST['thisgalaxy'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_start_system` = '". intval(($_POST['thissystem'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_start_planet` = '". intval(($_POST['thisplanet'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_start_type` = '". intval(($_POST['thisplanettype'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_end_time` = '". $fleet['end_time'] ."', ";
	$QryInsertFleet .= "`fleet_end_galaxy` = '". intval(($_POST['galaxy'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_end_system` = '". intval(($_POST['system'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_end_planet` = '". intval(($_POST['planet'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_end_type` = '". intval(($_POST['planettype'] ?? null)) ."', ";
	$QryInsertFleet .= "`fleet_target_owner` = '". $TargetRow['id_owner'] ."', ";
	$QryInsertFleet .= "`start_time` = '" . time() . "';";
	doquery( $QryInsertFleet, 'fleets');

	$UserDeuterium   -= $consumption;
	$QryUpdatePlanet  = "UPDATE {{table}} SET ";
	$QryUpdatePlanet .= $FleetSubQRY;
	$QryUpdatePlanet .= "`deuterium` = '".$UserDeuterium."' " ;
	$QryUpdatePlanet .= "WHERE ";
	$QryUpdatePlanet .= "`id` = '". $planetrow['id'] ."';";
	doquery( $QryUpdatePlanet, 'planets');

	$CurrentFlyingFleets++;

	$planetrow = doquery("SELECT * FROM {{table}} WHERE `id` = '". $user['current_planet'] ."';", 'planets', true);
	$ResultMessage  = "600;". $lang['gs_sending'] ." ". $FleetShipCount  ." ". $lang['tech'][$Ship] ." ". $lang['gs_to'] ." ". ($_POST['galaxy'] ?? null) .":". ($_POST['system'] ?? null) .":". ($_POST['planet'] ?? null) ."...|";
	// Compteurs apres l'envoi (avant : les valeurs lues avant l'envoi, la galaxie affichait l'ancien nombre)
	$ResultMessage .= $CurrentFlyingFleets ." ".$planetrow['spy_sonde']." ".$planetrow['recycler']." ".$planetrow['interplanetary_misil'];

	die ( $ResultMessage );
?>