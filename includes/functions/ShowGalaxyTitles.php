<?php

/**
 * ShowGalaxyTitles.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function ShowGalaxyTitles ( $Galaxy, $System ) {
	global $lang;

	$Result  = "\n";
	$Result .= "<tr>";
	$Result .= "<td class=c colspan=8>".$lang['solar_system']." ".$Galaxy.":".$System."</td>";
	$Result .= "</tr><tr>";
	$Result .= "<td class=c>".$lang['pos']."</td>";
	$Result .= "<td class=c>".$lang['planet_label']."</td>";
	$Result .= "<td class=c>".$lang['name_label']."</td>";
	$Result .= "<td class=c>".$lang['moon_label']."</td>";
	$Result .= "<td class=c>".$lang['debris_label']."</td>";
	$Result .= "<td class=c>".$lang['player_label']."</td>";
	$Result .= "<td class=c>".$lang['alliance_label']."</td>";
	$Result .= "<td class=c>".$lang['actions']."</td>";
	$Result .= "</tr>";

	return $Result;
}

?>