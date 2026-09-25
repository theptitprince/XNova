<?php

/**
 * SendNewPassword.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Tom1991 for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Mot de passe oublie : un nouveau mot de passe est envoye a l'adresse du compte.
// Retourne true si un mail est parti. Le mot de passe n'est change que si l'envoi a reussi
// (avant : change meme sans mail, le joueur ne pouvait plus se connecter). La page affiche le meme message
// que l'adresse existe ou non (avant : « L'adresse n'existe pas ! » revelait les adresses inscrites).
function sendnewpassword($mail) {
	global $lang, $game_config;

	$mail = trim((string) $mail);
	if ($mail == '') {
		return false;
	}
	$Account = doquery("SELECT `id`, `username`, `email` FROM {{table}} WHERE `email` = '". SqlEscape($mail) ."' LIMIT 1;", 'users', true);
	if (empty($Account['email'])) {
		return false;
	}

	// Caracteres du nouveau mot de passe (sans 0/O ni 1/l, faciles a confondre) ; random_int : tirage sur
	$Caracters = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
	$NewPass   = "";
	for ($i = 0; $i < 10; $i++) {
		$NewPass .= $Caracters[random_int(0, strlen($Caracters) - 1)];
	}

	$parse             = array();
	$parse['username'] = $Account['username'];
	$parse['gamename'] = $game_config['game_name'];
	$parse['password'] = $NewPass;
	$Body = html_entity_decode(str_replace('\n', "\n", parsetemplate($lang['lp_mail_body'], $parse)), ENT_QUOTES, 'UTF-8');
	$Title = html_entity_decode($game_config['game_name'] ." : ". $lang['lp_mail_title'], ENT_QUOTES, 'UTF-8');

	if (!mymail($Account['email'], $Title, $Body)) {
		return false;
	}

	doquery("UPDATE {{table}} SET `password` = '". SqlEscape(PasswordHash($NewPass)) ."' WHERE `id` = '". intval($Account['id']) ."' LIMIT 1;", 'users');
	return true;
}

?>
