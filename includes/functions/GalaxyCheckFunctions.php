<?php

/**
 * GalaxyCheckFunctions.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * GalaxyCheckFunctions
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// ----------------------------------------------------------------------------------------------------------------
//
// Verification sur la base des planetes
//

// Suppression complete d'une lune
function CheckAbandonMoonState ($lunarow) {
	if (($lunarow['destruyed'] + 172800) <= time() && $lunarow['destruyed'] != 0) {
		$query = doquery("DELETE FROM {{table}} WHERE id = '" . $lunarow['id'] . "'", "lunas");
	}
}

// Suppression complete d'une planete
function CheckAbandonPlanetState (&$planet) {
	if ($planet['destruyed'] <= time()) {
		doquery("DELETE FROM {{table}} WHERE id={$planet['id']}", 'planets');
		doquery("UPDATE {{table}} SET id_planet=0 WHERE id_planet={$planet['id']}", 'galaxy');
	}
}


?>