<?php

/**
 * moonlist.php
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
		includeLang('overview');

		$parse = $lang;
		$query = doquery("SELECT * FROM {{table}} WHERE planet_type='3'", "planets");
		$i = 0;
		$parse['moon'] = '';
		// Colonnes par leur nom (lues par numero de colonne) ; la « planete mere » affichee etait l'ID du proprietaire
		while ($u = mysqli_fetch_assoc($query)) {
			$parse['moon'] .= "<tr>";
			foreach (array(intval($u['id']), htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'), intval($u['id_owner']), intval($u['galaxy']), intval($u['system']), intval($u['planet'])) as $Cell) {
				$parse['moon'] .= "<td class=b><center><b>" . $Cell . "</b></center></td>";
			}
			$parse['moon'] .= "</tr>";
			$i++;
		}

		$parse['moon'] .= "<tr><th class=b colspan=6>" . (($i == 1) ? $lang['adm_count_moons_one'] : sprintf($lang['adm_count_moons'], $i)) . "</th></tr>";

		display(parsetemplate(gettemplate('admin/moonlist_body'), $parse), $lang['adm_moonlist_title'], false, '', true);
	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}
?>