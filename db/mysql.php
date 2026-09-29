<?php

/**
 * db/mysql.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original : Perberos (UGamela), voir mention en fin de fichier
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Connexion MySQL (mysqli) ouverte a la demande et partagee via $link
function DbConnect() {
	global $link, $debug, $xnova_root_path;

	if (!$link) {
		require($xnova_root_path.'config.php');

		// PHP 8.1+ : mysqli leve des exceptions par defaut, on garde la gestion d'erreur d'origine
		mysqli_report(MYSQLI_REPORT_OFF);

		$link = mysqli_connect($dbsettings["server"], $dbsettings["user"], $dbsettings["pass"], $dbsettings["name"]);
		if (!$link) {
			$debug->error(mysqli_connect_error(), "SQL Error");
		}
		mysqli_set_charset($link, 'utf8mb4');
		// XNova a ete ecrit pour le mode permissif de MySQL 5 (pas de STRICT_TRANS_TABLES)
		mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
		unset($dbsettings);
	}

	return $link;
}

// Remplace mysql_escape_string()
function SqlEscape($string) {
	return mysqli_real_escape_string(DbConnect(), (string) $string);
}

function doquery($query, $table, $fetch = false){
	global $link, $debug, $xnova_root_path, $game_config;
	// Prefixe des tables lu une seule fois par page (0.9k, performances) : config.php etait relu a chaque requete
	static $Prefix = null;

	DbConnect();
	if ($Prefix === null) {
		require($xnova_root_path.'config.php');
		$Prefix = $dbsettings["prefix"];
		unset($dbsettings);//se borra la array para liberar algo de memoria
	}

	$sql = str_replace("{{table}}", $Prefix.$table, $query);

	$sqlquery = mysqli_query($link, $sql) or
				$debug->error(mysqli_error($link)."<br />$sql<br />","SQL Error");

	global $numqueries,$debug;
	$numqueries++;
	// Journal des requetes rempli seulement quand il peut etre affiche (0.9k, performances) : mode debug, avant la
	// lecture des reglages, et dans l'administration (le mode debug s'y active en cours de page, admin/settings.php)
	if (!isset($game_config['debug']) || !empty($game_config['debug']) || defined('IN_ADMIN')) {
		$debug->add("<tr><th>Query $numqueries: </th><th>$query</th><th>$table</th><th>$fetch</th></tr>");
	}

	if($fetch)
	{ //hace el fetch y regresa $sqlrow
		$sqlrow = mysqli_fetch_array($sqlquery);
		return $sqlrow;
	}else{ //devuelve el $sqlquery ("sin fetch")
		return $sqlquery;
	}

}



// Created by Perberos. All rights reversed (C) 2006
?>
