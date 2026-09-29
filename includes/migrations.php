<?php

/**
 * includes/migrations.php
 *
 * XNova Renaissance
 * Écrit par theptitprince (2026)
 *
 * Mises a jour de la base de donnees, version par version (installeur : modes "Mise a jour" et "Transfere").
 * Les bases anterieures a XNova 0.9d Renaissance ne sont pas prises en charge.
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

if (!defined('INSIDE')) { die('attemp hacking'); }

// Version du schema de base installee par cette version du jeu
define('RENAISSANCE_DB_VERSION', '0.9k');

// Nom de la ligne de la table config qui memorise la version du schema
define('RENAISSANCE_DB_VERSION_KEY', 'renaissance_db_version');

// Modifications a appliquer, dans l'ordre, pour passer d'une version a la suivante.
// {{prefix}} est remplace par le prefixe des tables. Une entree peut etre une requete SQL, ou le nom d'une
// fonction PHP appelee avec ($Connection, $Prefix) pour les traitements de donnees.
// Pour une nouvelle version : ajouter une entree ici ET reporter la modification dans includes/databaseinfos.php.
$RenaissanceMigrations = array(
	'0.9e' => array(
		// Mots de passe : password_hash() (60 caracteres aujourd'hui, jusqu'a 255 recommandes par PHP)
		"ALTER TABLE `{{prefix}}users` MODIFY `password` varchar(255) character set latin1 NOT NULL default '';",
	),
	'0.9f' => array(
		// Formulaire de contact : messages recus, lus dans l'administration
		"CREATE TABLE IF NOT EXISTS `{{prefix}}contact` (
			`id` int(11) NOT NULL auto_increment,
			`time` int(11) NOT NULL default '0',
			`user_id` int(11) NOT NULL default '0',
			`name` varchar(64) NOT NULL default '',
			`email` varchar(128) NOT NULL default '',
			`subject` varchar(255) NOT NULL default '',
			`message` text NOT NULL,
			`ip` varchar(45) NOT NULL default '',
			`is_read` tinyint(1) NOT NULL default '0',
			PRIMARY KEY (`id`),
			KEY `ip_time` (`ip`, `time`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
		// Textes saisis avant la 0.9f (XNova 0.8e d'origine, 0.9d, 0.9e) : memes regles que la saisie
		'RenaissanceCleanPlayerTexts',
		// Lunes creees avec la temperature mini et maxi inversees (bug d'origine de CreateOneMoonRecord)
		'RenaissanceFixMoonTemperatures',
		// Liens des rapports de combat deja recus : ouverture dans la frame (plus de popup), HTML valide
		'RenaissanceFixReportLinks',
		// Le reglage « jeu clos » ne fonctionnait pas (installe a 1 par defaut, sans effet) : les serveurs etaient
		// en realite ouverts. On le remet a 0 pour qu'ils le restent maintenant que le reglage fonctionne.
		"UPDATE `{{prefix}}config` SET `config_value` = '0' WHERE `config_name` = 'game_disable';",
		// Adresses par defaut vers xnova.fr (domaine repris par des tiers) : videes
		"UPDATE `{{prefix}}config` SET `config_value` = '' WHERE `config_name` IN ('forum_url', 'bot_adress') AND `config_value` LIKE '%xnova.fr%';",
	),
	'0.9g' => array(
		// Table multi : second systeme de declaration de multi-compte, jamais fonctionnel (page orpheline, colonnes
		// incoherentes) et donc toujours vide. La declaration passe par la table declared (add_declare.php).
		"DROP TABLE IF EXISTS `{{prefix}}multi`;",
		// Bannissements : pseudo, auteur et e-mail etaient tronques a 11 et 20 caracteres (memes longueurs que users)
		"ALTER TABLE `{{prefix}}banned` MODIFY `who` varchar(64) NOT NULL default '',
			MODIFY `who2` varchar(64) NOT NULL default '',
			MODIFY `author` varchar(64) NOT NULL default '',
			MODIFY `email` varchar(64) NOT NULL default '';",
		// Titre du fondateur : "Leader" etait ecrit en dur a la creation. Vide = "Fondateur", traduit dans la langue du lecteur.
		"UPDATE `{{prefix}}alliance` SET `ally_owner_range` = '' WHERE `ally_owner_range` = 'Leader';",
		// Colonnes latin1 -> utf8mb4 (la connexion l'etait deja : un emoji faisait echouer la requete)
		'RenaissanceConvertUtf8mb4',
		// Nom des lunes affiche dans la galaxie : 11 caracteres (le renommage en accepte 32), noms complets recopies
		"ALTER TABLE `{{prefix}}lunas` MODIFY `name` varchar(64) NOT NULL default 'Lune';",
		"UPDATE `{{prefix}}lunas` l JOIN `{{prefix}}planets` p ON p.`galaxy` = l.`galaxy` AND p.`system` = l.`system`
			AND p.`planet` = l.`lunapos` AND p.`planet_type` = 3 SET l.`name` = LEFT(p.`name`, 64);",
		// Mot de passe oublie : jeton du lien de confirmation (empreinte) et heure de la demande
		'RenaissanceAddLostPasswordColumns',
		// Lunes detruites par une etoile de la mort : la ligne restait (marquee detruite, plus reliee a la galaxie)
		// et la lune reapparaissait dans la vue generale ; la destruction la supprime desormais
		"DELETE l FROM `{{prefix}}lunas` l LEFT JOIN `{{prefix}}galaxy` g ON g.`id_luna` = l.`id`
			WHERE l.`destruyed` <> 0 AND g.`id_luna` IS NULL;",
		// Messages : expediteur et sujet coupes a 48 caracteres (texte echappe, couleur du message a tous : balise
		// de fin coupee, « < » affiche)
		"ALTER TABLE `{{prefix}}messages` MODIFY `message_from` varchar(255) default NULL,
			MODIFY `message_subject` varchar(255) default NULL;",
	),
	'0.9h' => array(
		// Bouton « Signaler » (reglement, article VIII) : copie du message signale, lue par le staff (admin/reports.php)
		"CREATE TABLE IF NOT EXISTS `{{prefix}}reports` (
			`id` int(11) NOT NULL auto_increment,
			`time` int(11) NOT NULL default '0',
			`reporter_id` int(11) NOT NULL default '0',
			`reporter_name` varchar(64) NOT NULL default '',
			`sender_id` int(11) NOT NULL default '0',
			`message_id` bigint(11) NOT NULL default '0',
			`message_time` int(11) NOT NULL default '0',
			`message_type` int(11) NOT NULL default '0',
			`message_from` varchar(255) default NULL,
			`message_subject` varchar(255) default NULL,
			`message_text` text,
			`is_done` tinyint(1) NOT NULL default '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `message_id` (`message_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
		// « Effacer le compte » (Options) : la case etait enregistree mais le compte n'etait jamais supprime. La colonne
		// garde maintenant la date de suppression ; une demande deja faite part pour 7 jours a compter de la mise a jour
		"ALTER TABLE `{{prefix}}users` MODIFY `db_deaktjava` int(11) NOT NULL default '0';",
		"UPDATE `{{prefix}}users` SET `db_deaktjava` = UNIX_TIMESTAMP() + 604800 WHERE `db_deaktjava` = 1;",
		// Captcha a l'inscription (mod de theptitprince) : reglage de l'administration, active
		"INSERT INTO `{{prefix}}config` (`config_name`, `config_value`) SELECT 'reg_captcha', '1' FROM DUAL
			WHERE NOT EXISTS (SELECT 1 FROM `{{prefix}}config` WHERE `config_name` = 'reg_captcha');",
		// Taille de la file de construction et unites par commande (chantier, defense) : reglages de la Configuration
		"INSERT INTO `{{prefix}}config` (`config_name`, `config_value`) SELECT 'max_building_queue', '5' FROM DUAL
			WHERE NOT EXISTS (SELECT 1 FROM `{{prefix}}config` WHERE `config_name` = 'max_building_queue');",
		"INSERT INTO `{{prefix}}config` (`config_name`, `config_value`) SELECT 'max_order_units', '1000' FROM DUAL
			WHERE NOT EXISTS (SELECT 1 FROM `{{prefix}}config` WHERE `config_name` = 'max_order_units');",
		// Protection des planetes (option du staff) reservee aux administrateurs : retiree aux autres comptes
		"UPDATE `{{prefix}}planets` p, `{{prefix}}users` u SET p.`id_level` = 0
			WHERE p.`id_owner` = u.`id` AND u.`authlevel` < 3 AND p.`id_level` > 0;",
	),
	'0.9i' => array(
		// Attaque groupee (verband.php) : la table aks de la 0.8e n'avait jamais servi (la maquette d'origine y ecrivait
		// des groupes sans suite, avec la position de depart au lieu de la cible). Recreee avec le chef du groupe, le type
		// de la cible et la liste des invites ; flottes detachees des groupes laisses par la maquette.
		"DROP TABLE IF EXISTS `{{prefix}}aks`;",
		"CREATE TABLE `{{prefix}}aks` (
			`id` bigint(20) unsigned NOT NULL auto_increment,
			`name` varchar(50) default NULL,
			`owner` int(11) NOT NULL default '0',
			`ankunft` int(32) default NULL,
			`galaxy` int(2) default NULL,
			`system` int(4) default NULL,
			`planet` int(2) default NULL,
			`planet_type` int(2) NOT NULL default '1',
			`eingeladen` text,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
		"UPDATE `{{prefix}}fleets` SET `fleet_group` = 0 WHERE `fleet_group` <> 0;",
	),
	'0.9j' => array(
		// Unites des derniers officiers : SuperNova (Raideur), Destructeur planetaire (Empereur), Protecteur planetaire
		// (Bunker). Les officiers se recrutaient sans effet : leurs unites n'existaient pas
		'RenaissanceAddUnitColumns',
	),
	'0.9k' => array(
		// Chaque partie de la 0.9k a sa fonction (fin du fichier) ; toutes peuvent etre relancees sans erreur, et
		// l'installation neuve les appelle aussi (install/index.php)
		'RenaissanceMigration09kJeu',
		'RenaissanceMigration09kPages',
		'RenaissanceMigration09kSecurite',
		'RenaissanceMigration09kAdministration',
		'RenaissanceMigration09kFlottes',
		'RenaissanceMigration09kPerformances',
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

// Libelle affiche : une base sans numero de version est une XNova 0.8e d'origine ou une Renaissance 0.9d (meme structure)
function RenaissanceVersionLabel ( $Version ) {
	return ($Version == '0.9d') ? '0.8e / 0.9d' : $Version;
}

// Compare deux numeros de version XNova (0.9d, 0.9e, 1.0, 1.0a...) : -1, 0 ou 1.
// version_compare() de PHP ne convient pas : il considere 0.9d et 0.9e comme egales.
function RenaissanceVersionCompare ( $A, $B ) {
	$Parse = function ($V) {
		preg_match('/^(\d+)\.(\d+)([a-z]*)$/i', trim($V), $M);
		return $M ? array(intval($M[1]), intval($M[2]), strtolower($M[3])) : array(0, 0, '');
	};
	return $Parse($A) <=> $Parse($B);
}

// Applique toutes les mises a jour posterieures a $FromVersion. Retourne la liste des versions appliquees.
function RenaissanceRunMigrations ( $Connection, $Prefix, $FromVersion ) {
	global $RenaissanceMigrations;

	$Applied = array();
	foreach ($RenaissanceMigrations as $Version => $Queries) {
		if (RenaissanceVersionCompare($Version, $FromVersion) <= 0) {
			continue;
		}
		foreach ($Queries as $Query) {
			if (function_exists($Query)) {
				$Query($Connection, $Prefix);
				continue;
			}
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

// ----------------------------------------------------------------------------------------------------------------
// 0.9f : nettoyage des textes deja en base, avec les regles de la saisie (SafeName, SafeText, SafeUrl, SafePath).
// On decode puis on re-encode : sans effet sur un texte deja propre, on peut donc relancer sans risque.
function RenaissanceCleanColumns ( $Connection, $Table, $Columns, $Where = '1' ) {
	$Result = @mysqli_query($Connection, "SELECT * FROM `". $Table ."` WHERE ". $Where .";");
	if (!$Result) {
		return;
	}
	while ($Row = mysqli_fetch_assoc($Result)) {
		$Set = array();
		foreach ($Columns as $Column => $Rule) {
			if (!array_key_exists($Column, $Row) || $Row[$Column] === null) {
				continue;
			}
			$Plain = html_entity_decode((string) $Row[$Column], ENT_QUOTES, 'UTF-8');
			switch ($Rule) {
				case 'name':
					$Clean = SafeName($Plain, 64);
					break;
				case 'text':
					$Clean = SafeText(strip_tags(preg_replace('#<br\s*/?>#i', "\n", $Plain)));
					break;
				case 'html':
					$Clean = nl2br(SafeText(strip_tags(preg_replace('#<br\s*/?>\s*#i', "\n", $Plain))));
					break;
				case 'url':
					$Clean = SafeUrl($Plain);
					break;
				case 'path':
					$Clean = SafePath($Plain);
					break;
				case 'ranks':
					$Ranks = @unserialize($Plain, array('allowed_classes' => false));
					if (!is_array($Ranks)) {
						$Clean = $Row[$Column];
						break;
					}
					foreach ($Ranks as $Key => $Rank) {
						if (is_array($Rank) && isset($Rank['name'])) {
							$Ranks[$Key]['name'] = SafeName($Rank['name'], 32);
						}
					}
					$Clean = serialize($Ranks);
					break;
				default:
					$Clean = $Row[$Column];
			}
			if ($Clean !== $Row[$Column]) {
				$Set[] = "`". $Column ."` = '". mysqli_real_escape_string($Connection, $Clean) ."'";
			}
		}
		if (count($Set) > 0) {
			$Key = array_key_exists('id', $Row) ? 'id' : (array_key_exists('message_id', $Row) ? 'message_id' : null);
			if ($Key !== null) {
				mysqli_query($Connection, "UPDATE `". $Table ."` SET ". implode(', ', $Set) ." WHERE `". $Key ."` = '". intval($Row[$Key]) ."';");
			}
		}
	}
}

