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

// Classement de chaque categorie pour un type de statistiques (1 : joueurs, 2 : alliances)
function StatComputeRanks ( $StatType ) {
	foreach (array('tech', 'build', 'defs', 'fleet', 'total') as $Cat) {
		$Rank    = 1;
		$RankQry = doquery("SELECT `id_owner` FROM {{table}} WHERE `stat_type` = '". $StatType ."' AND `stat_code` = '1' ORDER BY `". $Cat ."_points` DESC;", 'statpoints');
		while ($TheRank = mysqli_fetch_assoc($RankQry)) {
			doquery("UPDATE {{table}} SET `". $Cat ."_rank` = '". $Rank ."' WHERE `stat_type` = '". $StatType ."' AND `stat_code` = '1' AND `id_owner` = '". $TheRank['id_owner'] ."';", 'statpoints');
			$Rank++;
		}
	}
}

// Recalcul complet des statistiques (joueurs puis alliances). Appele par admin/statbuilder.php (bouton de
// l'administration), par tools/stats.php (ligne de commande : tache planifiee, cron) et par le calcul automatique
// au passage des joueurs (common.php, avec $IfOlderThan en secondes). Retourne les compteurs, ou false si un calcul
// est deja en cours (ou, pour le calcul automatique, si le dernier est trop recent).
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

	$GameUsers  = doquery("SELECT * FROM {{table}}", 'users');

	while ($CurUser = mysqli_fetch_assoc($GameUsers)) {
		// Recuperation des anciennes statistiques
		$OldStatRecord  = doquery ("SELECT * FROM {{table}} WHERE `stat_type` = '1' AND `id_owner` = '".$CurUser['id']."';",'statpoints', true);
		if ($OldStatRecord) {
			$OldTotalRank = $OldStatRecord['total_rank'];
			$OldTechRank  = $OldStatRecord['tech_rank'];
			$OldBuildRank = $OldStatRecord['build_rank'];
			$OldDefsRank  = $OldStatRecord['defs_rank'];
			$OldFleetRank = $OldStatRecord['fleet_rank'];
			// Suppression de l'ancien enregistrement
			doquery ("DELETE FROM {{table}} WHERE `stat_type` = '1' AND `id_owner` = '".$CurUser['id']."';",'statpoints');
		} else {
			$OldTotalRank = 0;
			$OldTechRank  = 0;
			$OldBuildRank = 0;
			$OldDefsRank  = 0;
			$OldFleetRank = 0;
		}

		// Total des unitées consommée pour la recherche
		$Points         = GetTechnoPoints ( $CurUser );
		$TTechCount     = $Points['TechCount'];
		$TTechPoints    = ($Points['TechPoint'] / $Divider);

		// Totalisation des points accumulés par planete
		$TBuildCount    = 0;
		$TBuildPoints   = 0;
		$TDefsCount     = 0;
		$TDefsPoints    = 0;
		$TFleetCount    = 0;
		$TFleetPoints   = 0;
		$GCount         = $TTechCount;
		$GPoints        = $TTechPoints;
		$UsrPlanets     = doquery("SELECT * FROM {{table}} WHERE `id_owner` = '". $CurUser['id'] ."';", 'planets');
		while ($CurPlanet = mysqli_fetch_assoc($UsrPlanets) ) {
			$Points           = GetBuildPoints ( $CurPlanet );
			$TBuildCount     += $Points['BuildCount'];
			$GCount          += $Points['BuildCount'];
			$PlanetPoints     = ($Points['BuildPoint'] / $Divider);
			$TBuildPoints    += ($Points['BuildPoint'] / $Divider);

			$Points           = GetDefensePoints ( $CurPlanet );
			$TDefsCount      += $Points['DefenseCount'];
			$GCount          += $Points['DefenseCount'];
			$PlanetPoints    += ($Points['DefensePoint'] / $Divider);
			$TDefsPoints     += ($Points['DefensePoint'] / $Divider);

			$Points           = GetFleetPoints ( $CurPlanet );
			$TFleetCount     += $Points['FleetCount'];
			$GCount          += $Points['FleetCount'];
			$PlanetPoints    += ($Points['FleetPoint'] / $Divider);
			$TFleetPoints    += ($Points['FleetPoint'] / $Divider);

			$GPoints         += $PlanetPoints;
			doquery ( "UPDATE {{table}} SET `points` = '". $PlanetPoints ."' WHERE `id` = '". $CurPlanet['id'] ."';" , 'planets');
		}

		$QryInsertStats  = "INSERT INTO {{table}} SET ";
		$QryInsertStats .= "`id_owner` = '". $CurUser['id'] ."', ";
		$QryInsertStats .= "`id_ally` = '". $CurUser['ally_id'] ."', ";
		$QryInsertStats .= "`stat_type` = '1', "; // 1 pour joueur , 2 pour alliance
		$QryInsertStats .= "`stat_code` = '1', "; // de 1 a 2 mis a jour de maniere automatique
		$QryInsertStats .= "`tech_points` = '". $TTechPoints ."', ";
		$QryInsertStats .= "`tech_count` = '". $TTechCount ."', ";
		$QryInsertStats .= "`tech_old_rank` = '". $OldTechRank ."', ";
		$QryInsertStats .= "`build_points` = '". $TBuildPoints ."', ";
		$QryInsertStats .= "`build_count` = '". $TBuildCount ."', ";
		$QryInsertStats .= "`build_old_rank` = '". $OldBuildRank ."', ";
		$QryInsertStats .= "`defs_points` = '". $TDefsPoints ."', ";
		$QryInsertStats .= "`defs_count` = '". $TDefsCount ."', ";
		$QryInsertStats .= "`defs_old_rank` = '". $OldDefsRank ."', ";
		$QryInsertStats .= "`fleet_points` = '". $TFleetPoints ."', ";
		$QryInsertStats .= "`fleet_count` = '". $TFleetCount ."', ";
		$QryInsertStats .= "`fleet_old_rank` = '". $OldFleetRank ."', ";
		$QryInsertStats .= "`total_points` = '". $GPoints ."', ";
		$QryInsertStats .= "`total_count` = '". $GCount ."', ";
		$QryInsertStats .= "`total_old_rank` = '". $OldTotalRank ."', ";
		$QryInsertStats .= "`stat_date` = '". $StatDate ."';";
		doquery ( $QryInsertStats , 'statpoints');
		$UserCount++;
	}

	StatComputeRanks(1);

	// Statistiques des alliances ...
	$GameAllys  = doquery("SELECT * FROM {{table}}", 'alliance');

	while ($CurAlly = mysqli_fetch_assoc($GameAllys)) {
		// Recuperation des anciennes statistiques
		$OldStatRecord  = doquery ("SELECT * FROM {{table}} WHERE `stat_type` = '2' AND `id_owner` = '".$CurAlly['id']."';",'statpoints', true);
		if ($OldStatRecord) {
			$OldTotalRank = $OldStatRecord['total_rank'];
			$OldTechRank  = $OldStatRecord['tech_rank'];
			$OldBuildRank = $OldStatRecord['build_rank'];
			$OldDefsRank  = $OldStatRecord['defs_rank'];
			$OldFleetRank = $OldStatRecord['fleet_rank'];
			// Suppression de l'ancien enregistrement
			doquery ("DELETE FROM {{table}} WHERE `stat_type` = '2' AND `id_owner` = '".$CurAlly['id']."';",'statpoints');
		} else {
			$OldTotalRank = 0;
			$OldTechRank  = 0;
			$OldBuildRank = 0;
			$OldDefsRank  = 0;
			$OldFleetRank = 0;
		}

		// Somme des statistiques actuelles des membres (stat_code 1 : pas les restes d'un joueur supprime)
		$QrySumSelect   = "SELECT ";
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
		$QrySumSelect  .= "`stat_type` = '1' AND `stat_code` = '1' AND ";
		$QrySumSelect  .= "`id_ally` = '". $CurAlly['id'] ."';";
		$Points         = doquery( $QrySumSelect, 'statpoints', true);

		$QryInsertStats  = "INSERT INTO {{table}} SET ";
		$QryInsertStats .= "`id_owner` = '". $CurAlly['id'] ."', ";
		$QryInsertStats .= "`id_ally` = '0', ";
		$QryInsertStats .= "`stat_type` = '2', "; // 1 pour joueur , 2 pour alliance
		$QryInsertStats .= "`stat_code` = '1', ";
		$QryInsertStats .= "`tech_points` = '". floatval($Points['TechPoint']) ."', ";
		$QryInsertStats .= "`tech_count` = '". floatval($Points['TechCount']) ."', ";
		$QryInsertStats .= "`tech_old_rank` = '". $OldTechRank ."', ";
		$QryInsertStats .= "`build_points` = '". floatval($Points['BuildPoint']) ."', ";
		$QryInsertStats .= "`build_count` = '". floatval($Points['BuildCount']) ."', ";
		$QryInsertStats .= "`build_old_rank` = '". $OldBuildRank ."', ";
		$QryInsertStats .= "`defs_points` = '". floatval($Points['DefsPoint']) ."', ";
		$QryInsertStats .= "`defs_count` = '". floatval($Points['DefsCount']) ."', ";
		$QryInsertStats .= "`defs_old_rank` = '". $OldDefsRank ."', ";
		$QryInsertStats .= "`fleet_points` = '". floatval($Points['FleetPoint']) ."', ";
		$QryInsertStats .= "`fleet_count` = '". floatval($Points['FleetCount']) ."', ";
		$QryInsertStats .= "`fleet_old_rank` = '". $OldFleetRank ."', ";
		$QryInsertStats .= "`total_points` = '". floatval($Points['TotalPoint']) ."', ";
		$QryInsertStats .= "`total_count` = '". floatval($Points['TotalCount']) ."', ";
		$QryInsertStats .= "`total_old_rank` = '". $OldTotalRank ."', ";
		$QryInsertStats .= "`stat_date` = '". $StatDate ."';";
		doquery ( $QryInsertStats , 'statpoints');
		$AllyCount++;
	}

	// Classement des alliances : il n'etait jamais calcule (rangs toujours a 0)
	StatComputeRanks(2);

	doquery("DO RELEASE_LOCK(LEFT(CONCAT(DATABASE(), '.{{table}}'), 64));", 'statistics');
	return array('users' => $UserCount, 'allys' => $AllyCount, 'date' => $StatDate);
}
?>