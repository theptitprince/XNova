<?php

/**
 * banned.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	if ($user['authlevel'] >= 1) {
		includeLang('admin');

		$mode      = ($_POST['mode'] ?? null);

		$PageTpl   = gettemplate("admin/banned");

		$parse     = $lang;
		if ($mode == 'banit') {
			$name              = SqlEscape(($_POST['name'] ?? null));
			$reas              = SqlEscape(SafeText(($_POST['why'] ?? null))); // affiche dans le pilori public
			$days              = intval(($_POST['days'] ?? null));
			$hour              = intval(($_POST['hour'] ?? null));
			$mins              = intval(($_POST['mins'] ?? null));
			$secs              = intval(($_POST['secs'] ?? null));

			$admin             = $user['username'];
			$mail              = $user['email'];

			$Now               = time();
			$BanTime           = $days * 86400;
			$BanTime          += $hour * 3600;
			$BanTime          += $mins * 60;
			$BanTime          += $secs;
			$BannedUntil       = $Now + $BanTime;

			$QryInsertBan      = "INSERT INTO {{table}} SET ";
			$QryInsertBan     .= "`who` = \"". $name ."\", ";
			$QryInsertBan     .= "`theme` = '". $reas ."', ";
			$QryInsertBan     .= "`who2` = '". $name ."', ";
			$QryInsertBan     .= "`time` = '". $Now ."', ";
			$QryInsertBan     .= "`longer` = '". $BannedUntil ."', ";
			$QryInsertBan     .= "`author` = '". $admin ."', ";
			$QryInsertBan     .= "`email` = '". $mail ."';";
			doquery( $QryInsertBan, 'banned');

			$QryUpdateUser     = "UPDATE {{table}} SET ";
			$QryUpdateUser    .= "`bana` = '1', ";
			$QryUpdateUser    .= "`banaday` = '". $BannedUntil ."' ";
			$QryUpdateUser    .= "WHERE ";
			$QryUpdateUser    .= "`username` = \"". $name ."\";";
			doquery( $QryUpdateUser, 'users');

			$DoneMessage       = $lang['adm_bn_thpl'] ." ". $name ." ". $lang['adm_bn_isbn'];
			AdminMessage ($DoneMessage, $lang['adm_bn_ttle']);
		}

		$Page = parsetemplate($PageTpl, $parse);
		display( $Page, $lang['adm_bn_ttle'], false, '', true);
	} else {
		AdminMessage ($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}

?>