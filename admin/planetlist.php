<?php

/**
 * planetlist.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU GPL v2
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
		while ($u = mysqli_fetch_array($query)) {
			$parse['planetes'] .= "<tr>"
			. "<td class=b><center><b>" . $u[0] . "</center></b></td>"
			. "<td class=b><center><b>" . $u[1] . "</center></b></td>"
			. "<td class=b><center><b>" . $u[4] . "</center></b></td>"
			. "<td class=b><center><b>" . $u[5] . "</center></b></td>"
			. "<td class=b><center><b>" . $u[6] . "</center></b></td>"
			. "</tr>";
			$i++;
		}

		if ($i == "1")
			$parse['planetes'] .= "<tr><th class=b colspan=5>Il y a qu'une seule plan&egrave;te</th></tr>";
		else
			$parse['planetes'] .= "<tr><th class=b colspan=5>Il y a {$i} plan&egrave;tes</th></tr>";

		display(parsetemplate(gettemplate('admin/planetlist_body'), $parse), 'Planetlist', false, '', true);
	} else {
		message($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}

// Created by e-Zobar. All rights reversed (C) XNova Team 2008
?>