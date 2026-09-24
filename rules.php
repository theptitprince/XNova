<?php

/**
 * rules.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original : XNova Team, d'après UGamela
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

/**
 * rules.php
 * @version 1.0
 * @copyright 2008 by XxmangaxX for XNova
**/

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('rules');

	$parse = $lang;
	$parse['servername']   = $game_config['game_name'];

	$PageTPL  = gettemplate('rules_body');
	$page     = parsetemplate( $PageTPL, $parse);

	display($page, $lang['rules'], false);


?>