function RenaissanceCleanPlayerTexts ( $Connection, $Prefix ) {
	RenaissanceCleanColumns($Connection, $Prefix .'planets',  array('name' => 'name'));
	RenaissanceCleanColumns($Connection, $Prefix .'alliance', array('ally_name' => 'name', 'ally_tag' => 'name', 'ally_owner_range' => 'name',
		'ally_web' => 'url', 'ally_image' => 'url', 'ally_description' => 'text', 'ally_text' => 'text', 'ally_request' => 'text',
		'ally_ranks' => 'ranks'));
	RenaissanceCleanColumns($Connection, $Prefix .'users',    array('ally_name' => 'name', 'avatar' => 'url', 'dpath' => 'path', 'ally_request_text' => 'text'));
	RenaissanceCleanColumns($Connection, $Prefix .'notes',    array('title' => 'text', 'text' => 'text'));
	RenaissanceCleanColumns($Connection, $Prefix .'buddy',    array('text' => 'text'));
	RenaissanceCleanColumns($Connection, $Prefix .'banned',   array('theme' => 'text'));
	RenaissanceCleanColumns($Connection, $Prefix .'declared', array('declared_1' => 'text', 'declared_2' => 'text', 'declared_3' => 'text', 'reason' => 'text'));
	// Messages : seulement ceux ecrits par des joueurs (type 1) ; les rapports du jeu contiennent du HTML voulu
	RenaissanceCleanColumns($Connection, $Prefix .'messages', array('message_subject' => 'text', 'message_text' => 'html'), "`message_type` = '1'");
}

