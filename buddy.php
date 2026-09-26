<?php

/**
 * buddy.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true );
define('INSTALL' , false);

$xnova_root_path = './';
include( $xnova_root_path . 'extension.inc' );
include( $xnova_root_path . 'common.' . $phpEx );

	includeLang('buddy');

// La page choisit la liste selon la presence des parametres (isset). Le portage en PHP 8 les convertissait en
// entiers (0 si absents) : isset() toujours vrai, liens « Demandes » jamais affiches, demandes recues invisibles,
// amis listes comme des demandes a supprimer.
$a = isset($_GET['a']) ? intval($_GET['a']) : null;
$e = isset($_GET['e']) ? intval($_GET['e']) : null;
$s = isset($_GET['s']) ? intval($_GET['s']) : null;
$u = intval( ($_GET['u'] ?? null) );

if ( ($s == 1 || $s == 2) && isset( $_GET['bid'] ) ) {
	// s=2 : accepter une demande recue ; s=1 : supprimer (ami, demande recue rejetee, demande envoyee annulee).
	// L'original n'avait qu'un lien pour tout, l'action dependant de l'etat : un double clic ou un rechargement
	// apres « Accepter » supprimait l'ami tout juste accepte.
	$bid = intval( ($_GET['bid'] ?? null) );

	$buddy = doquery( "SELECT * FROM {{table}} WHERE `id` = '".$bid."';", 'buddy', true );
	if ( $buddy && ($buddy['owner'] == $user['id'] || $buddy['sender'] == $user['id']) ) {
		if ( $s == 2 ) {
			if ( $buddy['owner'] == $user['id'] && $buddy['active'] == 0 ) {
				doquery( "UPDATE {{table}} SET `active` = '1' WHERE `id` = '".$bid."';", 'buddy' );
			}
		} else {
			doquery( "DELETE FROM {{table}} WHERE `id` = '".$bid."';", 'buddy' );
		}
	}
	// Retour a la liste d'ou vient le clic, sans l'action dans l'adresse
	header( "Location: buddy.php" . (isset($a) ? "?a=" . $a . (isset($e) ? "&e=" . $e : "") : "") );
	die();
} elseif ( ($_POST["s"] ?? null) == 3 && ($_POST["a"] ?? null) == 1 && ($_POST["e"] ?? null) == 1 && isset( $_POST["u"] ) ) {
	// Traitement de l'enregistrement de la demande d'entree dans la liste d'amis
	$uid = $user["id"];
	$u = intval( ($_POST["u"] ?? null) );

	// Destinataire controle ici aussi (le formulaire pouvait etre forge : demande a soi-meme ou a un compte absent)
	if ( $u == $uid ) {
		message( $lang['you_cannot_ask_yourself_for_a_request'], $lang['buddy_request_label'] );
	} elseif ( !doquery( "SELECT `id` FROM {{table}} WHERE `id` = '".$u."';", 'users', true ) ) {
		message( $lang['bud_player_not_found'], $lang['buddy_request_label'] );
	}

	$buddy = doquery( "SELECT * FROM {{table}} WHERE sender={$uid} AND owner={$u} OR sender={$u} AND owner={$uid}", 'buddy', true );

	if ( !$buddy ) {
		if ( strlen( ($_POST['text'] ?? null) ) > 5000 ) {
			message( $lang['bud_text_too_long'], $lang['sys_error'] );
		}
		$text = SqlEscape( SafeText( ($_POST['text'] ?? null) ) );
		doquery( "INSERT INTO {{table}} SET sender={$uid}, owner={$u}, active=0, text='{$text}'", 'buddy' );
		message( $lang['request_sent'], $lang['buddy_request_label'], 'buddy.php' );
	} else {
		message( $lang['a_request_exists_already_for_this_user'], $lang['buddy_request_label'] );
	}
}

$page = "<br>";

if ( $a == 2 && $u > 0 ) {
	// Saisie texte de demande d'entree dans la liste d'amis
	$u = doquery( "SELECT * FROM {{table}} WHERE id='$u'", "users", true );
	if ( !$u ) {
		message( $lang['bud_player_not_found'], $lang['buddy_request_label'] );
	} elseif ( $u["id"] != $user["id"] ) {
		$page .= "
		<script src=\"scripts/cntchar.js\" type=\"text/javascript\"></script>
		<script src=\"scripts/win.js\" type=\"text/javascript\"></script>
		<center>
			<form action=buddy.php method=post>
			<input type=hidden name=a value=1>
			<input type=hidden name=s value=3>
			<input type=hidden name=e value=1>
			<input type=hidden name=u value=" . $u["id"] . ">
			<table width=519>
			<tr>
				<td class=c colspan=2>{$lang['buddy_request_label']}</td>
			</tr><tr>
				<th>{$lang['player_label']}</th>
				<th>" . htmlspecialchars($u["username"], ENT_QUOTES, 'UTF-8') . "</th>
			</tr><tr>
				<th>{$lang['request_text_label']} (<span id=\"cntChars\">0</span> / 5000 {$lang['characters']})</th>
				<th><textarea name=text cols=60 rows=10 onKeyUp=\"javascript:cntchar(5000)\"></textarea></th>
			</tr><tr>
				<td class=c><a href=\"javascript:history.back();\">{$lang['back']}</a></td>
				<td class=c><input type=submit value='{$lang['send_label']}'></td>
			</tr>
		</table></form>
		</center>";
		// (lien retour : la fonction back() n'existait pas ; titre « buddy » ecrit en dur)
		display( $page, $lang['buddy_request_label'] );
	} else {
		message( $lang['you_cannot_ask_yourself_for_a_request'], $lang['buddy_request_label'] );
	}
}
// con a indicamos las solicitudes y con e las distiguimos
if ( $a == 1 )
	$TableTitle = ( $e == 1 ) ? $lang['my_requests']:$lang['anothers_requests'];
else
	$TableTitle = $lang['buddy_list'];

$page .= "
<table width=519>
<tr>
	<td class=c colspan=6>{$TableTitle}</td>
</tr>";

if ( !isset( $a ) ) {
	$page .= "
	<tr>
		<th colspan=6><a href=?a=1>{$lang['requests_label']}</a></th>
	</tr><tr>
		<th colspan=6><a href=?a=1&e=1>{$lang['my_requests']}</a></th>
	</tr><tr>
		<td class=c></td>
		<td class=c>{$lang['name_label']}</td>
		<td class=c>{$lang['alliance_label']}</td>
		<td class=c>{$lang['coordinates_label']}</td>
		<td class=c>{$lang['bud_status_label']}</td>
		<td class=c></td>
	</tr>";
}

if ( $a == 1 ) {
	$query = ( $e == 1 ) ? "WHERE active=0 AND sender=" . $user["id"] : "WHERE active=0 AND owner=" . $user["id"];
} else {
	$query = "WHERE active=1 AND sender=" . $user["id"] . " OR active=1 AND owner=" . $user["id"];
}
$buddyrow = doquery( "SELECT * FROM {{table}} " . $query, 'buddy' );

$i = 0;
while ( $b = mysqli_fetch_array( $buddyrow ) ) {
	// para solicitudes
	if ( $i == 0 && isset( $a ) ) {
		$page .= "
		<tr>
			<td class=c></td>
			<td class=c>{$lang['user_label']}</td>
			<td class=c>{$lang['alliance_label']}</td>
			<td class=c>{$lang['coordinates_label']}</td>
			<td class=c>{$lang['text_label']}</td>
			<td class=c></td>
		</tr>";
	}

	$i++;
	$uid = ( $b["owner"] == $user["id"] ) ? $b["sender"] : $b["owner"];
	// query del user
	$u = doquery( "SELECT id,username,galaxy,system,planet,onlinetime,ally_id,ally_name FROM {{table}} WHERE id=" . intval($uid), "users", true );
	if ( !$u ) {
		$i--;
		continue;
	}
	// Alliance de CE joueur (l'original accumulait les alliances d'une ligne a l'autre, avec l'id du joueur dans le lien)
	$UserAlly = '';
	if ( $u["ally_id"] != 0 ) {
		$UserAlly = "<a href=alliance.php?mode=ainfo&a=" . $u["ally_id"] . ">" . htmlspecialchars($u["ally_name"], ENT_QUOTES, 'UTF-8') . "</a>";
	}

	if ( isset( $a ) ) {
		$LastOnline = $b["text"];
	} else {
		$LastOnline = "<font color=";
		if ( $u["onlinetime"] + 60 * 10 >= time() ) {
			$LastOnline .= "lime>{$lang['on_label']}";
		} elseif ( $u["onlinetime"] + 60 * 20 >= time() ) {
			$LastOnline .= "yellow>{$lang['15_min']}";
		} else {
			$LastOnline .= "red>{$lang['off']}";
		}
		$LastOnline .= "</font>";
	}

	if ( isset( $a ) && isset( $e ) ) {
		$UserCommand = "<a href=?a=1&e=1&s=1&bid=" . $b["id"] . ">{$lang['delete_request']}</a>";
	} elseif ( isset( $a ) ) {
		$UserCommand = "<a href=?s=2&bid=" . $b["id"] . ">{$lang['ok']}</a><br/>";
		$UserCommand .= "<a href=?a=1&s=1&bid=" . $b["id"] . ">{$lang['reject']}</a>";
	} else {
		$UserCommand = "<a href=?s=1&bid=" . $b["id"] . ">{$lang['delete_label']}</a>";
	}

	$page .= "
	<tr>
		<th width=20>" . $i . "</th>
		<th><a href=messages.php?mode=write&id=" . $u["id"] . ">" . htmlspecialchars($u["username"], ENT_QUOTES, 'UTF-8') . "</a></th>
		<th>{$UserAlly}</th>
		<th><a href=\"galaxy.php?mode=3&galaxy=" . $u["galaxy"] . "&system=" . $u["system"] . "\">" . $u["galaxy"] . ":" . $u["system"] . ":" . $u["planet"] . "</a></th>
		<th>{$LastOnline}</th>
		<th>{$UserCommand}</th>
	</tr>";
}

if ( $i == 0 ) {
	$page .= "
	<tr>
		<th colspan=6>" . (isset($a) ? $lang['there_is_no_request'] : $lang['bud_no_buddy']) . "</th>
	</tr>";
}

if ( $a == 1 ) {
	$page .= "
	<tr>
		<td colspan=6 class=c><a href=buddy.php>{$lang['back']}</a></td>
	</tr>";
}

$page .= "
	</table>
	</center>";

display ( $page, $lang['buddy_list'], false );
// Created by Perberos. All rights reversed (C) 2006
?>
