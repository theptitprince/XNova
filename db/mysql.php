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
	global $link, $debug, $xnova_root_path;

	DbConnect();
	require($xnova_root_path.'config.php');

	$sql = str_replace("{{table}}", $dbsettings["prefix"].$table, $query);

	$sqlquery = mysqli_query($link, $sql) or
				$debug->error(mysqli_error($link)."<br />$sql<br />","SQL Error");

	unset($dbsettings);//se borra la array para liberar algo de memoria

	global $numqueries,$debug;
	$numqueries++;
	$debug->add("<tr><th>Query $numqueries: </th><th>$query</th><th>$table</th><th>$fetch</th></tr>");

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