// ----------------------------------------------------------------------------------------------------------------
// 0.9f : les lunes etaient enregistrees avec temp_min et temp_max inverses ; on remet dans l'ordre (relancable).
function RenaissanceFixMoonTemperatures ( $Connection, $Prefix ) {
	$Result = @mysqli_query($Connection, "SELECT `id`, `temp_min`, `temp_max` FROM `". $Prefix ."planets` WHERE `planet_type` = '3' AND `temp_min` > `temp_max`;");
	if (!$Result) {
		return;
	}
	while ($Row = mysqli_fetch_assoc($Result)) {
		mysqli_query($Connection, "UPDATE `". $Prefix ."planets` SET `temp_min` = '". intval($Row['temp_max']) ."', `temp_max` = '". intval($Row['temp_min']) ."' WHERE `id` = '". intval($Row['id']) ."';");
	}
}

// ----------------------------------------------------------------------------------------------------------------
// 0.9f : liens des rapports de combat / destruction deja enregistres dans les messages.
// Ancien format : <a href # OnClick="f( 'rw.php?raport=X', '');" ><center>  (popup, HTML invalide)
// Nouveau       : <center><a href="rw.php?raport=X">                          (dans la frame). Relancable.
function RenaissanceFixReportLinks ( $Connection, $Prefix ) {
	$Result = @mysqli_query($Connection, "SELECT `message_id`, `message_text` FROM `". $Prefix ."messages` WHERE `message_text` LIKE '%rw.php?raport=%';");
	if (!$Result) {
		return;
	}
	while ($Row = mysqli_fetch_assoc($Result)) {
		$Text = preg_replace('#<a href \# OnClick="f\( \'rw\.php\?raport=([0-9a-f]+)\', \'\'\);" ><center>#i', '<center><a href="rw.php?raport=$1">', $Row['message_text']);
		$Text = preg_replace('#<a href="rw\.php\?raport=([0-9a-f]+)"><center>#i', '<center><a href="rw.php?raport=$1">', $Text);
		if ($Text !== $Row['message_text']) {
			mysqli_query($Connection, "UPDATE `". $Prefix ."messages` SET `message_text` = '". mysqli_real_escape_string($Connection, $Text) ."' WHERE `message_id` = '". intval($Row['message_id']) ."';");
		}
	}
}

