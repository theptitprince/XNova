<?php

/**
 * verband.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Attaque groupee (0.9i) : page du groupe. Dans la 0.8e, cette page n'etait qu'une maquette recopiee d'OGame
// (« Association de flotte », invites, invitation) ; elle est terminee ici selon les regles validees le 27/09/2026
// (TRAVAUX.md, 0.9i). Ouverte par le bouton « Associer » de la page Flotte (numero de la flotte) : une attaque encore
// en route forme le groupe, son proprietaire en est le chef. Le chef renomme le groupe et invite ses amis et les
// membres de son alliance (4 invites au plus, 5 joueurs avec lui) ; chaque invite recoit un message.

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('fleet');

	$FleetId = intval($_POST['fleetid'] ?? 0);
	$GroupId = intval($_POST['aks'] ?? ($_GET['aks'] ?? 0));

	if ($FleetId > 0) {
		$Fleet = doquery("SELECT * FROM {{table}} WHERE `fleet_id` = '". $FleetId ."';", 'fleets', true);
		if (!$Fleet || $Fleet['fleet_owner'] != $user['id']) {
			message("<font color=\"red\">". $lang['fl_acs_not_found'] ."</font>", $lang['fl_error'], "fleet.". $phpEx, 2);
		}
		if ($Fleet['fleet_group'] > 0) {
			$GroupId = intval($Fleet['fleet_group']);
		} else {
			// Former le groupe : seulement une attaque encore en route
			if ($Fleet['fleet_mission'] != 1 || $Fleet['fleet_mess'] != 0 || $Fleet['fleet_start_time'] <= time()) {
				message("<font color=\"red\">". $lang['fl_acs_bad_fleet'] ."</font>", $lang['fl_error'], "fleet.". $phpEx, 2);
			}
			// Nom par defaut « KV » suivi d'un numero, comme la maquette d'origine
			$QryInsertGroup  = "INSERT INTO {{table}} SET ";
			$QryInsertGroup .= "`name` = 'KV". mt_rand(100000, 999999999) ."', ";
			$QryInsertGroup .= "`owner` = '". intval($user['id']) ."', ";
			$QryInsertGroup .= "`ankunft` = '". intval($Fleet['fleet_start_time']) ."', ";
			$QryInsertGroup .= "`galaxy` = '". intval($Fleet['fleet_end_galaxy']) ."', ";
			$QryInsertGroup .= "`system` = '". intval($Fleet['fleet_end_system']) ."', ";
			$QryInsertGroup .= "`planet` = '". intval($Fleet['fleet_end_planet']) ."', ";
			$QryInsertGroup .= "`planet_type` = '". intval($Fleet['fleet_end_type']) ."', ";
			$QryInsertGroup .= "`eingeladen` = '';";
			doquery($QryInsertGroup, 'aks');
			$GroupId = intval(mysqli_insert_id($link));
			doquery("UPDATE {{table}} SET `fleet_group` = '". $GroupId ."' WHERE `fleet_id` = '". $FleetId ."';", 'fleets');
		}
	}

	$Group = ($GroupId > 0) ? doquery("SELECT * FROM {{table}} WHERE `id` = '". $GroupId ."';", 'aks', true) : false;
	if (!$Group) {
		message("<font color=\"red\">". $lang['fl_acs_not_found'] ."</font>", $lang['fl_error'], "fleet.". $phpEx, 2);
	}
	$Invited  = AcsInvitedIds($Group);
	$IsLeader = ($Group['owner'] == $user['id']);
	$Fleets   = array();
	$Query    = doquery("SELECT * FROM {{table}} WHERE `fleet_group` = '". $GroupId ."' ORDER BY `fleet_id`;", 'fleets');
	while ($Row = mysqli_fetch_assoc($Query)) {
		$Fleets[] = $Row;
	}
	$InGroup = false;
	foreach ($Fleets as $Row) {
		if ($Row['fleet_owner'] == $user['id']) {
			$InGroup = true;
		}
	}
	// Le groupe ne regarde que son chef, ses invites et ceux qui y ont une flotte ; parti au combat, il n'existe plus
	if ((!$IsLeader && !$InGroup && !in_array(intval($user['id']), $Invited)) || count($Fleets) == 0) {
		message("<font color=\"red\">". $lang['fl_acs_not_found'] ."</font>", $lang['fl_error'], "fleet.". $phpEx, 2);
	}

	$Info  = '';
	$Error = '';
	if ($IsLeader && isset($_POST['groupname'])) {
		$NewName = trim((string) $_POST['groupname']);
		if (!preg_match('/^[\p{L}\p{N} ._-]{1,20}$/u', $NewName)) {
			$Error = $lang['fl_acs_name_bad'];
		} else {
			doquery("UPDATE {{table}} SET `name` = '". SqlEscape($NewName) ."' WHERE `id` = '". $GroupId ."';", 'aks');
			$Group['name'] = $NewName;
			$Info = sprintf($lang['fl_acs_renamed'], htmlspecialchars($NewName, ENT_QUOTES, 'UTF-8'));
		}
	} elseif ($IsLeader && isset($_POST['addtogroup'])) {
		$Guest = doquery("SELECT `id`, `username` FROM {{table}} WHERE `username` = '". SqlEscape(trim((string) $_POST['addtogroup'])) ."' LIMIT 1;", 'users', true);
		if (!$Guest) {
			$Error = $lang['fl_acs_no_player'];
		} elseif ($Guest['id'] == $user['id'] || in_array(intval($Guest['id']), $Invited)) {
			$Error = $lang['fl_acs_already'];
		} elseif (!IsBuddyOrAllyMember($user['id'], $Guest['id'])) {
			$Error = $lang['fl_acs_not_ally'];
		} elseif (count($Invited) >= 4) {
			$Error = $lang['fl_acs_full'];
		} else {
			$Invited[] = intval($Guest['id']);
			doquery("UPDATE {{table}} SET `eingeladen` = '". implode(',', $Invited) ."' WHERE `id` = '". $GroupId ."';", 'aks');
			$Target  = doquery("SELECT `name` FROM {{table}} WHERE `galaxy` = '". intval($Group['galaxy']) ."' AND `system` = '". intval($Group['system']) ."' AND `planet` = '". intval($Group['planet']) ."' AND `planet_type` = '". intval($Group['planet_type']) ."';", 'planets', true);
			$Message = sprintf($lang['fl_acs_invite_text'], htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'), htmlspecialchars($Group['name'], ENT_QUOTES, 'UTF-8'),
			                   htmlspecialchars($Target['name'] ?? '', ENT_QUOTES, 'UTF-8'), intval($Group['galaxy']), intval($Group['system']), intval($Group['planet']),
			                   date("d/m/Y H:i:s", $Group['ankunft']));
			SendSimpleMessage($Guest['id'], $user['id'], time(), 1, $user['username'], $lang['fl_acs_invite_subject'], $Message);
			$Info = sprintf($lang['fl_acs_invited_ok'], htmlspecialchars($Guest['username'], ENT_QUOTES, 'UTF-8'));
		}
	}

	// Noms des joueurs (chef, flottes, invites) et cible
	$Ids = array_merge(array(intval($Group['owner'])), $Invited);
	foreach ($Fleets as $Row) {
		$Ids[] = intval($Row['fleet_owner']);
	}
	$Names = array();
	$Query = doquery("SELECT `id`, `username` FROM {{table}} WHERE `id` IN (". implode(',', array_unique($Ids)) .");", 'users');
	while ($Row = mysqli_fetch_assoc($Query)) {
		$Names[$Row['id']] = htmlspecialchars($Row['username'], ENT_QUOTES, 'UTF-8');
	}
	$Target = doquery("SELECT `name`, `id_owner` FROM {{table}} WHERE `galaxy` = '". intval($Group['galaxy']) ."' AND `system` = '". intval($Group['system']) ."' AND `planet` = '". intval($Group['planet']) ."' AND `planet_type` = '". intval($Group['planet_type']) ."';", 'planets', true);
	$TargetText  = htmlspecialchars($Target['name'] ?? '', ENT_QUOTES, 'UTF-8') ." [". intval($Group['galaxy']) .":". intval($Group['system']) .":". intval($Group['planet']) ."]";
	$TargetText .= ($Group['planet_type'] == 3) ? " ". $lang['fl_acs_moon'] : "";
	$GroupName   = htmlspecialchars($Group['name'], ENT_QUOTES, 'UTF-8');

	$page  = "<br /><center>";
	if ($Info != '') {
		$page .= "<font color=\"lime\">". $Info ."</font><br /><br />";
	}
	if ($Error != '') {
		$page .= "<font color=\"red\">". $Error ."</font><br /><br />";
	}
	$page .= "<table width=\"519\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\">";
	$page .= "<tr height=\"20\"><td class=\"c\" colspan=\"2\">". sprintf($lang['fl_acs_title'], $GroupName) ."</td></tr>";
	$page .= "<tr height=\"20\"><th width=\"40%\">". $lang['fl_acs_target'] ."</th><th>". $TargetText ."</th></tr>";
	$page .= "<tr height=\"20\"><th>". $lang['fl_acs_arrival'] ."</th><th>". date("d/m/Y H:i:s", $Group['ankunft']) ."</th></tr>";
	$page .= "<tr height=\"20\"><th>". $lang['fl_acs_owner'] ."</th><th>". ($Names[$Group['owner']] ?? $lang['fl_acs_deleted']) ."</th></tr>";
	if ($IsLeader) {
		$page .= "<tr height=\"20\"><td class=\"c\" colspan=\"2\">". $lang['fl_acs_rename'] ."</td></tr>";
		$page .= "<tr height=\"20\"><th colspan=\"2\"><form action=\"verband.php\" method=\"post\">";
		$page .= "<input type=\"hidden\" name=\"aks\" value=\"". $GroupId ."\">";
		$page .= "<input type=\"text\" name=\"groupname\" value=\"". $GroupName ."\" size=\"20\" maxlength=\"20\"> ";
		$page .= "<input type=\"submit\" value=\"OK\"></form></th></tr>";
	}
	$page .= "</table><br />";

	$page .= "<table width=\"519\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\">";
	$page .= "<tr height=\"20\"><td class=\"c\" colspan=\"4\">". $lang['fl_acs_fleets'] ."</td></tr>";
	$page .= "<tr height=\"20\"><th>". $lang['fl_acs_player'] ."</th><th>". $lang['fl_acs_ships'] ."</th><th>". $lang['fl_acs_from'] ."</th><th>". $lang['fl_acs_arrival'] ."</th></tr>";
	foreach ($Fleets as $Row) {
		$page .= "<tr height=\"20\"><th>". ($Names[$Row['fleet_owner']] ?? $lang['fl_acs_deleted']) ."</th>";
		$page .= "<th>". pretty_number($Row['fleet_amount']) ."</th>";
		$page .= "<th>[". intval($Row['fleet_start_galaxy']) .":". intval($Row['fleet_start_system']) .":". intval($Row['fleet_start_planet']) ."]</th>";
		$page .= "<th>". date("d/m/Y H:i:s", $Row['fleet_start_time']) ."</th></tr>";
	}
	$page .= "</table><br />";

	$page .= "<table width=\"519\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\">";
	$page .= "<tr height=\"20\"><td class=\"c\">". $lang['fl_acs_invited'] ."</td>";
	if ($IsLeader) {
		$page .= "<td class=\"c\">". $lang['fl_acs_invite'] ."</td>";
	}
	$page .= "</tr><tr><th width=\"50%\">";
	if (count($Invited) == 0) {
		$page .= $lang['fl_acs_nobody'];
	} else {
		$List = array();
		foreach ($Invited as $Id) {
			$List[] = $Names[$Id] ?? $lang['fl_acs_deleted'];
		}
		$page .= implode("<br />", $List);
	}
	$page .= "</th>";
	if ($IsLeader) {
		$page .= "<th><form action=\"verband.php\" method=\"post\">";
		$page .= "<input type=\"hidden\" name=\"aks\" value=\"". $GroupId ."\">";
		$page .= "<input type=\"text\" name=\"addtogroup\" size=\"20\" maxlength=\"64\"> ";
		$page .= "<input type=\"submit\" value=\"OK\"></form><br />". $lang['fl_acs_invite_help'] ."</th>";
	}
	$page .= "</tr></table><br />";
	if (!$IsLeader) {
		$page .= $lang['fl_acs_join_help'] ."<br /><br />";
	}
	$page .= "<a href=\"fleet.php\">". $lang['fl_acs_back'] ."</a></center>";

	display($page, sprintf($lang['fl_acs_title'], $GroupName));

?>
