<?php

/**
 * declare_list.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * userlist.php
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
		// (Le lien « supprimer le joueur » copie de userlist.php a ete retire : il n'etait affiche nulle part)

		$PageTPL = gettemplate('admin/declarelist_body');
		$RowsTPL = gettemplate('admin/declarelist_rows');

		$query   = doquery("SELECT * FROM {{table}} ORDER BY `declarator` DESC", 'declared');

		$parse                 = $lang;
		$parse['adm_ul_table'] = "";
		$i                     = 0;
		while ($u = mysqli_fetch_assoc($query) ) {
			$Bloc['adm_ul_data_id']     = $u['declarator_name'];
			$Bloc['adm_ul_data_name']   = $u['declarator'];
			$Bloc['adm_ul_data_mail']   = $u['declared_1'];
			$Bloc['adm_ul_data_adip']   = $u['declared_2'];
			$Bloc['adm_ul_data_detai']  = $u['declared_3'];
			$Bloc['adm_ul_data_regd']   = $u['reason'];
			

			$parse['adm_ul_table']     .= parsetemplate( $RowsTPL, $Bloc );
			$i++;
		}
		$parse['adm_ul_count'] = $i;

		$page = parsetemplate( $PageTPL, $parse );
		display( $page, $lang['adm_dl_title'], false, '', true);
	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

// Created by e-Zobar. All rights reversed (C) XNova Team 2008
?>