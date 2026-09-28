<?php

/**
 * allydeposit.php
 *
 * XNova Renaissance
 * Écrit par theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Depot de ravitaillement (0.9j) : livraison de deuterium a une flotte alliee en stationnement, depuis le formulaire
// de la fiche du depot (infos.php?gid=34). Regles dans includes/functions/AllyDeposit.php ; le jeton CSRF des
// formulaires est verifie par common.php.

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

includeLang('infos');

$Back = "infos.php?gid=34";
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	message($lang['depot_err_fleet'], $lang['depot_title'], $Back, 3);
}

$Result = AllyDepositSupply($user, $planetrow, intval($_POST['fleetid'] ?? 0), intval($_POST['hours'] ?? 0));
if ($Result['error'] != '') {
	message(sprintf($lang[$Result['error']], pretty_number($Result['max'] ?? 0)), $lang['depot_title'], $Back, 5);
}

// Deuterium de la barre du haut a jour sur la page de confirmation
$planetrow['deuterium'] -= $Result['cost'];
message(sprintf($lang['depot_done'], $Result['owner'], $Result['hours'], pretty_number($Result['cost']), date("d/m/Y H:i:s", $Result['end'])),
        $lang['depot_title'], $Back, 5);

?>
