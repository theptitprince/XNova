<?php

/**
 * logout.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

	includeLang('logout');

	setcookie($game_config['COOKIE_NAME'], "", time()-100000, "/", "", 0);

	message ( $lang['see_you'], $lang['session_closed'], "login.".$phpEx );

// -----------------------------------------------------------------------------------------------------------
// History version
?>