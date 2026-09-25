<?php


/**
 * constants.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// ----------------------------------------------------------------------------------------------------------------

if ( defined('INSIDE') ) {
	define('ADMINEMAIL'               , ""); // vide : adresse du compte administrateur (voir reg.php)
	// Adresse du jeu (mail de bienvenue) : https si le serveur l'utilise ; pas d'hote en ligne de commande (tools/)
	define('GAMEURL'                  , ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? "https://" : "http://") . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/");

	// Definition du monde connu !
	define('MAX_GALAXY_IN_WORLD'      , 9);
	define('MAX_SYSTEM_IN_GALAXY'     , 499);
	define('MAX_PLANET_IN_SYSTEM'     , 15);
	// Nombre de colones pour les rapports d'espionnage
	define('SPY_REPORT_ROW'           , 2);
	// Cases données par niveau de Base Lunaire
	define('FIELDS_BY_MOONBASIS_LEVEL', 4);
	// Nombre maximum de colonie par joueur
	define('MAX_PLAYER_PLANETS'       , 21);
	// Nombre maximum d'element dans la liste de construction de batiments
	define('MAX_BUILDING_QUEUE_SIZE'  , 5);
	// Nombre maximum d'element dans une ligne de liste de construction flotte et defenses
	define('MAX_FLEET_OR_DEFS_PER_ROW', 1000);
	// Taux de depassement possible dans l'espace de stockage des hangards ...
	// 1.0 pour 100% - 1.1 pour 110% etc ...
	define('MAX_OVERFLOW'             , 1.1);
	// Affiche les administrateur dans la page des records ...
	// 1 -> les affiche
	// 0 -> les affiche pas
	define('SHOW_ADMIN_IN_RECORDS'    , 0);

	// Valeurs de bases pour les colonies ou planetes fraichement crées
	define('BASE_STORAGE_SIZE'        , 1000000);
	define('BUILD_METAL'              , 500);
	define('BUILD_CRISTAL'            , 500);
	define('BUILD_DEUTERIUM'          , 500);

	// Debug Level
	define('DEBUG', 1); // Debugging off
	// (Liste de mots « interdits » retiree en 0.9g : elle abimait les pseudos et noms de planete ; la saisie est
	// protegee par SafeName / SafeText et par les regles de caracteres de l'inscription)
} else {
	die("Hacking attempt");
}



?>