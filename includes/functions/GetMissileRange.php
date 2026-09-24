<?php

/**
 * GetMissileRange.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function GetMissileRange () {
	global $resource, $user;

	if ($user[$resource[117]] > 0) {
		$MissileRange = ($user[$resource[117]] * 5) - 1;
	} elseif ($user[$resource[117]] == 0) {
		$MissileRange = 0;
	}

	return $MissileRange;
}

?>