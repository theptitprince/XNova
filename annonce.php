<?php

/**
 * annonce.php
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
include($xnova_root_path . 'common.' . $phpEx);

includeLang('annonce');

// Page desactivee par l'administrateur : le menu cachait le lien, l'adresse directe restait ouverte
if ($game_config['enable_announces'] != 1) {
	message($lang['sys_page_disabled'], $lang['ann_title']);
}

$action  = intval(($_GET['action'] ?? null));

if ($action == 5 && $_SERVER['REQUEST_METHOD'] == 'POST') { // publication : formulaire uniquement (protege par le jeton CSRF)
	$metalvendre    = max(0, intval(($_POST['metalvendre'] ?? null)));
	$cristalvendre  = max(0, intval(($_POST['cristalvendre'] ?? null)));
	$deutvendre     = max(0, intval(($_POST['deutvendre'] ?? null)));

	$metalsouhait   = max(0, intval(($_POST['metalsouhait'] ?? null)));
	$cristalsouhait = max(0, intval(($_POST['cristalsouhait'] ?? null)));
	$deutsouhait    = max(0, intval(($_POST['deutsouhait'] ?? null)));

	doquery("INSERT INTO {{table}} SET
user='". SqlEscape($user['username']) ."',
galaxie='". intval($user['galaxy']) ."',
systeme='". intval($user['system']) ."',
metala='{$metalvendre}',
cristala='{$cristalvendre}',
deuta='{$deutvendre}',
metals='{$metalsouhait}',
cristals='{$cristalsouhait}',
deuts='{$deutsouhait}'" , "annonce");

	message($lang['ann_saved'] . "<br><br><a href=\"annonce.php\">". $lang['ann_back'] ."</a>", $lang['ann_title']);
}

if ($action == 3) {
	doquery("DELETE FROM {{table}} WHERE `id` = '". intval($_GET['id'] ?? 0) ."' AND `user` = '". SqlEscape($user['username']) ."';", 'annonce');
}

$annonce = doquery("SELECT * FROM {{table}} ORDER BY `id` DESC ", "annonce");

$page  = "<center><br><table width=\"600\">";
$page .= "<tr><td class=\"c\" colspan=\"10\">". $lang['ann_title'] ."</td></tr>";
$page .= "<tr><th colspan=\"3\">". $lang['ann_delivery'] ."</th><th colspan=\"3\">". $lang['ann_for_sale'] ."</th><th colspan=\"3\">". $lang['ann_wanted'] ."</th><th>". $lang['ann_action'] ."</th></tr>";
$page .= "<tr><th>". $lang['ann_seller'] ."</th><th>". $lang['ann_galaxy'] ."</th><th>". $lang['ann_system'] ."</th>";
$page .= str_repeat("<th>". $lang['metal_label'] ."</th><th>". $lang['crystal_label'] ."</th><th>". $lang['deuterium_label'] ."</th>", 2);
$page .= "<th>". $lang['ann_delete'] ."</th></tr>";
$Count = 0;
while ($b = mysqli_fetch_array($annonce)) {
	$page .= "<tr><th>". htmlspecialchars($b["user"], ENT_QUOTES, 'UTF-8') ."</th><th>". intval($b["galaxie"]) ."</th><th>". intval($b["systeme"]) ."</th>";
	// (l'original lisait « gcristala » : colonne cristal toujours vide)
	foreach (array('metala', 'cristala', 'deuta', 'metals', 'cristals', 'deuts') as $Col) {
		$page .= "<th>". pretty_number($b[$Col]) ."</th>";
	}
	$page .= "<th>";
	if ($b["user"] == $user['username']) {
		$page .= "<a href=\"annonce.php?action=3&amp;id=". intval($b["id"]) ."\" title=\"". $lang['ann_delete'] ."\">X</a>";
	}
	$page .= "</th></tr>";
	$Count++;
}
if ($Count == 0) {
	$page .= "<tr><th colspan=\"10\">". $lang['ann_none'] ."</th></tr>";
}
$page .= "<tr><th colspan=\"10\" align=\"center\"><a href=\"annonce2.php?action=2\">". $lang['ann_add'] ."</a></th></tr>";
$page .= "</table></center>";

display($page, $lang['ann_title']);

// Créer par Tom1991 Copyright 2008
// Merci au site Spacon pour m'avoir donner l'inspiration
?>
