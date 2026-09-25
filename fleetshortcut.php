<?php

/**
 * fleetshortcut.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

includeLang('fleet');

$mode = ($_GET['mode'] ?? null);
$a    = intval(($_GET['a'] ?? null));

// Raccourcis de flotte : une ligne par raccourci dans users.fleet_shortcut, « nom,galaxie,systeme,position,type »
// (type 1 planete, 2 champ de debris, 3 lune). Textes dans la langue du joueur (melange francais / allemand / espagnol).

// Champs du formulaire d'un raccourci ($c : valeurs actuelles, vides pour un ajout)
function ShortcutForm ( $c ) {
	global $lang;
	$Esc  = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
	$Form  = "<input type=\"text\" name=\"n\" value=\"". $Esc($c[0] ?? '') ."\" size=\"32\" maxlength=\"32\" title=\"". $lang['fs_name'] ."\">\n";
	$Form .= "<input type=\"text\" name=\"g\" value=\"". $Esc($c[1] ?? '') ."\" size=\"3\" maxlength=\"1\" title=\"". $lang['fs_galaxy'] ."\">\n";
	$Form .= "<input type=\"text\" name=\"s\" value=\"". $Esc($c[2] ?? '') ."\" size=\"3\" maxlength=\"3\" title=\"". $lang['fs_system'] ."\">\n";
	$Form .= "<input type=\"text\" name=\"p\" value=\"". $Esc($c[3] ?? '') ."\" size=\"3\" maxlength=\"3\" title=\"". $lang['fs_position'] ."\">\n";
	$Form .= "<select name=\"t\">";
	foreach (array(1 => 'fs_type_planet', 2 => 'fs_type_debris', 3 => 'fs_type_moon') as $Type => $Key) {
		$Form .= "<option value=\"". $Type ."\"". ((($c[4] ?? 1) == $Type) ? " selected" : "") .">". $lang[$Key] ."</option>";
	}
	$Form .= "</select>";
	return $Form;
}

// Ligne enregistree : nom nettoye (pas de virgule, separateur), coordonnees entieres
function ShortcutLine () {
	global $lang;
	$Name = str_replace(',', ' ', SafeName(($_POST['n'] ?? ''), 32));
	if ($Name == '') {
		$Name = $lang['fs_unnamed'];
	}
	return $Name .",". intval(($_POST['g'] ?? 0)) .",". intval(($_POST['s'] ?? 0)) .",". intval(($_POST['p'] ?? 0)) .",". intval(($_POST['t'] ?? 1));
}

function ShortcutSave ( $List ) {
	global $user;
	$user['fleet_shortcut'] = implode("\r\n", $List);
	doquery("UPDATE {{table}} SET fleet_shortcut='". SqlEscape($user['fleet_shortcut']) ."' WHERE id='". intval($user['id']) ."'", "users");
}

$scarray = array_values(array_filter(explode("\r\n", (string) $user['fleet_shortcut']), 'strlen'));

if (isset($_GET['mode'])) {
	// Ajout
	if ($_POST) {
		$scarray[] = ShortcutLine();
		ShortcutSave($scarray);
		message($lang['fs_saved'], $lang['fs_title'], "fleetshortcut.php");
	}
	$page  = "<form method=\"POST\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"519\">";
	$page .= "<tr height=\"20\"><td colspan=\"2\" class=\"c\">". $lang['fs_name_coords'] ."</td></tr>";
	$page .= "<tr height=\"20\"><th>". ShortcutForm(array()) ."</th></tr>";
	$page .= "<tr><th><input type=\"reset\" value=\"". $lang['fs_reset'] ."\"> <input type=\"submit\" value=\"". $lang['fs_save'] ."\"></th></tr>";
	$page .= "<tr><td colspan=\"2\" class=\"c\"><a href=\"fleetshortcut.php\">". $lang['fs_back'] ."</a></td></tr></table></form>";
} elseif (isset($_GET['a'])) {
	// Modification ou suppression
	if (!isset($scarray[$a])) {
		message($lang['fs_not_found'], $lang['fs_title'], "fleetshortcut.php");
	}
	if ($_POST) {
		if (($_POST["delete"] ?? null)) {
			unset($scarray[$a]);
			ShortcutSave($scarray);
			message($lang['fs_deleted'], $lang['fs_title'], "fleetshortcut.php");
		}
		$scarray[$a] = ShortcutLine();
		ShortcutSave($scarray);
		message($lang['fs_edited'], $lang['fs_title'], "fleetshortcut.php");
	}
	$c     = explode(',', $scarray[$a]);
	$page  = "<form method=\"POST\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"519\">";
	$page .= "<tr height=\"20\"><td colspan=\"2\" class=\"c\">". $lang['fs_edit'] .": ". htmlspecialchars($c[0], ENT_QUOTES, 'UTF-8') ." [". intval($c[1] ?? 0) .":". intval($c[2] ?? 0) .":". intval($c[3] ?? 0) ."]</td></tr>";
	$page .= "<tr height=\"20\"><th>". ShortcutForm($c) ."</th></tr>";
	$page .= "<tr><th><input type=\"reset\" value=\"". $lang['fs_reset'] ."\"> <input type=\"submit\" value=\"". $lang['fs_save'] ."\"> <input type=\"submit\" name=\"delete\" value=\"". $lang['fs_delete'] ."\"></th></tr>";
	$page .= "<tr><td colspan=\"2\" class=\"c\"><a href=\"fleetshortcut.php\">". $lang['fs_back'] ."</a></td></tr></table></form>";
} else {
	// Liste, deux raccourcis par ligne
	$page  = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"519\">";
	$page .= "<tr height=\"20\"><td colspan=\"2\" class=\"c\">". $lang['fs_title'] ." (<a href=\"?mode=add\">". $lang['fs_add'] ."</a>)</td></tr>";
	if (count($scarray) > 0) {
		$i = 0;
		foreach ($scarray as $Index => $Line) {
			$c = explode(',', $Line);
			if ($i == 0) { $page .= "<tr height=\"20\">"; }
			$page .= "<th><a href=\"?a=". $Index ."\">". htmlspecialchars($c[0], ENT_QUOTES, 'UTF-8') ." ". intval($c[1] ?? 0) .":". intval($c[2] ?? 0) .":". intval($c[3] ?? 0);
			if (($c[4] ?? 1) == 2) { $page .= " ". $lang['fs_mark_debris']; } elseif (($c[4] ?? 1) == 3) { $page .= " ". $lang['fs_mark_moon']; }
			$page .= "</a></th>";
			if ($i == 1) { $page .= "</tr>"; }
			$i = 1 - $i;
		}
		if ($i == 1) { $page .= "<th></th></tr>"; }
	} else {
		$page .= "<tr><th colspan=\"2\">". $lang['fs_none'] ."</th></tr>";
	}
	$page .= "<tr><td colspan=\"2\" class=\"c\"><a href=\"fleet.php\">". $lang['fs_back'] ."</a></td></tr></table>";
}
display($page, $lang['fs_title']);

// Created by Perberos. All rights reversed (C) 2006
?>
