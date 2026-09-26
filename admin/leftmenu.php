<?PHP

/**
 * leftmenu.php
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
include($xnova_root_path . 'common.'.$phpEx);

includeLang('leftmenu');

	if ($user['authlevel'] >= "1") {
		$parse                 = $lang;
		$parse['mf']           = "Hauptframe";
		$parse['dpath']        = $dpath;
		$parse['xnova_release'] = VERSION .' '. VERSION_NAME;
		$parse['servername']   = 'XNova';
		// Messages signales par les joueurs : nombre a traiter a cote du lien
		$Pending               = doquery("SELECT COUNT(*) AS `count` FROM {{table}} WHERE `is_done` = '0';", 'reports', true);
		if ($Pending && $Pending['count'] > 0) {
			$parse['adm_reports'] .= " (<font color=\"red\">". intval($Pending['count']) ."</font>)";
		}
		$Page                  = parsetemplate(gettemplate('admin/left_menu'), $parse);
		display( $Page, "", false, '', true);
	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>
