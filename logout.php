<?php

/**
 * logout.php
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

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

	includeLang('logout');

	// Deconnexion d'un joueur connecte : seulement avec son jeton CSRF (formulaire du menu). Un simple lien, place
	// sur un autre site, deconnectait le joueur ; sans jeton, on lui demande de confirmer
	if (!empty($user['id']) && !CsrfValid()) {
		$Page  = "<br><br><form action=\"logout.php\" method=\"post\" target=\"_top\"><table width=\"519\">";
		$Page .= "<tr><td class=\"c\">". $lang['logout_confirm_title'] ."</td></tr>";
		$Page .= "<tr><th>". $lang['logout_confirm'] ."<br><br><input type=\"submit\" value=\"". $lang['logout_confirm_button'] ."\"></th></tr>";
		$Page .= "</table></form>";
		display($Page, $lang['logout_confirm_title'], false);
	}

	SetAuthCookie("", time()-100000);

	message ( $lang['see_you'], $lang['session_closed'], "login.".$phpEx );

// -----------------------------------------------------------------------------------------------------------
// History version
?>