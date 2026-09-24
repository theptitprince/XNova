<?php

/**
 * mipattack.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * ainfo.php
 * @version 0.5
 * @copyright 2008 by Tom1991 for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);
// Recup des variables
$Attaquant = intval($_GET['current']);
$NbreMip   = max(0, intval($_POST['SendMI']));

$Galaxy    = intval($_GET['galaxy']);
$System    = intval($_GET['system']);
$Planet    = intval($_GET['planet']);

$PlaneteAttaquant = doquery("SELECT * FROM {{table}} WHERE `id`='" . $Attaquant . "' AND `id_owner`='" . intval($user['id']) . "'", "planets", true);
$PlaneteAdverse   = doquery("SELECT * FROM {{table}} WHERE galaxy = " . $Galaxy . " AND system = " . $System . " AND planet = " . $Planet . "", "planets", true);

$MipAttaquant = $PlaneteAttaquant['interplanetary_misil'];
if ($MipAttaquant < $NbreMip) {
    message('Vous ne poss&eacute;dez pas assez de Missilles !', 'Erreur');
}

$AntiMipAdverse = $PlaneteAdverse['interceptor_misil'];
$MipRestant     = $NbreMip - $AntiMipAdverse;
$AntiMipRestant = $$AntiMipAdverse - $NbreMip;

echo $MipRestant;
echo $AntiMipRestant;
// L'attaquant se fait exploser tout ses MIP
if ($MipRestant <= 0) {
    doquery("UPDATE {{table}} SET `interplanetary_misil`='0' WHERE `id`='" . $Attaquant . "'", "planets");
    doquery("UPDATE {{table}} SET `interceptor_misil`='" . $AntiMipRestant . "' WHERE `id`='" . $PlaneteAdverse['id_owner'] . "'", "planets");
    // Message à l'attaquant
    $Owner    = $user['id'];
    $Sender   = "0";
    $Time     = time();
    $Type     = 3;
    $From     = "Quartier G&eacute;n&eacute;ral";
    $Subject  = "Rapport d'attaque par MIP";
    $Message  = "Malheureusement tout vos missiles interplan&eacute;taire ont &eacute;t&eacute; d&eacute;truits par le syst&egrave;me de d&eacute;fense adverse.";
    SendSimpleMessage($Owner, $Sender, $Time, $Type, $From, $Subject, $Message);

    // Message a l'attaqué
    $Owner2   = $PlaneteAdverse['id_owner'];
    $Message2 = "Vous avez d&eacute;truit " . $NbreMip . " Missiles Interplan&eacute;taire adverse. <br>Il vous reste " . $AntiMipRestant . " Missiles d'interception";
    SendSimpleMessage($Owner2, $Sender, $Time, $Type, $From, $Subject, $Message2);
}

if($MipRestant > 0){
	$Id = $PlaneteAdverse['id'];
	MipAttack($NbreMip, $Id);
}

?>