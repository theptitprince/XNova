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
		// Seulement les pages ouvertes au rang (meme niveau que le controle de chaque page) : moderateur = moderation
		// entre joueurs, operateur = gestion du jeu, administrateur = tout
		$PageLevels = array(
			'overview.php'      => 1, 'settings.php'          => 3, 'XNovaResetUnivers.php' => 3, 'credit.php'       => 3,
			'userlist.php'      => 2, 'paneladmina.php'       => 1, 'deletuser.php'         => 3, 'QueryExecute.php' => 3,
			'variables.php'     => 3, 'add_money.php'         => 2, 'add_fleet.php'         => 2, 'planetlist.php'   => 2,
			'activeplanet.php'  => 2, 'moonlist.php'          => 2, 'declare_list.php'      => 1, 'multi.php'        => 1,
			'add_moon.php'      => 2, 'ShowFlyingFleets.php'  => 1, 'banned.php'            => 1, 'md5changepass.php' => 3,
			'unbanned.php'      => 1, 'chat.php'              => 1, 'statbuilder.php'       => 1, 'messagelist.php'  => 2,
			'messall.php'       => 1, 'ElementQueueFixer.php' => 1, 'contactlist.php'  => 1,
			'reports.php'       => 1, 'errors.php'            => 3, 'serverinfo.php'        => 2,
		);
		foreach ($PageLevels as $AdminPage => $Level) {
			if ($user['authlevel'] < $Level) {
				$Page = preg_replace('#\t<td><div><a href="'. preg_quote($AdminPage, '#') .'"[^\n]*\n</tr><tr>\n#', '', $Page);
			}
		}
		display( $Page, "", false, '', true);
	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>
