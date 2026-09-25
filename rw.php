<?php

/**
 * rw.php
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

	// Rapport partageable par son lien (comme dans l'original), mais reserve aux joueurs connectes
	$raportrow = doquery("SELECT * FROM {{table}} WHERE `rid` = '".(SqlEscape(($_GET["raport"] ?? null)))."';", 'rw', true);
	if (!$raportrow) {
		message($lang['sys_rw_notfound'], $lang['sys_mess_attack_report']);
	}

	if (($raportrow["id_owner1"] == $user["id"]) and ($raportrow["a_zestrzelona"] == 1)) {
		$Body = $lang['sys_rw_lost_contact'];
	} else {
		$Body = stripslashes( $raportrow["raport"] );
	}

	// Tout le rapport dans un cadre, centre, avec un retour aux messages
	// (avant : page brute, titre et bilan colles a gauche, lignes de tir hors cadre)
	$Page  = "<style type=\"text/css\">.rapport td { text-align: center; }</style>"; // les cellules du rapport enregistre n'heritent pas du centrage
	$Page .= "<br><table width=\"95%\">";
	$Page .= "<tr><td class=\"c\">". $lang['sys_mess_attack_report'] ."</td></tr>";
	$Page .= "<tr><th class=\"rapport\" style=\"text-align: center; font-weight: normal; padding: 6px;\">". $Body ."</th></tr>";
	$Page .= "<tr><th><a href=\"messages.php?mode=show&amp;messcat=3\">". $lang['sys_rw_back'] ."</a></th></tr>";
	$Page .= "</table>";

	display($Page, $lang['sys_mess_attack_report']);

// -----------------------------------------------------------------------------------------------------------
// History version

?>
