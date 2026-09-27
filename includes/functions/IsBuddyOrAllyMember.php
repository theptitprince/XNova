<?php

/**
 * IsBuddyOrAllyMember.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Joueurs « allies » pour les missions a plusieurs (0.9i) : membres de la meme alliance, ou amis (demande acceptee
// dans la liste d'amis, dans un sens ou dans l'autre). Sert au stationnement chez un allie et aux invitations d'une
// attaque groupee. Jamais vrai pour soi-meme. A ne pas appeler pendant le traitement des flottes (la table des amis
// n'y est pas verrouillee).
function IsBuddyOrAllyMember ( $UserId, $OtherId ) {
	$UserId  = intval($UserId);
	$OtherId = intval($OtherId);
	if ($UserId < 1 || $OtherId < 1 || $UserId == $OtherId) {
		return false;
	}

	$Alliance = array();
	$Query    = doquery("SELECT `id`, `ally_id` FROM {{table}} WHERE `id` IN ('". $UserId ."', '". $OtherId ."');", 'users');
	while ($Row = mysqli_fetch_assoc($Query)) {
		$Alliance[$Row['id']] = intval($Row['ally_id']);
	}
	if (!empty($Alliance[$UserId]) && $Alliance[$UserId] == ($Alliance[$OtherId] ?? 0)) {
		return true;
	}

	$Buddy = doquery("SELECT `id` FROM {{table}} WHERE `active` = '1' AND ((`sender` = '". $UserId ."' AND `owner` = '". $OtherId ."') OR (`sender` = '". $OtherId ."' AND `owner` = '". $UserId ."')) LIMIT 1;", 'buddy', true);
	return !empty($Buddy);
}

?>
