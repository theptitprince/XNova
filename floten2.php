<?php

/**
 * floten2.php
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
	// Pas d'envoi de flotte en mode vacances
	check_urlaubmodus($user);

	// Champs numeriques du formulaire convertis en entiers (securite + PHP 8)
	SanitizeNumericInput ( array('mission', 'galaxy', 'system', 'planet', 'planettype', 'planet_type', 'thisgalaxy', 'thissystem', 'thisplanet',
	                           'thisplanettype', 'resource1', 'resource2', 'resource3', 'holdingtime', 'expeditiontime',
	                           'speed', 'speedfactor', 'speedallsmin', 'maxepedition', 'curepedition', 'target_mission', 'fleetid'), '/^ship[0-9]+$/' );

	$galaxy     = intval(($_POST['galaxy'] ?? null));
	$system     = intval(($_POST['system'] ?? null));
	$planet     = intval(($_POST['planet'] ?? null));
	$planettype = intval(($_POST['planettype'] ?? null));

	// Test d'existance et de proprieté de la planete
	$YourPlanet = false;
	$UsedPlanet = false;
	$TargetOwner = 0;
	$TargetPlanetId = 0;
	// Seulement la planete visee (avant : toute la table des planetes lue a chaque affichage)
	$row          = doquery("SELECT `id`, `id_owner` FROM {{table}} WHERE `galaxy` = '". $galaxy ."' AND `system` = '". $system ."' AND `planet` = '". $planet ."' AND `planet_type` = '". $planettype ."' LIMIT 1;", "planets", true);
	if ($row) {
		if ($row['id_owner'] == $user['id']) {
			$YourPlanet = true;
			$UsedPlanet = true;
		} else {
			$UsedPlanet = true;
		}
		$TargetOwner = $row['id_owner'];
		$TargetPlanetId = $row['id'];
	}

	// Flotte choisie a l'etape precedente : vaisseaux existants en quantites positives, comme a l'envoi (floten3.php)
	$fleetarray    = unserialize(base64_decode(str_rot13((string) ($_POST["usedfleet"] ?? ''))), array('allowed_classes' => false));
	$CleanFleet    = array();
	if (is_array($fleetarray)) {
		foreach ($fleetarray as $Ship => $Count) {
			$Ship  = intval($Ship);
			$Count = intval($Count);
			if ($Ship > 200 && $Ship < 300 && isset($resource[$Ship]) && $Count > 0) {
				$CleanFleet[$Ship] = $Count;
			}
		}
	}
	$fleetarray    = $CleanFleet;

	// Determinons les type de missions possibles par rapport a la planete cible
	// D'apres la flotte choisie ($fleetarray, 0.9k), avec les memes regles que l'envoi (floten3.php)
	if (($_POST['planettype'] ?? null) == "2") {
		if (($fleetarray[209] ?? 0) >= 1) {
			$missiontype = array(8 => $lang['type_mission'][8]);
		} else {
			$missiontype = array();
		}
	} elseif (($_POST['planettype'] ?? null) == "1" || ($_POST['planettype'] ?? null) == "3") {
		// Colonisation : jamais en position 16, reservee aux expeditions (0.9k)
		if (($fleetarray[208] ?? 0) >= 1 && !$UsedPlanet && $planet <= MAX_PLANET_IN_SYSTEM) {
			$missiontype = array(7 => $lang['type_mission'][7]);
		} elseif (($fleetarray[210] ?? 0) >= 1 && count($fleetarray) == 1 && !$YourPlanet) {
			// Espionnage avec des sondes seulement, comme dans OGame (0.9k)
			$missiontype = array(6 => $lang['type_mission'][6]);
		}

		if (($fleetarray[202] ?? 0) >= 1 ||
			($fleetarray[203] ?? 0) >= 1 ||
			($fleetarray[204] ?? 0) >= 1 ||
			($fleetarray[205] ?? 0) >= 1 ||
			($fleetarray[206] ?? 0) >= 1 ||
			($fleetarray[207] ?? 0) >= 1 ||
			($fleetarray[210] ?? 0) >= 1 ||
			($fleetarray[211] ?? 0) >= 1 ||
			($fleetarray[213] ?? 0) >= 1 ||
			($fleetarray[214] ?? 0) >= 1 ||
			($fleetarray[215] ?? 0) >= 1 ||
			($fleetarray[216] ?? 0) >= 1 ||
			($fleetarray[217] ?? 0) >= 1) {
			if (!$YourPlanet) {
				$missiontype[1] = $lang['type_mission'][1];
			}
			$missiontype[3] = $lang['type_mission'][3];
			// Stationner chez un allie : amis et membres de son alliance seulement (0.9i ; l'original la proposait vers
			// toute planete, ses propres planetes et celles d'un ennemi comprises)
			if (!$YourPlanet && $UsedPlanet && IsBuddyOrAllyMember($user['id'], $TargetOwner)) {
				$missiontype[5] = $lang['type_mission'][5];
			}
		}
		// Recycleurs et vaisseaux de colonisation peuvent aussi transporter (regle d'origine jamais atteinte : elle etait hors de ce bloc)
		if (($fleetarray[208] ?? 0) >= 1 || ($fleetarray[209] ?? 0) >= 1) {
			$missiontype[3] = $lang['type_mission'][3];
		}
	}
	if ($YourPlanet)
		$missiontype[4] = $lang['type_mission'][4];

	// Attaque groupee (0.9i) : groupe choisi a l'etape precedente (bloc « Attaques groupees »), vers sa cible, avec une
	// flotte qui pourrait attaquer
	$AcsGroup = false;
	if (intval($_POST['acs'] ?? 0) > 0 && !empty($missiontype[1])) {
		$AcsGroup = AcsGroupToJoin(intval($_POST['acs']), $user['id'], $galaxy, $system, $planet, $planettype);
		if (is_array($AcsGroup)) {
			$missiontype[2] = $lang['type_mission'][2];
		} elseif ($AcsGroup != 'fl_acs_other_target') {
			// Groupe choisi vers sa cible mais complet, parti ou quitte : on dit pourquoi (autre cible : simple envoi)
			message ("<font color=\"red\"><b>". $lang[$AcsGroup] ."</b></font>", $lang['fl_error'], "fleet." . $phpEx, 2);
		} else {
			$AcsGroup = false;
		}
	}

	if ( ($_POST['planettype'] ?? null) == 3 &&
	     ($fleetarray[214] ?? 0) >= 1    &&
           !$YourPlanet            &&
           $UsedPlanet) {
          $missiontype[9] = $lang['type_mission'][9];
   }
	// Destruction d'une colonie par le Destructeur planetaire (0.9j) : jamais la planete mere d'un joueur
	if ($planettype == 1 && ($fleetarray[217] ?? 0) >= 1 && !$YourPlanet && $UsedPlanet && !PlanetIsHome($TargetPlanetId, $TargetOwner)) {
		$missiontype[9] = $lang['type_mission'][9];
	}

	if (!$fleetarray) {
		// Pas de flotte transmise (acces direct) : retour a la page flotte
		header("Location: fleet.php");
		exit();
	}
	$mission       = ($AcsGroup) ? 2 : ($_POST['target_mission'] ?? null);
	$SpeedFactor   = GetGameSpeedFactor (); // valeur du serveur, pas celle du formulaire
	$AllFleetSpeed = GetFleetMaxSpeed ($fleetarray, 0, $user);
	$GenFleetSpeed = ($_POST['speed'] ?? null);
	$MaxFleetSpeed = (is_array($AllFleetSpeed) && $AllFleetSpeed) ? min($AllFleetSpeed) : 0;

	$distance      = GetTargetDistance ( ($_POST['thisgalaxy'] ?? null), ($_POST['galaxy'] ?? null), ($_POST['thissystem'] ?? null), ($_POST['system'] ?? null), ($_POST['thisplanet'] ?? null), ($_POST['planet'] ?? null) );
	$duration      = GetMissionDuration ( $GenFleetSpeed, $MaxFleetSpeed, $distance, $SpeedFactor );
	$consumption   = GetFleetConsumption ( $fleetarray, $SpeedFactor, $duration, $distance, $MaxFleetSpeed, $user );

	$MissionSelector  = "";
	if (count($missiontype) > 0) {
		if ($planet == 16) {
			$MissionSelector .= "<tr height=\"20\">";
			$MissionSelector .= "<th>";
			$MissionSelector .= "<input type=\"radio\" name=\"mission\" value=\"15\" checked=\"checked\">". $lang['type_mission'][15] ."<br /><br />";
			$MissionSelector .= "<font color=\"red\">". $lang['fl_expe_warning'] ."</font>";
			$MissionSelector .= "</th>";
			$MissionSelector .= "</tr>";
		} else {
			$i = 0;
			foreach ($missiontype as $a => $b) {
				$MissionSelector .= "<tr height=\"20\">";
				$MissionSelector .= "<th>";
				$MissionSelector .= "<input id=\"inpuT_".$i."\" type=\"radio\" name=\"mission\" value=\"".$a."\"". ($mission == $a ? " checked=\"checked\"":"") .">";
				$MissionSelector .= "<label for=\"inpuT_".$i."\">".$b."</label><br>";
				$MissionSelector .= "</th>";
				$MissionSelector .= "</tr>";
				$i++;
			}
		}
	} else {
		$MissionSelector .= "<tr height=\"20\">";
		$MissionSelector .= "<th>";
		$MissionSelector .= "<font color=\"red\">". $lang['fl_bad_mission'] ."</font>";
		$MissionSelector .= "</th>";
		$MissionSelector .= "</tr>";
	}

	// Titre : depart -> cible (avant, seule la planete de depart etait affichee, ce qui pretait a confusion)
	$PlaceType   = array(1 => $lang['fl_planet'], 2 => $lang['fl_ruins'], 3 => $lang['fl_moon']);
	$TableTitle  = ($_POST['thisgalaxy'] ?? null) .":". ($_POST['thissystem'] ?? null) .":". ($_POST['thisplanet'] ?? null) ." - ". ($PlaceType[($_POST['thisplanettype'] ?? null)] ?? '');
	$TableTitle .= " &rarr; ". $galaxy .":". $system .":". $planet ." - ". ($PlaceType[($_POST['planettype'] ?? null)] ?? '');

	$page  = "<script type=\"text/javascript\" src=\"scripts/flotten.js\">\n</script>";
	$page .= "<script type=\"text/javascript\">\n";
	$page .= "function getStorageFaktor() {\n";
	$page .= "    return 1;\n";
	$page .= "}\n";
	$page .= "</script>\n";
	$page .= "<br><center>";
	$page .= "<form action=\"floten3.php\" method=\"post\">\n";
	$page .= "<input type=\"hidden\" name=\"thisresource1\"  value=\"". floor($planetrow["metal"]) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"thisresource2\"  value=\"". floor($planetrow["crystal"]) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"thisresource3\"  value=\"". floor($planetrow["deuterium"]) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"consumption\"    value=\"". $consumption ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"dist\"           value=\"". $distance ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"speedfactor\"    value=\"". ($_POST['speedfactor'] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"thisgalaxy\"     value=\"". ($_POST["thisgalaxy"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"thissystem\"     value=\"". ($_POST["thissystem"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"thisplanet\"     value=\"". ($_POST["thisplanet"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"galaxy\"         value=\"". ($_POST["galaxy"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"system\"         value=\"". ($_POST["system"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"planet\"         value=\"". ($_POST["planet"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"thisplanettype\" value=\"". ($_POST["thisplanettype"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"planettype\"     value=\"". ($_POST["planettype"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"speedallsmin\"   value=\"". ($_POST["speedallsmin"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"speed\"          value=\"". ($_POST['speed'] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"speedfactor\"    value=\"". ($_POST["speedfactor"] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"usedfleet\"      value=\"". htmlspecialchars(($_POST["usedfleet"] ?? null), ENT_QUOTES) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"maxepedition\"   value=\"". ($_POST['maxepedition'] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"curepedition\"   value=\"". ($_POST['curepedition'] ?? null) ."\" />\n";
	$page .= "<input type=\"hidden\" name=\"acs\"            value=\"". (($AcsGroup) ? intval($AcsGroup['id']) : 0) ."\" />\n";
	foreach ($fleetarray as $Ship => $Count) {
		$page .= "<input type=\"hidden\" name=\"ship". $Ship ."\"        value=\"". $Count ."\" />\n";
		$page .= "<input type=\"hidden\" name=\"capacity". $Ship ."\"    value=\"". $pricelist[$Ship]['capacity'] ."\" />\n";
		$page .= "<input type=\"hidden\" name=\"consumption". $Ship ."\" value=\"". GetShipConsumption ( $Ship, $user ) ."\" />\n";
		$page .= "<input type=\"hidden\" name=\"speed". $Ship ."\"       value=\"". GetFleetMaxSpeed ( "", $Ship, $user ) ."\" />\n";

	}
	$page .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"519\">\n";
	$page .= "<tbody>\n";
	$page .= "<tr align=\"left\" height=\"20\">\n";
	$page .= "<td class=\"c\" colspan=\"2\">". $TableTitle ."</td>\n";
	$page .= "</tr>\n";
	$page .= "<tr align=\"left\" valign=\"top\">\n";
	$page .= "<th width=\"50%\">\n";
	$page .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"259\">\n";
	$page .= "<tbody>\n";
	$page .= "<tr height=\"20\">\n";
	$page .= "<td class=\"c\" colspan=\"2\">". $lang['fl_mission'] ."</td>\n";
	$page .= "</tr>\n";
	$page .= $MissionSelector;
	$page .= "</tbody>\n";
	$page .= "</table>\n";
	$page .= "</th>\n";
	$page .= "<th>\n";
	$page .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"259\">\n";
	$page .= "<tbody>\n";
	$page .= "<tr height=\"20\">\n";
	$page .= "<td colspan=\"3\" class=\"c\">". $lang['fl_ressources'] ."</td>\n";
	$page .= "</tr><tr height=\"20\">\n";
	$page .= "<th>". $lang['metal_label'] ."</th>\n";
	$page .= "<th><a href=\"javascript:maxResource('1');\">". $lang['fl_selmax'] ."</a></th>\n";
	$page .= "<th><input name=\"resource1\" alt=\"". $lang['metal_label'] ." ". floor($planetrow["metal"]) ."\" size=\"10\" onchange=\"calculateTransportCapacity();\" type=\"text\"></th>\n";
	$page .= "</tr><tr height=\"20\">\n";
	$page .= "<th>". $lang['crystal_label'] ."</th>\n";
	$page .= "<th><a href=\"javascript:maxResource('2');\">". $lang['fl_selmax'] ."</a></th>\n";
	$page .= "<th><input name=\"resource2\" alt=\"". $lang['crystal_label'] ." ". floor($planetrow["crystal"]) ."\" size=\"10\" onchange=\"calculateTransportCapacity();\" type=\"text\"></th>\n";
	$page .= "</tr><tr height=\"20\">\n";
	$page .= "<th>". $lang['deuterium_label'] ."</th>\n";
	$page .= "<th><a href=\"javascript:maxResource('3');\">". $lang['fl_selmax'] ."</a></th>\n";
	$page .= "<th><input name=\"resource3\" alt=\"". $lang['deuterium_label'] ." ". floor($planetrow["deuterium"]) ."\" size=\"10\" onchange=\"calculateTransportCapacity();\" type=\"text\"></th>\n";
	$page .= "</tr><tr height=\"20\">\n";
	$page .= "<th>". $lang['fl_space_left'] ."</th>\n";
	$page .= "<th colspan=\"2\"><div id=\"remainingresources\">-</div></th>\n";
	$page .= "</tr><tr height=\"20\">\n";
	$page .= "<th colspan=\"3\"><a href=\"javascript:maxResources()\">". $lang['fl_allressources'] ."</a></th>\n";
	$page .= "</tr><tr height=\"20\">\n";
	$page .= "<th colspan=\"3\">&nbsp;</th>\n";
	$page .= "</tr>\n";
	if ($planet == 16) {
		$page .= "<tr height=\"20\">";
		$page .= "<td class=\"c\" colspan=\"3\">". $lang['fl_expe_staytime'] ."</td>";
		$page .= "</tr>";
		$page .= "<tr height=\"20\">";
		$page .= "<th colspan=\"3\">";
		$page .= "<select name=\"expeditiontime\" >";
		$page .= "<option value=\"1\">1</option>";
		$page .= "<option value=\"2\">2</option>";
		$page .= "</select>";
		$page .= $lang['fl_expe_hours'];
		$page .= "</th>";
		$page .= "</tr>";
	} elseif ( !empty($missiontype[5]) ) {
		$page .= "<tr height=\"20\">";
		$page .= "<td class=\"c\" colspan=\"3\">". $lang['fl_expe_staytime'] ."</td>";
		$page .= "</tr>";
		$page .= "<tr height=\"20\">";
		$page .= "<th colspan=\"3\">";
		$page .= "<select name=\"holdingtime\" >";
		$page .= "<option value=\"0\">0</option>";
		$page .= "<option value=\"1\">1</option>";
		$page .= "<option value=\"2\">2</option>";
		$page .= "<option value=\"4\">4</option>";
		$page .= "<option value=\"8\">8</option>";
		$page .= "<option value=\"16\">16</option>";
		$page .= "<option value=\"32\">32</option>";
		$page .= "</select>";
		$page .= $lang['fl_expe_hours'];
		$page .= "</th>";
		$page .= "</tr>";
	}
	$page .= "</tbody>\n";
	$page .= "</table>\n";
	$page .= "</th>\n";
	$page .= "</tr><tr height=\"20\">\n";
	$page .= "<th colspan=\"2\"><input accesskey=\"z\" value=\"". $lang['fl_continue'] ."\" type=\"submit\"></th>\n";
	$page .= "</tr>\n";
	$page .= "</tbody>\n";
	$page .= "</table>\n";
	$page .= "</form></center>\n";

	display($page, $lang['fl_title']);

// Updated by Chlorel. 16 Jan 2008 (String extraction, bug corrections, code uniformisation)
// Created by Perberos. All rights reversed (C) 2006
?>