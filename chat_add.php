<?php

/**
 * chat_add.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by e-Zobar for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('chat');

	// Chat desactive dans l'administration : texte renvoye au lieu d'enregistrer (affiche par scripts/chat.js)
	if (($game_config['chat_enabled'] ?? '1') == '0') {
		die($lang['chat_disabled']);
	}

	// On récupère les informations du message et de l'envoyeur
	if (isset($_POST["msg"]) && is_string($_POST["msg"]) && isset($user['username'])) {
	   $nick = trim (str_replace ("+","plus",$user['username']));
	   $msg  = trim (str_replace ("+","plus",$_POST["msg"]));
	   // 500 caracteres au plus : rien ne limitait la taille, et chaque message est renvoye a tous toutes les 3 s
	   $msg  = SqlEscape (mb_substr ($_POST["msg"], 0, 500, 'UTF-8'));
	   $nick = SqlEscape ($user['username']);
	}
	else {
	   $msg="";
	   $nick="";
	}

	// Ajout du message dans la database
	if ($msg!="" && $nick!="") {
	   // Un message toutes les 2 secondes au plus par joueur : heure du dernier message (en millisecondes) changee en
	   // une seule requete, deux envois simultanes ne passent donc pas tous les deux
	   $Now   = intval(microtime(true) * 1000);
	   doquery("UPDATE {{table}} SET `chat_last` = '". $Now ."' WHERE `id` = '". intval($user['id']) ."' AND `chat_last` <= '". ($Now - 2000) ."';", 'users');
	   if (mysqli_affected_rows(DbConnect()) != 1) {
	      die($lang['chat_too_fast']);
	   }
	   $query = doquery("INSERT INTO {{table}}(user, message, timestamp) VALUES ('".$nick."', '".$msg."', '".time()."')", "chat");
	}

// Shoutbox by e-Zobar - Copyright XNova Team 2008
?>