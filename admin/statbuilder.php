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

// include_once : le calcul automatique de common.php a pu deja charger ce fichier
include_once($xnova_root_path . 'admin/statfunctions.' . $phpEx);


	if ($user['authlevel'] >= 1) {
	includeLang('admin');

	// Recalcul : formulaire POST seulement (jeton verifie par common.php). Il partait a la simple ouverture de la
	// page, donc aussi depuis une image placee ailleurs et vue par un membre du staff
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['run'])) {
		// Calcul commun avec tools/stats.php (tache planifiee) : admin/statfunctions.php
		if (BuildStatistics() === false) {
			AdminMessage ( $lang['adm_stat_running'], $lang['adm_stat_title'], 'statbuilder.php', 3 );
		}

		AdminMessage ( $lang['adm_done'], $lang['adm_stat_title'] );
	}

	// Calcul automatique au passage des joueurs : reglages des operateurs et des administrateurs
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save']) && $user['authlevel'] >= 2) {
		$Auto  = isset($_POST['stat_auto']) ? 1 : 0;
		$Hours = max(1, min(168, intval($_POST['stat_auto_hours'] ?? 6)));
		doquery("UPDATE {{table}} SET `config_value` = '". $Auto ."' WHERE `config_name` = 'stat_auto';", 'config');
		doquery("UPDATE {{table}} SET `config_value` = '". $Hours ."' WHERE `config_name` = 'stat_auto_hours';", 'config');
		AdminMessage ( $lang['adm_stat_saved'], $lang['adm_stat_title'], 'statbuilder.php', 2 );
	}

	$Last                   = intval($game_config['stat_last'] ?? 0);
	$parse                  = $lang;
	$parse['stat_last']     = ($Last > 0) ? date('d/m/Y H:i:s', $Last) : $lang['adm_stat_never'];
	$parse['auto_checked']  = (!empty($game_config['stat_auto'])) ? ' checked="checked"' : '';
	$parse['auto_hours']    = max(1, intval($game_config['stat_auto_hours'] ?? 6));
	// Moderateurs : reglages affiches sans pouvoir les changer
	$parse['auto_disabled'] = ($user['authlevel'] >= 2) ? '' : ' disabled="disabled"';
	$parse['auto_submit']   = ($user['authlevel'] >= 2) ? "<input type=\"submit\" value=\"". $lang['adm_stat_save'] ."\">" : $lang['adm_stat_level'];

	display ( parsetemplate(gettemplate('admin/statbuilder_body'), $parse), $lang['adm_stat_title'], false, '', true );

	} else {
		AdminMessage ( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>
