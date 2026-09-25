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
			$Text    = trim((string) ($_POST['tresc'] ?? ''));
			$Topic   = trim((string) ($_POST['temat'] ?? ''));
			// Sujet ou texte vide : message d'erreur (la page lisait une cle inexistante puis restait blanche)
			if ($Text == '' || $Topic == '') {
				message($lang['adm_mall_empty'], $lang['adm_mall_title'], 'messall.php', 3);
			}
			// Couleur selon le niveau, titre traduit (il etait force en anglais : « Administrator »)
			$kolor = ($user['authlevel'] >= 3) ? 'red' : 'orange';
			$ranga = $lang['user_level'][$user['authlevel']] ?? '';
			$sq      = doquery("SELECT `id` FROM {{table}}", "users");
			$Time    = time();
			$From    = "<font color=\"". $kolor ."\">". $ranga ." ".$user['username']."</font>";
			// Texte echappe : un moderateur pouvait injecter du HTML / du script chez tous les joueurs
			$Subject = "<font color=\"". $kolor ."\">". SafeText($Topic) ."</font>";
			$Message = "<font color=\"". $kolor ."\"><b>". nl2br(SafeText($Text)) ."</b></font>";
			while ($u = mysqli_fetch_array($sq)) {
				SendSimpleMessage ( $u['id'], $user['id'], $Time, 97, $From, $Subject, $Message);
			}
			message("<font color=\"lime\">". $lang['adm_mall_sent'] ."</font>", $lang['adm_done'], "../overview." . $phpEx, 3);
		} else {
			$parse = $lang;
			$parse['dpath'] = $dpath;
			$parse['debug'] = ($game_config['debug'] == 1) ? " checked='checked'/":'';
			$page = parsetemplate(gettemplate('admin/messall_body'), $parse);
			display($page, '', false,'', true);
		}
	} else {
		message($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}
?>