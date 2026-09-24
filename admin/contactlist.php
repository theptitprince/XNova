<?php

/**
 * admin/contactlist.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Messages recus par le formulaire de contact : lecture, lu / non lu, suppression.
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('admin/contact');

	if ($user['authlevel'] >= 1) {
		// Actions (liens proteges par le jeton CSRF, voir CsrfGetAction)
		if (isset($_GET['read'])) {
			doquery("UPDATE {{table}} SET `is_read` = '1' WHERE `id` = '". intval($_GET['read']) ."';", 'contact');
		} elseif (isset($_GET['unread'])) {
			doquery("UPDATE {{table}} SET `is_read` = '0' WHERE `id` = '". intval($_GET['unread']) ."';", 'contact');
		} elseif (isset($_GET['delete'])) {
			doquery("DELETE FROM {{table}} WHERE `id` = '". intval($_GET['delete']) ."';", 'contact');
		}

		$parse    = $lang;
		$RowsTPL  = gettemplate('admin/contactlist_rows');
		$List     = '';
		$Unread   = 0;
		$Messages = doquery("SELECT * FROM {{table}} ORDER BY `is_read` ASC, `time` DESC;", 'contact');
		while ($Row = mysqli_fetch_assoc($Messages)) {
			// Les textes ont ete echappes a l'enregistrement (formulaire de contact)
			$bloc                 = $lang;
			$bloc['id']           = $Row['id'];
			$bloc['date']         = date('d/m/Y H:i', $Row['time']);
			$bloc['name']         = SafeText($Row['name']);
			$bloc['email']        = SafeText($Row['email']);
			$bloc['player']       = ($Row['user_id'] > 0) ? $lang['adm_ctc_player'] .' #'. intval($Row['user_id']) : $lang['adm_ctc_visitor'];
			$bloc['ip']           = SafeText($Row['ip']);
			$bloc['subject']      = $Row['subject'];
			$bloc['message']      = nl2br($Row['message']);
			$bloc['status']       = ($Row['is_read'] == 1) ? $lang['adm_ctc_read'] : "<font color=\"lime\">". $lang['adm_ctc_new'] ."</font>";
			$bloc['toggle']       = ($Row['is_read'] == 1)
			                      ? "<a href=\"contactlist.php?unread=". $Row['id'] ."\">". $lang['adm_ctc_mark_unread'] ."</a>"
			                      : "<a href=\"contactlist.php?read=". $Row['id'] ."\">". $lang['adm_ctc_mark_read'] ."</a>";
			$bloc['delete']       = "<a href=\"contactlist.php?delete=". $Row['id'] ."\" onclick=\"return confirm('". $lang['adm_ctc_confirm'] ."');\">". $lang['adm_ctc_delete'] ."</a>";
			$List                .= parsetemplate($RowsTPL, $bloc);
			if ($Row['is_read'] == 0) {
				$Unread++;
			}
		}
		$parse['contact_list']   = ($List != '') ? $List : "<tr><th colspan=\"2\">". $lang['adm_ctc_none'] ."</th></tr>";
		$parse['contact_unread'] = str_replace('%d', $Unread, $lang['adm_ctc_unread_count']);

		$page = parsetemplate(gettemplate('admin/contactlist_body'), $parse);
		display($page, $lang['adm_ctc_title'], false, '', true);
	} else {
		message($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}

?>
