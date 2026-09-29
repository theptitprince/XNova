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
// TheCookie[2] = jeton HMAC (id + hash du mot de passe + TheCookie[3] + TheCookie[4], signe avec le mot secret)
// TheCookie[3] = se souvenir de moi (1 = 365 jours)
// TheCookie[4] = date de connexion (0.9k) : le cookie est refuse une fois sa duree de validite passee

function CheckCookies ( $IsUserChecked ) {
	global $lang, $game_config, $xnova_root_path, $phpEx;

	includeLang('cookies');

	$UserRow = array();

	if (isset($_COOKIE[$game_config['COOKIE_NAME']])) {
		// Cookie envoye sous forme de tableau : erreur fatale dans explode() auparavant
		$TheCookie  = is_string($_COOKIE[$game_config['COOKIE_NAME']]) ? explode("/%/", $_COOKIE[$game_config['COOKIE_NAME']]) : array();

		// Cookie mal forme (ou ancien format, sans date de connexion, d'avant la 0.9k) : on l'efface, il faudra se
		// reconnecter
		if (count($TheCookie) != 5) {
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
		if (!hash_equals(AuthCookieToken($UserRow, $TheCookie[3], $TheCookie[4]), (string) $TheCookie[2])) {
			SetAuthCookie('', time() - 100000);
			message( $lang['cookies']['Error3'] );
		}

		// Duree de validite depassee (date de connexion signee) : cookie efface, le joueur n'est plus connecte
		// (renvoye vers la page de connexion par common.php)
		$Lifetime = ($TheCookie[3] == 1) ? AUTH_COOKIE_REMEMBER : AUTH_COOKIE_SESSION;
		if (intval($TheCookie[4]) + $Lifetime < time() || intval($TheCookie[4]) > time() + 300) {
			SetAuthCookie('', time() - 100000);
			$Return['state']  = false;
			$Return['record'] = array();
			return $Return;
		}

		$NextCookie = implode("/%/", $TheCookie);
		// Se souvenir de moi : le cookie du navigateur dure jusqu'a la fin de sa validite (365 jours apres la connexion)
		if ($TheCookie[3] == 1) {
			$ExpireTime = intval($TheCookie[4]) + AUTH_COOKIE_REMEMBER;
		} else {
			$ExpireTime = 0;
		}

		if ($IsUserChecked == false) {
			SetAuthCookie($NextCookie, $ExpireTime);
		}
		// Informations de connexion : toutes echappees (l'adresse de la page et le navigateur viennent du visiteur).
		// Adresse de la page sans le jeton CSRF : elle est affichee au staff (vue generale de l'administration), un
		// moderateur voyait le jeton d'un administrateur et pouvait lui faire valider une action (changer un rang...)
		$CurrentPage    = preg_replace('/([?&])csrf_token=[^&]*(&|$)/', '$1', (string) $_SERVER['REQUEST_URI']);
		$CurrentPage    = rtrim($CurrentPage, '?&');
		$QryUpdateUser  = "UPDATE {{table}} SET ";
		$QryUpdateUser .= "`onlinetime` = '". time() ."', ";
		$QryUpdateUser .= "`current_page` = '". SqlEscape($CurrentPage) ."', ";
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