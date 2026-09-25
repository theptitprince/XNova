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

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);


includeLang('banned');

$parse = $lang;
$parse['dpath'] = $dpath;
$parse['banned'] = '';

// Sanctions les plus recentes en premier. La raison est deja echappee a l'enregistrement (admin/banned.php).
$query = doquery("SELECT `who`, `theme`, `time`, `longer`, `author` FROM {{table}} ORDER BY `time` DESC, `id` DESC;",'banned');
$i=0;
while($u = mysqli_fetch_assoc($query)){
	$parse['banned'] .=
	"<tr><td class=b><center><b>".htmlspecialchars($u['who'], ENT_QUOTES, 'UTF-8')."</b></center></td>".
	"<td class=b><center><b>".$u['theme']."</b></center></td>".
	"<td class=b><center><b>".date("d/m/Y H:i:s",$u['time'])."</b></center></td>".
	"<td class=b><center><b>".date("d/m/Y H:i:s",$u['longer'])."</b></center></td>".
	"<td class=b><center><b>".htmlspecialchars($u['author'], ENT_QUOTES, 'UTF-8')."</b></center></td></tr>";
	$i++;
}

// Textes de la langue du joueur (ils etaient ecrits en dur en francais, « Il y a 1 joueurs bannis »)
if ($i == 0) {
	$parse['banned'] .= "<tr><th class=b colspan=5>{$lang['ban_no']}</th></tr>";
} else {
	$parse['banned'] .= "<tr><th class=b colspan=5>" . (($i == 1) ? $lang['ban_count_one'] : sprintf($lang['ban_count'], $i)) . "</th></tr>";
}

display(parsetemplate(gettemplate('banned_body'), $parse), $lang['ban_title'], true);


// Created by e-Zobar (XNova Team). All rights reversed (C) 2008
?>