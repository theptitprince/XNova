<?php

/**
 * contact.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 2 (XNova Renaissance : formulaire de contact, plus aucune adresse e-mail affichee)
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('contact');

	// Signature de l'heure d'affichage du formulaire (anti-robot : ni formulaire trop rapide, ni formulaire recycle)
	function ContactFormSignature ( $Time ) {
		global $xnova_root_path;
		include($xnova_root_path . 'config.php');
		return hash_hmac('sha256', 'contact|' . intval($Time), $dbsettings['secretword']);
	}

	$parse  = $lang;
	$Errors = array();
	$Sent   = false;
	$Values = array('name' => '', 'email' => '', 'subject' => '', 'message' => '');

	// Joueur connecte : pseudo et e-mail pre-remplis
	if (is_array($user) && !empty($user['id'])) {
		$Values['name']  = $user['username'];
		$Values['email'] = $user['email'];
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$Values['name']    = SafeName(isset($_POST['name']) ? $_POST['name'] : '', 64);
		$Values['email']   = trim(isset($_POST['email']) ? $_POST['email'] : '');
		$Values['subject'] = trim(isset($_POST['subject']) ? $_POST['subject'] : '');
		$Values['message'] = trim(isset($_POST['message']) ? $_POST['message'] : '');
		$FormTime          = isset($_POST['ft']) ? intval($_POST['ft']) : 0;
		$FormSign          = isset($_POST['fs']) ? (string) $_POST['fs'] : '';
		$Ip                = $_SERVER['REMOTE_ADDR'];

		// Anti-robot : champ piege (invisible pour un humain), signature, delai de 3 s a 2 h
		if (!empty($_POST['website']) || !hash_equals(ContactFormSignature($FormTime), $FormSign)
			|| time() - $FormTime < 3 || time() - $FormTime > 7200) {
			$Errors[] = $lang['ctc_err_robot'];
		}
		if ($Values['name'] == '') {
			$Errors[] = $lang['ctc_err_name'];
		}
		if (!is_email($Values['email'])) {
			$Errors[] = $lang['ctc_err_email'];
		}
		if (mb_strlen($Values['subject'], 'UTF-8') < 2 || mb_strlen($Values['subject'], 'UTF-8') > 100) {
			$Errors[] = $lang['ctc_err_subject'];
		}
		if (mb_strlen($Values['message'], 'UTF-8') < 10 || mb_strlen($Values['message'], 'UTF-8') > 3000) {
			$Errors[] = $lang['ctc_err_message'];
		}
		// Au plus 3 messages par heure depuis la meme adresse
		$Recent = doquery("SELECT COUNT(*) AS `nb` FROM {{table}} WHERE `ip` = '". SqlEscape($Ip) ."' AND `time` > '". (time() - 3600) ."';", 'contact', true);
		if ($Recent['nb'] >= 3) {
			$Errors[] = $lang['ctc_err_flood'];
		}

		if (count($Errors) == 0) {
			$QryInsert  = "INSERT INTO {{table}} SET ";
			$QryInsert .= "`time` = '". time() ."', ";
			$QryInsert .= "`user_id` = '". ((is_array($user) && !empty($user['id'])) ? intval($user['id']) : 0) ."', ";
			$QryInsert .= "`name` = '". SqlEscape($Values['name']) ."', ";
			$QryInsert .= "`email` = '". SqlEscape($Values['email']) ."', ";
			$QryInsert .= "`subject` = '". SqlEscape(SafeText($Values['subject'])) ."', ";
			$QryInsert .= "`message` = '". SqlEscape(SafeText($Values['message'])) ."', ";
			$QryInsert .= "`ip` = '". SqlEscape($Ip) ."', ";
			$QryInsert .= "`is_read` = '0';";
			doquery($QryInsert, 'contact');
			$Sent = true;
		}
	}

	if ($Sent) {
		$parse['ctc_result'] = "<tr><th colspan=\"2\"><font color=\"lime\">". $lang['ctc_sent'] ."</font></th></tr>";
		$Values = array('name' => $Values['name'], 'email' => $Values['email'], 'subject' => '', 'message' => '');
	} elseif (count($Errors) > 0) {
		$parse['ctc_result'] = "<tr><th colspan=\"2\"><font color=\"red\">". implode('<br />', $Errors) ."</font></th></tr>";
	} else {
		$parse['ctc_result'] = '';
	}

	$Now = time();
	$parse['ctc_value_name']    = SafeText($Values['name']);
	$parse['ctc_value_email']   = SafeText($Values['email']);
	$parse['ctc_value_subject'] = SafeText($Values['subject']);
	$parse['ctc_value_message'] = SafeText($Values['message']);
	$parse['ctc_ft']            = $Now;
	$parse['ctc_fs']            = ContactFormSignature($Now);

	$page = parsetemplate(gettemplate('contact_body'), $parse);
	display($page, $lang['ctc_title'], false);

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Mise au propre (Virer tout ce qui ne sert pas a une prise de contact en fait)
// 2.0 - XNova Renaissance : formulaire de contact, messages lus dans l'administration, plus d'e-mail affiche
?>
