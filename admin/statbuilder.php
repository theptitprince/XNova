<?php

/**
 * statbuilder.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * StatBuilder.php
 * @version 1
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

include($xnova_root_path . 'admin/statfunctions.' . $phpEx);


	if ($user['authlevel'] >= 1) {
	includeLang('admin');

	// Calcul commun avec tools/stats.php (tache planifiee) : admin/statfunctions.php
	BuildStatistics();

	AdminMessage ( $lang['adm_done'], $lang['adm_stat_title'] );

	} else {
		AdminMessage ( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>
