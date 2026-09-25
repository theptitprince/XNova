<?php

/**
 * add_declare.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original : XNova Team, d'après UGamela
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

/**
 * add_declare.php
 *
 * @version 1.1
 Base SQL requise :
 
 CREATE TABLE `game_declared` (
  `declarator` text NOT NULL,
  `declared_1` text NOT NULL,
  `declared_2` text NOT NULL,
  `declared_3` text NOT NULL,
  `reason` text NOT NULL,
  `declarator_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 
 
 */


define('INSIDE'  , true );
define('INSTALL' , false);

$xnova_root_path = './';
include( $xnova_root_path . 'extension.inc' );
include( $xnova_root_path . 'common.' . $phpEx );


		includeLang('messages');

		$mode      = ($_POST['mode'] ?? null);

		$PageTpl   = gettemplate("add_declare");
		$parse     = $lang;

		if ($mode == 'addit') {
			$declarator              = intval($user['id']);
			$declarator_name  = SqlEscape(SafeText($user['username']));
			$decl1        	   		  = SqlEscape(SafeText(trim($_POST['dec1'] ?? '')));
			$decl2       		       = SqlEscape(SafeText(trim($_POST['dec2'] ?? '')));
			$decl3        		      = SqlEscape(SafeText(trim($_POST['dec3'] ?? '')));
			$reason1        	  	 = SqlEscape(SafeText(trim($_POST['reason'] ?? '')));

			// Sans joueur indique, la declaration n'a pas de sens (et suffisait a echapper au robot anti-multi)
			if ($decl1 == '') {
				message ( $lang['declare_empty'], $lang['declare_title'], "add_declare.php" );
			}

			$QryDeclare  = "INSERT INTO {{table}} SET ";
			$QryDeclare .= "`declarator` = '". $declarator ."', ";
			$QryDeclare .= "`declarator_name` = '". $declarator_name ."', ";
			$QryDeclare .= "`declared_1` = '". $decl1 ."', ";
			$QryDeclare .= "`declared_2` = '". $decl2 ."', ";
			$QryDeclare .= "`declared_3` = '". $decl3 ."', ";
			$QryDeclare .= "`reason`     = '". $reason1 ."' ";

			doquery( $QryDeclare, "declared");
			doquery("UPDATE {{table}} SET `multi_validated` = '1' WHERE `id` = '". $declarator ."';", "users");

			message ( $lang['declare_done'], $lang['declare_title'] );
		}
		$Page = parsetemplate($PageTpl, $parse);

		display ($Page, $lang['declare_title'], false);


?>