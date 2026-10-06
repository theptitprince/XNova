<?PHP

/**
 * leftmenu.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
// Menu : ni flottes ni missiles traites ici (voir common.php), frames.php charge juste avant s'en est charge (0.9k,
// performances)
define('NO_FLEET_PASS', true);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

function ShowLeftMenu ( $Level , $Template = 'left_menu') {
	global $lang, $dpath, $game_config, $user;

	includeLang('leftmenu');

	$MenuTPL                  = gettemplate( $Template );
	$InfoTPL                  = gettemplate( 'serv_infos' );
	$parse                    = $lang;
	$parse['lm_tx_serv']      = $game_config['resource_multiplier'];
	$parse['lm_tx_game']      = $game_config['game_speed'] / 2500;
	$parse['lm_tx_fleet']     = $game_config['fleet_speed'] / 2500;
	$parse['lm_tx_queue']     = OrderUnitsMax();
	$SubFrame                 = parsetemplate( $InfoTPL, $parse );
	$parse['server_info']     = $SubFrame;
	$parse['xnova_release']    = VERSION .' '. VERSION_NAME;
	$parse['dpath']           = $dpath;
	$parse['forum_url']       = $game_config['forum_url'];
	// Lien « Forum » masque tant qu'aucune adresse n'est configuree (l'ancienne pointait vers xnova.fr)
	$parse['forum_link']      = (!empty($game_config['forum_url'])) ? "<tr><td colspan=\"2\"><div><a href=\"". htmlspecialchars($game_config['forum_url'], ENT_QUOTES) ."\" accesskey=\"1\" target=\"_blank\" rel=\"noopener\">". $lang['board'] ."</a></div></td></tr>" : '';
	$parse['mf']              = "Hauptframe";
	$rank                     = doquery("SELECT `total_rank` FROM {{table}} WHERE `stat_code` = '1' AND `stat_type` = '1' AND `id_owner` = '". $user['id'] ."';",'statpoints',true);
	$parse['user_rank']       = $rank['total_rank'] ?? '';
	if ($Level > 0) {
		$parse['admin_link']  = "
		<tr>
			<td colspan=\"2\"><div><a href=\"admin/leftmenu.php\"><font color=\"lime\">".$lang['user_level'][$Level]."</font></a></div></td>
		</tr>";
	} else {
		$parse['admin_link']  = "";
	}
	//Lien supplémentaire déterminé dans le panel admin
	if ($game_config['link_enable'] == 1) {
		$parse['added_link']  = "
		<tr>
			<td colspan=\"2\"><div><a href=\"".htmlspecialchars($game_config['link_url'], ENT_QUOTES, 'UTF-8')."\" target=\"_blank\" rel=\"noopener\">".htmlspecialchars(stripslashes($game_config['link_name']), ENT_QUOTES, 'UTF-8')."</a></div></td>
		</tr>";
	} else {
		$parse['added_link']  = "";
	}
	
	//Maintenant on vérifie si les annonces sont activées ou non
	if ($game_config['enable_announces'] == 1) {
		$parse['announce_link']  = "
		<tr>
			<td colspan=\"2\"><div><a href=\"annonce.php\" target=\"Hauptframe\">".$lang['annonces']."</a></div></td>
		</tr>";
	} else {
		$parse['announce_link']  = "";
	}
	
		//Maintenant le marchand
	if ($game_config['enable_marchand'] == 1) {
		$parse['marchand_link']  = "
		<tr>
			<td colspan=\"2\"><div><a href=\"marchand.php\" target=\"Hauptframe\">".$lang['marchand_label']."</a></div></td>
		</tr>
		<tr>
			<td colspan=\"2\"><div><a href=\"tradingscrapmetal.php\" target=\"Hauptframe\">".$lang['tradingscrapmetal_label']."</a></div></td>
		</tr>";
	} else {
		$parse['marchand_link']  = "";
	}
			//Maintenant les notes
	if ($game_config['enable_notes'] == 1) {
		$parse['notes_link']  = "
		<tr>
			<td colspan=\"2\"><div><a href=\"notes.php\" accesskey=\"n\" onClick=\"f('notes.php', 'Notes', 600, 500); return false;\">".$lang['notes']."</a></div></td>
		</tr>";
	} else {
		$parse['notes_link']  = "";
	}
	// Chat : lien retire quand il est desactive dans l'administration (reglage chat_enabled)
	if (($game_config['chat_enabled'] ?? '1') != '0') {
		$parse['chat_link']  = "<tr>
	<td colspan=\"2\"><div><a href=\"chat.php\" accesskey=\"a\" onClick=\"f('chat.php', 'Chat', 700, 550); return false;\">".$lang['chat']."</a></div></td>
</tr>";
	} else {
		$parse['chat_link']  = "";
	}
	$parse['servername']   = htmlspecialchars($game_config['game_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	$Menu                  = parsetemplate( $MenuTPL, $parse);

	return $Menu;
}
	$Menu = ShowLeftMenu ( $user['authlevel'] );
	display ( $Menu, "Menu", '', false );

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Passage en fonction pour XNova version future
// 1.1 - Modification pour gestion Admin / Game OP / Modo
?>
