<?php

/**
 * SendNewPassword.php
 *
 * XNova Renaissance
 * Écrit par theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Mot de passe oublie, en deux etapes (0.9g). Avant, saisir l'adresse d'un joueur suffisait a changer son mot de
// passe : n'importe qui pouvait le faire. Desormais la demande envoie un lien de confirmation, et c'est ce lien
// qui fait envoyer le nouveau mot de passe, par mail comme dans l'original. La page affiche le meme message que
// l'adresse existe ou non (avant : « L'adresse n'existe pas ! » revelait les adresses inscrites).

// Validite du lien, et delai minimum entre deux demandes pour un meme compte (sinon on remplissait sa boite)
define('LOSTPW_LINK_LIFETIME', 3600);
define('LOSTPW_REQUEST_DELAY', 300);

// Adresse de la page Mot de passe oublie, dossier du jeu compris, pour le lien du mail. 0.9k : adresse du jeu
// reglee (game_url, voir GameUrl) et non plus celle de l'en-tete Host envoye par le visiteur : avec un Host
// falsifie, la victime recevait du jeu un lien valide vers le domaine de l'attaquant, qui recuperait le jeton.
function LostPasswordPageUrl () {
	return GameUrl() . 'lostpassword.php';
}

// Texte d'un mail : {cles} remplacees, entites HTML des fichiers de langue decodees (mail en texte brut)
function LostPasswordMailText ($Template, $Values) {
	return html_entity_decode(str_replace('\n', "\n", parsetemplate($Template, $Values)), ENT_QUOTES, 'UTF-8');
}

// Etape 1 : envoie le lien de confirmation. Le mot de passe ne change pas. Retourne true si un mail est parti.
function LostPasswordSendLink ($mail) {
	global $lang, $game_config;

	// Texte seulement (un tableau envoye par le formulaire ecrivait un avertissement)
	$mail = is_string($mail) ? trim($mail) : '';
	if ($mail == '') {
		return false;
	}
	$Account = doquery("SELECT `id`, `username`, `email`, `lostpw_time` FROM {{table}} WHERE `email` = '". SqlEscape($mail) ."' LIMIT 1;", 'users', true);
	if (empty($Account['email']) || $Account['lostpw_time'] > time() - LOSTPW_REQUEST_DELAY) {
		return false;
	}

	// Jeton aleatoire : seule son empreinte est gardee en base
	$Token = bin2hex(random_bytes(32));
	doquery("UPDATE {{table}} SET `lostpw_hash` = '". hash('sha256', $Token) ."', `lostpw_time` = '". time() ."' WHERE `id` = '". intval($Account['id']) ."' LIMIT 1;", 'users');

	$Link  = LostPasswordPageUrl() . '?action=2&id=' . intval($Account['id']) . '&token=' . $Token;
	$Body  = str_replace('%LINK%', $Link, LostPasswordMailText($lang['lp_link_mail_body'], array('username' => $Account['username'], 'gamename' => $game_config['game_name'], 'link' => '%LINK%')));
	$Title = html_entity_decode($game_config['game_name'] ." : ". $lang['lp_link_mail_title'], ENT_QUOTES, 'UTF-8');

	return mymail($Account['email'], $Title, $Body);
}

// Etape 2 : lien du mail (compte, jeton valable, moins d'une heure). Le lien ne sert qu'une fois ; le nouveau
// mot de passe part par mail et n'est change que si l'envoi a reussi. Retourne true si tout s'est bien passe.
function LostPasswordConfirm ($UserId, $Token) {
	global $lang, $game_config;

	$Token = is_string($Token) ? $Token : '';
	if (!preg_match('/^[0-9a-f]{64}$/', $Token)) {
		return false;
	}
	$Account = doquery("SELECT `id`, `username`, `email`, `lostpw_hash`, `lostpw_time` FROM {{table}} WHERE `id` = '". intval($UserId) ."' LIMIT 1;", 'users', true);
	if (!$Account || $Account['lostpw_hash'] == '' || !hash_equals($Account['lostpw_hash'], hash('sha256', $Token))
	    || $Account['lostpw_time'] < time() - LOSTPW_LINK_LIFETIME) {
		return false;
	}
	doquery("UPDATE {{table}} SET `lostpw_hash` = '' WHERE `id` = '". intval($Account['id']) ."' LIMIT 1;", 'users');

	// Caracteres du nouveau mot de passe (sans 0/O ni 1/l, faciles a confondre) ; random_int : tirage sur
	$Caracters = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
	$NewPass   = "";
	for ($i = 0; $i < 10; $i++) {
		$NewPass .= $Caracters[random_int(0, strlen($Caracters) - 1)];
	}

	$Body  = LostPasswordMailText($lang['lp_mail_body'], array('username' => $Account['username'], 'gamename' => $game_config['game_name'], 'password' => $NewPass));
	$Title = html_entity_decode($game_config['game_name'] ." : ". $lang['lp_mail_title'], ENT_QUOTES, 'UTF-8');
	if (!mymail($Account['email'], $Title, $Body)) {
		return false;
	}

	doquery("UPDATE {{table}} SET `password` = '". SqlEscape(PasswordHash($NewPass)) ."' WHERE `id` = '". intval($Account['id']) ."' LIMIT 1;", 'users');
	return true;
}

?>
