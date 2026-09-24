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
	$Result .= "<td class=c colspan=8>".$lang['Solar_system']." ".$Galaxy.":".$System."</td>";
	$Result .= "</tr><tr>";
	$Result .= "<td class=c>".$lang['Pos']."</td>";
	$Result .= "<td class=c>".$lang['Planet']."</td>";
	$Result .= "<td class=c>".$lang['Name']."</td>";
	$Result .= "<td class=c>".$lang['Moon']."</td>";
	$Result .= "<td class=c>".$lang['Debris']."</td>";
	$Result .= "<td class=c>".$lang['Player']."</td>";
	$Result .= "<td class=c>".$lang['Alliance']."</td>";
	$Result .= "<td class=c>".$lang['Actions']."</td>";
	$Result .= "</tr>";

	return $Result;
}

?>