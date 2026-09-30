<?php

/**
 * statfunctions.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * StatFunctions.php
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function GetTechnoPoints ( $CurrentUser ) {
	global $resource, $pricelist, $reslist;

	$TechCounts = 0;
	$TechPoints = 0;
	foreach ( $reslist['tech'] as $n => $Techno ) {
		if ( $CurrentUser[ $resource[ $Techno ] ] > 0 ) {
			for ( $Level = 1; $Level < $CurrentUser[ $resource[ $Techno ] ]; $Level++ ) {
				$Units       = $pricelist[ $Techno ]['metal'] + $pricelist[ $Techno ]['crystal'] + $pricelist[ $Techno ]['deuterium'];
				$LevelMul    = pow( $pricelist[ $Techno ]['factor'], $Level );
				$TechPoints += ($Units * $LevelMul);
				$TechCounts += 1;
			}
		}
	}
	$RetValue['TechCount'] = $TechCounts;
	$RetValue['TechPoint'] = $TechPoints;

	return $RetValue;
}

function GetBuildPoints ( $CurrentPlanet ) {
	global $resource, $pricelist, $reslist;

	$BuildCounts = 0;
	$BuildPoints = 0;
	foreach($reslist['build'] as $n => $Building) {
		if ( $CurrentPlanet[ $resource[ $Building ] ] > 0 ) {
			for ( $Level = 1; $Level < $CurrentPlanet[ $resource[ $Building ] ]; $Level++ ) {
				$Units        = $pricelist[ $Building ]['metal'] + $pricelist[ $Building ]['crystal'] + $pricelist[ $Building ]['deuterium'];
				$LevelMul     = pow( $pricelist[ $Building ]['factor'], $Level );
				$BuildPoints += ($Units * $LevelMul);
				$BuildCounts += 1;
			}
		}
	}
	$RetValue['BuildCount'] = $BuildCounts;
	$RetValue['BuildPoint'] = $BuildPoints;

	return $RetValue;
}

function GetDefensePoints ( $CurrentPlanet ) {
	global $resource, $pricelist, $reslist;

	$DefenseCounts = 0;
	$DefensePoints = 0;
	foreach($reslist['defense'] as $n => $Defense) {
		if ($CurrentPlanet[ $resource[ $Defense ] ] > 0) {
			$Units          = $pricelist[ $Defense ]['metal'] + $pricelist[ $Defense ]['crystal'] + $pricelist[ $Defense ]['deuterium'];
			$DefensePoints += ($Units * $CurrentPlanet[ $resource[ $Defense ] ]);
			$DefenseCounts += $CurrentPlanet[ $resource[ $Defense ] ];
		}
	}
	$RetValue['DefenseCount'] = $DefenseCounts;
	$RetValue['DefensePoint'] = $DefensePoints;

	return $RetValue;
}

function GetFleetPoints ( $CurrentPlanet ) {
	global $resource, $pricelist, $reslist;

	$FleetCounts = 0;
	$FleetPoints = 0;
	foreach($reslist['fleet'] as $n => $Fleet) {
		if ($CurrentPlanet[ $resource[ $Fleet ] ] > 0) {
			$Units          = $pricelist[ $Fleet ]['metal'] + $pricelist[ $Fleet ]['crystal'] + $pricelist[ $Fleet ]['deuterium'];
			$FleetPoints   += ($Units * $CurrentPlanet[ $resource[ $Fleet ] ]);
			$FleetCounts   += $CurrentPlanet[ $resource[ $Fleet ] ];
		}
	}
	$RetValue['FleetCount'] = $FleetCounts;
	$RetValue['FleetPoint'] = $FleetPoints;

	return $RetValue;
}

// Lignes par requete groupee (UPDATE ... CASE, INSERT de plusieurs lignes)
define('STAT_LOT', 200);

// Classement de chaque categorie pour un type de statistiques (1 : joueurs, 2 : alliances). Meme lecture triee que
// l'original (memes ex aequo) ; avec $Grouped (lignes qui ne changent ni de place ni de taille, voir StatTableFormat),
// les rangs sont ecrits par lots : un UPDATE par joueur relisait toute la table
function StatComputeRanks ( $StatType, $Grouped ) {
	$Cats  = array('tech', 'build', 'defs', 'fleet', 'total');
	$Ranks = array();
	foreach ($Cats as $Cat) {
		$Rank    = 1;
		$RankQry = doquery("SELECT `id_owner` FROM {{table}} WHERE `stat_type` = '". $StatType ."' AND `stat_code` = '1' ORDER BY `". $Cat ."_points` DESC;", 'statpoints');
		while ($TheRank = mysqli_fetch_assoc($RankQry)) {
			if ($Grouped) {
				// Proprietaire present deux fois : le dernier rang l'emporte, comme avec les UPDATE successifs
				$Ranks[$TheRank['id_owner']][$Cat] = $Rank;
			} else {
				// Lignes qui peuvent grandir (MyISAM a lignes dynamiques, autre moteur) : l'ordre des ecritures decide de la
				// place des morceaux de ligne, donc un UPDATE par rang comme a l'origine
				doquery("UPDATE {{table}} SET `". $Cat ."_rank` = '". $Rank ."' WHERE `stat_type` = '". $StatType ."' AND `stat_code` = '1' AND `id_owner` = '". $TheRank['id_owner'] ."';", 'statpoints');
			}
			$Rank++;
		}
	}
	foreach (array_chunk($Ranks, STAT_LOT, true) as $Lot) {
		$QryUpdate = array();
		foreach ($Cats as $Cat) {
			// Proprietaire absent d'une des lectures (ligne supprimee entre-temps) : rang laisse tel quel, comme a l'origine
			$Case = "";
			foreach ($Lot as $Owner => $OwnerRanks) {
				if (isset($OwnerRanks[$Cat])) {
					$Case .= " WHEN ". intval($Owner) ." THEN '". $OwnerRanks[$Cat] ."'";
				}
			}
			if ($Case != "") {
				$QryUpdate[] = "`". $Cat ."_rank` = CASE `id_owner`". $Case ." ELSE `". $Cat ."_rank` END";
			}
		}
		doquery("UPDATE {{table}} SET ". implode(', ', $QryUpdate) ." WHERE `stat_type` = '". $StatType ."' AND `stat_code` = '1' AND `id_owner` IN (". implode(',', array_map('intval', array_keys($Lot))) .");", 'statpoints');
	}
}

// Anciennes statistiques d'un type, lues en une fois : rangs de l'ancienne ligne de chaque proprietaire et nombre
// de lignes
function StatReadOld ( $StatType, &$OldCount ) {
	$OldStats = array();
	$OldCount = array();
	$OldQry   = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '". $StatType ."';", 'statpoints');
	while ($OldRow = mysqli_fetch_assoc($OldQry)) {
		if (!isset($OldStats[$OldRow['id_owner']])) {
			$OldStats[$OldRow['id_owner']] = array('total_rank' => $OldRow['total_rank'], 'tech_rank' => $OldRow['tech_rank'], 'build_rank' => $OldRow['build_rank'],
			                                       'defs_rank' => $OldRow['defs_rank'], 'fleet_rank' => $OldRow['fleet_rank']);
			$OldCount[$OldRow['id_owner']] = 0;
		}
		$OldCount[$OldRow['id_owner']]++;
	}
	mysqli_free_result($OldQry);
	// Lignes en double (rare) : premiere ligne de la requete d'origine, faite par proprietaire (son ordre peut suivre
	// un index)
	foreach ($OldCount as $Owner => $Count) {
		if ($Count > 1) {
			$OldRow = doquery ("SELECT * FROM {{table}} WHERE `stat_type` = '". $StatType ."' AND `id_owner` = '". $Owner ."';",'statpoints', true);
			if ($OldRow) {
				$OldStats[$Owner] = array('total_rank' => $OldRow['total_rank'], 'tech_rank' => $OldRow['tech_rank'], 'build_rank' => $OldRow['build_rank'],
				                          'defs_rank' => $OldRow['defs_rank'], 'fleet_rank' => $OldRow['fleet_rank']);
			} else {
				unset($OldStats[$Owner]);
			}
		}
	}
	return $OldStats;
}

// Moteur et format de la table statpoints :
//  - 'fixed'   : lignes qui ne changent jamais de place ni de taille (MyISAM a lignes fixes, InnoDB rangee par sa cle) :
//                des rangs ecrits dans un autre ordre donnent la meme table ;
//  - 'inplace' : MyISAM a lignes fixes avec les colonnes creees par le jeu (entiers NOT NULL, rangs a 0 quand l'INSERT
//                ne les donne pas) : une ligne supprimee puis inseree aussitot reprend la meme place. La reecrire sur
//                place donne alors la meme table (memes lignes, meme ordre)
function StatTableFormat () {
	$Table = doquery("SELECT `ENGINE` AS `engine`, `ROW_FORMAT` AS `row_format`, (SELECT GROUP_CONCAT(`COLUMN_NAME`, IF(`IS_NULLABLE` = 'NO' AND `DATA_TYPE` LIKE '%int' AND IFNULL(`COLUMN_DEFAULT`, '0') IN ('0', '''0'''), '', '?') ORDER BY `ORDINAL_POSITION`) FROM information_schema.COLUMNS WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = '{{table}}') AS `cols` FROM information_schema.TABLES WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = '{{table}}';", 'statpoints', true);
	$Cols  = 'id_owner,id_ally,stat_type,stat_code,tech_rank,tech_old_rank,tech_points,tech_count,build_rank,build_old_rank,build_points,build_count,'
	       . 'defs_rank,defs_old_rank,defs_points,defs_count,fleet_rank,fleet_old_rank,fleet_points,fleet_count,total_rank,total_old_rank,total_points,total_count,stat_date';
	$Fixed = ($Table && strtolower($Table['engine']) == 'myisam' && strtolower($Table['row_format']) == 'fixed');
	return array('fixed'   => ($Fixed || ($Table && strtolower($Table['engine']) == 'innodb')),
	             'inplace' => ($Fixed && strtolower($Table['cols']) == $Cols));
}

// Nouvelles lignes d'un type de statistiques, ecrites dans l'ordre d'origine (suppression de l'ancienne ligne puis
// insertion, proprietaire par proprietaire) avec le meme resultat :
//  - une seule ancienne ligne et $InPlace : reecrite sur place, par lots (voir StatTableFormat) ;
//  - sinon (nouveau proprietaire, lignes en double, autre moteur) : memes DELETE et INSERT, dans le meme ordre
//    (insertions qui se suivent groupees)
function StatWriteRows ( $StatType, $Rows, $OldCount, $InPlace ) {
	$Update = array();
	$Insert = array();
	foreach ($Rows as $Row) {
		$Old = isset($OldCount[$Row['id_owner']]) ? $OldCount[$Row['id_owner']] : 0;
		if ($InPlace && $Old == 1) {
			$Update[] = $Row;
			if (count($Update) >= STAT_LOT) {
				StatUpdateRows($StatType, $Update);
				$Update = array();
			}
		} else {
			if ($Old > 0) {
				StatInsertRows($Insert);
				$Insert = array();
				doquery ("DELETE FROM {{table}} WHERE `stat_type` = '". $StatType ."' AND `id_owner` = '". $Row['id_owner'] ."';",'statpoints');
			}
			$Insert[] = $Row;
			if (count($Insert) >= STAT_LOT) {
				StatInsertRows($Insert);
				$Insert = array();
			}
		}
	}
	StatUpdateRows($StatType, $Update);
	StatInsertRows($Insert);
}

// Insertion de plusieurs lignes (colonnes et valeurs de l'INSERT d'origine, rangs a 0 par defaut)
function StatInsertRows ( $Rows ) {
	if (count($Rows) == 0) {
		return;
	}
	$QryValues = array();
	foreach ($Rows as $Row) {
		$Values = array();
		foreach ($Row as $Value) {
			$Values[] = "'". $Value ."'";
		}
		$QryValues[] = "(". implode(', ', $Values) .")";
	}
	doquery ( "INSERT INTO {{table}} (`". implode('`, `', array_keys($Rows[0])) ."`) VALUES ". implode(', ', $QryValues) .";" , 'statpoints');
}

// Reecriture sur place de lignes (une seule par proprietaire) : toutes les colonnes, comme une ligne inseree
function StatUpdateRows ( $StatType, $Rows ) {
	if (count($Rows) == 0) {
		return;
	}
	$QryUpdate = array();
	foreach (array('tech', 'build', 'defs', 'fleet', 'total') as $Cat) {
		$QryUpdate[] = "`". $Cat ."_rank` = '0'";
	}
	$Owners = array();
	foreach (array_keys($Rows[0]) as $Col) {
		if ($Col == 'id_owner' || $Col == 'stat_type') {
			continue;
		}
		$Case = "`". $Col ."` = CASE `id_owner`";
		foreach ($Rows as $Row) {
			$Case .= " WHEN ". intval($Row['id_owner']) ." THEN '". $Row[$Col] ."'";
		}
		$QryUpdate[] = $Case ." END";
	}
	foreach ($Rows as $Row) {
		$Owners[] = intval($Row['id_owner']);
	}
	doquery ( "UPDATE {{table}} SET ". implode(', ', $QryUpdate) ." WHERE `stat_type` = '". $StatType ."' AND `id_owner` IN (". implode(',', $Owners) .");" , 'statpoints');
}

// Points des planetes, par lots, dans l'ordre d'origine (joueur par joueur, puis ordre de la table) : ORDER BY FIELD
// garde cet ordre d'ecriture (place des lignes qui grandissent dans le fichier MyISAM)
function StatUpdatePlanets ( $Planets ) {
	foreach (array_chunk($Planets, STAT_LOT * 2) as $Lot) {
		$Case = "";
		$Ids  = array();
		foreach ($Lot as $Planet) {
			$Case .= " WHEN ". intval($Planet[0]) ." THEN '". $Planet[1] ."'";
			$Ids[] = intval($Planet[0]);
		}
		$Ids = implode(',', $Ids);
		doquery ( "UPDATE {{table}} SET `points` = CASE `id`". $Case ." END WHERE `id` IN (". $Ids .") ORDER BY FIELD(`id`, ". $Ids .");" , 'planets');
	}
}

// Recalcul complet des statistiques (joueurs puis alliances). Appele par admin/statbuilder.php (bouton de
// l'administration), par tools/stats.php (ligne de commande : tache planifiee, cron) et par le calcul automatique
// au passage des joueurs (common.php, avec $IfOlderThan en secondes). Retourne les compteurs, ou false si un calcul
// est deja en cours (ou, pour le calcul automatique, si le dernier est trop recent).
// 0.9k : chaque table lue une fois et ecritures groupees (une requete par joueur et par rang auparavant, plus de 40 000
// requetes pour 3 000 joueurs) ; memes valeurs, meme place des lignes et memes ex aequo que le calcul d'origine
function BuildStatistics ( $IfOlderThan = 0 ) {
	global $game_config;

	// Un seul calcul a la fois : verrou nomme de MySQL, pris sans attendre (deux calculs simultanes decalaient ou
	// vidaient les classements)
	$Lock = doquery("SELECT GET_LOCK(LEFT(CONCAT(DATABASE(), '.{{table}}'), 64), 0) AS `locked`;", 'statistics', true);
	if (empty($Lock['locked'])) {
		return false;
	}
	// Calcul automatique : date relue sous le verrou (une autre page a pu faire le calcul depuis la lecture des reglages)
	if ($IfOlderThan > 0) {
		$Last = doquery("SELECT `config_value` FROM {{table}} WHERE `config_name` = 'stat_last';", 'config', true);
		if ($Last && time() - intval($Last['config_value']) < $IfOlderThan) {
			doquery("DO RELEASE_LOCK(LEFT(CONCAT(DATABASE(), '.{{table}}'), 64));", 'statistics');
			return false;
		}
	}
	// Plus de 30 s possibles sur un gros univers : ni la limite de PHP ni un joueur qui quitte la page ne coupent le calcul
	ignore_user_abort(true);
	if (function_exists('set_time_limit')) {
		@set_time_limit(0);
	}

	$StatDate   = time();
	// Date du dernier calcul (page Statistiques de l'administration, calcul automatique), enregistree des le debut :
	// un calcul interrompu n'est pas relance a chaque page
	doquery("UPDATE {{table}} SET `config_value` = '". $StatDate ."' WHERE `config_name` = 'stat_last';", 'config');
	$game_config['stat_last'] = $StatDate;
	$Divider    = max(1, floatval($game_config['stat_settings']));
	$UserCount  = 0;
	$AllyCount  = 0;
	// Rotation des statistiques
	doquery ( "DELETE FROM {{table}} WHERE `stat_code` = '2';" , 'statpoints');
	doquery ( "UPDATE {{table}} SET `stat_code` = `stat_code` + '1';" , 'statpoints');

	// Rangs par lots et ecriture sur place possibles (voir StatTableFormat), anciennes statistiques des joueurs lues en
	// une fois
	$Format     = StatTableFormat();
	$InPlace    = $Format['inplace'];
	$OldStats   = StatReadOld(1, $OldCount);

	// Recherches de chaque joueur (joueurs dans l'ordre de la table, comme a l'origine)
	$Users      = array();
	$GameUsers  = doquery("SELECT * FROM {{table}}", 'users');
	while ($CurUser = mysqli_fetch_assoc($GameUsers)) {
		// Total des unitées consommée pour la recherche
		$Points     = GetTechnoPoints ( $CurUser );
		$Users[$CurUser['id']] = array('id' => $CurUser['id'], 'ally_id' => $CurUser['ally_id'], 'TechCount' => $Points['TechCount'],
		                               'TechPoint' => ($Points['TechPoint'] / $Divider));
	}
	// Memoire : resultats lus liberes des qu'ils ne servent plus (gros univers)
	mysqli_free_result($GameUsers);

	// Planetes lues en une seule fois (une requete par joueur parcourait toute la table) : dans l'ordre de la table,
	// donc pour chaque joueur dans l'ordre de la requete d'origine (memes sommes, dans le meme ordre)
	$UsrPlanets = array();
	$GamePlanets = doquery("SELECT * FROM {{table}}", 'planets');
	while ($CurPlanet = mysqli_fetch_assoc($GamePlanets)) {
		if (isset($Users[$CurPlanet['id_owner']])) {
			$Build   = GetBuildPoints ( $CurPlanet );
			$Defense = GetDefensePoints ( $CurPlanet );
			$Fleet   = GetFleetPoints ( $CurPlanet );
			$UsrPlanets[$CurPlanet['id_owner']][] = array($CurPlanet['id'], $Build['BuildCount'], $Build['BuildPoint'], $Defense['DefenseCount'],
			                                              $Defense['DefensePoint'], $Fleet['FleetCount'], $Fleet['FleetPoint']);
		}
	}
	mysqli_free_result($GamePlanets);

	$StatRows   = array();
	$PlanetRows = array();
	foreach ($Users as $CurUser) {
		// Recuperation des anciennes statistiques
		if (isset($OldStats[$CurUser['id']])) {
			$OldStatRecord = $OldStats[$CurUser['id']];
			$OldTotalRank = $OldStatRecord['total_rank'];
			$OldTechRank  = $OldStatRecord['tech_rank'];
			$OldBuildRank = $OldStatRecord['build_rank'];
			$OldDefsRank  = $OldStatRecord['defs_rank'];
			$OldFleetRank = $OldStatRecord['fleet_rank'];
		} else {
			$OldTotalRank = 0;
			$OldTechRank  = 0;
			$OldBuildRank = 0;
			$OldDefsRank  = 0;
			$OldFleetRank = 0;
		}

		$TTechCount     = $CurUser['TechCount'];
		$TTechPoints    = $CurUser['TechPoint'];

		// Totalisation des points accumulés par planete
		$TBuildCount    = 0;
		$TBuildPoints   = 0;
		$TDefsCount     = 0;
		$TDefsPoints    = 0;
		$TFleetCount    = 0;
		$TFleetPoints   = 0;
		$GCount         = $TTechCount;
		$GPoints        = $TTechPoints;
		if (isset($UsrPlanets[$CurUser['id']])) {
			foreach ($UsrPlanets[$CurUser['id']] as $CurPlanet) {
				// $CurPlanet : id, batiments (nombre, points), defenses (nombre, points), flotte (nombre, points)
				$TBuildCount     += $CurPlanet[1];
				$GCount          += $CurPlanet[1];
				$PlanetPoints     = ($CurPlanet[2] / $Divider);
				$TBuildPoints    += ($CurPlanet[2] / $Divider);

				$TDefsCount      += $CurPlanet[3];
				$GCount          += $CurPlanet[3];
				$PlanetPoints    += ($CurPlanet[4] / $Divider);
				$TDefsPoints     += ($CurPlanet[4] / $Divider);

				$TFleetCount     += $CurPlanet[5];
				$GCount          += $CurPlanet[5];
				$PlanetPoints    += ($CurPlanet[6] / $Divider);
				$TFleetPoints    += ($CurPlanet[6] / $Divider);

				$GPoints         += $PlanetPoints;
				$PlanetRows[]     = array($CurPlanet[0], $PlanetPoints);
			}
			unset($UsrPlanets[$CurUser['id']]);
		}

		$StatRows[] = array(
			'id_owner'       => $CurUser['id'],
			'id_ally'        => $CurUser['ally_id'],
			'stat_type'      => '1', // 1 pour joueur , 2 pour alliance
			'stat_code'      => '1', // de 1 a 2 mis a jour de maniere automatique
			'tech_points'    => $TTechPoints,
			'tech_count'     => $TTechCount,
			'tech_old_rank'  => $OldTechRank,
			'build_points'   => $TBuildPoints,
			'build_count'    => $TBuildCount,
			'build_old_rank' => $OldBuildRank,
			'defs_points'    => $TDefsPoints,
			'defs_count'     => $TDefsCount,
			'defs_old_rank'  => $OldDefsRank,
			'fleet_points'   => $TFleetPoints,
			'fleet_count'    => $TFleetCount,
			'fleet_old_rank' => $OldFleetRank,
			'total_points'   => $GPoints,
			'total_count'    => $GCount,
			'total_old_rank' => $OldTotalRank,
			'stat_date'      => $StatDate);
		$UserCount++;
	}
	StatUpdatePlanets($PlanetRows);
	unset($PlanetRows);
	StatWriteRows(1, $StatRows, $OldCount, $InPlace);
	unset($StatRows);

	StatComputeRanks(1, $Format['fixed']);

	// Statistiques des alliances ...
	$OldStats   = StatReadOld(2, $OldCount);
	// Somme des statistiques actuelles des membres (stat_code 1 : pas les restes d'un joueur supprime), pour toutes
	// les alliances en une requete
	$AllySums   = array();
	$QrySumSelect   = "SELECT `id_ally`, ";
	$QrySumSelect  .= "SUM(`tech_points`)  as `TechPoint`, ";
	$QrySumSelect  .= "SUM(`tech_count`)   as `TechCount`, ";
	$QrySumSelect  .= "SUM(`build_points`) as `BuildPoint`, ";
	$QrySumSelect  .= "SUM(`build_count`)  as `BuildCount`, ";
	$QrySumSelect  .= "SUM(`defs_points`)  as `DefsPoint`, ";
	$QrySumSelect  .= "SUM(`defs_count`)   as `DefsCount`, ";
	$QrySumSelect  .= "SUM(`fleet_points`) as `FleetPoint`, ";
	$QrySumSelect  .= "SUM(`fleet_count`)  as `FleetCount`, ";
	$QrySumSelect  .= "SUM(`total_points`) as `TotalPoint`, ";
	$QrySumSelect  .= "SUM(`total_count`)  as `TotalCount` ";
	$QrySumSelect  .= "FROM {{table}} ";
	$QrySumSelect  .= "WHERE ";
	$QrySumSelect  .= "`stat_type` = '1' AND `stat_code` = '1' ";
	$QrySumSelect  .= "GROUP BY `id_ally`;";
	$SumQry     = doquery( $QrySumSelect, 'statpoints');
	while ($Points = mysqli_fetch_assoc($SumQry)) {
		$AllySums[$Points['id_ally']] = $Points;
	}
	// Alliance sans membre : sommes NULL, comme la requete d'origine
	$NoMember   = array('TechPoint' => null, 'TechCount' => null, 'BuildPoint' => null, 'BuildCount' => null, 'DefsPoint' => null,
	                    'DefsCount' => null, 'FleetPoint' => null, 'FleetCount' => null, 'TotalPoint' => null, 'TotalCount' => null);

	$StatRows   = array();
	$GameAllys  = doquery("SELECT * FROM {{table}}", 'alliance');

	while ($CurAlly = mysqli_fetch_assoc($GameAllys)) {
		// Recuperation des anciennes statistiques
		if (isset($OldStats[$CurAlly['id']])) {
			$OldStatRecord = $OldStats[$CurAlly['id']];
			$OldTotalRank = $OldStatRecord['total_rank'];
			$OldTechRank  = $OldStatRecord['tech_rank'];
			$OldBuildRank = $OldStatRecord['build_rank'];
			$OldDefsRank  = $OldStatRecord['defs_rank'];
			$OldFleetRank = $OldStatRecord['fleet_rank'];
		} else {
			$OldTotalRank = 0;
			$OldTechRank  = 0;
			$OldBuildRank = 0;
			$OldDefsRank  = 0;
			$OldFleetRank = 0;
		}

		$Points     = isset($AllySums[$CurAlly['id']]) ? $AllySums[$CurAlly['id']] : $NoMember;

		$StatRows[] = array(
			'id_owner'       => $CurAlly['id'],
			'id_ally'        => '0',
			'stat_type'      => '2', // 1 pour joueur , 2 pour alliance
			'stat_code'      => '1',
			'tech_points'    => floatval($Points['TechPoint']),
			'tech_count'     => floatval($Points['TechCount']),
			'tech_old_rank'  => $OldTechRank,
			'build_points'   => floatval($Points['BuildPoint']),
			'build_count'    => floatval($Points['BuildCount']),
			'build_old_rank' => $OldBuildRank,
			'defs_points'    => floatval($Points['DefsPoint']),
			'defs_count'     => floatval($Points['DefsCount']),
			'defs_old_rank'  => $OldDefsRank,
			'fleet_points'   => floatval($Points['FleetPoint']),
			'fleet_count'    => floatval($Points['FleetCount']),
			'fleet_old_rank' => $OldFleetRank,
			'total_points'   => floatval($Points['TotalPoint']),
			'total_count'    => floatval($Points['TotalCount']),
			'total_old_rank' => $OldTotalRank,
			'stat_date'      => $StatDate);
		$AllyCount++;
	}
	StatWriteRows(2, $StatRows, $OldCount, $InPlace);

	// Classement des alliances : il n'etait jamais calcule (rangs toujours a 0)
	StatComputeRanks(2, $Format['fixed']);

	doquery("DO RELEASE_LOCK(LEFT(CONCAT(DATABASE(), '.{{table}}'), 64));", 'statistics');
	return array('users' => $UserCount, 'allys' => $AllyCount, 'date' => $StatDate);
}
?>