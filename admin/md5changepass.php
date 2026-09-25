<?php

/**
 * md5changepass.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * md5enc.php
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	if ($user['authlevel'] >= 3) { // changer le mot de passe d'un compte : administrateur uniquement
		includeLang('admin/changepass');

		$parse   = $lang;

		if (($_POST['md5q'] ?? null) != "") {

			doquery ("UPDATE {{table}} SET `password` = '" . SqlEscape(PasswordHash(($_POST['md5q'] ?? null))) . "' WHERE `username` = '". SqlEscape(($_POST['user'] ?? null)) ."';", 'users');
			//$QueryUpdatePass = "UPDATE {{table}} SET ";
			//$QueryUpdatePass .= "`password` = '" . md5 (($_POST['md5q'] ?? null)) . "', ";
			//$QueryUpdatePass = "WHERE ";
	        //$QueryUpdatePass .= "`username`=" . ($_POST['user'] ?? null) . "";
      //  doquery($QueryUpdatePass, 'users');
		} else {

		}
		
		$PageTpl = gettemplate("admin/changepass");
		$Page    = parsetemplate( $PageTpl, $parse);

		display( $Page, $lang['md5_title'], false, '', true );
	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>