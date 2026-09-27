<?php

/**
 * NoobProtection.php
 *
 * XNova Renaissance
 * Écrit par theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Protection des debutants entre le joueur qui agit et le proprietaire de la cible, d'apres les points du classement
// general et les reglages de la Configuration. Retourne 0 si l'action est permise, 1 si la cible est trop faible
// (elle est protegee), 2 si la cible est trop forte (c'est le joueur qui agit qui est protege).
// Regle unique pour tous les envois hostiles : l'original la recopiait mission par mission et avait oublie la
// destruction de lune et les missiles interplanetaires. L'attaque groupee (0.9i) devra l'appliquer aussi.
function NoobProtection ( $AttackerId, $TargetId ) {
	global $game_config;

	$AttackerId = intval($AttackerId);
	$TargetId   = intval($TargetId);
	if (($game_config['noobprotection'] ?? 0) != 1 || $TargetId < 1 || $AttackerId == $TargetId) {
		return 0;
	}

	$Multi = floatval($game_config['noobprotectionmulti'] ?? 5);
	$Limit = floatval($game_config['noobprotectiontime'] ?? 5000) * 1000;
	if ($Limit < 1) {
		// 0 : pas de plafond, tout joueur beaucoup plus faible est protege (comme l'original)
		$Limit = INF;
	}

	// Pas encore de statistiques (joueur inscrit depuis le dernier calcul) : 0 point
	$Points = array($AttackerId => 0, $TargetId => 0);
	$Query  = doquery("SELECT `id_owner`, `total_points` FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` IN ('". $AttackerId ."', '". $TargetId ."');", 'statpoints');
	while ($Row = mysqli_fetch_assoc($Query)) {
		$Points[intval($Row['id_owner'])] = floatval($Row['total_points']);
	}
	$MyPoints  = $Points[$AttackerId];
	$HisPoints = $Points[$TargetId];

	if ($MyPoints > ($HisPoints * $Multi) && $HisPoints < $Limit) {
		return 1;
	}
	if (($MyPoints * $Multi) < $HisPoints && $MyPoints < $Limit) {
		return 2;
	}
	return 0;
}

?>
