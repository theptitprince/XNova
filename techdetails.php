<?php

/**
 * techdetails.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Arbre complet des prerequis d'un element (page [i] des Technologies).
// L'original n'etait jamais fini : une seule technologie codee en dur et un bloc fige dans le modele.

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

includeLang('tech');

$Id = intval(($_GET['techid'] ?? null));
if (!isset($lang['tech'][$Id]) || !isset($resource[$Id])) {
	message($lang['te_dt_unknown'], $lang['Tech'], 'techtree.php', 2);
}

// Niveau actuel du joueur : recherches sur le compte, batiments sur la planete courante
function TechDetailsLevel ( $Element ) {
	global $user, $planetrow, $resource;

	if (isset($user[$resource[$Element]])) {
		return intval($user[$resource[$Element]]);
	}
	return isset($planetrow[$resource[$Element]]) ? intval($planetrow[$resource[$Element]]) : 0;
}

// Parcours en largeur : etape 1 = prerequis directs, etape 2 = leurs prerequis, etc.
// Un element deja rencontre n'est affiche qu'une fois, avec le niveau le plus eleve demande.
$Required = array();   // element => niveau demande
$Step     = array();   // element => etape ou il apparait
$Current  = isset($requeriments[$Id]) ? $requeriments[$Id] : array();
$Depth    = 1;
while (!empty($Current) && $Depth <= 20) {
	$Next = array();
	foreach ($Current as $Element => $Level) {
		if (!isset($Required[$Element])) {
			$Required[$Element] = $Level;
			$Step[$Element]     = $Depth;
			if (isset($requeriments[$Element])) {
				foreach ($requeriments[$Element] as $SubElement => $SubLevel) {
					$Next[$SubElement] = max($SubLevel, isset($Next[$SubElement]) ? $Next[$SubElement] : 0);
				}
			}
		} else {
			$Required[$Element] = max($Required[$Element], $Level);
		}
	}
	$Current = $Next;
	$Depth++;
}

$Liste = "";
if (empty($Required)) {
	$Liste .= "<tr><th colspan=\"2\">". $lang['te_dt_none'] ."</th></tr>";
} else {
	for ($i = 1; $i < $Depth; $i++) {
		$Rows = "";
		foreach ($Step as $Element => $ElementStep) {
			if ($ElementStep != $i) {
				continue;
			}
			$Color = (TechDetailsLevel($Element) >= $Required[$Element]) ? "#00ff00" : "#ff0000";
			$Rows .= "<tr><th style=\"text-align: left;\"><a href=\"infos.php?gid=". $Element ."\"><font color=\"". $Color ."\">". $lang['tech'][$Element] ." (". $lang['level'] ." ". $Required[$Element] .")</font></a></th>";
			$Rows .= "<th width=\"30\">". (isset($requeriments[$Element]) ? "<a href=\"techdetails.php?techid=". $Element ."\">". $lang['treeinfo'] ."</a>" : "") ."</th></tr>";
		}
		if ($Rows != "") {
			$Liste .= "<tr><td class=\"c\" colspan=\"2\">". $lang['te_dt_step'] ." ". $i ."</td></tr>". $Rows;
		}
	}
}

$parse               = $lang;
$parse['te_dt_id']   = $Id;
$parse['te_dt_name'] = $lang['tech'][$Id];
$parse['Liste']      = $Liste;
$page = parsetemplate(gettemplate('techtree_details'), $parse);

display ($page, $lang['Tech']);

?>
