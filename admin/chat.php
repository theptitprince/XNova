<?php

/**
 * chat.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By e-Zobar for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

includeLang('admin');
$parse = $lang;

	// Moderation du chat : ouverte aux moderateurs (reservee a l'administrateur dans l'original)
	if ($user['authlevel'] >= 1) {

		// Système de suppression
		// extract($_GET) remplace par des lectures explicites (il permettait d'ecraser n'importe quelle variable)
		$delete    = isset($_GET['delete']) ? intval($_GET['delete']) : null;
		$deleteall = isset($_GET['deleteall']) ? ($_GET['deleteall'] ?? null) : '';
		if (isset($delete)) {
			doquery("DELETE FROM {{table}} WHERE `messageid`=$delete", 'chat');
		} elseif ($deleteall == 'yes') {
			doquery("DELETE FROM {{table}}", 'chat');
		}

		// Chat active ou desactive (reglage chat_enabled) : interrupteur des operateurs et des administrateurs,
		// formulaire POST (jeton verifie par common.php)
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['chat_enabled']) && $user['authlevel'] >= 2) {
			$game_config['chat_enabled'] = (intval($_POST['chat_enabled']) == 1) ? '1' : '0';
			doquery("UPDATE {{table}} SET `config_value` = '". $game_config['chat_enabled'] ."' WHERE `config_name` = 'chat_enabled';", 'config');
		}
		$ChatOn                = (($game_config['chat_enabled'] ?? '1') != '0');
		$parse['chat_state']   = $ChatOn ? "<font color=\"lime\">". $lang['adm_ch_on'] ."</font>" : "<font color=\"red\">". $lang['adm_ch_off'] ."</font>";
		if ($user['authlevel'] >= 2) {
			$parse['chat_switch'] = "<form action=\"chat.php\" method=\"post\" style=\"margin:0\"><input type=\"hidden\" name=\"chat_enabled\" value=\"". ($ChatOn ? 0 : 1) ."\">"
			                      . "<input type=\"submit\" value=\"". ($ChatOn ? $lang['adm_ch_disable'] : $lang['adm_ch_enable']) ."\"></form>";
		} else {
			$parse['chat_switch'] = $lang['adm_ch_switch_level'];
		}

		// Affichage des messages
		$query = doquery("SELECT * FROM {{table}} ORDER BY messageid DESC LIMIT 25", 'chat');
		$i = 0;
		$parse['msg_list'] = '';
		while ($e = mysqli_fetch_array($query)) {
			$i++;
			// Message et pseudo echappes : ils s'affichaient tels quels (un <script> poste dans le chat s'executait
			// chez l'administrateur) ; plus de stripslashes (il effacait les \ des messages)
			$parse['msg_list'] .= "<tr><th class=b>" . date('H:i:s', $e['timestamp']) . "</th>".
			"<th class=b>". htmlspecialchars($e['user'], ENT_QUOTES, 'UTF-8') . "</th>".
			"<td class=b>" . nl2br(htmlspecialchars($e['message'], ENT_QUOTES, 'UTF-8')) . "</td>".
			"<th class=b><a href=?delete=".$e['messageid']."><img src=\"../images/r1.png\" border=\"0\"></a></th></tr>";
		}
		$parse['msg_list'] .= "<tr><th class=b colspan=4>{$i} ".$lang['adm_ch_nbs']."</th></tr>";

		display(parsetemplate(gettemplate('admin/chat_body'), $parse), "Chat", false, '', true);

	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>