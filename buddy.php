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

$a = intval( ($_GET['a'] ?? null) );
$e = intval( ($_GET['e'] ?? null) );
$s = intval( ($_GET['s'] ?? null) );
$u = intval( ($_GET['u'] ?? null) );

if ( $s == 1 && isset( $_GET['bid'] ) ) {
	// Effacer une entree de la liste d'amis
	$bid = intval( ($_GET['bid'] ?? null) );

	$buddy = doquery( "SELECT * FROM {{table}} WHERE `id` = '".$bid."';", 'buddy', true );
	if ( $buddy['owner'] == $user['id'] ) {
		if ( $buddy['active'] == 0 && $a == 1 ) {
			doquery( "DELETE FROM {{table}} WHERE `id` = '".$bid."';", 'buddy' );
		} elseif ( $buddy['active'] == 1 ) {
			doquery( "DELETE FROM {{table}} WHERE `id` = '".$bid."';", 'buddy' );
		} elseif ( $buddy['active'] == 0 ) {
			doquery( "UPDATE {{table}} SET `active` = '1' WHERE `id` = '".$bid."';", 'buddy' );
		}
	} elseif ( $buddy['sender'] == $user['id'] ) {
		doquery( "DELETE FROM {{table}} WHERE `id` = '".$bid."';", 'buddy' );
	}
} elseif ( ($_POST["s"] ?? null) == 3 && ($_POST["a"] ?? null) == 1 && ($_POST["e"] ?? null) == 1 && isset( $_POST["u"] ) ) {
	// Traitement de l'enregistrement de la demande d'entree dans la liste d'amis
	$uid = $user["id"];
	$u = intval( ($_POST["u"] ?? null) );

	$buddy = doquery( "SELECT * FROM {{table}} WHERE sender={$uid} AND owner={$u} OR sender={$u} AND owner={$uid}", 'buddy', true );

	if ( !$buddy ) {
		if ( strlen( ($_POST['text'] ?? null) ) > 5000 ) {
			message( "Le texte ne doit pas faire plus de 5000 caract&egrave;res !", "Erreur" );
		}
		$text = SqlEscape( SafeText( ($_POST['text'] ?? null) ) );
		doquery( "INSERT INTO {{table}} SET sender={$uid}, owner={$u}, active=0, text='{$text}'", 'buddy' );
		message( $lang['request_sent'], $lang['buddy_request_label'], 'buddy.php' );
	} else {
		message( $lang['a_request_exists_already_for_this_user'], $lang['buddy_request_label'] );
	}
}

$page = "<br>";

if ( $a == 2 && isset( $u ) ) {
	// Saisie texte de demande d'entree dans la liste d'amis
	$u = doquery( "SELECT * FROM {{table}} WHERE id='$u'", "users", true );
	if ( isset( $u ) && $u["id"] != $user["id"] ) {
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
				<th>" . $u["username"] . "</th>
			</tr><tr>
				<th>{$lang['request_text_label']} (<span id=\"cntChars\">0</span> / 5000 {$lang['characters']})</th>
				<th><textarea name=text cols=60 rows=10 onKeyUp=\"javascript:cntchar(5000)\"></textarea></th>
			</tr><tr>
				<td class=c><a href=\"javascript:back();\">{$lang['back']}</a></td>
				<td class=c><input type=submit value='{$lang['send_label']}'></td>
			</tr>
		</table></form>
		</center>
		</body>
		</html>";
		display( $page, 'buddy' );
	} elseif ( $u["id"] == $user["id"] ) {
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
		<td class=c>{$lang['position_label']}</td>
		<td class=c></td>
	</tr>";
}

if ( $a == 1 ) {
	$query = ( $e == 1 ) ? "WHERE active=0 AND sender=" . $user["id"] : "WHERE active=0 AND owner=" . $user["id"];
} else {
	$query = "WHERE active=1 AND sender=" . $user["id"] . " OR active=1 AND owner=" . $user["id"];
}
$buddyrow = doquery( "SELECT * FROM {{table}} " . $query, 'buddy' );

while ( $b = mysqli_fetch_array( $buddyrow ) ) {
	// para solicitudes
	if ( !isset( $i ) && isset( $a ) ) {
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
	$u = doquery( "SELECT id,username,galaxy,system,planet,onlinetime,ally_id,ally_name FROM {{table}} WHERE id=" . $uid, "users", true );
	// $g = doquery("SELECT galaxy, system, planet FROM {{table}} WHERE id_planet=".$u["id_planet"],"galaxy",true);
	// $a = doquery("SELECT * FROM {{table}} WHERE id=".$uid,"aliance",true);
	if ( $u["ally_id"] != 0 ) { // Alianza
		// $allyrow = doquery("SELECT id,ally_tag FROM {{table}} WHERE id=".$u["ally_id"],"alliance",true);
		// if($allyrow){
		$UserAlly .= "<a href=alliance.php?mode=ainfo&a=" . $u["id"] . ">" . $u["ally_name"] . "</a>";
		// }
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
		$UserCommand = "<a href=?s=1&bid=" . $b["id"] . ">{$lang['delete_request']}</a>";
	} elseif ( isset( $a ) ) {
		$UserCommand = "<a href=?s=1&bid=" . $b["id"] . ">{$lang['ok']}</a><br/>";
		$UserCommand .= "<a href=?a=1&s=1&bid=" . $b["id"] . ">{$lang['reject']}</a></a>";
	} else {
		$UserCommand = "<a href=?s=1&bid=" . $b["id"] . ">{$lang['delete_label']}</a>";
	}

	$page .= "
	<tr>
		<th width=20>" . $i . "</th>
		<th><a href=messages.php?mode=write&id=" . $u["id"] . ">" . $u["username"] . "</a></th>
		<th>{$UserAlly}</th>
		<th><a href=\"galaxy.php?mode=3&galaxy=" . $u["galaxy"] . "&system=" . $u["system"] . "\">" . $u["galaxy"] . ":" . $u["system"] . ":" . $u["planet"] . "</a></th>
		<th>{$LastOnline}</th>
		<th>{$UserCommand}</th>
	</tr>";
}

if ( !isset( $i ) ) {
	$page .= "
	<tr>
		<th colspan=6>{$lang['there_is_no_request']}</th>
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
