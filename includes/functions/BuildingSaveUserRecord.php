<?php

/**
 * BuildingSaveUserRecord.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function BuildingSaveUserRecord ( $CurrentUser ) {

	$QryUpdateUser  = "UPDATE {{table}} SET ";
	$QryUpdateUser .= "`xpminier` = '".      $CurrentUser['xpminier']      ."' ";
	$QryUpdateUser .= "WHERE ";
	$QryUpdateUser .= "`id` = '".            $CurrentUser["id"]            ."';";
	doquery( $QryUpdateUser, 'users');

	return;
}

?>