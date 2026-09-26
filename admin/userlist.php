<?php

/**
 * userlist.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	if ($user['authlevel'] >= 2) {
		includeLang('admin');
		// (suppression en un clic retiree : l'icone mene a la page de confirmation, deletuser.php)
		if (($_GET['cmd'] ?? null) == 'sort') {
			$TypeSort = preg_replace('/[^a-z_]/', '', ($_GET['type'] ?? null)); // nom de colonne uniquement
			if ($TypeSort == '') { $TypeSort = 'id'; }
		} else {
			$TypeSort = "id";
		}

		$PageTPL = gettemplate('admin/userlist_body');
		$RowsTPL = gettemplate('admin/userlist_rows');

		$query   = doquery("SELECT * FROM {{table}} ORDER BY `". $TypeSort ."` ASC", 'users');

		$parse                 = $lang;
		$parse['adm_ul_table'] = "";
		$i                     = 0;
		$Color                 = "lime";
		$PrevIP = '';
		while ($u = mysqli_fetch_assoc($query) ) {
			if ($PrevIP != "") {
				if ($PrevIP == $u['user_lastip']) {
					$Color = "red";
				} else {
					$Color = "lime";
				}
			}

			
			
			
			
			$Bloc['adm_ul_data_id']     = $u['id'];
			$Bloc['adm_ul_data_name']   = $u['username'];
			$Bloc['adm_ul_data_mail']   = $u['email'];
			$Bloc['ip_adress_at_register']   = $u['ip_at_reg'];
			$Bloc['adm_ul_data_adip']   = "<font color=\"".$Color."\">". $u['user_lastip'] ."</font>";
			$Bloc['adm_ul_data_regd']   = date ( "d/m/Y H:i:s", $u['register_time'] );
			// Jamais connecte : « - » (la date zero s'affichait 01/01/1970)
			$Bloc['adm_ul_data_lconn']  = ($u['onlinetime'] > 0) ? date ( "d/m/Y H:i:s", $u['onlinetime'] ) : '-';
			$Bloc['adm_ul_data_banna']  = ( $u['bana'] == 1 ) ? "<a href # title=\"". date ( "d/m/Y H:i:s", $u['banaday']) ."\">". $lang['adm_ul_yes'] ."</a>" : $lang['adm_ul_no'];
			// Fiche du joueur (colonne vide dans l'original : « Lien vers une page de details genre Empire »)
			$Bloc['adm_ul_data_detai']  = "<a href=\"paneladmina.php?result=usr_data&id=". $u['id'] ."\">". $lang['adm_ul_sheet'] ."</a>";
			// Suppression : page de confirmation, pour les administrateurs et les comptes de rang inferieur
			$Bloc['adm_ul_data_actio']  = ($user['authlevel'] >= 3 && $u['authlevel'] < $user['authlevel'] && $u['id'] != $user['id']) ? "<a href=\"deletuser.php?id=". $u['id'] ."\" title=\"". $lang['adm_delplayer_title'] ."\"><img src=\"../images/r1.png\" border=\"0\"></a>" : "";


			$PrevIP                     = $u['user_lastip'];
			$parse['adm_ul_table']     .= parsetemplate( $RowsTPL, $Bloc );
			$i++;
		}
		$parse['adm_ul_count'] = $i;

		$page = parsetemplate( $PageTPL, $parse );
		display( $page, $lang['adm_ul_title'], false, '', true);
	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>