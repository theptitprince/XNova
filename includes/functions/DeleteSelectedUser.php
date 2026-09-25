<?php

/**
 * DeleteSelectedUser.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function DeleteSelectedUser ( $UserID ) {

	$TheUser = doquery ( "SELECT * FROM {{table}} WHERE `id` = '" . $UserID . "';", 'users', true );
	if ( $TheUser['ally_id'] != 0 ) {
		$TheAlly = doquery ( "SELECT * FROM {{table}} WHERE `id` = '" . $TheUser['ally_id'] . "';", 'alliance', true );
		// Membres restants (le compte supprime mis a part)
		$Heir = $TheAlly ? doquery ( "SELECT `id` FROM {{table}} WHERE `ally_id` = '" . $TheAlly['id'] . "' AND `id` <> '" . intval($UserID) . "' ORDER BY `ally_register_time` ASC LIMIT 1;", 'users', true ) : false;
		if ( $Heir ) {
			$Count = doquery ( "SELECT COUNT(*) AS `number` FROM {{table}} WHERE `ally_id` = '" . $TheAlly['id'] . "' AND `id` <> '" . intval($UserID) . "';", 'users', true );
			$Owner = ( $TheAlly['ally_owner'] == $UserID ) ? ", `ally_owner` = '" . $Heir['id'] . "'" : '';
			doquery ( "UPDATE {{table}} SET `ally_members` = '" . intval($Count['number']) . "'" . $Owner . " WHERE `id` = '" . $TheAlly['id'] . "';", 'alliance' );
			// Fondateur supprime : l'alliance passe au membre le plus ancien (sinon plus personne ne pourrait l'administrer)
			if ( $Owner != '' ) {
				doquery ( "UPDATE {{table}} SET `ally_rank_id` = '0' WHERE `id` = '" . $Heir['id'] . "';", 'users' );
			}
		} elseif ( $TheAlly ) {
			doquery ( "DELETE FROM {{table}} WHERE `id` = '" . $TheAlly['id'] . "';", 'alliance' );
			doquery ( "DELETE FROM {{table}} WHERE `stat_type` = '2' AND `id_owner` = '" . $TheAlly['id'] . "';", 'statpoints' );
			// Candidatures en attente vers l'alliance disparue
			doquery ( "UPDATE {{table}} SET `ally_request` = '0', `ally_request_text` = '' WHERE `ally_request` = '" . $TheAlly['id'] . "';", 'users' );
		}
	}
	doquery ( "DELETE FROM {{table}} WHERE `stat_type` = '1' AND `id_owner` = '" . $UserID . "';", 'statpoints' );

	$ThePlanets = doquery ( "SELECT * FROM {{table}} WHERE `id_owner` = '" . $UserID . "';", 'planets' );
	while ( $OnePlanet = mysqli_fetch_assoc( $ThePlanets ) ) {
		if ( $OnePlanet['planet_type'] == 1 ) {
			doquery ( "DELETE FROM {{table}} WHERE `galaxy` = '" . $OnePlanet['galaxy'] . "' AND `system` = '" . $OnePlanet['system'] . "' AND `planet` = '" . $OnePlanet['planet'] . "';", 'galaxy' );
		} elseif ( $OnePlanet['planet_type'] == 3 ) {
			doquery ( "DELETE FROM {{table}} WHERE `galaxy` = '" . $OnePlanet['galaxy'] . "' AND `system` = '" . $OnePlanet['system'] . "' AND `lunapos` = '" . $OnePlanet['planet'] . "';", 'lunas' );
		}
		doquery ( "DELETE FROM {{table}} WHERE `id` = '" . $OnePlanet['id'] . "';", 'planets' );
	}
	doquery ( "DELETE FROM {{table}} WHERE `message_sender` = '" . $UserID . "';", 'messages' );
	doquery ( "DELETE FROM {{table}} WHERE `message_owner` = '" . $UserID . "';", 'messages' );
	doquery ( "DELETE FROM {{table}} WHERE `owner` = '" . $UserID . "';", 'notes' );
	doquery ( "DELETE FROM {{table}} WHERE `fleet_owner` = '" . $UserID . "';", 'fleets' );
	doquery ( "DELETE FROM {{table}} WHERE `id_owner1` = '" . $UserID . "';", 'rw' );
	doquery ( "DELETE FROM {{table}} WHERE `id_owner2` = '" . $UserID . "';", 'rw' );
	doquery ( "DELETE FROM {{table}} WHERE `sender` = '" . $UserID . "';", 'buddy' );
	doquery ( "DELETE FROM {{table}} WHERE `owner` = '" . $UserID . "';", 'buddy' );
	doquery ( "DELETE FROM {{table}} WHERE `user` = '" . $UserID . "';", 'annonce' );
	doquery ( "DELETE FROM {{table}} WHERE `id` = '" . $UserID . "';", 'users' );

}

?>