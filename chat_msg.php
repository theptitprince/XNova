<?php

/**
 * chat_msg.php
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

// On efface les anciens messages
$timemoment=time();
$time_1h=$timemoment - 3600;

// On selectionne les messages présents dans la base de donnée
$query = doquery("SELECT * FROM {{table}} ORDER BY messageid ASC", "chat");
while($v=mysqli_fetch_object($query)){
	$nick=htmlentities($v->user);
	$msg=htmlentities($v->message);

	// Les différentes polices (gras, italique, couleurs, etc...)
	$msg=preg_replace("#\[a=(ft|https?://)(.+)\](.+)\[/a\]#isU", "<a href=\"$1$2\" target=\"_blank\">$3</a>", $msg);
	$msg=preg_replace("#\[b\](.+)\[/b\]#isU","<b>$1</b>",$msg);
	$msg=preg_replace("#\[i\](.+)\[/i\]#isU","<i>$1</i>",$msg);
	$msg=preg_replace("#\[u\](.+)\[/u\]#isU","<u>$1</u>",$msg);
	$msg=preg_replace("#\[c=(blue|yellow|green|pink|red|orange)\](.+)\[/c\]#isU","<font color=\"$1\">$2</font>",$msg);

	// Les smileys avec leurs raccourcis. XNova Renaissance : code isole seulement (debut ou fin du message, espace,
	// bord d'une balise) ; remplaces partout, ils coupaient les mots et les liens (« :cool: », « https:// », « &quot;) »)
	$Smileys = array(':c' => 'cry', ':/' => 'confused', 'o0' => 'dizzy', '^^' => 'happy', ':D' => 'lol', ':|' => 'neutral',
	                 ':)' => 'smile', ':o' => 'omg', ':p' => 'tongue', ':(' => 'sad', ';)' => 'wink', ':s' => 'shit');
	$Codes = implode('|', array_map(function ($Code) { return preg_quote($Code, '#'); }, array_keys($Smileys)));
	$msg = preg_replace_callback('#(?<=^|\s|>)(' . $Codes . ')(?=$|\s|<)#i', function ($m) use ($Smileys) {
		$Code = strtolower($m[1]) == ':d' ? ':D' : strtolower($m[1]);
		$Code = isset($Smileys[$Code]) ? $Code : $m[1];
		return '<img src="images/smileys/' . $Smileys[$Code] . '.png" align="absmiddle" title="' . $Code . '" alt="' . $Code . '">';
	}, $msg);

	// Affichage du message
	// (plus de stripslashes : sans magic quotes, il effacait les \ tapes par les joueurs)
	$msg="<div align=\"left\">".$nick." &gt; ".$msg."<br></div>";
	print $msg;
}

// Shoutbox by e-Zobar - Copyright XNova Team 2008
?>