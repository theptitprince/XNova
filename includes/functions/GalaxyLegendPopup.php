<?php

/**
 * GalaxyLegendPopup.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function GalaxyLegendPopup () {
	global $lang;

	$Result  = "<a href=# style=\"cursor: pointer;\"";
	$Result .= " onmouseover='return overlib(\"";

	$Result .= "<table width=240>";
	$Result .= "<tr>";
	$Result .= "<td class=c colspan=2>".$lang['legend']."</td>";
	$Result .= "</tr><tr>";
	$Result .= "<td width=220>".$lang['strong_player']."</td><td><span class=strong>".$lang['strong_player_shortcut']."</span></td>";
	$Result .= "</tr><tr>";
	$Result .= "<td width=220>".$lang['weak_player']."</td><td><span class=noob>".$lang['weak_player_shortcut']."</span></td>";
	$Result .= "</tr><tr>";
	$Result .= "<td width=220>".$lang['way_vacation']."</td><td><span class=vacation>".$lang['vacation_shortcut']."</span></td>";
	$Result .= "</tr><tr>";
	$Result .= "<td width=220>".$lang['pendent_user']."</td><td><span class=banned>".$lang['banned_shortcut']."</span></td>";
	$Result .= "</tr><tr>";
	$Result .= "<td width=220>".$lang['inactive_7_days']."</td><td><span class=inactive>".$lang['inactif_7_shortcut']."</span></td>";
	$Result .= "</tr><tr>";
	$Result .= "<td width=220>".$lang['inactive_28_days']."</td><td><span class=longinactive>".$lang['inactif_28_shortcut']."</span></td>";
	$Result .= "</tr><tr>";
	$Result .= "<td width=220>Admin</td><td><font color=lime><blink>A</blink></font></td>";
	$Result .= "</tr>";
	$Result .= "</table>";
	$Result .= "\");' onmouseout='return nd();'>";
	$Result .= $lang['legend']."</a>";




	return $Result;
}

?>