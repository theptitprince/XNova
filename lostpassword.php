<?php

/**
 * lostpassword.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Tom1991 for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('lostpassword');

	// Le formulaire envoie sur lostpassword.php?action=1 : jamais lu depuis la fin de extract(), l'envoi ne partait plus
	$action = intval($_GET['action'] ?? 0);

	// Lien de confirmation recu par mail (action=2) : le nouveau mot de passe part par mail
	if ($action == 2) {
		if (LostPasswordConfirm($_GET['id'] ?? 0, $_GET['token'] ?? '')) {
			message($lang['lp_done'], $lang['reset_pass'], "login.php", 5);
		}
		message($lang['lp_invalid'], $lang['reset_pass']);
	}

	if ($action != 1 || empty($_POST['email'])) {
		$parse               = $lang;
		$parse['servername'] = $game_config['game_name'];
		$page = parsetemplate(gettemplate('lostpassword'), $parse);
		display($page, $lang['reset_pass'], false);
	}
	if ($action == 1) {
		LostPasswordSendLink($_POST['email'] ?? '');
		// Meme message que l'adresse existe ou non
		message($lang['lp_link_sent'], $lang['reset_pass'], "login.php", 8);
	}

// History version
// 1.0 Création (Tom)
?>
