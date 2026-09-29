<?php

/**
 * MissionCaseSpy.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// ----------------------------------------------------------------------------------------------------------------
// Mission Case 6: -> Espionner
//
function MissionCaseSpy ( $FleetRow ) {
	global $lang, $resource;

	if ($FleetRow['fleet_start_time'] <= time()) {
		$fleet               = explode(";", $FleetRow['fleet_array']);
		$fquery              = "";
		// Sondes de la flotte (0.9k) : la flotte est traitee une seule fois. L'original refaisait tout pour chaque type de
		// vaisseau : avec des sondes et un autre vaisseau, la flotte etait supprimee avant le rapport ($TargetChances lu
		// sans avoir ete calcule, avertissement PHP).
		$LS                  = 0;
		foreach ($fleet as $a => $b) {
			if ($b != '') {
				$a = explode(",", $b);
				$fquery .= "{$resource[$a[0]]}={$resource[$a[0]]} + {$a[1]}, \n";
				if ($a[0] == "210") {
					$LS += $a[1];
				}
			}
		}
		$Destroyed = false;
		if ($FleetRow["fleet_mess"] != "1") {
			// Joueurs et planetes lus seulement a l'arrivee (0.9k, performances) : le retour des sondes n'en a pas besoin
			$CurrentUser         = doquery("SELECT `spy_tech` FROM {{table}} WHERE `id` = '".$FleetRow['fleet_owner']."';", 'users', true);
			$CurrentUserID       = $FleetRow['fleet_owner'];
			$QryGetTargetPlanet  = "SELECT * FROM {{table}} ";
			$QryGetTargetPlanet .= "WHERE ";
			$QryGetTargetPlanet .= "`galaxy` = '". $FleetRow['fleet_end_galaxy'] ."' AND ";
			$QryGetTargetPlanet .= "`system` = '". $FleetRow['fleet_end_system'] ."' AND ";
			$QryGetTargetPlanet .= "`planet` = '". $FleetRow['fleet_end_planet'] ."' AND ";
			$QryGetTargetPlanet .= "`planet_type` = '". $FleetRow['fleet_end_type'] ."';";
			$TargetPlanet        = doquery( $QryGetTargetPlanet, 'planets', true);
			$TargetUserID        = $TargetPlanet['id_owner'];
			// Sans le type : planete ou lune, la premiere dans l'ordre de la table (USE INDEX () : pas l'ordre de l'index)
			$CurrentPlanet       = doquery("SELECT * FROM {{table}} USE INDEX () WHERE `galaxy` = '".$FleetRow['fleet_start_galaxy']."' AND `system` = '".$FleetRow['fleet_start_system']."' AND `planet` = '".$FleetRow['fleet_start_planet']."';", 'planets', true);
			$CurrentSpyLvl       = $CurrentUser['spy_tech'];
			$TargetUser          = doquery("SELECT * FROM {{table}} WHERE `id` = '".$TargetUserID."';", 'users', true);
			$TargetSpyLvl        = $TargetUser['spy_tech'];
			// Flotte sans sonde : pas de rapport, elle rentre
			$TargetChances = 0;
			$SpyerChances  = 1;
			if ($LS > 0) {
				// (ligne de galaxie lue ici pour rien, retiree en 0.9k)
				$SpyToolDebris    = $LS * 300;

				$MaterialsInfo    = SpyTarget ( $TargetPlanet, 0, $lang['sys_spy_maretials'] );
				$Materials        = $MaterialsInfo['String'];

				$PlanetFleetInfo  = SpyTarget ( $TargetPlanet, 1, $lang['sys_spy_fleet'] );
				$PlanetFleet      = $Materials;
				$PlanetFleet     .= $PlanetFleetInfo['String'];

				$PlanetDefenInfo  = SpyTarget ( $TargetPlanet, 2, $lang['sys_spy_defenses'] );
				$PlanetDefense    = $PlanetFleet;
				$PlanetDefense   .= $PlanetDefenInfo['String'];

				$PlanetBuildInfo  = SpyTarget ( $TargetPlanet, 3, $lang['tech'][0] );
				$PlanetBuildings  = $PlanetDefense;
				$PlanetBuildings .= $PlanetBuildInfo['String'];

				$TargetTechnInfo  = SpyTarget ( $TargetUser, 4, $lang['tech'][100] );
				$TargetTechnos    = $PlanetBuildings;
				$TargetTechnos   .= $TargetTechnInfo['String'];

				$TargetForce      = ($PlanetFleetInfo['Count'] * $LS) / 4;

				if ($TargetForce > 100) {
					$TargetForce = 100;
				}
				// Borne entiere, comme le faisait PHP 5 sans le dire (PHP 8 : conversion depreciee, avertissement)
				$TargetForce   = intval($TargetForce);
				$TargetChances = rand(0, $TargetForce);
				$SpyerChances  = rand(0, 100);
				if ($TargetChances >= $SpyerChances) {
					$DestProba = "<font color=\"red\">".$lang['sys_mess_spy_destroyed']."</font>";
				} else {
					// Probabilite reelle de ce tirage (detruite si rand(0, T) >= rand(0, 100)) : (T + 2) / 202. La force T
					// etait affichee : « 100 % » pour une sonde qui revenait une fois sur deux, 0 % pour 1 % de risque
					$DestProba = sprintf( $lang['sys_mess_spy_lostproba'], round(($TargetForce + 2) / 202 * 100));
				}
				$AttackLink = "<center>";
				$AttackLink .= "<a href=\"fleet.php?galaxy=". $FleetRow['fleet_end_galaxy'] ."&system=". $FleetRow['fleet_end_system'] ."";
				$AttackLink .= "&planet=".$FleetRow['fleet_end_planet']."";
				$AttackLink .= "&target_mission=1";
				$AttackLink .= " \">". $lang['type_mission'][1] ."";
				$AttackLink .= "</a></center>";


				$MessageEnd = "<center>".$DestProba."</center>";

				$pT = ($TargetSpyLvl - $CurrentSpyLvl);
				$pW = ($CurrentSpyLvl - $TargetSpyLvl);
				if ($TargetSpyLvl > $CurrentSpyLvl) {
					$ST = ($LS - pow($pT, 2));
				}
				if ($CurrentSpyLvl > $TargetSpyLvl) {
					$ST = ($LS + pow($pW, 2));
				}
				if ($TargetSpyLvl == $CurrentSpyLvl) {
					$ST = $CurrentSpyLvl;
				}
				if ($ST <= "1") {
					$SpyMessage = $Materials."<br />".$AttackLink.$MessageEnd;
				}
				if ($ST == "2") {
					$SpyMessage = $PlanetFleet."<br />".$AttackLink.$MessageEnd;
				}
				if ($ST == "4" or $ST == "3") {
					$SpyMessage = $PlanetDefense."<br />".$AttackLink.$MessageEnd;
				}
				if ($ST == "5" or $ST == "6") {
					$SpyMessage = $PlanetBuildings."<br />".$AttackLink.$MessageEnd;
				}
				if ($ST >= "7") {
					$SpyMessage = $TargetTechnos."<br />".$AttackLink.$MessageEnd;
				}

				SendSimpleMessage ( $CurrentUserID, '', $FleetRow['fleet_start_time'], 0, $lang['sys_mess_qg'], $lang['sys_mess_spy_report'], $SpyMessage);

				$TargetMessage  = $lang['sys_mess_spy_ennemyfleet'] ." ". $CurrentPlanet['name'];
				$TargetMessage .= "<a href=\"galaxy.php?mode=3&galaxy=". $CurrentPlanet["galaxy"] ."&system=". $CurrentPlanet["system"] ."\">";
				$TargetMessage .= "[". $CurrentPlanet["galaxy"] .":". $CurrentPlanet["system"] .":". $CurrentPlanet["planet"] ."]</a> ";
				$TargetMessage .= $lang['sys_mess_spy_seen_at'] ." ". $TargetPlanet['name'];
				$TargetMessage .= " [". $TargetPlanet["galaxy"] .":". $TargetPlanet["system"] .":". $TargetPlanet["planet"] ."].";

				SendSimpleMessage ( $TargetUserID, '', $FleetRow['fleet_start_time'], 0, $lang['sys_mess_spy_control'], $lang['sys_mess_spy_activity'], $TargetMessage);

			}
			if ($TargetChances >= $SpyerChances) {
				$QryUpdateGalaxy  = "UPDATE {{table}} SET ";
				$QryUpdateGalaxy .= "`crystal` = `crystal` + '". (0 + $SpyToolDebris) ."' ";
				$QryUpdateGalaxy .= "WHERE `id_planet` = '". $TargetPlanet['id'] ."';";
				doquery( $QryUpdateGalaxy, 'galaxy');

				doquery("DELETE FROM {{table}} WHERE `fleet_id` = '". $FleetRow["fleet_id"] ."';", 'fleets');
				$Destroyed = true;
			} else {
				doquery("UPDATE {{table}} SET `fleet_mess` = '1' WHERE `fleet_id` = '". $FleetRow["fleet_id"] ."';", 'fleets');
			}
		}
		// Retour de sondes : une seule fois (l'original le faisait sur l'element vide qui termine la liste des vaisseaux,
		// meme pour des sondes detruites juste avant, quand l'arrivee etait traitee en retard)
		if (!$Destroyed && $FleetRow['fleet_end_time'] <= time()) {
			RestoreFleetToPlanet ( $FleetRow, true );
			doquery("DELETE FROM {{table}} WHERE `fleet_id` = ". $FleetRow["fleet_id"], 'fleets');
		}
	}
}

?>