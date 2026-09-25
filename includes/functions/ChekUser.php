<?php

/**
 * ChekUser.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * CheckUser.php
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function CheckTheUser ( $IsUserChecked ) {
	global $user, $lang;
	$Result        = CheckCookies( $IsUserChecked );
	$IsUserChecked = $Result['state'];

	if ($Result['record'] != false) {
		$user = $Result['record'];
		// Langue du joueur connue a partir d'ici (charge avant, admin.mo l'etait toujours dans la langue par defaut)
		includeLang('admin');
		if ($user['bana'] == "1") {
			if ($user['banaday'] > 0 && $user['banaday'] <= time()) {
				// Sanction terminee : levee automatique (la duree etait ignoree, le bannissement restait definitif)
				doquery("UPDATE {{table}} SET `bana` = '0', `banaday` = '0' WHERE `id` = '". intval($user['id']) ."';", 'users');
				$user['bana']    = 0;
				$user['banaday'] = 0;
			} else {
				includeLang('system');
				$lang['sys_banned_text'] = ($user['banaday'] > 0) ? sprintf($lang['sys_banned_until'], date("d/m/Y H:i:s", $user['banaday'])) : $lang['sys_banned_forever'];
				$lang['dpath']           = empty($user['dpath']) ? DEFAULT_SKINPATH : SafePath($user['dpath']);
				die ( parsetemplate(gettemplate('usr_banned'), $lang) );
			}
		}
		$RetValue['record'] = $user;
		$RetValue['state']  = $IsUserChecked;
	} else {
		includeLang('admin');
		$RetValue['record'] = array();
		$RetValue['state']  = false;
	}

	return $RetValue;
}


?>