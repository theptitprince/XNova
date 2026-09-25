<?php

/**
 * messall.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	// Le formulaire envoie sur ?mode=change : jamais lu depuis la fin de extract(), le message ne partait plus
	$mode = ($_GET['mode'] ?? '');
	if ($user['authlevel'] >= 1) {
		if ($_POST && $mode == "change") {
			if (isset($_POST["tresc"]) && ($_POST["tresc"] ?? null) != '') {
				$game_config['tresc'] = ($_POST['tresc'] ?? null);
			}
			if (isset($_POST["temat"]) && ($_POST["temat"] ?? null) != '') {
				$game_config['temat'] = ($_POST['temat'] ?? null);
			}
			// Couleur et titre selon le niveau (niveaux 1 et 2 : aucune valeur avant)
			$kolor = 'orange';
			$ranga = $lang['user_level'][$user['authlevel']] ?? '';
			if ($user['authlevel'] == 3) {
				$kolor = 'red';
				$ranga = 'Administrator';
			} elseif ($user['authlevel'] == 4) {
				$kolor = 'skyblue';
				$ranga = 'GameOperator';
			} elseif ($user['authlevel'] == 5) {
				$kolor = 'yellow';
				$ranga = 'SuperGameOperator';
			}
			if ($game_config['tresc'] != '' and $game_config['temat']) {
				$sq      = doquery("SELECT `id` FROM {{table}}", "users");
				$Time    = time();
				$From    = "<font color=\"". $kolor ."\">". $ranga ." ".$user['username']."</font>";
				// Texte echappe : un moderateur pouvait injecter du HTML / du script chez tous les joueurs
				$Subject = "<font color=\"". $kolor ."\">". SafeText($game_config['temat']) ."</font>";
				$Message = "<font color=\"". $kolor ."\"><b>". nl2br(SafeText($game_config['tresc'])) ."</b></font>";
				while ($u = mysqli_fetch_array($sq)) {
					SendSimpleMessage ( $u['id'], $user['id'], $Time, 97, $From, $Subject, $Message);
				}
				message("<font color=\"lime\">Message envoy&eacute; &agrave; tous les joueurs</font>", "Termin&eacute;", "../overview." . $phpEx, 3);
			}
		} else {
			$parse = $game_config;
			$parse['dpath'] = $dpath;
			$parse['debug'] = ($game_config['debug'] == 1) ? " checked='checked'/":'';
			$page = parsetemplate(gettemplate('admin/messall_body'), $parse);
			display($page, '', false,'', true);
		}
	} else {
		message($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}
?>