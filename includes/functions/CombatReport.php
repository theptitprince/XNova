<?php

/**
 * CombatReport.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Tours du rapport de combat (0.9i), d'apres le resultat de CombatEngine() : a chaque tour, un cadre par attaquant
// puis par defenseur (nom, position, technologies, vaisseaux), puis les tirs de chaque camp. Pour un attaquant contre
// un defenseur, c'est le rapport de l'original (mission Attaquer). Match nul en 7 tours : le dernier tour montre les
// flottes a la fin du combat avec les valeurs du tour 7 (l'original y affichait des zeros, avec des avertissements).
//
// $Attackers, $Defenders : dans l'ordre donne au moteur, array('name' =>, 'galaxy' =>, 'system' =>, 'planet' =>,
// 'techno' => array(...)). Retourne array('html' => ..., 'first_round' => 1 si les attaquants sont tous detruits des
// le premier tour : le rapport detaille ne leur est alors pas montre, colonne a_zestrzelona).
function CombatReportRounds ( $Result, $Attackers, $Defenders ) {
	global $lang;

	$Html       = '';
	$FirstRound = 0;
	$Last       = count($Result['rounds']);
	foreach ($Result['rounds'] as $Round => $Data) {
		$Final     = ($Round == 7 && $Last == 7 && $Data['units']['att'] > 0 && $Data['units']['def'] > 0);
		$Destroyed = ($Data['units']['att'] == 0 || $Data['units']['def'] == 0);
		if ($Round == 2 && $Data['units']['att'] == 0) {
			$FirstRound = 1;
		}
		foreach (array('att' => $Attackers, 'def' => $Defenders) as $Side => $List) {
			foreach ($List as $Id => $Who) {
				$Tech  = $Who['techno'];
				$Html .= "<table border=1 width=100%><tr><th><br /><center>";
				$Html .= sprintf($lang[($Side == 'att') ? 'sys_attack_attacker_pos' : 'sys_attack_defender_pos'], $Who['name'], $Who['galaxy'], $Who['system'], $Who['planet']);
				$Html .= "<br />". sprintf($lang['sys_attack_techologies'], $Tech['military_tech'] * 10, $Tech['defence_tech'] * 10, $Tech['shield_tech'] * 10);
				$Html .= "<table border=1>";
				$Stats = $Data['stats'][$Side][$Id] ?? array();
				$Units = 0;
				foreach ($Stats as $Type => $Values) {
					$Units = $Units + $Values['count'];
				}
				if ($Units > 0) {
					$Row = array(
						"<tr><th>". $lang['sys_ship_type'] ."</th>",
						"<tr><th>". $lang['sys_ship_count'] ."</th>",
						"<tr><th>". $lang['sys_ship_weapon'] ."</th>",
						"<tr><th>". $lang['sys_ship_shield'] ."</th>",
						"<tr><th>". $lang['sys_ship_armour'] ."</th>",
					);
					foreach ($Stats as $Type => $Values) {
						$Count = ($Final) ? $Result['combat_end'][$Side][$Id][$Type] : $Values['count'];
						if ($Count > 0) {
							$Row[0] .= "<th>". $lang['tech_rc'][$Type] ."</th>";
							$Row[1] .= "<th>". pretty_number($Count) ."</th>";
							$Row[2] .= "<th>". pretty_number(round($Values['attack'] / $Values['count'])) ."</th>";
							$Row[3] .= "<th>". pretty_number(round($Values['shield'] / $Values['count'])) ."</th>";
							$Row[4] .= "<th>". pretty_number(round($Values['armour'] / $Values['count'])) ."</th>";
						}
					}
					$Html .= implode("</tr>", $Row) ."</tr>";
				} else {
					$Html .= "<br />". $lang['sys_destroyed'];
				}
				$Html .= "</table></center></th></tr></table>";
			}
		}
		if (!$Destroyed) {
			$Html .= "<br /><center>". sprintf($lang['sys_attack_attack_wave'], pretty_number(floor($Data['attack']['att'])), pretty_number(floor($Data['absorbed']['def'])));
			$Html .= "<br />". sprintf($lang['sys_attack_defend_wave'], pretty_number(floor($Data['attack']['def'])), pretty_number(floor($Data['absorbed']['att']))) ."</center>";
		}
	}
	return array('html' => $Html, 'first_round' => $FirstRound);
}

?>
