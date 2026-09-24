<?php

/**
 * login.php
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
define('LOGIN'   , true);

$InLogin = true;

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('login');

	if ($_POST) {
		$login = doquery("SELECT * FROM {{table}} WHERE `username` = '" . SqlEscape($_POST['username']) . "' LIMIT 1", "users", true);

		if ($login) {
			if (PasswordCheck($_POST['password'], $login)) {
				if (isset($_POST["rememberme"])) {
					$expiretime = time() + 31536000;
					$rememberme = 1;
				} else {
					$expiretime = 0;
					$rememberme = 0;
				}

				$cookie = $login["id"] . "/%/" . $login["username"] . "/%/" . AuthCookieToken($login) . "/%/" . $rememberme;
				SetAuthCookie($cookie, $expiretime);
				header("Location: ./frames.php");
				exit;
			} else {
				message($lang['Login_FailPassword'], $lang['Login_Error']);
			}
		} else {
			message($lang['Login_FailUser'], $lang['Login_Error']);
		}
	} else {
		$parse                 = $lang;
		$Count                 = doquery('SELECT COUNT(*) as `players` FROM {{table}} WHERE 1', 'users', true);
		$LastPlayer            = doquery('SELECT `username` FROM {{table}} ORDER BY `register_time` DESC', 'users', true);
		$parse['last_user']    = $LastPlayer['username'];
		$PlayersOnline         = doquery("SELECT COUNT(DISTINCT(id)) as `onlinenow` FROM {{table}} WHERE `onlinetime` > '" . (time()-900) ."';", 'users', true);
		$parse['online_users'] = $PlayersOnline['onlinenow'];
		$parse['users_amount'] = $Count['players'];
		$parse['servername']   = $game_config['game_name'];
		$parse['forum_url']    = $game_config['forum_url'];
		$parse['PasswordLost'] = $lang['PasswordLost'];

		$page = parsetemplate(gettemplate('login_body'), $parse);

		// Test pour prendre le nombre total de joueur et le nombre de joueurs connectés
		if ($_GET['ucount'] == 1) {
			$page = $PlayersOnline['onlinenow']."/".$Count['players'];
			die ( $page );
		} else {
			display($page, $lang['Login']);
		}
	}

// -----------------------------------------------------------------------------------------------------------
// History version

?>
