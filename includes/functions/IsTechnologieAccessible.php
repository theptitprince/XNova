<?php

/**
 * IsTechnologieAccessible.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Verification si l'on a le droit ou non a un element
function IsTechnologieAccessible($user, $planet, $Element) {
	global $requeriments, $resource;

	if (isset($requeriments[$Element])) {
		$enabled = true;
		foreach($requeriments[$Element] as $ReqElement => $EleLevel) {
			if (@$user[$resource[$ReqElement]] && $user[$resource[$ReqElement]] >= $EleLevel) {
				// break;
			} elseif ($planet[$resource[$ReqElement]] && $planet[$resource[$ReqElement]] >= $EleLevel) {
				$enabled = true;
			} else {
				return false;
			}
		}
		return $enabled;
	} else {
		return true;
	}
}

?>