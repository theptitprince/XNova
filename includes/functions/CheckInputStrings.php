<?php

/**
 * CheckInputStrings.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function CheckInputStrings ( $String ) {
	global $ListCensure;

	$ValidString = $String;
	for ($Mot = 0; $Mot < count($ListCensure); $Mot++) {
		$ValidString = preg_replace( "/". preg_quote($ListCensure[$Mot], "/") ."/i", "*", $ValidString );
	}
	return ($ValidString);
}

?>