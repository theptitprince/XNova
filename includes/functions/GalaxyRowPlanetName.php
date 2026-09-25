<?php

/**
 * GalaxyRowPlanetName.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function GalaxyRowPlanetName ( $GalaxyRow, $GalaxyRowPlanet, $GalaxyRowUser, $Galaxy, $System, $Planet, $PlanetType ) {
	global $lang, $user, $HavePhalanx, $CurrentSystem, $CurrentGalaxy;

	// Planete (Nom)
	$Result  = "<th style=\"white-space: nowrap;\" width=130>";
	if (!$GalaxyRow || !$GalaxyRowPlanet) {
		return $Result . "</th>";
	}
	// Planete detruite ou proprietaire supprime : pas de joueur (memes comparaisons qu'avec une valeur vide)
	if (!is_array($GalaxyRowUser)) {
		$GalaxyRowUser = array('id' => null, 'ally_id' => null);
	}

	// Vert : membre de mon alliance (« sans alliance » vaut 0 : tous les joueurs sans alliance sortaient en vert)
	if ($GalaxyRowUser['ally_id'] == $user['ally_id'] AND
		$GalaxyRowUser['id']      != $user['id']      AND
		!empty($user['ally_id'])) {
		$TextColor = "<font color=\"green\">";
		$EndColor  = "</font>";
	} elseif ($GalaxyRowUser['id'] == $user['id']) {
		$TextColor = "<font color=\"red\">";
		$EndColor  = "</font>";
	} else {
		$TextColor = '';
		$EndColor  = "";
	}

	if ($GalaxyRowPlanet['last_update'] > (time()-59 * 60) AND
		$GalaxyRowUser['id'] != $user['id']) {
		$Inactivity = pretty_time_hour(time() - $GalaxyRowPlanet['last_update']);
	}
	if ($GalaxyRow && $GalaxyRowPlanet["destruyed"] == 0) {
		if ($HavePhalanx <> 0) {
			if ($GalaxyRowPlanet["galaxy"] == $CurrentGalaxy) {
				$Range = GetPhalanxRange ( $HavePhalanx );
				if ($CurrentGalaxy + $Range <= $CurrentSystem AND
					$CurrentSystem >= $CurrentGalaxy - $Range) {
					$PhalanxTypeLink = "<a href=# onclick=fenster('phalanx.php?galaxy=".$Galaxy."&amp;system=".$System."&amp;planet=".$Planet."&amp;planettype=".$PlanetType."')  title=\"".$lang['gl_phalanx']."\">".$GalaxyRowPlanet['name']."</a><br />";
				} else {
					$PhalanxTypeLink = stripslashes($GalaxyRowPlanet['name']);
				}
			} else {
				$PhalanxTypeLink = stripslashes($GalaxyRowPlanet['name']);
			}
		} else {
			$PhalanxTypeLink = stripslashes($GalaxyRowPlanet['name']);
		}

		$Result .= $TextColor . $PhalanxTypeLink . $EndColor;

		if ($GalaxyRowPlanet['last_update']  > (time()-59 * 60) AND
			$GalaxyRowUser['id']            != $user['id']) {
			if ($GalaxyRowPlanet['last_update']  > (time()-10 * 60) AND
				$GalaxyRowUser['id']            != $user['id']) {
				$Result .= "(*)";
			} else {
				$Result .= " (".$Inactivity.")";
			}
		}
	} elseif ($GalaxyRowPlanet["destruyed"] != 0) {
		$Result .= $lang['gl_destroyedplanet'];
	}

	$Result .= "</th>";

	return $Result;
}

?>