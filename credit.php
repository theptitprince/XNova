<?php

/**
 * credit.php
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

	includeLang('credit');
	$parse = $lang;

	if ($game_config['ExtCopyFrame'] == '1') {
		$parse['ext_copy_frame'] = "<tr><td colspan=\"2\" class=\"c\">". $lang['cred_ext'] ."</td></tr><tr><th>". nl2br($game_config['ExtCopyOwner']) ."</th><th>". nl2br($game_config['ExtCopyFunct']) ."</th></tr>";
	}

	$BodyTPL = gettemplate('credit_body');

	$page = parsetemplate($BodyTPL, $parse);
	display($page, $lang['cred_credit'], false);

?>