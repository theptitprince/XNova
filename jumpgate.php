<?php

/**
 * jumpgate.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

function DoFleetJump ( $CurrentUser, $CurrentPlanet ) {
	global $lang, $resource, $planetrow;

	includeLang ('infos');

	if ($_POST) {
		// Depart : une lune du joueur equipee d'une porte de saut (0.9k). Avant, n'importe quelle planete convenait,
		// l'attente d'une porte de niveau 0 valant 0
		if ($CurrentPlanet['id_owner'] != $CurrentUser['id'] || $CurrentPlanet['planet_type'] != 3 || $CurrentPlanet[ $resource[43] ] < 1) {
			return $lang['gate_no_start_g'];
		}
		$RestString   = GetNextJumpWaitTime ( $CurrentPlanet );
		$NextJumpTime = $RestString['value'];
		$JumpTime     = time();
		// Dit monsieur, j'ai le droit de sauter ???
		if ( $NextJumpTime == 0 ) {
			// Dit monsieur, ou je veux aller ca existe ???
			// Une autre lune du joueur (0.9k) : la cible etait lue par son seul numero, le joueur prenait la main sur la
			// lune d'un autre (planete courante)
			$TargetPlanet = intval(($_POST['jmpto'] ?? null));
			$TargetGate   = doquery ( "SELECT `id`, `sprungtor`, `last_jump_time` FROM {{table}} WHERE `id` = '". $TargetPlanet ."' AND `id_owner` = '". intval($CurrentUser['id']) ."' AND `planet_type` = '3' AND `id` <> '". intval($CurrentPlanet['id']) ."';", 'planets', true);
			// Dit monsieur, ou je veux aller y a une porte de saut ???
			if (($TargetGate['sprungtor'] ?? 0) > 0) {
				$RestString   = GetNextJumpWaitTime ( $TargetGate );
				$NextDestTime = $RestString['value'];
				// Dit monsieur, chez toi aussi peut y avoir un saut ???
				if ( $NextDestTime == 0 ) {
					// Bon j'ai eu toutes les autorisations, donc je compte les radis !!!
					$ShipArray   = array();
					$SubQueryOri = "";
					$SubQueryDes = "";
					$SubQueryChk = "";
					for ( $Ship = 200; $Ship < 300; $Ship++ ) {
						if (empty($resource[ $Ship ])) {
							continue; // numero sans vaisseau
						}
						$ShipLabel = "c". $Ship;
						// Quantites entieres et positives uniquement (pas de vaisseaux crees par une valeur negative)
						$_POST[ $ShipLabel ] = isset($_POST[ $ShipLabel ]) ? max(0, intval($_POST[ $ShipLabel ])) : 0;
						if ( $_POST[ $ShipLabel ] > $CurrentPlanet[ $resource[ $Ship ] ] ) {
							$ShipArray[ $Ship ] = $CurrentPlanet[ $resource[ $Ship ] ];
						} else {
							$ShipArray[ $Ship ] = ($_POST[ $ShipLabel ] ?? null);
						}
						if ($ShipArray[ $Ship ] <> 0) {
							$SubQueryOri .= "`". $resource[ $Ship ] ."` = `". $resource[ $Ship ] ."` - '". $ShipArray[ $Ship ] ."', ";
							$SubQueryDes .= "`". $resource[ $Ship ] ."` = `". $resource[ $Ship ] ."` + '". $ShipArray[ $Ship ] ."', ";
							$SubQueryChk .= "`". $resource[ $Ship ] ."` >= '". $ShipArray[ $Ship ] ."' AND ";
						}
					}
					// Dit monsieur, y avait quelque chose a envoyer ???
					if ($SubQueryOri != "") {
						// Soustraction de la lune de depart !
						// Sous condition (0.9k) : vaisseaux encore presents et porte pas utilisee entre-temps (des sauts
						// simultanes multipliaient les vaisseaux)
						$QryUpdateOri  = "UPDATE {{table}} SET ";
						$QryUpdateOri .= $SubQueryOri;
						$QryUpdateOri .= "`last_jump_time` = '". $JumpTime ."' ";
						$QryUpdateOri .= "WHERE ";
						$QryUpdateOri .= "`id` = '". intval($CurrentPlanet['id']) ."' AND ";
						$QryUpdateOri .= "`id_owner` = '". intval($CurrentUser['id']) ."' AND ";
						$QryUpdateOri .= "`planet_type` = '3' AND ";
						$QryUpdateOri .= "`sprungtor` > 0 AND ";
						$QryUpdateOri .= $SubQueryChk;
						$QryUpdateOri .= "`last_jump_time` = '". intval($CurrentPlanet['last_jump_time']) ."';";
						doquery ( $QryUpdateOri, 'planets');
						if (mysqli_affected_rows(DbConnect()) != 1) {
							$planetrow = doquery ( "SELECT * FROM {{table}} WHERE `id` = '". intval($CurrentPlanet['id']) ."';", 'planets', true);
							// Porte utilisee entre-temps : temps d'attente affiche ; sinon vaisseaux partis entre-temps
							$RestString = is_array($planetrow) ? GetNextJumpWaitTime ( $planetrow ) : array('value' => 0, 'string' => '');
							if ($RestString['value'] > 0) {
								return $lang['gate_wait_star'] . $RestString['string'];
							}
							return $lang['gate_wait_data'];
						}

						// Addition à la lune d'arrivée !
						// Sous condition aussi : porte d'arrivee pas utilisee entre-temps, sinon les vaisseaux restent
						$QryUpdateDes  = "UPDATE {{table}} SET ";
						$QryUpdateDes .= $SubQueryDes;
						$QryUpdateDes .= "`last_jump_time` = '". $JumpTime ."' ";
						$QryUpdateDes .= "WHERE ";
						$QryUpdateDes .= "`id` = '". intval($TargetGate['id']) ."' AND ";
						$QryUpdateDes .= "`id_owner` = '". intval($CurrentUser['id']) ."' AND ";
						$QryUpdateDes .= "`planet_type` = '3' AND ";
						$QryUpdateDes .= "`sprungtor` > 0 AND ";
						$QryUpdateDes .= "`last_jump_time` = '". intval($TargetGate['last_jump_time']) ."';";
						doquery ( $QryUpdateDes, 'planets');
						if (mysqli_affected_rows(DbConnect()) != 1) {
							$QryUpdateOri  = "UPDATE {{table}} SET ";
							$QryUpdateOri .= str_replace("` - '", "` + '", $SubQueryOri);
							$QryUpdateOri .= "`last_jump_time` = '". intval($CurrentPlanet['last_jump_time']) ."' ";
							$QryUpdateOri .= "WHERE ";
							$QryUpdateOri .= "`id` = '". intval($CurrentPlanet['id']) ."';";
							doquery ( $QryUpdateOri, 'planets');
							$planetrow = doquery ( "SELECT * FROM {{table}} WHERE `id` = '". intval($CurrentPlanet['id']) ."';", 'planets', true);
							// Porte d'arrivee utilisee entre-temps : son temps d'attente ; sinon elle n'est plus disponible
							$TargetGate = doquery ( "SELECT `id`, `sprungtor`, `last_jump_time` FROM {{table}} WHERE `id` = '". intval($TargetGate['id']) ."' AND `id_owner` = '". intval($CurrentUser['id']) ."' AND `planet_type` = '3';", 'planets', true);
							$RestString = is_array($TargetGate) ? GetNextJumpWaitTime ( $TargetGate ) : array('value' => 0, 'string' => '');
							if ($RestString['value'] > 0) {
								return $lang['gate_wait_dest'] . $RestString['string'];
							}
							return $lang['gate_no_dest_g'];
						}

						// Deplacement vers la lune d'arrivée
						$QryUpdateUsr  = "UPDATE {{table}} SET ";
						$QryUpdateUsr .= "`current_planet` = '". intval($TargetGate['id']) ."' ";
						$QryUpdateUsr .= "WHERE ";
						$QryUpdateUsr .= "`id` = '". $CurrentUser['id'] ."';";
						doquery ( $QryUpdateUsr, 'users');

						// Lune de depart relue : la barre du haut du message reecrit ses ressources et ses vaisseaux construits
						$planetrow = doquery ( "SELECT * FROM {{table}} WHERE `id` = '". intval($CurrentPlanet['id']) ."';", 'planets', true);

						$CurrentPlanet['last_jump_time'] = $JumpTime;
						$RestString    = GetNextJumpWaitTime ( $CurrentPlanet );
						$RetMessage    = $lang['gate_jump_done'] . $RestString['string'];
					} else {
						$RetMessage = $lang['gate_wait_data'];
					}
				} else {
					$RetMessage = $lang['gate_wait_dest'] . $RestString['string'];
				}
			} else {
				$RetMessage = $lang['gate_no_dest_g'];
			}
		} else {
			$RetMessage = $lang['gate_wait_star'] . $RestString['string'];
		}
	} else {
		$RetMessage = $lang['gate_wait_data'];
	}

	return $RetMessage;
}

	$Message = DoFleetJump($user, $planetrow);
	message ($Message, $lang['tech'][43], "infos.php?gid=43", 4);

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Version from scrap .. y avait pas ... bin maintenant y a !!

?>