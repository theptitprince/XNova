<?php

/**
 * fleetback.php
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

	includeLang('fleet');

	$BoxTitle   = $lang['fl_error'];
	$TxtColor   = "red";
	$BoxMessage = $lang['fl_notback'];
	if ( is_numeric(($_POST['fleetid'] ?? null)) ) {
		$fleetid  = intval(($_POST['fleetid'] ?? null));

		$FleetRow = doquery("SELECT * FROM {{table}} WHERE `fleet_id` = '". $fleetid ."';", 'fleets', true);
		$i = 0;

		if (!empty($FleetRow) && $FleetRow['fleet_owner'] == $user['id']) {
			// Encore rappelable (0.9k) : en route et pas encore arrivee (une expedition : jusqu'a la fin de son
			// exploration), ou en stationnement chez un allie jusqu'a sa fin. A la seconde meme de l'arrivee, la
			// flotte etait encore rappelable (common.php la voit arrivee, son traitement attend la seconde suivante).
			$Now       = time();
			$Recalling = ($FleetRow['fleet_mess'] == 0 && ($FleetRow['fleet_start_time'] > $Now || ($FleetRow['fleet_mission'] == 15 && $FleetRow['fleet_end_stay'] > $Now)))
			          || ($FleetRow['fleet_mess'] == 2 && $FleetRow['fleet_end_stay'] > $Now); // 2 : stationnement (0.9i)
			if ($Recalling) {
				// Temps deja vole (conditions inversees dans l'original : voir FleetRecallFlyingTime)
				$CurrentFlyingTime = FleetRecallFlyingTime( $FleetRow );
				// Allez houste au bout du compte y a la maison !! (E.T. phone home.............)
				$ReturnFlyingTime  = $CurrentFlyingTime + time();

				$QryUpdateFleet  = "UPDATE {{table}} SET ";
				$QryUpdateFleet .= "`fleet_start_time` = '". (time() - 1) ."', ";
				$QryUpdateFleet .= "`fleet_end_stay` = '0', ";
				$QryUpdateFleet .= "`fleet_end_time` = '". ($ReturnFlyingTime + 1) ."', ";
				$QryUpdateFleet .= "`fleet_target_owner` = '". $user['id'] ."', ";
				$QryUpdateFleet .= "`fleet_group` = '0', ";
				$QryUpdateFleet .= "`fleet_mess` = '1' ";
				$QryUpdateFleet .= "WHERE ";
				$QryUpdateFleet .= "`fleet_id` = '" . $fleetid . "' ";
				// Seulement si la flotte n'a pas change depuis sa lecture (0.9k) : son arrivee a pu etre traitee
				// entre-temps par une autre page (combat, livraison, fin du stationnement)
				$QryUpdateFleet .= "AND `fleet_owner` = '". intval($user['id']) ."' ";
				$QryUpdateFleet .= "AND `fleet_mess` = '". intval($FleetRow['fleet_mess']) ."' ";
				$QryUpdateFleet .= "AND `fleet_start_time` = '". intval($FleetRow['fleet_start_time']) ."' ";
				$QryUpdateFleet .= "AND `fleet_end_stay` = '". intval($FleetRow['fleet_end_stay']) ."' ";
				$QryUpdateFleet .= "AND `fleet_end_time` = '". intval($FleetRow['fleet_end_time']) ."';";
				doquery( $QryUpdateFleet, 'fleets');
				if (mysqli_affected_rows(DbConnect()) == 1) {
					// Attaque groupee (0.9i) : la flotte rappelee quitte son groupe, les autres continuent ; groupe vide supprime
					if ($FleetRow['fleet_group'] > 0) {
						AcsDeleteIfEmpty($FleetRow['fleet_group']);
					}

					$BoxTitle   = $lang['fl_sback'];
					$TxtColor   = "lime";
					$BoxMessage = $lang['fl_isback'];
				}
			} elseif ($FleetRow['fleet_mess'] == 1) {
				$BoxMessage = $lang['fl_notback'];
			}
		} else {
			$BoxMessage = $lang['fl_onlyyours'];
		}
	}

	message ("<font color=\"".$TxtColor."\">". $BoxMessage ."</font>", $BoxTitle, "fleet.". $phpEx, 2);

// -----------------------------------------------------------------------------------------------------------
// History version
// Updated by Chlorel. 22 Jan 2008 (String extraction, bug corrections, code uniformisation
// Created by DxPpLmOs. All rights reversed (C) 2007
// Updated by -= MoF =- for Deutsches Ugamela Forum
// 06.12.2007 - 08:41
// Open Source
// (c) by MoF
?>
