<?php

/**
 * tools/stats.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Recalcul des statistiques (classements des joueurs et des alliances) en ligne de commande, pour une tache
 * planifiee. Meme calcul que le bouton « Statistiques » de l'administration (admin/statfunctions.php).
 *
 * Usage, depuis la racine du jeu :  php tools/stats.php
 *  - Linux (cron, toutes les heures) :  0 * * * * cd /chemin/du/jeu && php tools/stats.php >> stats.log
 *  - Windows (Planificateur de taches) : programme  C:\chemin\php.exe  arguments  tools\stats.php
 *    dossier de demarrage  C:\chemin\du\jeu
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

if (PHP_SAPI !== 'cli') { die('Outil en ligne de commande uniquement.'); }

define('INSIDE'  , true);
define('INSTALL' , false);
define('LOGIN'   , true);   // pas de joueur connecte : common.php ne renvoie pas vers la page de connexion
$InLogin = true;

$xnova_root_path = dirname(__DIR__) . '/';
chdir($xnova_root_path);
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);
include($xnova_root_path . 'admin/statfunctions.' . $phpEx);

$Start  = microtime(true);
$Result = BuildStatistics();
echo date('d/m/Y H:i:s', $Result['date']) . ' : statistiques recalculees (' . $Result['users'] . ' joueurs, '
   . $Result['allys'] . ' alliances) en ' . round(microtime(true) - $Start, 2) . " s\n";
