<?php

/**
 * RemoveBuildingFromQueue.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 * @license GNU GPL v2
 */

function RemoveBuildingFromQueue ( &$CurrentPlanet, $CurrentUser, $QueueID ) {

	if ($QueueID > 1) {
		$CurrentQueue  = $CurrentPlanet['b_building_id'];
		if ($CurrentQueue != 0) {
			$QueueArray    = explode ( ";", $CurrentQueue );
			$ActualCount   = count ( $QueueArray );
			$ListIDArray   = explode ( ",", $QueueArray[$QueueID - 2] );
			$BuildEndTime  = $ListIDArray[3];
			for ($ID = $QueueID; $ID < $ActualCount; $ID++ ) {
				$ListIDArray          = explode ( ",", $QueueArray[$ID] );
				$BuildEndTime        += $ListIDArray[2];
				$ListIDArray[3]       = $BuildEndTime;
				$QueueArray[$ID - 1]  = implode ( ",", $ListIDArray );
			}
			unset ($QueueArray[$ActualCount - 1]);
			$NewQueue     = implode ( ";", $QueueArray );
		}
		$CurrentPlanet['b_building_id'] = $NewQueue;
	}

	return $QueueID;

}
?>