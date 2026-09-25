<?php

/**
 * SendGameMail.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original : XNova Team, d'apres UGamela (fonction mymail de reg.php)
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Envoi d'un mail du jeu (inscription, mot de passe oublie). Retourne le resultat de mail().
// Expediteur : ADMINEMAIL si defini, sinon l'adresse du compte administrateur (avant : admin@xnova.fr)
function mymail($to, $title, $body, $from = '')
{
    global $game_config;

    $from = trim($from);

    $AdminMail = ADMINEMAIL;
    if ($AdminMail == '') {
        $AdminRow  = doquery("SELECT `email` FROM {{table}} WHERE `authlevel` >= 3 ORDER BY `id` LIMIT 1;", 'users', true);
        $AdminMail = $AdminRow ? $AdminRow['email'] : '';
    }
    if (!$from) {
        $from = $AdminMail;
    }

    $rp  = $AdminMail;
    $org = $game_config['game_name'] ?? 'XNova';

    $head = '';
    $head .= "Content-Type: text/plain; charset=UTF-8 \r\n";
    $head .= "Date: " . date('r') . " \r\n";
    $head .= "Return-Path: $rp \r\n";
    $head .= "From: $from \r\n";
    $head .= "Sender: $from \r\n";
    $head .= "Reply-To: $from \r\n";
    $head .= "Organization: $org \r\n";
    $head .= "X-Sender: $from \r\n";
    $head .= "X-Priority: 3 \r\n";
    $body = str_replace("\r\n", "\n", $body);
    $body = str_replace("\n", "\r\n", $body);

    // Sujet encode (accents)
    $title = '=?UTF-8?B?' . base64_encode($title) . '?=';

    return @mail($to, $title, $body, $head);
}

?>
