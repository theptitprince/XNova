<?php

/**
 * frames.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * frame.php
 * @version 1.0
 * @copyright 2008 by e-Zobar for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$InLogin = false;

$XNova_Host    = $_SERVER['HTTP_HOST'];
$XNova_Script  = $_SERVER['SCRIPT_NAME'];
$Uri_Array     = explode ('/', $XNova_Script);
// On vire le script
array_pop($Uri_Array);
$XNova_URI     = implode ('/', $Uri_Array);

$XNovaRootURL  = "http://". $XNova_Host ."/". $XNova_URI ."/";

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

	$page  = "<html>";
	$page .= "<head>";
	$page .= "<meta http-equiv=\"Content-Type\" content=\"text/html;charset=". $langInfos['encoding']."\">";
	$page .= "<link rel=\"shortcut icon\" href=\"favicon.ico\">";
	$page .= "<title>". $game_config['game_name'] ."</title>";
	$page .= "</head>";

	$page .= "<frameset framespacing=\"0\" border=\"0\" cols=\"190,*\" frameborder=\"0\">";
	// Menu defilant si besoin (scrolling="no" d'origine : sur un ecran peu haut, Regles, Contact, Options et
	// Deconnexion etaient hors d'atteinte)
	$page .= "<frame name=\"LeftMenu\" target=\"Mainframe\" src=\"leftmenu.php\" noresize scrolling=\"auto\" marginwidth=\"0\" marginheight=\"0\">";
	$page .= "<frame name=\"Hauptframe\" src=\"overview.php\">";
	$page .= "<noframes>";
	$page .= "<body>";
	$page .= "<p>". ($lang['no_frames'] ?? '') ."</p>";
	$page .= "</body>";
	$page .= "</noframes>";
	$page .= "</frameset>";
	$page .= "</html>";

	echo $page;

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Euuhh ... je ne sais plus ...
?>
