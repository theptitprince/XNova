<?php

/**
 * annonce2.php
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

includeLang('annonce');

// Page desactivee par l'administrateur : le menu cachait le lien, l'adresse directe restait ouverte
if ($game_config['enable_announces'] != 1) {
	message($lang['sys_page_disabled'], $lang['ann_title']);
}

$actions = intval(($_GET['action'] ?? null));

if ($actions == 2) {
	// Formulaire de publication : ressources a vendre, ressources souhaitees (textes de la langue du joueur)
	$Rows = function ($Suffix) use ($lang) {
		$Html = '';
		foreach (array('metal' => 'metal_label', 'cristal' => 'crystal_label', 'deut' => 'deuterium_label') as $Field => $Label) {
			$Html .= "<tr><th colspan=\"5\">". $lang[$Label] ."</th><th colspan=\"5\"><input type=\"text\" value=\"0\" name=\"". $Field . $Suffix ."\" /></th></tr>";
		}
		return $Html;
	};
	$page  = "<center><br><form action=\"annonce.php?action=5\" method=\"post\"><table width=\"600\">";
	$page .= "<tr><td class=\"c\" colspan=\"10\" align=\"center\"><b>". $lang['ann_add'] ."</b></td></tr>";
	$page .= "<tr><td class=\"c\" colspan=\"10\" align=\"center\">". $lang['ann_for_sale'] ."</td></tr>";
	$page .= $Rows('vendre');
	$page .= "<tr><td class=\"c\" colspan=\"10\" align=\"center\">". $lang['ann_wanted'] ."</td></tr>";
	$page .= $Rows('souhait');
	$page .= "<tr><th colspan=\"10\"><input type=\"submit\" value=\"". $lang['ann_send'] ."\" /></th></tr>";
	$page .= "<tr><td class=\"c\" colspan=\"10\"><a href=\"annonce.php\">". $lang['ann_back'] ."</a></td></tr>";
	$page .= "</table></form></center>";

	display($page, $lang['ann_add']);
}
?>
