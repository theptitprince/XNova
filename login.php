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

	// Erreur de connexion : affichee sur la page de connexion elle-meme (avant : page d'erreur sans habillage),
	// avec un message unique qui ne revele pas si le pseudo existe
	$LoginError = '';
	if ($_POST) {
		$login = doquery("SELECT * FROM {{table}} WHERE `username` = '" . SqlEscape(($_POST['username'] ?? null)) . "' LIMIT 1", "users", true);

		if ($login) {
			if (PasswordCheck(($_POST['password'] ?? null), $login)) {
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
				$LoginError = $lang['login_fail'];
			}
		} else {
			$LoginError = $lang['login_fail'];
		}
	}
	{
		$parse                 = $lang;
		$parse['login_error']  = ($LoginError != '') ? "<tr><td style=\"padding-right: 4px; color: #ff5050; font-weight: bold;\">". $LoginError ."</td></tr>" : "";
		$parse['login_username'] = htmlspecialchars((string) ($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
		$Count                 = doquery('SELECT COUNT(*) as `players` FROM {{table}} WHERE 1', 'users', true);
		$LastPlayer            = doquery('SELECT `username` FROM {{table}} ORDER BY `register_time` DESC', 'users', true);
		$parse['last_user']    = $LastPlayer['username'];
		$PlayersOnline         = doquery("SELECT COUNT(DISTINCT(id)) as `onlinenow` FROM {{table}} WHERE `onlinetime` > '" . (time()-900) ."';", 'users', true);
		$parse['online_users'] = $PlayersOnline['onlinenow'];
		$parse['users_amount'] = $Count['players'];
		$parse['servername']   = $game_config['game_name'];
		$parse['forum_link']   = (!empty($game_config['forum_url'])) ? "<a href=\"". htmlspecialchars($game_config['forum_url'], ENT_QUOTES) ."\">Forum</a>" : '';
		$parse['password_lost'] = $lang['password_lost'];

		$page = parsetemplate(gettemplate('login_body'), $parse);

		// Test pour prendre le nombre total de joueur et le nombre de joueurs connectés
		if (($_GET['ucount'] ?? null) == 1) {
			$page = $PlayersOnline['onlinenow']."/".$Count['players'];
			die ( $page );
		} else {
			display($page, $lang['login']);
		}
	}

// -----------------------------------------------------------------------------------------------------------
// History version

?>
