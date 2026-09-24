<?php

/**
 * changelog.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Perberos
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = '../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

includeLang('changelog');
$template = gettemplate('changelog_table');

$parse = $lang;

$IsLatest = true;
foreach($lang['changelog'] as $a => $b)
{

	// Seule la version la plus recente (la premiere de la liste) est en vert
	$parse['version_number'] = ($IsLatest) ? '<font color="lime">'. $a .'</font>' : $a;
	$IsLatest = false;
	$parse['description']    = nl2br($b);

	$body .= parsetemplate($template, $parse);

}

$parse['body'] = $body;

$page .= parsetemplate(gettemplate('changelog_body'), $parse);

display( $page, "Changelog", false, '', true);

?>