<?php

/**
 * officier.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.1
 * @copyright 2008 By Tom1991 for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

function ShowOfficierPage ( &$CurrentUser ) {
	global $lang, $resource, $reslist, $pricelist, $_GET;

	includeLang('officier');

	// Vérification que le joueur n'a pas un nombre de points négatif
	if ($CurrentUser['rpg_points'] < 0) {
		doquery("UPDATE {{table}} SET `rpg_points` = '0' WHERE `id` = '". $CurrentUser['id'] ."';", 'users');
	}

	// Si recrutement d'un officier
	if (($_GET['mode'] ?? null) == 2) {
		$Message = '';
		if ($CurrentUser['rpg_points'] > 0) {
			$Selected    = intval(($_GET['offi'] ?? null));
			if ( in_array($Selected, $reslist['officier']) ) {
				$Result = IsOfficierAccessible ( $CurrentUser, $Selected );
				if ( $Result == 1 ) {
					// Point depense et niveau ajoute en une seule requete, seulement s'il reste un point et que le
					// niveau maximum n'est pas atteint : des requetes simultanees donnaient plusieurs niveaux (ou
					// plusieurs officiers) pour un seul point
					$Field          = $resource[$Selected];
					$QryUpdateUser  = "UPDATE {{table}} SET ";
					$QryUpdateUser .= "`rpg_points` = `rpg_points` - 1, ";
					if       ($Selected == 610) {
						$QryUpdateUser .= "`spy_tech` = `spy_tech` + 5, ";
					} elseif ($Selected == 611) {
						$QryUpdateUser .= "`computer_tech` = `computer_tech` + 3, ";
					}
					$QryUpdateUser .= "`".$Field."` = `".$Field."` + 1 ";
					$QryUpdateUser .= "WHERE ";
					$QryUpdateUser .= "`id` = '". intval($CurrentUser['id']) ."' AND `rpg_points` >= 1 AND `".$Field."` < '". intval($pricelist[$Selected]['max']) ."';";
					doquery( $QryUpdateUser, 'users' );
					if (mysqli_affected_rows(DbConnect()) == 1) {
						$CurrentUser[$Field]               += 1;
						$CurrentUser['rpg_points']         -= 1;
						if       ($Selected == 610) {
							$CurrentUser['spy_tech']      += 5;
						} elseif ($Selected == 611) {
							$CurrentUser['computer_tech'] += 3;
						}
						$Message = $lang['offi_recrute'];
					} else {
						// Point deja depense ou niveau maximum atteint entre-temps (autre page ouverte en meme temps)
						$Now     = doquery("SELECT `rpg_points`, `".$Field."` FROM {{table}} WHERE `id` = '". intval($CurrentUser['id']) ."';", 'users', true);
						$Message = ($Now && $Now[$Field] >= $pricelist[$Selected]['max']) ? $lang['maxlvl'] : $lang['no_points'];
					}
				} elseif ( $Result == -1 ) {
					$Message = $lang['maxlvl'];
				} elseif ( $Result == 0 ) {
					$Message = $lang['noob'];
				}
			}
		} else {
			$Message = $lang['no_points'];
		}
		$MessTPL        = gettemplate('message_body');
		$parse['title'] = $lang['officier_label'];
		$parse['mes']   = $Message;

		$page           = parsetemplate( $MessTPL, $parse);
	} else {
		// Pas de recrutement d'officier
		$PageTPL = gettemplate('officier_body');
		$RowsTPL = gettemplate('officier_rows');
		$parse['off_points']   = $lang['off_points'];
		$parse['alv_points']   = $CurrentUser['rpg_points'];
		$parse['disp_off_tbl'] = "";
		for ( $Officier = 601; $Officier <= 615; $Officier++ ) {
			$Result = IsOfficierAccessible ( $CurrentUser, $Officier );
			if ( $Result != 0 ) {
				$bloc['off_id']       = $Officier;
				$bloc['off_tx_lvl']   = $lang['off_tx_lvl'];
				$bloc['off_lvl']      = $CurrentUser[$resource[$Officier]];
				$bloc['off_desc']     = $lang['desc'][$Officier];
				if ($Result == 1) {
					$bloc['off_link'] = "<a href=\"officier.php?mode=2&offi=".$Officier."\"><font color=\"#00ff00\">". $lang['link'][$Officier]."</font>";
				} else {
					$bloc['off_link'] = $lang['maxlvl'];
				}
				$parse['disp_off_tbl'] .= parsetemplate( $RowsTPL, $bloc );
			}
		}
		$page           = parsetemplate( $PageTPL, $parse);
	}

	return $page;
}

	$page = ShowOfficierPage ( $user );
	display($page, $lang['officier']);

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Version originelle (Tom1991)
// 1.1 - Réécriture Chlorel pour integration complete dans XNova
?>