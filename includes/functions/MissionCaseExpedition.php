<?php

/**
 * MissionCaseExpedition.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function MissionCaseExpedition ( $FleetRow ) {
	global $lang, $resource, $pricelist;

	$FleetOwner = $FleetRow['fleet_owner'];
	$MessSender = $lang['sys_mess_qg'];
	$MessTitle  = $lang['sys_expe_report'];

	if ($FleetRow['fleet_mess'] == 0) {
		// Flotte en vol aller
		if ($FleetRow['fleet_end_stay'] < time()) {
			// La Flotte vient de finir son exploration
			// Table de ratio de points par type de vaisseau
			$PointsFlotte = array(
				202 => 1.0,  // 'Petit transporteur'
				203 => 1.5,  // 'Grand transporteur'
				204 => 0.5,  // 'Chasseur léger'
				205 => 1.5,  // 'Chasseur lourd'
				206 => 2.0,  // 'Croiseur'
				207 => 2.5,  // 'Vaisseau de bataille'
				208 => 0.5,  // 'Vaisseau de colonisation'
				209 => 1.0,  // 'Recycleur'
				210 => 0.01, // 'Sonde espionnage'
				211 => 3.0,  // 'Bombardier'
				212 => 0.0,  // 'Satellite solaire'
				213 => 3.5,  // 'Destructeur'
				214 => 5.0,  // 'Etoile de la mort'
				215 => 3.2,  // 'Traqueur'
				216 => 4.0,  // 'SuperNova' (0.9j)
				217 => 8.0,  // 'Destructeur planetaire' (0.9j)
			);

			// Table de ratio de gains en nombre par type de vaisseau
			$RatioGain = array (
				202 => 0.1,     // 'Petit transporteur'
				203 => 0.1,     // 'Grand transporteur'
				204 => 0.1,     // 'Chasseur léger'
				205 => 0.5,     // 'Chasseur lourd'
				206 => 0.25,    // 'Croiseur'
				207 => 0.125,   // 'Vaisseau de bataille'
				208 => 0.5,     // 'Vaisseau de colonisation'
				209 => 0.1,     // 'Recycleur'
				210 => 0.1,     // 'Sonde espionnage'
				211 => 0.0625,  // 'Bombardier'
				212 => 0.0,     // 'Satellite solaire'
				213 => 0.0625,  // 'Destructeur'
				214 => 0.03125, // 'Etoile de la mort'
				215 => 0.0625,  // 'Traqueur'
				216 => 0.0625,  // 'SuperNova' (0.9j)
				217 => 0.03125, // 'Destructeur planetaire' (0.9j)
			);

			$FleetStayDuration = ($FleetRow['fleet_end_stay'] - $FleetRow['fleet_start_time']) / 3600;

			// Initialisation du contenu de la Flotte
			$LaFlotte     = array();
			$FleetCapacity = 0;
			$FleetPoints   = 0;
			$farray = explode(";", $FleetRow['fleet_array']);
			foreach ($farray as $Item => $Group) {
				if ($Group != '') {
					$Class = explode (",", $Group);
					$TypeVaisseau = $Class[0];
					$NbreVaisseau = $Class[1];

					$LaFlotte[$TypeVaisseau] = $NbreVaisseau;

					//On calcul les ressources maximum qui peuvent être récupéré
					$FleetCapacity += $pricelist[$TypeVaisseau]['capacity'] * $NbreVaisseau; // (l'original oubliait le nombre)
					// Maintenant on calcul en points toute la flotte
					$FleetPoints   += ($NbreVaisseau * ($PointsFlotte[$TypeVaisseau] ?? 0));
				}
			}

			// Espace deja occupé dans les soutes si ce devait etre le cas
			$FleetUsedCapacity  = $FleetRow['fleet_resource_metal'] + $FleetRow['fleet_resource_crystal'] + $FleetRow['fleet_resource_deuterium'];
			$FleetCapacity     -= $FleetUsedCapacity;

			//On récupère le nombre total de vaisseaux
			$FleetCount = $FleetRow['fleet_amount'];

			// Bon on les mange comment ces explorateurs ???
			$Hasard = rand(0, 10);


			if ($Hasard < 2) {
				// XNova Renaissance 0.9j : combat contre des pirates (0) ou des aliens (1), comme dans OGame. L'original
				// faisait perdre 34 ou 67 % de la flotte sans combat (« trou noir », message sys_expe_blackholl_1)
				ExpeditionBattle ( $FleetRow, $LaFlotte, ($Hasard == 1) );
			} elseif ($Hasard == 2) {
				// Pas de bol, on les mange tout crus : trou noir, flotte entierement perdue
				SendSimpleMessage ( $FleetOwner, '', $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $lang['sys_expe_blackholl_2'] );
				doquery ("DELETE FROM {{table}} WHERE `fleet_id` = ". $FleetRow["fleet_id"], 'fleets');
			} elseif ($Hasard == 3) {
				// Ah un tour pour rien ; ou (0.9j, comme dans OGame, une fois sur deux) une avarie qui retarde le retour
				if (rand(0, 1) == 1) {
					ExpeditionReturnTime ( $FleetRow, true );
				} else {
					doquery("UPDATE {{table}} SET `fleet_mess` = '1' WHERE `fleet_id` = ". $FleetRow["fleet_id"], 'fleets');
					SendSimpleMessage ( $FleetOwner, '', $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $lang['sys_expe_nothing_1'] );
				}
			} elseif ($Hasard >= 4 && $Hasard < 7) {
				// Gains de ressources
				if ($FleetCapacity > 5000) {
					$MinCapacity = $FleetCapacity - 5000;
					$MaxCapacity = $FleetCapacity;
					$FoundGoods  = rand($MinCapacity, $MaxCapacity);
					$FoundMetal  = intval($FoundGoods / 2);
					$FoundCrist  = intval($FoundGoods / 4);
					$FoundDeute  = intval($FoundGoods / 6);

					$QryUpdateFleet  = "UPDATE {{table}} SET ";
					$QryUpdateFleet .= "`fleet_resource_metal` = `fleet_resource_metal` + '". $FoundMetal ."', ";
					$QryUpdateFleet .= "`fleet_resource_crystal` = `fleet_resource_crystal` + '". $FoundCrist ."', ";
					$QryUpdateFleet .= "`fleet_resource_deuterium` = `fleet_resource_deuterium` + '". $FoundDeute ."', ";
					$QryUpdateFleet .= "`fleet_mess` = '1'  ";
					$QryUpdateFleet .= "WHERE ";
					$QryUpdateFleet .= "`fleet_id` = '". $FleetRow["fleet_id"] ."';";
					doquery( $QryUpdateFleet, 'fleets');
					$Message = sprintf($lang['sys_expe_found_goods'],
						pretty_number($FoundMetal), $lang['metal_label'],
						pretty_number($FoundCrist), $lang['crystal_label'],
						pretty_number($FoundDeute), $lang['deuterium_label']);
					SendSimpleMessage ( $FleetOwner, '', $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message );
				}
			} elseif ($Hasard == 7) {
				// Ah un tour pour rien ; ou (0.9j, comme dans OGame, une fois sur deux) un retour plus rapide que prevu
				if (rand(0, 1) == 1) {
					ExpeditionReturnTime ( $FleetRow, false );
				} else {
					doquery("UPDATE {{table}} SET `fleet_mess` = '1' WHERE `fleet_id` = ". $FleetRow["fleet_id"], 'fleets');
					SendSimpleMessage ( $FleetOwner, '', $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $lang['sys_expe_nothing_2'] );
				}
			} elseif ($Hasard >= 8 && $Hasard < 11) {
				// Gain de vaisseaux
				$FoundChance = $FleetPoints / $FleetCount;
				$FoundShip = array();
				for ($Ship = 202; $Ship < 218; $Ship++) {
					if (($LaFlotte[$Ship] ?? 0) != 0) {
						$FoundShip[$Ship] = round($LaFlotte[$Ship] * ($RatioGain[$Ship] ?? 0));
						if ($FoundShip[$Ship] > 0) {
							$LaFlotte[$Ship] += $FoundShip[$Ship];
						}
					}
				}
				$NewFleetArray = "";
				$FoundShipMess = "";
				foreach ($LaFlotte as $Ship => $Count) {
					if ($Count > 0) {
						$NewFleetArray   .= $Ship.",". $Count .";";
					}
				}
				$FoundList = array();
				foreach ($FoundShip as $Ship => $Count) {
					if ($Count != 0) {
						$FoundList[] = $Count." ".$lang['tech'][$Ship];
					}
				}
				$FoundShipMess = implode(", ", $FoundList);

				$QryUpdateFleet  = "UPDATE {{table}} SET ";
				$QryUpdateFleet .= "`fleet_array` = '". $NewFleetArray ."', ";
				$QryUpdateFleet .= "`fleet_mess` = '1'  ";
				$QryUpdateFleet .= "WHERE ";
				$QryUpdateFleet .= "`fleet_id` = '". $FleetRow["fleet_id"] ."';";
				doquery( $QryUpdateFleet, 'fleets');
				// Flotte trop petite pour rapporter un vaisseau entier : expedition sans resultat (avant : « Ils ont trouve : » vide)
				$Message = ($FoundShipMess != '') ? $lang['sys_expe_found_ships']. $FoundShipMess : $lang['sys_expe_nothing_2'];
				SendSimpleMessage ( $FleetOwner, '', $FleetRow['fleet_end_stay'], 15, $MessSender, $MessTitle, $Message );
			}

		}
	} else {
		// La Flotte est de retour a quai
		if ($FleetRow['fleet_end_time'] < time()) {
			// Reintegration de ce qui se ballade avec la flotte
			$FleetAutoQuery = "";
			$farray = explode(";", $FleetRow['fleet_array']);
			foreach ($farray as $Item => $Group) {
				if ($Group != '') {
					$Class = explode (",", $Group);
					$FleetAutoQuery .= "`". $resource[$Class[0]]. "` = `". $resource[$Class[0]] ."` + ". $Class[1] .", ";
				}
			}
			$QryUpdatePlanet  = "UPDATE {{table}} SET ";
			$QryUpdatePlanet .= $FleetAutoQuery;
			$QryUpdatePlanet .= "`metal` = `metal` + ". $FleetRow['fleet_resource_metal'] .", ";
			$QryUpdatePlanet .= "`crystal` = `crystal` + ". $FleetRow['fleet_resource_crystal'] .", ";
			$QryUpdatePlanet .= "`deuterium` = `deuterium` + ". $FleetRow['fleet_resource_deuterium'] ." ";
			$QryUpdatePlanet .= "WHERE ";
			$QryUpdatePlanet .= "`galaxy` = '". $FleetRow['fleet_start_galaxy'] ."' AND ";
			$QryUpdatePlanet .= "`system` = '". $FleetRow['fleet_start_system'] ."' AND ";
			$QryUpdatePlanet .= "`planet` = '". $FleetRow['fleet_start_planet'] ."' AND ";
			$QryUpdatePlanet .= "`planet_type` = '". $FleetRow['fleet_start_type'] ."' ";
			$QryUpdatePlanet .= "LIMIT 1 ;";
			doquery( $QryUpdatePlanet, 'planets');

			// Message pour annoncer le retour de flotte
			SendSimpleMessage ( $FleetOwner, '', $FleetRow['fleet_end_time'], 15, $MessSender, $MessTitle, $lang['sys_expe_back_home'] );

			// Suppression de la flotte
			doquery ("DELETE FROM {{table}} WHERE `fleet_id` = ". $FleetRow["fleet_id"], 'fleets');
		}
	}
}

// ----------------------------------------------------------------------------------------------------------------
// XNova Renaissance 0.9j : combat d'expedition contre des pirates ou des aliens, comme dans OGame. Flotte ennemie : les
// memes types de vaisseaux que l'expedition, 30 a 60 % de leur nombre pour les pirates, 40 a 90 % pour les aliens (au
// moins un de chaque type), avec les technologies du joueur moins 3 (pirates) ou plus 3 (aliens). Combat joue par
// CombatEngine, rapport enregistre et envoye dans les rapports d'expedition, sans debris ni butin ; la flotte rentre
// avec ce qui lui reste (entierement detruite : supprimee).
function ExpeditionBattle ( $FleetRow, $Fleet, $Aliens ) {
	global $lang;

	$Owner = doquery("SELECT `username`, `military_tech`, `defence_tech`, `shield_tech`, `rpg_amiral` FROM {{table}} WHERE `id` = '". intval($FleetRow['fleet_owner']) ."' LIMIT 1;", 'users', true);
	$Tech  = array('military_tech' => intval($Owner['military_tech'] ?? 0), 'defence_tech' => intval($Owner['defence_tech'] ?? 0),
	               'shield_tech'   => intval($Owner['shield_tech'] ?? 0),   'rpg_amiral'   => intval($Owner['rpg_amiral'] ?? 0));
	$Shift = $Aliens ? 3 : -3;
	$EnemyTech = array('military_tech' => max(0, $Tech['military_tech'] + $Shift), 'defence_tech' => max(0, $Tech['defence_tech'] + $Shift),
	                   'shield_tech'   => max(0, $Tech['shield_tech'] + $Shift),   'rpg_amiral'   => 0);
	$Ships = array();
	$Enemy = array();
	foreach ($Fleet as $Ship => $Count) {
		if (intval($Count) > 0) {
			$Ships[$Ship] = intval($Count);
			$Enemy[$Ship] = max(1, (int) ceil($Count * ($Aliens ? rand(40, 90) : rand(30, 60)) / 100));
		}
	}
	$Result = CombatEngine(array(array('fleet' => $Enemy, 'techno' => $EnemyTech)), array(array('fleet' => $Ships, 'techno' => $Tech)));

	// Rapport, comme celui d'une attaque : les pirates ou les aliens attaquent la flotte, a la position exploree
	$Place   = array('galaxy' => $FleetRow['fleet_end_galaxy'], 'system' => $FleetRow['fleet_end_system'], 'planet' => $FleetRow['fleet_end_planet']);
	$AttInfo = array(array('name' => $lang[$Aliens ? 'sys_expe_aliens_name' : 'sys_expe_pirates_name'], 'techno' => $EnemyTech) + $Place);
	$DefInfo = array(array('name' => ($Owner['username'] ?? ''), 'techno' => $Tech) + $Place);
	$Rounds  = CombatReportRounds($Result, $AttInfo, $DefInfo);
	$Outcome = array('a' => 'sys_attacker_won', 'w' => 'sys_defender_won', 'r' => 'sys_both_won');
	$raport  = "<center><table><tr><td>". sprintf($lang['sys_attack_title'], date("d/m/Y H:i:s", $FleetRow['fleet_end_stay'])) ."<br />";
	$raport .= $Rounds['html'] . $lang[$Outcome[$Result['result']]] ."<br /></table>";
	$rid     = md5($raport . '-' . $FleetRow['fleet_id']);
	doquery("INSERT INTO {{table}} SET `time` = UNIX_TIMESTAMP(), `id_owner1` = '0', `id_owner2` = '". intval($FleetRow['fleet_owner']) ."', `rid` = '". $rid ."', `a_zestrzelona` = '0', `raport` = '". addslashes($raport) ."';", 'rw');

	// Flotte : ce qui reste rentre, sinon elle est supprimee
	$NewFleetArray = '';
	$Left          = 0;
	foreach ($Result['defenders'][0] as $Ship => $Count) {
		if (intval($Count) > 0) {
			$NewFleetArray .= $Ship .",". intval($Count) .";";
			$Left          += intval($Count);
		}
	}
	if ($Left > 0) {
		doquery("UPDATE {{table}} SET `fleet_array` = '". $NewFleetArray ."', `fleet_amount` = '". $Left ."', `fleet_mess` = '1' WHERE `fleet_id` = '". intval($FleetRow['fleet_id']) ."';", 'fleets');
	} else {
		doquery("DELETE FROM {{table}} WHERE `fleet_id` = '". intval($FleetRow['fleet_id']) ."';", 'fleets');
	}

	$Color    = array('a' => 'red', 'r' => 'orange', 'w' => 'green');
	$Coords   = " [". $Place['galaxy'] .":". $Place['system'] .":". $Place['planet'] ."] ";
	$Message  = $lang[$Aliens ? 'sys_expe_aliens' : 'sys_expe_pirates'] ."<br /><br />";
	$Message .= "<center><a href=\"rw.php?raport=". $rid ."\"><font color=\"". $Color[$Result['result']] ."\">". $lang['sys_mess_attack_report'] . $Coords ."</font></a><br />";
	$Message .= sprintf($lang['sys_expe_battle_losses'], pretty_number(array_sum($Ships) - $Left), pretty_number($Result['lost_by']['def'][0] ?? 0));
	$Message .= (($Left > 0) ? '' : "<br />". $lang['sys_expe_fleet_lost']) ."</center>";
	SendSimpleMessage ( $FleetRow['fleet_owner'], '', $FleetRow['fleet_end_stay'], 15, $lang['sys_mess_qg'], $lang['sys_expe_report'], $Message );
}

// ----------------------------------------------------------------------------------------------------------------
// XNova Renaissance 0.9j : retour d'expedition retarde (trajet 2 a 3 fois plus long) ou anticipe (2 fois plus court),
// comme dans OGame
function ExpeditionReturnTime ( $FleetRow, $Late ) {
	global $lang;

	$Trip    = max(1, $FleetRow['fleet_end_time'] - $FleetRow['fleet_end_stay']);
	$NewTrip = $Late ? (int) round($Trip * rand(200, 300) / 100) : (int) round($Trip / 2);
	$NewEnd  = $FleetRow['fleet_end_stay'] + max(1, $NewTrip);
	doquery("UPDATE {{table}} SET `fleet_end_time` = '". $NewEnd ."', `fleet_mess` = '1' WHERE `fleet_id` = '". intval($FleetRow['fleet_id']) ."';", 'fleets');
	$Message = sprintf($lang[$Late ? 'sys_expe_delay' : 'sys_expe_early'], date("d/m/Y H:i:s", $NewEnd));
	SendSimpleMessage ( $FleetRow['fleet_owner'], '', $FleetRow['fleet_end_stay'], 15, $lang['sys_mess_qg'], $lang['sys_expe_report'], $Message );
}

?>