<?php

/**
 * CheckCookies.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */
// TheCookie[0] = `id`
// TheCookie[1] = `username`
// TheCookie[2] = jeton HMAC (id + hash du mot de passe, signe avec le mot secret de config.php)
// TheCookie[3] = se souvenir de moi (1 = 365 jours)

function CheckCookies ( $IsUserChecked ) {
	global $lang, $game_config, $xnova_root_path, $phpEx;

	includeLang('cookies');

	$UserRow = array();

	if (isset($_COOKIE[$game_config['COOKIE_NAME']])) {
		$TheCookie  = explode("/%/", $_COOKIE[$game_config['COOKIE_NAME']]);

		// Cookie mal forme (ou ancien format md5 d'avant la 0.9e) : on l'efface, il faudra se reconnecter
		if (count($TheCookie) != 4) {
			SetAuthCookie('', time() - 100000);
			message( $lang['cookies']['Error3'] );
		}

		// Recherche par id (entier) : plus d'injection SQL possible par le cookie
		$UserResult = doquery("SELECT * FROM {{table}} WHERE `id` = '". intval($TheCookie[0]) ."';", 'users');

		if (mysqli_num_rows($UserResult) != 1) {
			SetAuthCookie('', time() - 100000);
			message( $lang['cookies']['Error1'] );
		}

		$UserRow    = mysqli_fetch_array($UserResult);

		// On teste si le cookie correspond bien a ce joueur
		if ($UserRow["username"] !== $TheCookie[1]) {
			SetAuthCookie('', time() - 100000);
			message( $lang['cookies']['Error2'] );
		}

		// On teste la signature (comparaison a temps constant)
		if (!hash_equals(AuthCookieToken($UserRow), (string) $TheCookie[2])) {
			SetAuthCookie('', time() - 100000);
			message( $lang['cookies']['Error3'] );
		}

		$NextCookie = implode("/%/", $TheCookie);
		// Au cas ou dans l'ancien cookie il etait question de se souvenir de moi
		// 3600 = 1 Heure // 86400 = 1 Jour // 31536000 = 365 Jours
		// on ajoute au compteur!
		if ($TheCookie[3] == 1) {
			$ExpireTime = time() + 31536000;
		} else {
			$ExpireTime = 0;
		}

		if ($IsUserChecked == false) {
			SetAuthCookie($NextCookie, $ExpireTime);
		}
		// Informations de connexion : toutes echappees (l'adresse de la page et le navigateur viennent du visiteur)
		$QryUpdateUser  = "UPDATE {{table}} SET ";
		$QryUpdateUser .= "`onlinetime` = '". time() ."', ";
		$QryUpdateUser .= "`current_page` = '". SqlEscape($_SERVER['REQUEST_URI']) ."', ";
		$QryUpdateUser .= "`user_lastip` = '". SqlEscape($_SERVER['REMOTE_ADDR']) ."', ";
		$QryUpdateUser .= "`user_agent` = '". SqlEscape(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') ."' ";
		$QryUpdateUser .= "WHERE ";
		$QryUpdateUser .= "`id` = '". intval($UserRow['id']) ."' LIMIT 1;";
		doquery( $QryUpdateUser, 'users');
		$IsUserChecked = true;
	}

	$Return['state']  = $IsUserChecked;
	$Return['record'] = $UserRow;

	return $Return;
}

?>