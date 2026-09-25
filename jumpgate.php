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
	global $lang, $resource;

	includeLang ('infos');

	if ($_POST) {
		$RestString   = GetNextJumpWaitTime ( $CurrentPlanet );
		$NextJumpTime = $RestString['value'];
		$JumpTime     = time();
		// Dit monsieur, j'ai le droit de sauter ???
		if ( $NextJumpTime == 0 ) {
			// Dit monsieur, ou je veux aller ca existe ???
			$TargetPlanet = intval(($_POST['jmpto'] ?? null));
			$TargetGate   = doquery ( "SELECT `id`, `sprungtor`, `last_jump_time` FROM {{table}} WHERE `id` = '". $TargetPlanet ."';", 'planets', true);
			// Dit monsieur, ou je veux aller y a une porte de saut ???
			if ($TargetGate['sprungtor'] > 0) {
				$RestString   = GetNextJumpWaitTime ( $TargetGate );
				$NextDestTime = $RestString['value'];
				// Dit monsieur, chez toi aussi peut y avoir un saut ???
				if ( $NextDestTime == 0 ) {
					// Bon j'ai eu toutes les autorisations, donc je compte les radis !!!
					$ShipArray   = array();
					$SubQueryOri = "";
					$SubQueryDes = "";
					for ( $Ship = 200; $Ship < 300; $Ship++ ) {
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
						}
					}
					// Dit monsieur, y avait quelque chose a envoyer ???
					if ($SubQueryOri != "") {
						// Soustraction de la lune de depart !
						$QryUpdateOri  = "UPDATE {{table}} SET ";
						$QryUpdateOri .= $SubQueryOri;
						$QryUpdateOri .= "`last_jump_time` = '". $JumpTime ."' ";
						$QryUpdateOri .= "WHERE ";
						$QryUpdateOri .= "`id` = '". $CurrentPlanet['id'] ."';";
						doquery ( $QryUpdateOri, 'planets');

						// Addition à la lune d'arrivée !
						$QryUpdateDes  = "UPDATE {{table}} SET ";
						$QryUpdateDes .= $SubQueryDes;
						$QryUpdateDes .= "`last_jump_time` = '". $JumpTime ."' ";
						$QryUpdateDes .= "WHERE ";
						$QryUpdateDes .= "`id` = '". $TargetGate['id'] ."';";
						doquery ( $QryUpdateDes, 'planets');

						// Deplacement vers la lune d'arrivée
						$QryUpdateUsr  = "UPDATE {{table}} SET ";
						$QryUpdateUsr .= "`current_planet` = '". $TargetGate['id'] ."' ";
						$QryUpdateUsr .= "WHERE ";
						$QryUpdateUsr .= "`id` = '". $CurrentUser['id'] ."';";
						doquery ( $QryUpdateUsr, 'users');

						$CurrentPlanet['last_jump_time'] = $JumpTime;
						$RestString    = GetNextJumpWaitTime ( $CurrentPlanet );
						$RetMessage    = $lang['gate_jump_done'] ." - ". $RestString['string'];
					} else {
						$RetMessage = $lang['gate_wait_data'];
					}
				} else {
					$RetMessage = $lang['gate_wait_dest'] ." - ". $RestString['string'];
				}
			} else {
				$RetMessage = $lang['gate_no_dest_g'];
			}
		} else {
			$RetMessage = $lang['gate_wait_star'] ." - ". $RestString['string'];
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