<?php

/**
 * annonce2.php
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

$actions = $_GET['action'];

if($actions == 2)
{
$page .=<<<HTML
<center>
<br>
<table width="600">
<td class="c" colspan="10" align="center"><b><font color="white">Ajouter une Annonce</font></b></td></tr>
<td class="c" colspan="10" align="center"><b>Ressources &agrave; Vendre</font></b></td></tr>

<form action="annonce.php?action=5" method="post">
<tr><th colspan="5">M&eacute;tal</th><th colspan="5"><input type="texte" value="0" name="metalvendre" /></th></tr>
<tr><th colspan="5">Cristal</th><th colspan="5"><input type="texte" value="0" name="cristalvendre" /></th></tr>
<tr><th colspan="5">Deuterium</th><th colspan="5"><input type="texte" value="0" name="deutvendre" /></th></tr>
<td class="c" colspan="10" align="center"><b>Ressources Souhait&eacute;es</font></b></td></tr>
<tr><th colspan="5">M&eacute;tal</th><th colspan="5"><input type="texte" value="0" name="metalsouhait" /></th></tr>
<tr><th colspan="5">Cristal</th><th colspan="5"><input type="texte" value="0" name="cristalsouhait" /></th></tr>
<tr><th colspan="5">Deuterium</th><th colspan="5"><input type="texte" value="0" name="deutsouhait" /></th></tr>
<tr><th colspan="10"><input type="submit" value="Envoyer" /></th></tr>

<form>
</table>
HTML;

display($page);
}
?>