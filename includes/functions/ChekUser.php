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
	global $user;
		includeLang('admin');
	$Result        = CheckCookies( $IsUserChecked );
	$IsUserChecked = $Result['state'];
	

	if ($Result['record'] != false) {
		$user = $Result['record'];
		if ($user['bana'] == "1") {
			die (

			$page .= parsetemplate(gettemplate('usr_banned'), $lang)

			);
		}
		$RetValue['record'] = $user;
		$RetValue['state']  = $IsUserChecked;
	} else {
		$RetValue['record'] = array();
		$RetValue['state']  = false;
	}

	return $RetValue;
}


?>