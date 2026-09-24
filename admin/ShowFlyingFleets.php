<?php

/**
 * ShowFlyingFleets.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	if ($user['authlevel'] >= 1) {
		includeLang('admin/fleets');
		$PageTPL            = gettemplate('admin/fleet_body');

		$parse              = $lang;
		$parse['flt_table'] = BuildFlyingFleetTable ();

		$page               = parsetemplate( $PageTPL, $parse );
		display ( $page, $lang['flt_title'], false, '', true);

	} else {
		AdminMessage ( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>