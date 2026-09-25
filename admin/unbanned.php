<?php

/**
 * unbanned.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by e-Zobar for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	if ($user['authlevel'] >= "2") {

		$parse = $lang;
		$parse['dpath'] = $dpath;

		$mode = ($_GET['mode'] ?? null);

		if ($mode == 'change' && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$nam = SqlEscape(($_POST['nam'] ?? null));
			doquery("DELETE FROM {{table}} WHERE who2='{$nam}'", 'banned');
			doquery("UPDATE {{table}} SET bana=0, banaday=0 WHERE username='{$nam}'", "users");
			message(sprintf($lang['adm_unban_done'], htmlspecialchars(($_POST['nam'] ?? ''), ENT_QUOTES, 'UTF-8')), $lang['adm_unban_title']);
		}

		display(parsetemplate(gettemplate('admin/unbanned'), $parse), $lang['adm_unban_title'], false, '', true);
	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>