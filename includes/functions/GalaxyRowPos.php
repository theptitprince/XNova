<?php

/**
 * GalaxyRowPos.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU GPL v2
 */

function GalaxyRowPos ( $Planet, $GalaxyRow ) {
	// Pos
	$Result  = "<th width=30>";
	$Result .= "<a href=\"#\"";
	if ($GalaxyRow) {
		$Result .= " tabindex=\"". ($Planet + 1) ."\"";
	}
	$Result .= ">". $Planet ."</a>";
	$Result .= "</th>";

	return $Result;
}

?>