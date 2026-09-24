<?php

/**
 * lostpassword.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Tom1991 for XNova
 * @license GNU GPL v2
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('lostpassword');

	if ($action != 1) {
		$parse               = $lang;
		$parse['servername'] = $game_config['game_name'];
		$page .= parsetemplate(gettemplate('lostpassword'), $parse);
		display($page, $lang['system'], false);
	}
	if ($action == 1) {
		$email               = $_POST['email'];
		sendnewpassword($email);
		message('Le nouveau mot de passe a &eacute;t&eacute; envoy&eacute; avec succ&egrave;s !', 'OK');
	}

// History version
// 1.0 Création (Tom)
?>
