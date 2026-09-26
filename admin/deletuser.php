<?php

/**
 * deletuser.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Tom1991 for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	// Suppression d'un compte, reservee aux administrateurs. L'original n'etait qu'un formulaire que rien ne
	// traitait ($CurrentUser inexistant : acces toujours refuse) ; la liste des joueurs supprimait en un clic.
	if ($user['authlevel'] >= 3) {
		includeLang('admin');
		includeLang('admin/deletuser');

		$parse                  = $lang;
		$parse['dlu_result']    = '';
		$parse['dlu_search_value'] = '';

		// Joueur choisi : par ID (lien de la liste des joueurs, confirmation) ou par pseudo exact / ID tape
		$TargetId = intval($_POST['id'] ?? $_GET['id'] ?? 0);
		$Search   = trim((string) ($_POST['player'] ?? ''));
		$Target   = false;
		if ($TargetId > 0) {
			$Target = doquery("SELECT * FROM {{table}} WHERE `id` = '". $TargetId ."';", 'users', true);
		} elseif ($Search != '') {
			$parse['dlu_search_value'] = htmlspecialchars($Search, ENT_QUOTES, 'UTF-8');
			$Where  = ctype_digit($Search) ? "`id` = '". intval($Search) ."'" : "`username` = '". SqlEscape($Search) ."'";
			$Target = doquery("SELECT * FROM {{table}} WHERE ". $Where ." LIMIT 1;", 'users', true);
		}

		if (($TargetId > 0 || $Search != '') && !$Target) {
			$parse['dlu_result'] = "<br><table width=\"519\"><tr><th class=\"errormessage\">". $lang['dlu_notfound'] ."</th></tr></table>";
		} elseif ($Target) {
			$Name = htmlspecialchars($Target['username'], ENT_QUOTES, 'UTF-8');
			if ($Target['id'] == $user['id']) {
				$parse['dlu_result'] = "<br><table width=\"519\"><tr><th class=\"errormessage\">". $lang['dlu_self'] ."</th></tr></table>";
			} elseif ($Target['authlevel'] >= $user['authlevel']) {
				$parse['dlu_result'] = "<br><table width=\"519\"><tr><th class=\"errormessage\">". sprintf($lang['dlu_level'], $Name) ."</th></tr></table>";
			} elseif (intval($_POST['confirm'] ?? 0) == $Target['id']) {
				// Confirmation envoyee (formulaire POST, jeton CSRF verifie par common.php)
				DeleteSelectedUser(intval($Target['id']));
				AdminMessage(sprintf($lang['dlu_done'], $Name), $lang['dlu_title'], 'userlist.php', 3);
			} else {
				// Fiche du compte avant confirmation
				$Worlds = doquery("SELECT COUNT(*) AS `total`, SUM(`planet_type` = 3) AS `moons` FROM {{table}} WHERE `id_owner` = '". intval($Target['id']) ."';", 'planets', true);
				$Stats  = doquery("SELECT `total_points`, `total_rank` FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '". intval($Target['id']) ."';", 'statpoints', true);
				$Ally   = ($Target['ally_id'] > 0) ? doquery("SELECT `ally_name`, `ally_tag` FROM {{table}} WHERE `id` = '". intval($Target['ally_id']) ."';", 'alliance', true) : false;
				$Moons  = intval($Worlds['moons'] ?? 0);
				$Rows   = array(
					$lang['dlu_id']        => intval($Target['id']),
					$lang['dlu_name']      => $Name,
					$lang['dlu_mail']      => htmlspecialchars($Target['email'], ENT_QUOTES, 'UTF-8'),
					$lang['dlu_rank']      => $lang['user_level'][ $Target['authlevel'] ] ?? $Target['authlevel'],
					$lang['dlu_ally']      => $Ally ? htmlspecialchars('['. $Ally['ally_tag'] .'] '. $Ally['ally_name'], ENT_QUOTES, 'UTF-8') : $lang['dlu_none'],
					$lang['dlu_worlds']    => (intval($Worlds['total'] ?? 0) - $Moons) .' / '. $Moons,
					$lang['dlu_points']    => $Stats ? pretty_number($Stats['total_points']) .' ('. intval($Stats['total_rank']) .')' : '0',
					$lang['dlu_register']  => date('d/m/Y H:i', $Target['register_time']),
					$lang['dlu_lastlogin'] => ($Target['onlinetime'] > 0) ? date('d/m/Y H:i', $Target['onlinetime']) : $lang['dlu_never'],
					$lang['dlu_ip']        => htmlspecialchars($Target['user_lastip'], ENT_QUOTES, 'UTF-8'),
					$lang['dlu_banned']    => ($Target['bana'] == 1) ? $lang['dlu_yes'] : $lang['dlu_no'],
					$lang['dlu_vacation']  => ($Target['urlaubs_modus'] == 1) ? $lang['dlu_yes'] : $lang['dlu_no'],
					$lang['dlu_pending']   => ($Target['db_deaktjava'] > 0) ? date('d/m/Y H:i', $Target['db_deaktjava']) : $lang['dlu_no'],
				);
				$Sheet  = "<br><form action=\"deletuser.php\" method=\"post\">";
				$Sheet .= "<input type=\"hidden\" name=\"id\" value=\"". intval($Target['id']) ."\">";
				$Sheet .= "<input type=\"hidden\" name=\"confirm\" value=\"". intval($Target['id']) ."\">";
				$Sheet .= "<table width=\"519\"><tr><td class=\"c\" colspan=\"2\">". $lang['dlu_sheet'] ."</td></tr>";
				foreach ($Rows as $Label => $Value) {
					$Sheet .= "<tr><th width=\"40%\">". $Label ."</th><th>". $Value ."</th></tr>";
				}
				$Sheet .= "<tr><th colspan=\"2\"><font color=\"red\">". $lang['dlu_warning'] ."</font></th></tr>";
				$Sheet .= "<tr><th colspan=\"2\"><input type=\"submit\" value=\"". $lang['dlu_submit'] ."\"></th></tr>";
				$Sheet .= "</table></form>";
				$parse['dlu_result'] = $Sheet;
			}
		}

		$page = parsetemplate(gettemplate('admin/deletuser'), $parse);
		display($page, $lang['dlu_title'], false, '', true);
	} else {
		AdminMessage($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}

?>
