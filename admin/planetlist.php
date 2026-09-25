<?php

/**
 * planetlist.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	if ($user['authlevel'] >= "2") {

		$parse = $lang;
		$query = doquery("SELECT * FROM {{table}} WHERE planet_type='1'", "planets");
		$i = 0;
		$parse['planetes'] = '';
		// Colonnes par leur nom (lues par numero de colonne)
		while ($u = mysqli_fetch_assoc($query)) {
			$parse['planetes'] .= "<tr>";
			foreach (array(intval($u['id']), htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'), intval($u['galaxy']), intval($u['system']), intval($u['planet'])) as $Cell) {
				$parse['planetes'] .= "<td class=b><center><b>" . $Cell . "</b></center></td>";
			}
			$parse['planetes'] .= "</tr>";
			$i++;
		}

		$parse['planetes'] .= "<tr><th class=b colspan=5>" . (($i == 1) ? $lang['adm_count_planets_one'] : sprintf($lang['adm_count_planets'], $i)) . "</th></tr>";

		display(parsetemplate(gettemplate('admin/planetlist_body'), $parse), $lang['adm_planetlist_title'], false, '', true);
	} else {
		message($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}

// Created by e-Zobar. All rights reversed (C) XNova Team 2008
?>