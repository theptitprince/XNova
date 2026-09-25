<?php

/**
 * galaxy.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.3
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

// Coordonnees recues converties en entiers (securite + PHP 8)
SanitizeNumericInput ( array('galaxy', 'system', 'planet', 'planettype', 'current') );

	includeLang('galaxy');

	$CurrentPlanet = doquery("SELECT * FROM {{table}} WHERE `id` = '". $user['current_planet'] ."';", 'planets', true);
	$lunarow       = doquery("SELECT * FROM {{table}} WHERE `id` = '". $user['current_luna'] ."';", 'lunas', true);
	$galaxyrow     = doquery("SELECT * FROM {{table}} WHERE `id_planet` = '". $CurrentPlanet['id'] ."';", 'galaxy', true);

	$dpath         = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
	$fleetmax      = $user['computer_tech'] + 1;
	$CurrentPlID   = $CurrentPlanet['id'];
	$CurrentMIP    = $CurrentPlanet['interplanetary_misil'];
	$CurrentRC     = $CurrentPlanet['recycler'];
	$CurrentSP     = $CurrentPlanet['spy_sonde'];
	$HavePhalanx   = $CurrentPlanet['phalanx'];
	$CurrentSystem = $CurrentPlanet['system'];
	$CurrentGalaxy = $CurrentPlanet['galaxy'];
	$CanDestroy    = $CurrentPlanet[$resource[213]] + $CurrentPlanet[$resource[214]];

	$maxfleet       = doquery("SELECT * FROM {{table}} WHERE `fleet_owner` = '". $user['id'] ."';", 'fleets');
	$maxfleet_count = mysqli_num_rows($maxfleet);

	CheckPlanetUsedFields($CurrentPlanet);
	// (CheckPlanetUsedFields($lunarow) retire : $lunarow vient de la table des lunes, sans batiments ni cases)

	// Imperatif, dans quel mode suis-je (pour savoir dans quel etat j'ere)
	if (!isset($mode)) {
		if (isset($_GET['mode'])) {
			$mode          = intval(($_GET['mode'] ?? null));
		} else {
			// ca ca sent l'appel sans parametres a plein nez
			$mode          = 0;
		}
	}

	if ($mode == 0) {
		// On vient du menu
		// Y a pas de parametres de passé
		// On met ce qu'il faut pour commencer là ou l'on se trouve

		$galaxy        = $CurrentPlanet['galaxy'];
		$system        = $CurrentPlanet['system'];
		$planet        = $CurrentPlanet['planet'];
	} elseif ($mode == 1) {
		// On vient du selecteur de galaxie
		// Il nous poste :
		// $_POST['galaxy']      => Galaxie affichée dans la case a saisir
		// $_POST['galaxyLeft']  => <- A ete cliqué
		// $_POST['galaxyRight'] => -> A ete cliqué
		// $_POST['system']      => Systeme affiché dans la case a saisir
		// $_POST['systemLeft']  => <- A ete cliqué
		// $_POST['systemRight'] => -> A ete cliqué

		if (($_POST["galaxyLeft"] ?? null)) {
			if (($_POST["galaxy"] ?? null) < 1) {
				$_POST["galaxy"] = 1;
				$galaxy          = 1;
			} elseif (($_POST["galaxy"] ?? null) == 1) {
				$_POST["galaxy"] = 1;
				$galaxy          = 1;
			} else {
				$galaxy = ($_POST["galaxy"] ?? null) - 1;
			}
		} elseif (($_POST["galaxyRight"] ?? null)) {
			if (($_POST["galaxy"] ?? null)      > MAX_GALAXY_IN_WORLD OR
				($_POST["galaxyRight"] ?? null) > MAX_GALAXY_IN_WORLD) {
				$_POST["galaxy"]      = MAX_GALAXY_IN_WORLD;
				$_POST["galaxyRight"] = MAX_GALAXY_IN_WORLD;
				$galaxy               = MAX_GALAXY_IN_WORLD;
			} elseif (($_POST["galaxy"] ?? null) == MAX_GALAXY_IN_WORLD) {
				$_POST["galaxy"]      = MAX_GALAXY_IN_WORLD;
				$galaxy               = MAX_GALAXY_IN_WORLD;
			} else {
				$galaxy = ($_POST["galaxy"] ?? null) + 1;
			}
		} else {
			$galaxy = ($_POST["galaxy"] ?? null);
		}

		if (($_POST["systemLeft"] ?? null)) {
			if (($_POST["system"] ?? null) < 1) {
				$_POST["system"] = 1;
				$system          = 1;
			} elseif (($_POST["system"] ?? null) == 1) {
				$_POST["system"] = 1;
				$system          = 1;
			} else {
				$system = ($_POST["system"] ?? null) - 1;
			}
		} elseif (($_POST["systemRight"] ?? null)) {
			if (($_POST["system"] ?? null)      > MAX_SYSTEM_IN_GALAXY OR
				($_POST["systemRight"] ?? null) > MAX_SYSTEM_IN_GALAXY) {
				$_POST["system"]      = MAX_SYSTEM_IN_GALAXY;
				$system               = MAX_SYSTEM_IN_GALAXY;
			} elseif (($_POST["system"] ?? null) == MAX_SYSTEM_IN_GALAXY) {
				$_POST["system"]      = MAX_SYSTEM_IN_GALAXY;
				$system               = MAX_SYSTEM_IN_GALAXY;
			} else {
				$system = ($_POST["system"] ?? null) + 1;
			}
		} else {
			$system = ($_POST["system"] ?? null);
		}
	} elseif ($mode == 2) {
		// Mais c'est qu'il mordrait !
		// A t'on idée de vouloir lancer des MIP sur ce pauvre bonhomme !!

		$galaxy        = ($_GET['galaxy'] ?? null);
		$system        = ($_GET['system'] ?? null);
		$planet        = ($_GET['planet'] ?? null);
	} elseif ($mode == 3) {
		// Appel depuis un menu avec uniquement galaxy et system de passé !
		$galaxy        = ($_GET['galaxy'] ?? null);
		$system        = ($_GET['system'] ?? null);
	} else {
		// Si j'arrive ici ...
		// C'est qu'il y a vraiment eu un bug
		$galaxy        = 1;
		$system        = 1;
	}

	$planetcount = 0;
	$lunacount   = 0;

	$page  = InsertGalaxyScripts ( $CurrentPlanet );

	$page .= "<body style=\"overflow: hidden;\" onUnload=\"\"><br><br>";
	$page .= ShowGalaxySelector ( $galaxy, $system );

	if ($mode == 2) {
		$CurrentPlanetID = ($_GET['current'] ?? null);
		$page .= ShowGalaxyMISelector ( $galaxy, $system, $planet, $CurrentPlanetID, $CurrentMIP );
	}

	$page .= "<table width=569><tbody>";

	$page .= ShowGalaxyTitles ( $galaxy, $system );
    $page .= ShowGalaxyRows   ( $galaxy, $system );
    $page .= ShowGalaxyFooter ( $galaxy, $system,  $CurrentMIP, $CurrentRC, $CurrentSP);

	$page .= "</tbody></table></div>";

	display ($page, $lang['galaxy_label'] ?? '', false, '', false);

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Created by Perberos
// 1.1 - Modified by -MoF- (UGamela germany)
// 1.2 - 1er Nettoyage Chlorel ...
// 1.3 - 2eme Nettoyage Chlorel ... Mise en fonction et debuging complet
?>