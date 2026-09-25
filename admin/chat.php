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

	if ($user['authlevel'] >= 3) {

		// Système de suppression
		// extract($_GET) remplace par des lectures explicites (il permettait d'ecraser n'importe quelle variable)
		$delete    = isset($_GET['delete']) ? intval($_GET['delete']) : null;
		$deleteall = isset($_GET['deleteall']) ? ($_GET['deleteall'] ?? null) : '';
		if (isset($delete)) {
			doquery("DELETE FROM {{table}} WHERE `messageid`=$delete", 'chat');
		} elseif ($deleteall == 'yes') {
			doquery("DELETE FROM {{table}}", 'chat');
		}

		// Affichage des messages
		$query = doquery("SELECT * FROM {{table}} ORDER BY messageid DESC LIMIT 25", 'chat');
		$i = 0;
		while ($e = mysqli_fetch_array($query)) {
			$i++;
			$parse['msg_list'] .= stripslashes("<tr><th class=b>" . date('H:i:s', $e['timestamp']) . "</th>".
			"<th class=b>". $e['user'] . "</th>".
			"<td class=b>" . nl2br($e['message']) . "</td>".
			"<th class=b><a href=?delete=".$e['messageid']."><img src=\"../images/r1.png\" border=\"0\"></a></th></tr>");
		}
		$parse['msg_list'] .= "<tr><th class=b colspan=4>{$i} ".$lang['adm_ch_nbs']."</th></tr>";

		display(parsetemplate(gettemplate('admin/chat_body'), $parse), "Chat", false, '', true);

	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>