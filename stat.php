<?php

/**
 * stat.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('stat');

	// Classement (0.9k ; avant : pour chaque ligne, une lecture du joueur ou de l'alliance et une ecriture du rang).
	// Lignes de la page lues d'abord, puis leurs joueurs ou alliances en une seule requete (cle primaire : memes
	// lignes). $Table : users ou alliance
	function StatReadRows ( $Query, $Table ) {
		$Rows = array();
		$Ids  = array();
		while ($StatRow = mysqli_fetch_assoc($Query)) {
			$Rows[] = $StatRow;
			$Ids[]  = $StatRow['id_owner'];
		}
		$Owners = array();
		if (count($Ids) > 0) {
			$Found = doquery("SELECT * FROM {{table}} WHERE `id` IN ('". implode("','", $Ids) ."');", $Table);
			while ($Row = mysqli_fetch_array($Found)) {
				$Owners[$Row['id']] = $Row;
			}
		}
		return array($Rows, $Owners);
	}

	// Rangs de la page enregistres en une seule requete, avec les valeurs finales des ecritures d'une ligne a la fois
	// d'avant : pour chaque id_owner, le dernier rang ecrit, et le dernier ancien rang ecrit s'il y en a eu un (toutes
	// les lignes de ce joueur ou de cette alliance, comme avant)
	function StatSaveRanks ( $StatType, $Rank, $OldRank, $RankOf, $OldOf ) {
		if (count($RankOf) == 0) {
			return;
		}
		$QryUpdRank  = "UPDATE {{table}} SET `".$Rank."` = CASE `id_owner`";
		foreach ($RankOf as $Owner => $Start) {
			$QryUpdRank .= " WHEN '". $Owner ."' THEN '". $Start ."'";
		}
		$QryUpdRank .= " END";
		if (count($OldOf) > 0) {
			$QryUpdRank .= ", `".$OldRank."` = CASE `id_owner`";
			foreach ($OldOf as $Owner => $Start) {
				$QryUpdRank .= " WHEN '". $Owner ."' THEN '". $Start ."'";
			}
			$QryUpdRank .= " ELSE `".$OldRank."` END";
		}
		$QryUpdRank .= " WHERE `stat_type` = '". intval($StatType) ."' AND `stat_code` = '1' AND `id_owner` IN ('". implode("','", array_keys($RankOf)) ."');";
		doquery($QryUpdRank, "statpoints");
	}

	$parse = $lang;
	$who   = intval((isset($_POST['who']))   ? ($_POST['who'] ?? null)   : ($_GET['who'] ?? null));
	if ($who < 1 || $who > 2) {
		$who   = 1;
	}
	$type  = intval((isset($_POST['type']))  ? ($_POST['type'] ?? null)  : ($_GET['type'] ?? null));
	// Types 1 a 5 seulement : au-dela, tri vide et erreur SQL enregistree a chaque appel
	if ($type < 1 || $type > 5) {
		$type  = 1;
	}
	$range = (isset($_POST['range'])) ? ($_POST['range'] ?? null) : ($_GET['range'] ?? null);
	// PHP 8 : le rang peut etre vide (joueur pas encore classe), division impossible sur une chaine
	$range = intval($range);
	if ($range < 1) {
		$range = 1;
	}

	$parse['who']    = "<option value=\"1\"". (($who == "1") ? " SELECTED" : "") .">". $lang['stat_player'] ."</option>";
	$parse['who']   .= "<option value=\"2\"". (($who == "2") ? " SELECTED" : "") .">". $lang['stat_allys']  ."</option>";

	$parse['type']   = "<option value=\"1\"". (($type == "1") ? " SELECTED" : "") .">". $lang['stat_main']     ."</option>";
	$parse['type']  .= "<option value=\"2\"". (($type == "2") ? " SELECTED" : "") .">". $lang['stat_fleet']    ."</option>";
	$parse['type']  .= "<option value=\"3\"". (($type == "3") ? " SELECTED" : "") .">". $lang['stat_research'] ."</option>";
	$parse['type']  .= "<option value=\"4\"". (($type == "4") ? " SELECTED" : "") .">". $lang['stat_building'] ."</option>";
	$parse['type']  .= "<option value=\"5\"". (($type == "5") ? " SELECTED" : "") .">". $lang['stat_defenses'] ."</option>";

	if       ($type == 1) {
		$Order   = "total_points";
		$Points  = "total_points";
		$Counts  = "total_count";
		$Rank    = "total_rank";
		$OldRank = "total_old_rank";
	} elseif ($type == 2) {
		$Order   = "fleet_count";
		$Points  = "fleet_points";
		$Counts  = "fleet_count";
		$Rank    = "fleet_rank";
		$OldRank = "fleet_old_rank";
	} elseif ($type == 3) {
		$Order   = "tech_count";
		$Points  = "tech_points";
		$Counts  = "tech_count";
		$Rank    = "tech_rank";
		$OldRank = "tech_old_rank";
	} elseif ($type == 4) {
		$Order   = "build_points";
		$Points  = "build_points";
		$Counts  = "build_count";
		$Rank    = "build_rank";
		$OldRank = "build_old_rank";
	} elseif ($type == 5) {
		$Order   = "defs_points";
		$Points  = "defs_points";
		$Counts  = "defs_count";
		$Rank    = "defs_rank";
		$OldRank = "defs_old_rank";
	}

	if ($who == 2) {
		$MaxAllys = doquery ("SELECT COUNT(*) AS `count` FROM {{table}} WHERE 1;", 'alliance', true);
		$LastPage = 0;
		if ($MaxAllys['count'] > 100) {
			$LastPage = floor($MaxAllys['count'] / 100);
		}
		$parse['range'] = "";
		for ($Page = 0; $Page <= $LastPage; $Page++) {
			$PageValue      = ($Page * 100) + 1;
			$PageRange      = $PageValue + 99;
			$parse['range'] .= "<option value=\"". $PageValue ."\"". (($range == $PageValue) ? " SELECTED" : "") .">". $PageValue ."-". $PageRange ."</option>";
		}

		$parse['stat_header'] = parsetemplate(gettemplate('stat_alliancetable_header'), $parse);

		$start = intdiv($range - 1, 100) * 100;
		$query = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '2' AND `stat_code` = '1' ORDER BY `". $Order ."` DESC LIMIT ". $start .",100;", 'statpoints');

		$start++;
		$parse['stat_date']   = $game_config['stats'] ?? '';
		$parse['stat_values'] = "";
		list($StatRows, $Owners) = StatReadRows($query, 'alliance');
		$RankOf = array();
		$OldOf  = array();
		foreach ($StatRows as $StatRow) {
			// Date du calcul, comme dans le classement des joueurs (l'original lisait un reglage inexistant : titre vide)
			$parse['stat_date']       = date("d/m/Y - H:i:s", $StatRow['stat_date']);
			$parse['ally_rank']       = $start;

			$AllyRow                  = $Owners[$StatRow['id_owner']] ?? null;
			if (!$AllyRow) { continue; } // alliance dissoute depuis le dernier calcul des statistiques

			$rank_old                 = $StatRow[ $OldRank ];
			if ( $rank_old == 0) {
				$rank_old             = $start;
				$OldOf[$StatRow['id_owner']] = $start;
			}
			$RankOf[$StatRow['id_owner']] = $start;
			$rank_new                 = $start;
			$ranking                  = $rank_old - $rank_new;
			if ($ranking == "0") {
				$parse['ally_rankplus']   = "<font color=\"#87CEEB\">*</font>";
			}
			if ($ranking < "0") {
				$parse['ally_rankplus']   = "<font color=\"red\">".$ranking."</font>";
			}
			if ($ranking > "0") {
				$parse['ally_rankplus']   = "<font color=\"green\">+".$ranking."</font>";
			}
			$parse['ally_id']         = intval($AllyRow['id']);
			$parse['ally_tag']        = htmlspecialchars($AllyRow['ally_tag'], ENT_QUOTES, 'UTF-8');
			$parse['ally_name']       = htmlspecialchars($AllyRow['ally_name'], ENT_QUOTES, 'UTF-8');
			$parse['ally_mes']        = '';
			$parse['ally_members']    = $AllyRow['ally_members'];
			$parse['ally_points']     = pretty_number( $StatRow[ $Order ] );
			$parse['ally_members_points'] =  pretty_number( floor($StatRow[ $Order ] / max(1, $AllyRow['ally_members'])) );

			$parse['stat_values']    .= parsetemplate(gettemplate('stat_alliancetable'), $parse);
			$start++;
		}
		StatSaveRanks(2, $Rank, $OldRank, $RankOf, $OldOf);
	} else {
		$MaxUsers = doquery ("SELECT COUNT(*) AS `count` FROM {{table}} WHERE `db_deaktjava` = '0';", 'users', true);
		$LastPage = 0;
		if ($MaxUsers['count'] > 100) {
			$LastPage = floor($MaxUsers['count'] / 100);
		}
		$parse['range'] = "";
		for ($Page = 0; $Page <= $LastPage; $Page++) {
			$PageValue      = ($Page * 100) + 1;
			$PageRange      = $PageValue + 99;
			$parse['range'] .= "<option value=\"". $PageValue ."\"". (($range == $PageValue) ? " SELECTED" : "") .">". $PageValue ."-". $PageRange ."</option>";
		}

		$parse['stat_header'] = parsetemplate(gettemplate('stat_playertable_header'), $parse);

		$start = intdiv($range - 1, 100) * 100;
		$query = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1' ORDER BY `". $Order ."` DESC LIMIT ". $start .",100;", 'statpoints');

		$start++;
		$parse['stat_date']   = $game_config['stats'] ?? '';
		$parse['stat_values'] = "";
		list($StatRows, $Owners) = StatReadRows($query, 'users');
		$RankOf = array();
		$OldOf  = array();
		foreach ($StatRows as $StatRow) {
			$parse['stat_date']       = date("d/m/Y - H:i:s", $StatRow['stat_date']);
			$parse['player_rank']     = $start;

			$UsrRow                   = $Owners[$StatRow['id_owner']] ?? null;
			if (!$UsrRow) { continue; } // joueur supprime depuis le dernier calcul des statistiques


			$rank_old                 = $StatRow[ $OldRank ];
			if ( $rank_old == 0) {
				$rank_old             = $start;
				$OldOf[$StatRow['id_owner']] = $start;
			}
			$RankOf[$StatRow['id_owner']] = $start;
			$rank_new                 = $start;
			$ranking                  = $rank_old - $rank_new;
			if ($ranking == "0") {
				$parse['player_rankplus'] = "<font color=\"#87CEEB\">*</font>";
			}
			if ($ranking < "0") {
				$parse['player_rankplus'] = "<font color=\"red\">".$ranking."</font>";
			}
			if ($ranking > "0") {
				$parse['player_rankplus'] = "<font color=\"green\">+".$ranking."</font>";
			}
			if ($UsrRow['id'] == $user['id']) {
				$parse['player_name']     = "<font color=\"lime\">".$UsrRow['username']."</font>";
			} else {
				$parse['player_name']     = $UsrRow['username'];
			}
			$parse['player_mes']      = "<a href=\"messages.php?mode=write&id=" . $UsrRow['id'] . "\"><img src=\"" . $dpath . "img/m.gif\" border=\"0\" alt=\"". $lang['ecrire'] ."\" /></a>";
			// Alliance du joueur : lien vers sa page (texte simple dans l'original), en bleu pour sa propre alliance
			$parse['player_alliance'] = '';
			if ($UsrRow['ally_id'] > 0 && $UsrRow['ally_name'] != '') {
				$AllyName = htmlspecialchars($UsrRow['ally_name'], ENT_QUOTES, 'UTF-8');
				if ($UsrRow['ally_id'] == $user['ally_id']) {
					$AllyName = "<font color=\"#33CCFF\">". $AllyName ."</font>";
				}
				$parse['player_alliance'] = "<a href=\"alliance.php?mode=ainfo&amp;a=". intval($UsrRow['ally_id']) ."\">". $AllyName ."</a>";
			}
			$parse['player_points']   = pretty_number( $StatRow[ $Order ] );
			$parse['stat_values']    .= parsetemplate(gettemplate('stat_playertable'), $parse);
			$start++;
		}
		StatSaveRanks(1, $Rank, $OldRank, $RankOf, $OldOf);
	}

	$page = parsetemplate( gettemplate('stat_body'), $parse );

	display($page, $lang['stat_title']);

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Réécriture module
?>