// ----------------------------------------------------------------------------------------------------------------
// 0.9g : tables et colonnes en utf8mb4. Les colonnes etaient en latin1 alors que la connexion est en utf8mb4 :
// un caractere hors cp1252 (emoji...) faisait echouer la requete. Les donnees ont ete ecrites par une connexion
// utf8mb4 (conversion faite par le serveur) : CONVERT TO les retrouve intactes. Seules les tables du jeu presentes
// sont converties (une ancienne base peut ne pas toutes les avoir) ; sans effet sur une table deja convertie.
function RenaissanceConvertUtf8mb4 ( $Connection, $Prefix ) {
	$Tables = array('aks', 'alliance', 'annonce', 'banned', 'buddy', 'chat', 'config', 'contact', 'declared', 'errors', 'fleets',
	                'galaxy', 'iraks', 'lunas', 'messages', 'notes', 'planets', 'rw', 'statpoints', 'users');
	foreach ($Tables as $Table) {
		$Name   = $Prefix . $Table;
		$Exists = mysqli_query($Connection, "SHOW TABLES LIKE '". mysqli_real_escape_string($Connection, $Name) ."';");
		if (!$Exists || mysqli_num_rows($Exists) == 0) {
			continue;
		}
		mysqli_query($Connection, "ALTER TABLE `". $Name ."` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
			or die("MySQL Error (0.9g, ". $Name ."): <b>". mysqli_error($Connection) ."</b>");
	}
}

// ----------------------------------------------------------------------------------------------------------------
// 0.9g : mot de passe oublie avec lien de confirmation. Colonnes ajoutees seulement si elles manquent (MySQL 8 ne
// connait pas ADD COLUMN IF NOT EXISTS) : on peut relancer la mise a jour sans erreur.
function RenaissanceAddLostPasswordColumns ( $Connection, $Prefix ) {
	$Columns = array(
		'lostpw_hash' => "varchar(64) NOT NULL default '' AFTER `password`",
		'lostpw_time' => "int(11) NOT NULL default '0' AFTER `lostpw_hash`",
	);
	foreach ($Columns as $Column => $Definition) {
		$Exists = mysqli_query($Connection, "SHOW COLUMNS FROM `". $Prefix ."users` LIKE '". $Column ."';");
		if ($Exists && mysqli_num_rows($Exists) > 0) {
			continue;
		}
		mysqli_query($Connection, "ALTER TABLE `". $Prefix ."users` ADD `". $Column ."` ". $Definition .";")
			or die("MySQL Error (0.9g, users.". $Column ."): <b>". mysqli_error($Connection) ."</b>");
	}
}

// ----------------------------------------------------------------------------------------------------------------
// 0.9k : outils communs des mises a jour, sans syntaxe propre a MariaDB (MySQL 8 ne connait ni ADD COLUMN IF NOT
// EXISTS ni ADD INDEX IF NOT EXISTS). Chacun verifie avant d'agir : on peut relancer sans erreur.

// Colonnes ajoutees a une table si elles manquent : array('colonne' => "definition AFTER `autre`")
function RenaissanceAddColumns ( $Connection, $Prefix, $Table, $Columns ) {
	foreach ($Columns as $Column => $Definition) {
		$Exists = mysqli_query($Connection, "SHOW COLUMNS FROM `". $Prefix . $Table ."` LIKE '". $Column ."';");
		if ($Exists && mysqli_num_rows($Exists) > 0) {
			continue;
		}
		mysqli_query($Connection, "ALTER TABLE `". $Prefix . $Table ."` ADD `". $Column ."` ". $Definition .";")
			or die("MySQL Error (". $Table .".". $Column ."): <b>". mysqli_error($Connection) ."</b>");
	}
}

// Index ajoutes a une table s'ils manquent : array('nom' => "KEY `nom` (`col1`, `col2`)") ; tout index portant deja ce
// nom est garde tel quel
function RenaissanceAddIndexes ( $Connection, $Prefix, $Table, $Indexes ) {
	foreach ($Indexes as $Name => $Definition) {
		$Exists = mysqli_query($Connection, "SHOW INDEX FROM `". $Prefix . $Table ."` WHERE `Key_name` = '". $Name ."';");
		if ($Exists && mysqli_num_rows($Exists) > 0) {
			continue;
		}
		mysqli_query($Connection, "ALTER TABLE `". $Prefix . $Table ."` ADD ". $Definition .";")
			or die("MySQL Error (". $Table .", index ". $Name ."): <b>". mysqli_error($Connection) ."</b>");
	}
}

// Lignes de la table config ajoutees si elles manquent (une valeur deja reglee n'est jamais ecrasee)
function RenaissanceAddConfig ( $Connection, $Prefix, $Values ) {
	foreach ($Values as $Name => $Value) {
		$Name   = mysqli_real_escape_string($Connection, $Name);
		$Exists = mysqli_query($Connection, "SELECT 1 FROM `". $Prefix ."config` WHERE `config_name` = '". $Name ."' LIMIT 1;");
		if ($Exists && mysqli_num_rows($Exists) > 0) {
			continue;
		}
		mysqli_query($Connection, "INSERT INTO `". $Prefix ."config` (`config_name`, `config_value`) VALUES ('". $Name ."', '". mysqli_real_escape_string($Connection, $Value) ."');")
			or die("MySQL Error (config ". $Name ."): <b>". mysqli_error($Connection) ."</b>");
	}
}

// 0.9j : colonnes des unites des derniers officiers (la 0.9j publiee utilisait ADD COLUMN IF NOT EXISTS, que MySQL 8
// refuse ; les bases deja passees en 0.9j sous MariaDB ne sont pas concernees)
function RenaissanceAddUnitColumns ( $Connection, $Prefix ) {
	RenaissanceAddColumns($Connection, $Prefix, 'planets', array(
		'supernova'        => "bigint(11) NOT NULL default '0' AFTER `battleship`",
		'planet_destroyer' => "bigint(11) NOT NULL default '0' AFTER `supernova`",
		'planet_protector' => "int(11) NOT NULL default '0' AFTER `big_protection_shield`",
	));
}

// ----------------------------------------------------------------------------------------------------------------
// 0.9k, une fonction par partie (voir la liste '0.9k' en tete de fichier)

// Jeu : triches et controles des pages du jeu
function RenaissanceMigration09kJeu ( $Connection, $Prefix ) {
	// Files du chantier spatial : seulement des vaisseaux et des defenses (listes $reslist['fleet'] et
	// $reslist['defense'] de includes/vars.php, non chargees par l'installeur). Un batiment commande au chantier par un
	// formulaire forge (niveaux au prix de base) est retire de la file, sans remboursement ; relancable
	$Units  = array(202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217,
	                401, 402, 403, 404, 405, 406, 407, 408, 409, 502, 503);
	$Result = @mysqli_query($Connection, "SELECT `id`, `b_hangar_id` FROM `". $Prefix ."planets` WHERE `b_hangar_id` <> '';");
	if (!$Result) {
		return;
	}
	while ($Row = mysqli_fetch_assoc($Result)) {
		$Queue = '';
		foreach (explode(';', $Row['b_hangar_id']) as $Item) {
			$Parts = explode(',', $Item);
			if (count($Parts) >= 2 && in_array(intval($Parts[0]), $Units) && intval($Parts[1]) > 0) {
				$Queue .= intval($Parts[0]) .",". intval($Parts[1]) .";";
			}
		}
		if ($Queue !== $Row['b_hangar_id']) {
			mysqli_query($Connection, "UPDATE `". $Prefix ."planets` SET `b_hangar_id` = '". mysqli_real_escape_string($Connection, $Queue) ."' WHERE `id` = '". intval($Row['id']) ."';")
				or die("MySQL Error (0.9k, planets.b_hangar_id): <b>". mysqli_error($Connection) ."</b>");
		}
	}
}


// Pages : notes, messages, marchand, officiers, alliance, options...
function RenaissanceMigration09kPages ( $Connection, $Prefix ) {
}


// Securite : connexion, jetons, en-tetes
function RenaissanceMigration09kSecurite ( $Connection, $Prefix ) {
}


// Administration : chat desactivable, informations du serveur, statistiques automatiques
function RenaissanceMigration09kAdministration ( $Connection, $Prefix ) {
}


// Flottes : traitement des flottes et des missiles
function RenaissanceMigration09kFlottes ( $Connection, $Prefix ) {
}


// Performances : index
function RenaissanceMigration09kPerformances ( $Connection, $Prefix ) {
}

?>
