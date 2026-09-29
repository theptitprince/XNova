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

	// Essais de mot de passe limites (decide par theptitprince) : apres 5 essais rates pour un meme pseudo depuis
	// une meme adresse IP (IPv6 : un meme /64), la connexion a ce pseudo depuis cette adresse est refusee pendant
	// 15 minutes. Compte aussi pour un pseudo qui n'existe pas : le refus ne revele pas si le compte existe.
	define('LOGIN_MAX_FAILURES', 5);
	define('LOGIN_BLOCK_TIME', 900);

	// Essais rates dans les 15 minutes qui precedent le dernier essai rate ; 0 si ce dernier a plus de 15 minutes
	function LoginFailures ( $Name, $Ip ) {
		$Where = "`username` = '". SqlEscape($Name) ."' AND `ip` = '". SqlEscape($Ip) ."'";
		$Last  = doquery("SELECT MAX(`time`) AS `last` FROM {{table}} WHERE ". $Where .";", 'login_attempts', true);
		if (empty($Last['last']) || $Last['last'] <= time() - LOGIN_BLOCK_TIME) {
			return 0;
		}
		$Count = doquery("SELECT COUNT(*) AS `count` FROM {{table}} WHERE ". $Where ." AND `time` > '". (intval($Last['last']) - LOGIN_BLOCK_TIME) ."';", 'login_attempts', true);
		return intval($Count['count']);
	}

	// Adresse comptee pour la limite : IPv4 telle quelle, IPv6 regroupee par /64 (un abonne ou un serveur dispose
	// en general d'un /64 entier : en changeant d'adresse a chaque essai, il echappait a la limite). Adresse IPv4
	// vue en IPv6 (::ffff:a.b.c.d, serveur double pile) : l'adresse IPv4 elle-meme, pas le /64 commun a toutes.
	// Derriere un proxy inverse, REMOTE_ADDR est l'adresse du proxy : le serveur web doit la remplacer par celle du
	// visiteur (mod_remoteip d'Apache, real_ip de nginx) ; X-Forwarded-For n'est pas lu, le visiteur le choisit.
	function LoginAttemptIp ( $Ip ) {
		$Ip = (string) $Ip;
		if (filter_var($Ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
			$Bin = inet_pton($Ip);
			if (substr($Bin, 0, 12) == str_repeat(chr(0), 10) . chr(255) . chr(255)) {
				return inet_ntop(substr($Bin, 12));
			}
			return inet_ntop(substr($Bin, 0, 8) . str_repeat(chr(0), 8)) . '/64';
		}
		return substr($Ip, 0, 45);
	}

	// Erreur de connexion : affichee sur la page de connexion elle-meme (avant : page d'erreur sans habillage),
	// avec un message unique qui ne revele pas si le pseudo existe
	$LoginError = '';
	// Champs recus en texte seulement (un tableau faisait une erreur fatale dans password_verify)
	$LoginName  = is_string($_POST['username'] ?? null) ? $_POST['username'] : '';
	$LoginPass  = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
	// Table des essais absente (fichiers de la 0.9k deja copies, base pas encore mise a jour) : pas de limite plutot
	// qu'une erreur SQL, la connexion reste possible jusqu'a la mise a jour
	$AttemptsTable = ($_POST && mysqli_num_rows(doquery("SHOW TABLES LIKE '{{table}}';", 'login_attempts')) == 1);
	if ($AttemptsTable) {
		$AttemptName = mb_substr($LoginName, 0, USERNAME_MAX_LENGTH, 'UTF-8');
		$AttemptIp   = LoginAttemptIp($_SERVER['REMOTE_ADDR'] ?? '');
		if (LoginFailures($AttemptName, $AttemptIp) >= LOGIN_MAX_FAILURES) {
			$LoginError = $lang['login_blocked'];
		} else {
			// Essai enregistre avant de verifier le mot de passe : des requetes simultanees ne depassent pas la limite
			doquery("DELETE FROM {{table}} WHERE `time` < '". (time() - 2 * LOGIN_BLOCK_TIME) ."';", 'login_attempts');
			doquery("INSERT INTO {{table}} SET `username` = '". SqlEscape($AttemptName) ."', `ip` = '". SqlEscape($AttemptIp) ."', `time` = '". time() ."';", 'login_attempts');
			$AttemptId = mysqli_insert_id(DbConnect());
			if (LoginFailures($AttemptName, $AttemptIp) > LOGIN_MAX_FAILURES) {
				doquery("DELETE FROM {{table}} WHERE `id` = '". intval($AttemptId) ."';", 'login_attempts');
				$LoginError = $lang['login_blocked'];
			}
		}
	}
	if ($_POST && $LoginError == '') {
		$login = doquery("SELECT * FROM {{table}} WHERE `username` = '" . SqlEscape($LoginName) . "' LIMIT 1", "users", true);

		if ($login) {
			if (PasswordCheck($LoginPass, $login)) {
				// Connexion reussie : les essais rates de ce pseudo depuis cette adresse sont oublies
				if ($AttemptsTable) {
					doquery("DELETE FROM {{table}} WHERE `username` = '". SqlEscape($AttemptName) ."' AND `ip` = '". SqlEscape($AttemptIp) ."';", 'login_attempts');
				}
				if (isset($_POST["rememberme"])) {
					$expiretime = time() + AUTH_COOKIE_REMEMBER;
					$rememberme = 1;
				} else {
					$expiretime = 0;
					$rememberme = 0;
				}

				SetAuthCookie(AuthCookieValue($login, $rememberme, time()), $expiretime);
				header("Location: ./frames.php");
				exit;
			} else {
				$LoginError = $lang['login_fail'];
			}
		} else {
			// Pseudo inconnu : meme temps de calcul qu'un vrai essai (la reponse plus rapide revelait les pseudos)
			PasswordHash($LoginPass);
			$LoginError = $lang['login_fail'];
		}
	}
	{
		$parse                 = $lang;
		$parse['login_error']  = ($LoginError != '') ? "<tr><td style=\"padding-right: 4px; color: #ff5050; font-weight: bold;\">". $LoginError ."</td></tr>" : "";
		$parse['login_username'] = htmlspecialchars($LoginName, ENT_QUOTES, 'UTF-8');
		$Count                 = doquery('SELECT COUNT(*) as `players` FROM {{table}} WHERE 1', 'users', true);
		// Dernier inscrit : les deux plus recents suffisent (avant : tous les joueurs tries et transferes pour en lire
		// un). Deux inscrits dans la meme seconde : la requete d'origine decide, pour garder le meme joueur
		$LastPlayers           = array();
		$LastPlayer            = doquery('SELECT `username`, `register_time` FROM {{table}} ORDER BY `register_time` DESC LIMIT 2', 'users');
		while ($LastRow = mysqli_fetch_assoc($LastPlayer)) {
			$LastPlayers[] = $LastRow;
		}
		if (count($LastPlayers) == 2 && $LastPlayers[0]['register_time'] == $LastPlayers[1]['register_time']) {
			$LastPlayer        = doquery('SELECT `username` FROM {{table}} ORDER BY `register_time` DESC', 'users', true);
			$parse['last_user'] = $LastPlayer['username'];
		} else {
			$parse['last_user'] = $LastPlayers[0]['username'] ?? null;
		}
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
