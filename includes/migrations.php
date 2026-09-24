<?php

/**
 * includes/migrations.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Mises a jour de la base de donnees, version par version (installeur : modes "Mise a jour" et "Transfere").
 * Les bases anterieures a XNova 0.9d Renaissance ne sont pas prises en charge.
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

if (!defined('INSIDE')) { die('attemp hacking'); }

// Version du schema de base installee par cette version du jeu
define('RENAISSANCE_DB_VERSION', '0.9e');

// Nom de la ligne de la table config qui memorise la version du schema
define('RENAISSANCE_DB_VERSION_KEY', 'renaissance_db_version');

// Modifications a appliquer, dans l'ordre, pour passer d'une version a la suivante.
// {{prefix}} est remplace par le prefixe des tables.
// Pour une nouvelle version : ajouter une entree ici ET reporter la modification dans includes/databaseinfos.php.
$RenaissanceMigrations = array(
	'0.9e' => array(
		// Mots de passe : password_hash() (60 caracteres aujourd'hui, jusqu'a 255 recommandes par PHP)
		"ALTER TABLE `{{prefix}}users` MODIFY `password` varchar(255) character set latin1 NOT NULL default '';",
	),
);

// Version du schema d'une base existante : la ligne de config, ou 0.9d si la base XNova n'en a pas encore.
// Retourne false si ce n'est pas une base XNova Renaissance.
function RenaissanceSchemaVersion ( $Connection, $Prefix ) {
	$Users = @mysqli_query($Connection, "SHOW TABLES LIKE '". mysqli_real_escape_string($Connection, $Prefix) ."users';");
	if (!$Users || mysqli_num_rows($Users) == 0) {
		return false;
	}
	$Row = @mysqli_fetch_assoc(@mysqli_query($Connection, "SELECT `config_value` FROM `". $Prefix ."config` WHERE `config_name` = '". RENAISSANCE_DB_VERSION_KEY ."';"));
	if ($Row) {
		return $Row['config_value'];
	}
	// Base sans version : XNova 0.9d Renaissance (premiere version de la reprise), ou plus ancien
	$Hash = @mysqli_query($Connection, "SHOW COLUMNS FROM `". $Prefix ."users` LIKE 'password';");
	return ($Hash && mysqli_num_rows($Hash) == 1) ? '0.9d' : false;
}

// Applique toutes les mises a jour posterieures a $FromVersion. Retourne la liste des versions appliquees.
function RenaissanceRunMigrations ( $Connection, $Prefix, $FromVersion ) {
	global $RenaissanceMigrations;

	$Applied = array();
	foreach ($RenaissanceMigrations as $Version => $Queries) {
		if (version_compare($Version, $FromVersion, '<=')) {
			continue;
		}
		foreach ($Queries as $Query) {
			mysqli_query($Connection, str_replace('{{prefix}}', $Prefix, $Query)) or die("MySQL Error (". $Version ."): <b>". mysqli_error($Connection) ."</b>");
		}
		RenaissanceSetSchemaVersion($Connection, $Prefix, $Version);
		$Applied[] = $Version;
	}
	return $Applied;
}

// Enregistre la version du schema dans la table config
function RenaissanceSetSchemaVersion ( $Connection, $Prefix, $Version ) {
	$Version = mysqli_real_escape_string($Connection, $Version);
	mysqli_query($Connection, "DELETE FROM `". $Prefix ."config` WHERE `config_name` = '". RENAISSANCE_DB_VERSION_KEY ."';");
	mysqli_query($Connection, "INSERT INTO `". $Prefix ."config` (`config_name`, `config_value`) VALUES ('". RENAISSANCE_DB_VERSION_KEY ."', '". $Version ."');");
}

?>
