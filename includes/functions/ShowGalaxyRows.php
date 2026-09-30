<?php

/**
 * ShowGalaxyRows.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function ShowGalaxyRows ($Galaxy, $System) {
	global $lang, $planetcount, $CurrentRC, $dpath, $user;

	// Lignes de galaxie du systeme lues en une seule fois (0.9k ; avant : une requete par position). Relues position
	// par position comme avant quand une position a plusieurs lignes (la premiere depend alors de l'ordre de lecture
	// de MySQL) et apres CheckAbandonPlanetState, qui modifie la table galaxy
	$SystemRows  = array();
	$SystemQuery = doquery("SELECT * FROM {{table}} WHERE `galaxy` = '".$Galaxy."' AND `system` = '".$System."';", 'galaxy');
	while ($SystemRow = mysqli_fetch_array($SystemQuery)) {
		$SystemRows[intval($SystemRow['planet'])][] = $SystemRow;
	}
	$SystemRowsValid = true;

	$Result = "";
	for ($Planet = 1; $Planet < 16; $Planet++) {
		$GalaxyRowPlanet = null;
		$GalaxyRowMoon   = null;
		$GalaxyRowPlayer = null;
		$GalaxyRowAlly   = null;

		if ($SystemRowsValid && count($SystemRows[$Planet] ?? array()) <= 1) {
			$GalaxyRow = $SystemRows[$Planet][0] ?? null;
		} else {
			$GalaxyRow = doquery("SELECT * FROM {{table}} WHERE `galaxy` = '".$Galaxy."' AND `system` = '".$System."' AND `planet` = '".$Planet."';", 'galaxy', true);
		}

		$Result .= "\n";
		$Result .= "<tr>"; // Depart de ligne
		if ($GalaxyRow) {
			// Il existe des choses sur cette ligne de planete
			if ($GalaxyRow["id_planet"] != 0) {
				$GalaxyRowPlanet = doquery("SELECT * FROM {{table}} WHERE `id` = '". $GalaxyRow["id_planet"] ."';", 'planets', true);

				$PlanetChecked = false;
				if ($GalaxyRowPlanet['destruyed'] != 0 AND
					$GalaxyRowPlanet['id_owner'] != '' AND
					$GalaxyRow["id_planet"] != '') {
					CheckAbandonPlanetState ($GalaxyRowPlanet);
					$PlanetChecked   = true;
					$SystemRowsValid = false;
				} else {
					$planetcount++;
					$GalaxyRowPlayer = doquery("SELECT * FROM {{table}} WHERE `id` = '". $GalaxyRowPlanet["id_owner"] ."';", 'users', true);
				}

				if ($GalaxyRow["id_luna"] != 0) {
					$GalaxyRowMoon   = doquery("SELECT * FROM {{table}} WHERE `id` = '". $GalaxyRow["id_luna"] ."';", 'lunas', true);
					if ($GalaxyRowMoon["destruyed"] != 0) {
						CheckAbandonMoonState ($GalaxyRowMoon);
					}
				}
				// Planete relue seulement si CheckAbandonPlanetState a pu la supprimer : sinon, c'est la meme ligne
				// (le joueur, relu ensuite dans $GalaxyRowUser, ne servait a rien)
				if ($PlanetChecked) {
					$GalaxyRowPlanet = doquery("SELECT * FROM {{table}} WHERE `id` = '". $GalaxyRow["id_planet"] ."';", 'planets', true);
				}
			}
		}
		$Result .= "\n";
		$Result .= GalaxyRowPos        ( $Planet, $GalaxyRow );
		$Result .= "\n";
		$Result .= GalaxyRowPlanet     ( $GalaxyRow, $GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, 1 );
		$Result .= "\n";
		$Result .= GalaxyRowPlanetName ( $GalaxyRow, $GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, 1 );
		$Result .= "\n";
		$Result .= GalaxyRowMoon       ( $GalaxyRow, $GalaxyRowMoon  , $GalaxyRowPlayer, $Galaxy, $System, $Planet, 3 );
		$Result .= "\n";
		$Result .= GalaxyRowDebris     ( $GalaxyRow, $GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, 2 );
		$Result .= "\n";
		$Result .= GalaxyRowUser       ( $GalaxyRow, $GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, 0 );
		$Result .= "\n";
		$Result .= GalaxyRowAlly       ( $GalaxyRow, $GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, 0 );
		$Result .= "\n";
		$Result .= GalaxyRowActions    ( $GalaxyRow, $GalaxyRowPlanet, $GalaxyRowPlayer, $Galaxy, $System, $Planet, 0 );
		$Result .= "\n";
		$Result .= "</tr>";
	}

	return $Result;
}

// Amis et membres de son alliance (lien « Stationner » de la planete et de la lune) : resultat garde pour toute la
// page (0.9k), la meme paire de joueurs etait verifiee pour la planete, pour la lune et pour chaque position
function GalaxyIsBuddyOrAllyMember ( $UserId, $OtherId ) {
	static $Pairs = array();

	$Key = intval($UserId) .':'. intval($OtherId);
	if (!isset($Pairs[$Key])) {
		$Pairs[$Key] = IsBuddyOrAllyMember($UserId, $OtherId);
	}
	return $Pairs[$Key];
}

?>