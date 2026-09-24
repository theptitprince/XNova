<?php

/**
 * changelog.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU GPL v2
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

includeLang('changelog');

$template = gettemplate('changelog_table');


$IsLatest = true;
foreach($lang['changelog'] as $a => $b)
{

	// Seule la version la plus recente (la premiere de la liste) est en vert
	$parse['version_number'] = ($IsLatest) ? '<font color="lime">'. $a .'</font>' : $a;
	$IsLatest = false;
	$parse['description'] = nl2br($b);

	$body .= parsetemplate($template, $parse);

}

$parse = $lang;
$parse['body'] = $body;

$page .= parsetemplate(gettemplate('changelog_body'), $parse);

display($page,"Change Log");

// Created by Perberos. All rights reversed (C) 2006
?>