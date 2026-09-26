<?php

/**
 * admin/reports.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Messages signales par les joueurs (bouton « Signaler » des messages, reglement article VIII) : lecture, fiche
 * de l'auteur, bannissement, traite / a traiter, suppression.
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('admin/reports');
	includeLang('leftmenu');

	if ($user['authlevel'] >= 1) {
		// Actions (liens proteges par le jeton CSRF, voir CsrfGetAction)
		if (isset($_GET['done'])) {
			doquery("UPDATE {{table}} SET `is_done` = '1' WHERE `id` = '". intval($_GET['done']) ."';", 'reports');
		} elseif (isset($_GET['undone'])) {
			doquery("UPDATE {{table}} SET `is_done` = '0' WHERE `id` = '". intval($_GET['undone']) ."';", 'reports');
		} elseif (isset($_GET['delete'])) {
			doquery("DELETE FROM {{table}} WHERE `id` = '". intval($_GET['delete']) ."';", 'reports');
		}

		$Reports = array();
		$Ids     = array();
		$Query   = doquery("SELECT * FROM {{table}} ORDER BY `is_done` ASC, `time` DESC;", 'reports');
		while ($Row = mysqli_fetch_assoc($Query)) {
			$Reports[] = $Row;
			$Ids[intval($Row['sender_id'])]   = true;
			$Ids[intval($Row['reporter_id'])] = true;
		}
		// Comptes concernes (rang et bannissement de l'auteur ; compte supprime depuis le signalement)
		$Users = array();
		if (count($Ids) > 0) {
			$Query = doquery("SELECT `id`, `username`, `authlevel`, `bana` FROM {{table}} WHERE `id` IN (". implode(',', array_keys($Ids)) .");", 'users');
			while ($Row = mysqli_fetch_assoc($Query)) {
				$Users[$Row['id']] = $Row;
			}
		}

		$parse   = $lang;
		$RowsTPL = gettemplate('admin/reports_rows');
		$List    = '';
		$Pending = 0;
		foreach ($Reports as $Row) {
			$Sender = $Users[$Row['sender_id']] ?? null;
			$Author = stripslashes($Row['message_from']);
			if ($Sender) {
				// Messages d'alliance : l'expediteur affiche est le tag de l'alliance, le nom du joueur est ajoute
				if (strpos(html_entity_decode(strip_tags($Author), ENT_QUOTES, 'UTF-8'), $Sender['username']) === false) {
					$Author = htmlspecialchars($Sender['username'], ENT_QUOTES, 'UTF-8') ." (". $Author .")";
				}
				$Author .= " &mdash; <a href=\"paneladmina.php?result=usr_data&id=". intval($Sender['id']) ."\">". $lang['adm_rep_sheet'] ."</a>";
				if ($Sender['bana'] == 1) {
					$Author .= " (<font color=\"red\">". $lang['adm_rep_banned'] ."</font>)";
				} elseif ($Sender['id'] != $user['id'] && $Sender['authlevel'] < $user['authlevel']) {
					// Bannir : pas soi-meme ni un compte de rang egal ou superieur
					$Author .= " | <a href=\"banned.php?name=". rawurlencode($Sender['username']) ."\">". $lang['adm_rep_ban'] ."</a>";
				}
			} else {
				$Author .= " (". $lang['adm_rep_deleted'] .")";
			}
			$Reporter = htmlspecialchars($Row['reporter_name'], ENT_QUOTES, 'UTF-8');
			$Reporter .= isset($Users[$Row['reporter_id']])
			           ? " &mdash; <a href=\"paneladmina.php?result=usr_data&id=". intval($Row['reporter_id']) ."\">". $lang['adm_rep_sheet'] ."</a>"
			           : " (". $lang['adm_rep_deleted'] .")";

			$bloc             = $lang;
			$bloc['date']     = date('d/m/Y H:i', $Row['time']);
			$bloc['subject']  = stripslashes($Row['message_subject']);
			$bloc['author']   = $Author;
			$bloc['reporter'] = $Reporter;
			$bloc['kind']     = sprintf($lang['adm_rep_kind'], $lang['adm_rep_type'][$Row['message_type']] ?? '', date('d/m/Y H:i', $Row['message_time']));
			// Texte tel qu'il s'affichait dans la messagerie du joueur (deja echappe a l'envoi)
			$bloc['message']  = nl2br(stripslashes($Row['message_text']));
			$bloc['status']   = ($Row['is_done'] == 1) ? $lang['adm_rep_done'] : "<font color=\"red\">". $lang['adm_rep_new'] ."</font>";
			$bloc['toggle']   = ($Row['is_done'] == 1)
			                  ? "<a href=\"reports.php?undone=". $Row['id'] ."\">". $lang['adm_rep_mark_undone'] ."</a>"
			                  : "<a href=\"reports.php?done=". $Row['id'] ."\">". $lang['adm_rep_mark_done'] ."</a>";
			$bloc['delete']   = "<a href=\"reports.php?delete=". $Row['id'] ."\" onclick=\"return confirm('". $lang['adm_rep_confirm'] ."');\">". $lang['adm_rep_delete'] ."</a>";
			$List            .= parsetemplate($RowsTPL, $bloc);
			if ($Row['is_done'] == 0) {
				$Pending++;
			}
		}
		$parse['reports_list']    = ($List != '') ? $List : "<tr><th colspan=\"2\">". $lang['adm_rep_none'] ."</th></tr>";
		$parse['reports_pending'] = sprintf($lang['adm_rep_pending_count'], $Pending);
		// Menu (cadre de gauche) : nombre a traiter mis a jour ici, le menu n'etant pas recharge apres une action
		// (son chargement renvoie le cadre principal vers la vue generale)
		$MenuLabel = $lang['adm_reports'] . (($Pending > 0) ? " (<font color=\"red\">". $Pending ."</font>)" : '');
		$parse['reports_menu_script'] = "<script type=\"text/javascript\">try { var ReportsLink = parent.frames['LeftMenu'].document.querySelector('a[href=\"reports.php\"]'); "
		                               . "if (ReportsLink) { ReportsLink.innerHTML = '". str_replace("'", "\\'", $MenuLabel) ."'; } } catch (e) {}</script>";

		$page = parsetemplate(gettemplate('admin/reports_body'), $parse);
		display($page, $lang['adm_rep_title'], false, '', true);
	} else {
		message($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}

?>
