<?php

/**
 * GalaxyRowAlly.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function GalaxyRowAlly ( $GalaxyRow, $GalaxyRowPlanet, $GalaxyRowUser, $Galaxy, $System, $Planet, $PlanetType ) {
	global $lang, $user;

	// Alliances
	$Result  = "<th width=80>";
	if (!empty($GalaxyRowUser['ally_id'])) {
		$allyquery = doquery("SELECT * FROM {{table}} WHERE id=" . $GalaxyRowUser['ally_id'], "alliance", true);
		if ($allyquery) {
			$members_count = doquery("SELECT COUNT(DISTINCT(id)) FROM {{table}} WHERE ally_id=" . $allyquery['id'] . ";", "users", true);

			// Pluriel dans la langue du joueur (un « s » francais etait ajoute au mot, en toute langue)
			$MembersLabel = ($members_count[0] > 1) ? $lang['gl_membres'] : $lang['gl_membre'];

			$Result .= "<a style=\"cursor: pointer;\"";
			$Result .= " onmouseover='return overlib(\"";
			$Result .= "<table width=240>";
			$Result .= "<tr>";
			$Result .= "<td class=c>".$lang['alliance_label']." ". $allyquery['ally_name'] ." ".$lang['gl_with']." ". $members_count[0] ." ". $MembersLabel ."</td>";
			$Result .= "</tr>";
			$Result .= "<th>";
			$Result .= "<table>";
			$Result .= "<tr>";
			$Result .= "<td><a href=alliance.php?mode=ainfo&a=". $allyquery['id'] .">".$lang['gl_ally_internal']."</a></td>";
			$Result .= "</tr><tr>";
			$Result .= "<td><a href=stat.php?start=101&who=ally>".$lang['gl_stats']."</a></td>";
			if ($allyquery["ally_web"] != "") {
				$Result .= "</tr><tr>";
				$Result .= "<td><a href=". $allyquery["ally_web"] ." target=_new>".$lang['gl_ally_web']."</a></td>";
			}
			$Result .= "</tr>";
			$Result .= "</table>";
			$Result .= "</th>";
			$Result .= "</table>\"";
			$Result .= ", STICKY, MOUSEOFF, DELAY, 750, CENTER, OFFSETX, -40, OFFSETY, -40 );'";
			$Result .= " onmouseout='return nd();'>";
			if ($user['ally_id'] == $GalaxyRowUser['ally_id']) {
				$Result .= "<span class=\"allymember\">". $allyquery['ally_tag'] ."</span></a>";
			} else {
				$Result .= $allyquery['ally_tag'] ."</a>";
			}
		}
	}
	$Result .= "</th>";

	return $Result;
}

?>