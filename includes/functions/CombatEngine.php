<?php

/**
 * CombatEngine.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Moteur de combat de Renaissance (0.9i), a plusieurs participants par camp : attaque groupee, flottes alliees en
// stationnement. Il remplace includes/ataki.php (licence CC BY-NC-SA, non commerciale) et donne exactement les memes
// resultats pour un attaquant contre un defenseur : memes formules jusqu'a l'ordre des operations (meme arrondi des
// nombres a virgule), memes tirages aleatoires dans le meme ordre. Preuve : tests\test_moteur.php compare les deux
// moteurs, avec la meme graine aleatoire, sur des milliers de combats.
//
// $Attackers, $Defenders : listes de participants, chacun array('fleet' => array(type => nombre), 'techno' =>
// array('military_tech' =>, 'defence_tech' =>, 'shield_tech' =>, 'rpg_amiral' =>)). Le premier defenseur est le
// proprietaire de la planete : lui seul a des defenses, reconstruites en partie apres le combat.
//
// Deroulement (celui de l'original, etendu a plusieurs joueurs par camp) :
// - 7 tours au plus. Au debut de chaque tour, chaque groupe (un type de vaisseau ou de defense chez un joueur) tire
//   un bouclier puis une attaque entre 80 et 120 %, avec les technologies de son joueur ; les attaquants d'abord,
//   puis les defenseurs. Le combat s'arrete des qu'un camp n'a plus rien ;
// - les tirs recus par un camp se repartissent sur tous ses groupes au prorata de leur nombre. Le bouclier du groupe
//   absorbe d'abord, le reste detruit une unite par coque de base (prix / 10), au plus autant qu'il y a d'unites en
//   face a proportion. Comme dans l'original, la technologie Protection et la coque de l'Amiral ne servent qu'a
//   l'affichage (A : a corriger apres la preuve d'identite, decision du 27/09/2026) ;
// - tir rapide : chaque type present dans un camp (meme detruit, quel que soit son nombre) retire 50 a 100 % de sa
//   valeur de tir rapide a chaque type present en face ; pertes reparties entre les joueurs qui ont ce type ;
// - apres le combat, 60 a 80 % des defenses detruites sont reconstruites ; debris : vaisseaux au taux de la flotte
//   (Fleet_Cdr), defenses au taux des defenses (Defs_Cdr).
//
// Resultat : 'attackers' / 'defenders' (flottes restantes, defenses reconstruites), 'combat_end' (flottes a la fin
// du combat, avant la reconstruction), 'result' ('a' attaquants vainqueurs, 'w' defenseurs, 'r' match nul), 'rounds'
// (pour le rapport : a chaque tour, nombre, attaque, bouclier et protection de chaque groupe, attaque et unites de
// chaque camp, tirs absorbes par les boucliers de chaque camp), 'debris' (metal, cristal), 'lost' (valeur perdue
// par camp) et 'lost_by' (par participant).
function CombatEngine ( $Attackers, $Defenders ) {
	global $pricelist, $CombatCaps, $game_config;

	$Fleets = array('att' => array(), 'def' => array());
	$Techno = array('att' => array(), 'def' => array());
	foreach (array('att' => $Attackers, 'def' => $Defenders) as $Side => $List) {
		foreach ($List as $Id => $Participant) {
			$Fleets[$Side][$Id] = array();
			foreach ($Participant['fleet'] as $Type => $Count) {
				$Fleets[$Side][$Id][$Type] = $Count;
			}
			$Techno[$Side][$Id] = $Participant['techno'];
		}
	}
	$Begin  = $Fleets;
	$Rounds = array();
	$Units  = array('att' => 0, 'def' => 0);

	for ($Round = 1; $Round <= 7; $Round++) {
		$Power    = array('att' => 0, 'def' => 0);
		$Units    = array('att' => 0, 'def' => 0);
		$Absorbed = array('att' => 0, 'def' => 0);
		$Stats    = array('att' => array(), 'def' => array());
		foreach (array('att', 'def') as $Side) {
			foreach ($Fleets[$Side] as $Id => $Fleet) {
				$Tech   = $Techno[$Side][$Id];
				$Amiral = ($Tech['rpg_amiral'] ?? 0);
				$Stats[$Side][$Id] = array();
				foreach ($Fleet as $Type => $Count) {
					$Armour  = $Count * ($pricelist[$Type]['metal'] + $pricelist[$Type]['crystal']) / 10 * (1 + (0.1 * ($Tech['defence_tech']) + (0.05 * $Amiral)));
					$Rand    = rand(80, 120) / 100;
					$Shield  = $Count * $CombatCaps[$Type]['shield'] * (1 + (0.1 * $Tech['shield_tech']) + (0.05 * $Amiral)) * $Rand;
					// (l'original n'ecrivait pas le bonus d'armes pareil pour l'attaquant et le defenseur : meme valeur,
					// arrondi du dernier chiffre different, garde tel quel)
					if ($Side == 'att') {
						$Weapons = (1 + (0.1 * $Tech['military_tech'] + (0.05 * $Amiral)));
					} else {
						$Weapons = (1 + (0.1 * $Tech['military_tech']) + (0.05 * $Amiral));
					}
					$Rand    = rand(80, 120) / 100;
					$Attack  = $Count * $CombatCaps[$Type]['attack'] * $Weapons * $Rand;
					$Stats[$Side][$Id][$Type] = array('count' => $Count, 'attack' => $Attack, 'shield' => $Shield, 'armour' => $Armour);
					$Power[$Side] = $Power[$Side] + $Attack;
					$Units[$Side] = $Units[$Side] + $Count;
				}
			}
		}
		$Rounds[$Round] = array('stats' => $Stats, 'attack' => $Power, 'units' => $Units, 'absorbed' => $Absorbed);
		if ($Units['att'] == 0 || $Units['def'] == 0) {
			break;
		}

		// Pertes : tirs du camp d'en face repartis au prorata du nombre, boucliers d'abord, puis coque de base
		$After = array('att' => array(), 'def' => array());
		foreach (array('att' => 'def', 'def' => 'att') as $Side => $Enemy) {
			foreach ($Fleets[$Side] as $Id => $Fleet) {
				$After[$Side][$Id] = array();
				foreach ($Fleet as $Type => $Count) {
					$Received = $Count * $Power[$Enemy] / $Units[$Side];
					$Shield   = $Stats[$Side][$Id][$Type]['shield'];
					if ($Shield < $Received) {
						$MaxLoss  = floor($Count * $Units[$Enemy] / $Units[$Side]);
						$Received = $Received - $Shield;
						$Absorbed[$Side] = $Absorbed[$Side] + $Shield;
						$Loss     = floor(($Received / (($pricelist[$Type]['metal'] + $pricelist[$Type]['crystal']) / 10)));
						if ($Loss > $MaxLoss) {
							$Loss = $MaxLoss;
						}
						$Left = ceil($Count - $Loss);
						if ($Left <= 0) {
							$Left = 0;
						}
					} else {
						$Left = $Count;
						$Absorbed[$Side] = $Absorbed[$Side] + $Received;
					}
					$After[$Side][$Id][$Type] = $Left;
				}
			}
		}

		// Tir rapide : les types des attaquants, puis ceux des defenseurs
		foreach (array('att' => 'def', 'def' => 'att') as $Side => $Enemy) {
			foreach (CombatTypesPresent($Fleets[$Side]) as $Type) {
				foreach ($CombatCaps[$Type]['sd'] as $Target => $Value) {
					$Holders = array();
					foreach ($Fleets[$Enemy] as $Id => $Fleet) {
						if (isset($Fleet[$Target])) {
							$Holders[] = $Id;
						}
					}
					if (count($Holders) == 0) {
						continue;
					}
					$Kills = floor($Value * rand(50, 100) / 100);
					CombatRemoveUnits($After[$Enemy], $Holders, $Target, $Kills);
				}
			}
		}

		$Rounds[$Round]['absorbed'] = $Absorbed;
		$Fleets = $After;
	}

	// Vainqueur : d'apres les unites du dernier tour joue (7 tours sans vainqueur : match nul)
	if ($Units['att'] == 0 || $Units['def'] == 0) {
		if ($Units['att'] == 0 && $Units['def'] == 0) {
			$Result = 'r';
		} elseif ($Units['att'] == 0) {
			$Result = 'w';
		} else {
			$Result = 'a';
		}
	} else {
		$Result = 'r';
	}
	$CombatEnd = $Fleets;

	// Valeur perdue (metal, cristal) : vaisseaux de chaque camp, defenses a part
	$Value = array();
	foreach (array('att', 'def') as $Side) {
		foreach (array('begin' => $Begin, 'end' => $CombatEnd) as $When => $Set) {
			$Value[$Side][$When] = array('metal' => 0, 'crystal' => 0, 'def_metal' => 0, 'def_crystal' => 0);
			foreach ($Set[$Side] as $Id => $Fleet) {
				foreach ($Fleet as $Type => $Count) {
					if ($Type < 300) {
						$Value[$Side][$When]['metal']   = $Value[$Side][$When]['metal']   + $Count * $pricelist[$Type]['metal'];
						$Value[$Side][$When]['crystal'] = $Value[$Side][$When]['crystal'] + $Count * $pricelist[$Type]['crystal'];
					} else {
						$Value[$Side][$When]['def_metal']   = $Value[$Side][$When]['def_metal']   + $Count * $pricelist[$Type]['metal'];
						$Value[$Side][$When]['def_crystal'] = $Value[$Side][$When]['def_crystal'] + $Count * $pricelist[$Type]['crystal'];
					}
				}
			}
		}
	}

	// Defenses detruites reconstruites a 60-80 %
	$DefensesLost = 0;
	$DefensesLeft = 0;
	$LostBy       = array('att' => array(), 'def' => array());
	foreach ($Fleets['def'] as $Id => $Fleet) {
		foreach ($Fleet as $Type => $Count) {
			if ($Type > 300) {
				$DefensesLost = $DefensesLost + (($Begin['def'][$Id][$Type] - $Count) * ($pricelist[$Type]['metal'] + $pricelist[$Type]['crystal']));
				$Fleets['def'][$Id][$Type] = $Count + (($Begin['def'][$Id][$Type] - $Count) * rand(60, 80) / 100);
				$DefensesLeft = $DefensesLeft + $Fleets['def'][$Id][$Type];
			}
		}
	}
	if ($DefensesLeft > 0 && $Units['att'] == 0) {
		$Result = 'w';
	}
	foreach (array('att', 'def') as $Side) {
		foreach ($Begin[$Side] as $Id => $Fleet) {
			$LostBy[$Side][$Id] = 0;
			foreach ($Fleet as $Type => $Count) {
				$LostBy[$Side][$Id] = $LostBy[$Side][$Id] + ($Count - $CombatEnd[$Side][$Id][$Type]) * ($pricelist[$Type]['metal'] + $pricelist[$Type]['crystal']);
			}
		}
	}

	$Debris = array();
	$Debris['metal']   = ((($Value['att']['begin']['metal']   - $Value['att']['end']['metal'])   + ($Value['def']['begin']['metal']   - $Value['def']['end']['metal']))   * ($game_config['Fleet_Cdr'] / 100));
	$Debris['crystal'] = ((($Value['att']['begin']['crystal'] - $Value['att']['end']['crystal']) + ($Value['def']['begin']['crystal'] - $Value['def']['end']['crystal'])) * ($game_config['Fleet_Cdr'] / 100));
	$Debris['metal']   += (($Value['def']['begin']['def_metal']   - $Value['def']['end']['def_metal'])   * ($game_config['Defs_Cdr'] / 100));
	$Debris['crystal'] += (($Value['def']['begin']['def_crystal'] - $Value['def']['end']['def_crystal']) * ($game_config['Defs_Cdr'] / 100));

	$Lost = array();
	$Lost['att'] = (($Value['att']['begin']['metal'] - $Value['att']['end']['metal']) + ($Value['att']['begin']['crystal'] - $Value['att']['end']['crystal']));
	$Lost['def'] = (($Value['def']['begin']['metal'] - $Value['def']['end']['metal']) + ($Value['def']['begin']['crystal'] - $Value['def']['end']['crystal']) + $DefensesLost);

	return array(
		'attackers'  => $Fleets['att'],
		'defenders'  => $Fleets['def'],
		'combat_end' => $CombatEnd,
		'result'     => $Result,
		'rounds'     => $Rounds,
		'debris'     => $Debris,
		'lost'       => $Lost,
		'lost_by'    => $LostBy,
	);
}

// Types presents dans un camp (tous participants), dans l'ordre ou ils apparaissent
function CombatTypesPresent ( $SideFleets ) {
	$Types = array();
	foreach ($SideFleets as $Fleet) {
		foreach ($Fleet as $Type => $Count) {
			$Types[$Type] = $Type;
		}
	}
	return array_values($Types);
}

// Retire $Kills unites du type $Type aux participants $Holders d'un camp (tir rapide). Un seul participant : comme
// l'original. Plusieurs : au prorata de ce qu'il leur reste, le reste de l'arrondi une unite a la fois, dans l'ordre.
function CombatRemoveUnits ( &$SideFleets, $Holders, $Type, $Kills ) {
	if (count($Holders) == 1) {
		$Id = $Holders[0];
		$SideFleets[$Id][$Type] = $SideFleets[$Id][$Type] - $Kills;
		if ($SideFleets[$Id][$Type] <= 0) {
			$SideFleets[$Id][$Type] = 0;
		}
		return;
	}
	$Total = 0;
	foreach ($Holders as $Id) {
		$Total = $Total + max(0, $SideFleets[$Id][$Type]);
	}
	if ($Total <= 0 || $Kills <= 0) {
		return;
	}
	$Kills   = min($Kills, $Total);
	$Removed = 0;
	foreach ($Holders as $Id) {
		$Part = floor($Kills * max(0, $SideFleets[$Id][$Type]) / $Total);
		$SideFleets[$Id][$Type] = $SideFleets[$Id][$Type] - $Part;
		$Removed = $Removed + $Part;
	}
	foreach ($Holders as $Id) {
		if ($Removed >= $Kills) {
			break;
		}
		if ($SideFleets[$Id][$Type] > 0) {
			$SideFleets[$Id][$Type] = $SideFleets[$Id][$Type] - 1;
			$Removed = $Removed + 1;
		}
	}
}

?>
