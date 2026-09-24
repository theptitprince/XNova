<?php

/**
 * chat.php
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

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('chat');
	$BodyTPL = gettemplate('chat_body');

	$nick = $user['username'];
	$parse = $lang;
	$parse['csrf_token'] = CsrfToken(); // protection CSRF (le chat envoie ses messages en JavaScript)

	$page = parsetemplate($BodyTPL, $parse);
	display($page, $lang['Chat'], false);

// Shoutbox by e-Zobar - Copyright XNova Team 2008
?>