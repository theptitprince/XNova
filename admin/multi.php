<?php

/**
 * multi.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 by e-Zobar for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE' , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	// Liste des multi-comptes : comptes qui partagent une adresse IP (derniere connexion ou inscription), avec leurs
	// declarations (add_declare.php) et leurs bannissements. L'original lisait une table multi aux colonnes
	// inexistantes : la liste restait toujours vide.
	if ($user['authlevel'] >= 1) {
		includeLang('admin/multi');

		$OnlyUndeclared = (($_GET['filter'] ?? '') == 'undeclared');

		// Comptes et adresses IP
		$Users = array();
		$ByIp  = array();
		$Names = array();
		$Query = doquery("SELECT `id`, `username`, `ally_id`, `user_lastip`, `ip_at_reg`, `onlinetime`, `bana`, `banaday`, `authlevel` FROM {{table}} ORDER BY `id` ASC;", 'users');
		while ($Row = mysqli_fetch_assoc($Query)) {
			$Users[$Row['id']] = $Row;
			$Names[mb_strtolower($Row['username'], 'UTF-8')] = $Row['id'];
			// Adresses d'un compte de rang superieur : jamais montrees (un moderateur y lisait l'IP d'un administrateur)
			if ($Row['authlevel'] > $user['authlevel']) {
				continue;
			}
			foreach (array('last' => $Row['user_lastip'], 'reg' => $Row['ip_at_reg']) as $Kind => $Ip) {
				$Ip = trim((string) $Ip);
				if ($Ip != '') {
					$ByIp[$Ip][$Row['id']][$Kind] = true;
				}
			}
		}

		// Declarations : le declarant et les joueurs qu'il nomme sont declares (raison en infobulle)
		$Declared = array();
		$Query    = doquery("SELECT * FROM {{table}};", 'declared');
		while ($Row = mysqli_fetch_assoc($Query)) {
			$Declared[intval($Row['declarator'])] = $Row['reason'];
			foreach (array('declared_1', 'declared_2', 'declared_3') as $Field) {
				$Name = mb_strtolower(trim((string) $Row[$Field]), 'UTF-8');
				if ($Name != '' && isset($Names[$Name]) && !isset($Declared[$Names[$Name]])) {
					$Declared[$Names[$Name]] = $Row['reason'];
				}
			}
		}

		// Alliances et points
		$Allys  = array();
		$Query  = doquery("SELECT `id`, `ally_tag` FROM {{table}};", 'alliance');
		while ($Row = mysqli_fetch_assoc($Query)) {
			$Allys[$Row['id']] = $Row['ally_tag'];
		}
		$Points = array();
		$Query  = doquery("SELECT `id_owner`, `total_points` FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1';", 'statpoints');
		while ($Row = mysqli_fetch_assoc($Query)) {
			$Points[$Row['id_owner']] = $Row['total_points'];
		}

		// Groupes : une adresse partagee par au moins deux comptes (les plus grands groupes d'abord)
		uasort($ByIp, function ($A, $B) { return count($B) - count($A); });
		$Table    = '';
		$Groups   = 0;
		$Accounts = array();
		foreach ($ByIp as $Ip => $Members) {
			if (count($Members) < 2) {
				continue;
			}
			$AllDeclared = true;
			foreach ($Members as $Id => $Kinds) {
				if (!isset($Declared[$Id])) {
					$AllDeclared = false;
				}
			}
			if ($OnlyUndeclared && $AllDeclared) {
				continue;
			}
			$Groups++;
			$Status = $AllDeclared ? "<font color=\"lime\">". $lang['adm_mt_group_ok'] ."</font>" : "<font color=\"red\">". $lang['adm_mt_group_ko'] ."</font>";
			$Table .= "<tr><td class=\"c\" colspan=\"7\">". sprintf($lang['adm_mt_ip'], htmlspecialchars($Ip, ENT_QUOTES, 'UTF-8'), count($Members)) ." &mdash; ". $Status ."</td></tr>";
			foreach ($Members as $Id => $Kinds) {
				$Row  = $Users[$Id];
				$Name = htmlspecialchars($Row['username'], ENT_QUOTES, 'UTF-8');
				$Accounts[$Id] = true;
				if (isset($Kinds['last']) && isset($Kinds['reg'])) {
					$Match = $lang['adm_mt_match_both'];
				} else {
					$Match = isset($Kinds['last']) ? $lang['adm_mt_match_last'] : $lang['adm_mt_match_reg'];
				}
				if (isset($Declared[$Id])) {
					$Decl = "<font color=\"lime\" title=\"". htmlspecialchars(html_entity_decode($Declared[$Id], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') ."\">". $lang['adm_mt_yes'] ."</font>";
				} else {
					$Decl = "<font color=\"red\">". $lang['adm_mt_no'] ."</font>";
				}
				if ($Row['bana'] == 1) {
					$Ban = ($Row['banaday'] > 0) ? sprintf($lang['adm_mt_until'], date('d/m/Y H:i', $Row['banaday'])) : $lang['adm_mt_forever'];
				} else {
					$Ban = $lang['adm_mt_no'];
				}
				$Links  = "<br><a href=\"paneladmina.php?result=usr_data&id=". intval($Id) ."\">". $lang['adm_mt_sheet'] ."</a>";
				// Bannir : pas soi-meme ni un compte de rang egal ou superieur
				$Links .= ($Row['bana'] != 1 && $Id != $user['id'] && $Row['authlevel'] < $user['authlevel']) ? " | <a href=\"banned.php?name=". rawurlencode($Row['username']) ."\">". $lang['adm_mt_ban'] ."</a>" : '';
				$Table .= "<tr>";
				$Table .= "<th>". $Name . $Links ."</th>";
				$Table .= "<th>". (isset($Allys[$Row['ally_id']]) ? htmlspecialchars($Allys[$Row['ally_id']], ENT_QUOTES, 'UTF-8') : '-') ."</th>";
				$Table .= "<th>". pretty_number($Points[$Id] ?? 0) ."</th>";
				$Table .= "<th>". (($Row['onlinetime'] > 0) ? date('d/m/Y H:i', $Row['onlinetime']) : $lang['adm_mt_never']) ."</th>";
				$Table .= "<th>". $Match ."</th>";
				$Table .= "<th>". $Decl ."</th>";
				$Table .= "<th>". $Ban ."</th>";
				$Table .= "</tr>";
			}
		}
		if ($Groups == 0) {
			$Table = "<tr><th colspan=\"7\">". $lang['adm_mt_none'] ."</th></tr>";
		}

		$parse                 = $lang;
		$parse['adm_mt_table'] = $Table;
		$parse['adm_mt_count'] = sprintf($lang['adm_mt_count'], $Groups, count($Accounts));
		$parse['adm_mt_filters'] = $OnlyUndeclared
			? "<a href=\"multi.php\">". $lang['adm_mt_show_all'] ."</a>"
			: "<a href=\"multi.php?filter=undeclared\">". $lang['adm_mt_show_undeclared'] ."</a>";

		$page = parsetemplate(gettemplate('admin/multi_body'), $parse);
		display($page, $lang['adm_mt_title'], false, '', true);
	} else {
		AdminMessage($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}

?>
