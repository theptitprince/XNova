<?php

/**
 * GetBuildingTime.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * GetBuildingTime
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Calcul du temps de construction d'un Element (Batiment / Recherche / Defense / Vaisseau )
// $user       -> Le Joueur lui meme
// $planet     -> La planete sur laquelle l'Element doit etre construit
// $Element    -> L'Element que l'on convoite
function GetBuildingTime ($user, $planet, $Element) {
	global $pricelist, $resource, $reslist, $game_config;
	// Laboratoires des autres planetes (reseau de recherche), lus une fois par joueur et par planete de recherche
	// pendant une page (0.9k) : la page Recherche les relisait pour chaque technologie affichee. Aucune page ne
	// change le laboratoire d'une autre planete entre deux calculs de temps de recherche
	static $OtherLabsOf = array();


	$level = (!empty($planet[$resource[$Element]])) ? $planet[$resource[$Element]] : ($user[$resource[$Element]] ?? 0);
	if       (in_array($Element, $reslist['build'])) {
		// Pour un batiment ...
		$cost_metal   = floor($pricelist[$Element]['metal']   * pow($pricelist[$Element]['factor'], $level));
		$cost_crystal = floor($pricelist[$Element]['crystal'] * pow($pricelist[$Element]['factor'], $level));
		$time         = ((($cost_crystal) + ($cost_metal)) / $game_config['game_speed']) * (1 / ($planet[$resource['14']] + 1)) * pow(0.5, $planet[$resource['15']]);
		$time         = floor(($time * 60 * 60) * (1 - (($user['rpg_constructeur']) * 0.1)));
	} elseif (in_array($Element, $reslist['tech'])) {
		// Pour une recherche
		$cost_metal   = floor($pricelist[$Element]['metal']   * pow($pricelist[$Element]['factor'], $level));
		$cost_crystal = floor($pricelist[$Element]['crystal'] * pow($pricelist[$Element]['factor'], $level));
		// Reseau de recherche intergalactique : le laboratoire de la planete de recherche, plus un laboratoire par niveau,
		// les plus developpes des autres planetes (description du jeu). L'original triait sans effet (asort garde les
		// cles) : il additionnait les premieres planetes de la base, lunes comprises, sans forcement compter celle de la
		// recherche (au niveau 1, une recherche lancee depuis le plus gros laboratoire pouvait etre plus lente).
		$lablevel     = intval($planet[$resource['31']] ?? 0);
		$intergal_lab = intval($user[$resource[123]] ?? 0);
		if ($intergal_lab >= 1) {
			$LabsKey = intval($user['id']) .':'. intval($planet['id']);
			if (!isset($OtherLabsOf[$LabsKey])) {
				$OtherLabs = array();
				$empire    = doquery("SELECT `". $resource['31'] ."` FROM {{table}} WHERE `id_owner` = '". intval($user['id']) ."' AND `id` <> '". intval($planet['id']) ."' AND `planet_type` = '1';", 'planets');
				while ($colonie = mysqli_fetch_assoc($empire)) {
					$OtherLabs[] = intval($colonie[$resource['31']]);
				}
				rsort($OtherLabs);
				$OtherLabsOf[$LabsKey] = $OtherLabs;
			}
			$OtherLabs = $OtherLabsOf[$LabsKey];
			$lablevel += array_sum(array_slice($OtherLabs, 0, $intergal_lab));
		}
		$time         = (($cost_metal + $cost_crystal) / $game_config['game_speed']) / (($lablevel + 1) * 2);
		$time         = floor(($time * 60 * 60) * (1 - (($user['rpg_scientifique']) * 0.1)));
	} elseif (in_array($Element, $reslist['defense'])) {
		// Pour les defenses ou la flotte 'tarif fixe' durée adaptée a u niveau nanite et usine robot
		$time         = (($pricelist[$Element]['metal'] + $pricelist[$Element]['crystal']) / $game_config['game_speed']) * (1 / ($planet[$resource['21']] + 1)) * pow(1 / 2, $planet[$resource['15']]);
		$time         = floor(($time * 60 * 60) * (1 - (($user['rpg_defenseur'])   * 0.375)));
	} elseif (in_array($Element, $reslist['fleet'])) {
		$time         = (($pricelist[$Element]['metal'] + $pricelist[$Element]['crystal']) / $game_config['game_speed']) * (1 / ($planet[$resource['21']] + 1)) * pow(1 / 2, $planet[$resource['15']]);
		$time         = floor(($time * 60 * 60) * (1 - (($user['rpg_technocrate']) * 0.05)));
	}


	return $time;
}

?>