<?php

/**
 * serverinfo.php
 *
 * XNova Renaissance
 * Écrit par theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Informations du serveur, pour les operateurs et les administrateurs (0.9k) : versions du jeu, de la base, de PHP et
// de MySQL / MariaDB, OPcache, reglages de PHP, extensions, taille de la base, contenu de l'univers, dernier calcul des
// statistiques, avertissements de securite, heure. Le phpinfo complet (variables.php) reste reserve a l'administrateur.

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);
include_once($xnova_root_path . 'includes/migrations.' . $phpEx);

	if ($user['authlevel'] >= 2) {
		includeLang('admin');

		// Ligne du tableau, titre de partie, valeur en couleur, taille lisible
		$Row   = function ($Label, $Value) {
			return "<tr><th width=\"45%\" style=\"text-align:left\">". $Label ."</th><th style=\"text-align:left\">". $Value ."</th></tr>";
		};
		$Title = function ($Label) {
			return "<tr><td class=\"c\" colspan=\"2\">". $Label ."</td></tr>";
		};
		$Color = function ($Text, $Color) {
			return "<font color=\"". $Color ."\">". $Text ."</font>";
		};
		$Size  = function ($Bytes) use ($lang) {
			return ($Bytes < 1048576) ? number_format($Bytes / 1024, 1, ',', '.') .' '. $lang['adm_si_kb'] : number_format($Bytes / 1048576, 1, ',', '.') .' '. $lang['adm_si_mb'];
		};

		// Jeu
		$Schema = doquery("SELECT `config_value` FROM {{table}} WHERE `config_name` = '". RENAISSANCE_DB_VERSION_KEY ."';", 'config', true);
		$Schema = $Schema ? $Schema['config_value'] : '0.9d';
		$Rows   = $Title($lang['adm_si_game']);
		$Rows  .= $Row($lang['adm_si_version'], VERSION .' '. VERSION_NAME);
		$Rows  .= $Row($lang['adm_si_schema'], htmlspecialchars($Schema, ENT_QUOTES, 'UTF-8') .' &mdash; '. ((RenaissanceVersionCompare($Schema, RENAISSANCE_DB_VERSION) >= 0) ? $Color($lang['adm_si_schema_ok'], 'lime') : $Color($lang['adm_si_schema_old'], 'red')));

		// PHP
		$Rows  .= $Title($lang['adm_si_php']);
		$Rows  .= $Row($lang['adm_si_php_version'], PHP_VERSION .' ('. PHP_SAPI .')');
		$OpCache = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;
		if (is_array($OpCache) && !empty($OpCache['opcache_enabled'])) {
			$Used    = intval($OpCache['memory_usage']['used_memory'] ?? 0);
			$Free    = intval($OpCache['memory_usage']['free_memory'] ?? 0);
			$Rate    = floatval($OpCache['opcache_statistics']['opcache_hit_rate'] ?? 0);
			$Rows   .= $Row($lang['adm_si_opcache'], $Color(sprintf($lang['adm_si_opcache_on'], $Size($Used), $Size($Used + $Free), number_format($Rate, 1, ',', '.') .' %'), 'lime'));
		} else {
			$Rows   .= $Row($lang['adm_si_opcache'], $Color($lang['adm_si_opcache_off'], 'orange'));
		}
		$Rows  .= $Row($lang['adm_si_memory'], htmlspecialchars((string) ini_get('memory_limit'), ENT_QUOTES, 'UTF-8'));
		$MaxTime = intval(ini_get('max_execution_time'));
		$Rows  .= $Row($lang['adm_si_time'], ($MaxTime > 0) ? $MaxTime .' s' : $lang['adm_si_unlimited']);
		$Display = strtolower(trim((string) ini_get('display_errors')));
		$Rows  .= $Row($lang['adm_si_errors'], in_array($Display, array('', '0', 'off', 'false', 'no'), true) ? $Color($lang['adm_si_errors_off'], 'lime') : $Color($lang['adm_si_errors_on'], 'orange'));
		$Extensions = array();
		foreach (array('mysqli', 'mbstring', 'gd', 'json', 'ctype', 'filter') as $Extension) {
			$Extensions[] = extension_loaded($Extension) ? $Color($Extension, 'lime') : $Color($Extension .' ('. $lang['adm_si_ext_missing'] .')', 'red');
		}
		$Rows  .= $Row($lang['adm_si_ext'], implode(', ', $Extensions));

		// Base de donnees : serveur, heure, taille des tables du jeu (celles qui portent son prefixe)
		$Server = doquery("SELECT VERSION() AS `version`, NOW() AS `now`;", 'config', true);
		$Tables = doquery("SELECT `TABLE_NAME`, `ENGINE`, `TABLE_ROWS`, `DATA_LENGTH`, `INDEX_LENGTH` FROM information_schema.`TABLES` WHERE `TABLE_SCHEMA` = DATABASE() AND LEFT(`TABLE_NAME`, CHAR_LENGTH('{{table}}')) = '{{table}}' ORDER BY `TABLE_NAME`;", '');
		$Total  = 0;
		$List   = '';
		while ($Table = mysqli_fetch_assoc($Tables)) {
			$Bytes  = intval($Table['DATA_LENGTH']) + intval($Table['INDEX_LENGTH']);
			$Total += $Bytes;
			$List  .= "<tr><th style=\"text-align:left\">". htmlspecialchars($Table['TABLE_NAME'], ENT_QUOTES, 'UTF-8') ."</th><th>". htmlspecialchars((string) $Table['ENGINE'], ENT_QUOTES, 'UTF-8') ."</th>"
			        . "<th>". pretty_number(intval($Table['TABLE_ROWS'])) ."</th><th>". $Size(intval($Table['DATA_LENGTH'])) ."</th><th>". $Size(intval($Table['INDEX_LENGTH'])) ."</th><th>". $Size($Bytes) ."</th></tr>";
		}
		$Rows  .= $Title($lang['adm_si_db']);
		$Rows  .= $Row($lang['adm_si_db_server'], htmlspecialchars((string) ($Server['version'] ?? ''), ENT_QUOTES, 'UTF-8'));
		$Rows  .= $Row($lang['adm_si_db_size'], $Size($Total));

		// Univers
		$Players  = doquery("SELECT COUNT(*) AS `count` FROM {{table}};", 'users', true);
		$Worlds   = doquery("SELECT SUM(`planet_type` = 1) AS `planets`, SUM(`planet_type` = 3) AS `moons` FROM {{table}};", 'planets', true);
		$Fleets   = doquery("SELECT COUNT(*) AS `count` FROM {{table}};", 'fleets', true);
		$Messages = doquery("SELECT COUNT(*) AS `count` FROM {{table}};", 'messages', true);
		$StatLast = intval($game_config['stat_last'] ?? 0);
		$StatText = ($StatLast > 0) ? date('d/m/Y H:i:s', $StatLast) : $lang['adm_si_stats_never'];
		if (!empty($game_config['stat_auto'])) {
			$StatText .= ' ('. sprintf($lang['adm_si_stats_auto'], max(1, intval($game_config['stat_auto_hours'] ?? 6))) .')';
		}
		$Rows  .= $Title($lang['adm_si_universe']);
		$Rows  .= $Row($lang['adm_si_players'], pretty_number(intval($Players['count'] ?? 0)));
		$Rows  .= $Row($lang['adm_si_planets'], pretty_number(intval($Worlds['planets'] ?? 0)) .' ('. pretty_number(intval($Worlds['moons'] ?? 0)) .')');
		$Rows  .= $Row($lang['adm_si_fleets'], pretty_number(intval($Fleets['count'] ?? 0)));
		$Rows  .= $Row($lang['adm_si_messages'], pretty_number(intval($Messages['count'] ?? 0)));
		$Rows  .= $Row($lang['adm_si_stats'], $StatText);

		// Securite : config.php en lecture seule et dossier install retire une fois le jeu installe
		$Rows  .= $Title($lang['adm_si_security']);
		$Rows  .= $Row($lang['adm_si_config'], is_writable($xnova_root_path . 'config.php') ? $Color($lang['adm_si_config_rw'], 'orange') : $Color($lang['adm_si_config_ro'], 'lime'));
		$Rows  .= $Row($lang['adm_si_install'], is_dir($xnova_root_path . 'install') ? $Color($lang['adm_si_install_on'], 'orange') : $Color($lang['adm_si_install_off'], 'lime'));

		// Heure
		$Rows  .= $Title($lang['adm_si_clock']);
		$Rows  .= $Row($lang['adm_si_server_time'], date('d/m/Y H:i:s') .' ('. htmlspecialchars(date_default_timezone_get(), ENT_QUOTES, 'UTF-8') .')');
		$Rows  .= $Row($lang['adm_si_db_time'], htmlspecialchars((string) ($Server['now'] ?? ''), ENT_QUOTES, 'UTF-8'));

		$parse              = $lang;
		$parse['si_rows']   = $Rows;
		$parse['si_tables'] = $List;
		display(parsetemplate(gettemplate('admin/serverinfo_body'), $parse), $lang['adm_si_title'], false, '', true);
	} else {
		AdminMessage($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}

?>
