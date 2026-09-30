<?php

/**
 * marchand.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.2
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

// Quantite maximale d'un echange (1 million de milliards)
define('MARCHAND_MAX_AMOUNT', 1000000000000000);

// Quantite demandee : nombre entier, borne (les chaines recues passaient telles quelles dans les calculs). Une
// quantite negative reste une tentative de triche, comme dans l'original.
function MarchandAmount ( $Value, &$CheatTry ) {
	$Value = (is_array($Value) || $Value === null) ? 0 : floor(floatval($Value));
	if ($Value < 0) {
		$Value    *= -1;
		$CheatTry  = true;
	}
	return min($Value, MARCHAND_MAX_AMOUNT);
}

// Ressources de la planete relues apres l'echange (la barre du haut les enregistre ensuite)
function MarchandReloadPlanet ( &$CurrentPlanet ) {
	// Compteurs des flottes (*_fleets) relus avec les ressources : PlanetResourceUpdate ajoute leur difference aux
	// ressources ; relus a part, une livraison traitee entre-temps aurait ete comptee deux fois
	$Fields = array('metal', 'crystal', 'deuterium', 'last_update');
	foreach (array('metal_fleets', 'crystal_fleets', 'deuterium_fleets') as $Field) {
		if (isset($CurrentPlanet[$Field])) {
			$Fields[] = $Field;
		}
	}
	$Fresh = doquery("SELECT `". implode("`, `", $Fields) ."` FROM {{table}} WHERE `id` = '". intval($CurrentPlanet['id']) ."';", 'planets', true);
	if ($Fresh) {
		foreach ($Fields as $Field) {
			$CurrentPlanet[$Field] = $Fresh[$Field];
		}
	}
}

function ModuleMarchand ( $CurrentUser, &$CurrentPlanet ) {
	global $lang, $_POST, $game_config;

	includeLang('marchand');

	// Page desactivee par l'administrateur : le menu cachait le lien, l'adresse directe restait ouverte
	if ($game_config['enable_marchand'] != 1) {
		message($lang['sys_page_disabled'], $lang['mod_ma_title']);
	}

	$parse   = $lang;

	// Ressource vendue : metal, cristal ou deuterium seulement (une autre valeur creditait tout gratuitement)
	$Sold    = array('metal' => 'metal', 'cristal' => 'crystal', 'deuterium' => 'deuterium');
	$Ress    = $_POST['ress'] ?? '';
	if (is_string($Ress) && isset($Sold[$Ress])) {
		$PageTPL   = gettemplate('message_body');
		$Error     = false;
		$CheatTry  = false;
		$Metal     = MarchandAmount($_POST['metal'] ?? null, $CheatTry);
		$Crystal   = MarchandAmount($_POST['cristal'] ?? null, $CheatTry);
		$Deuterium = MarchandAmount($_POST['deut'] ?? null, $CheatTry);
		if ($CheatTry  == false) {
			// La ressource vendue n'est pas achetee en meme temps (elle etait creditee en plus)
			switch ($Ress) {
				case 'metal':
					$Metal        = 0;
					$Necessaire   = (( $Crystal * 2) + ( $Deuterium * 4));
					break;

				case 'cristal':
					$Crystal      = 0;
					$Necessaire   = (( $Metal * 0.5) + ( $Deuterium * 2));
					break;

				case 'deuterium':
					$Deuterium    = 0;
					$Necessaire   = (( $Metal * 0.25) + ( $Crystal * 0.5));
					break;
			}
			$Column = $Sold[$Ress];
			if ($Metal + $Crystal + $Deuterium == 0) {
				// Rien a acheter : meme reponse que l'original, sans requete
				$Error = !($CurrentPlanet[$Column] > 0);
			} else {
				// Paiement et livraison en une seule requete, seulement si le stock suffit encore : des requetes
				// simultanees ne paient plus deux fois avec le meme stock
				$Delta           = array('metal' => $Metal, 'crystal' => $Crystal, 'deuterium' => $Deuterium);
				$Delta[$Column]  = -$Necessaire;
				$QryUpdatePlanet  = "UPDATE {{table}} SET ";
				$QryUpdatePlanet .= "`metal` = `metal` + ".         sprintf('%.2F', $Delta['metal'])     .", ";
				$QryUpdatePlanet .= "`crystal` = `crystal` + ".     sprintf('%.2F', $Delta['crystal'])   .", ";
				$QryUpdatePlanet .= "`deuterium` = `deuterium` + ". sprintf('%.2F', $Delta['deuterium']) ." ";
				$QryUpdatePlanet .= "WHERE ";
				$QryUpdatePlanet .= "`id` = '". intval($CurrentPlanet['id']) ."' AND `". $Column ."` > ". sprintf('%.2F', $Necessaire) .";";
				doquery ( $QryUpdatePlanet , 'planets');
				$Error = (mysqli_affected_rows(DbConnect()) < 1);
				MarchandReloadPlanet($CurrentPlanet);
			}
			if ($Error) {
				$Label   = array('metal' => 'metal_label', 'cristal' => 'crystal_label', 'deuterium' => 'deuterium_label');
				$Message = $lang['mod_ma_noten'] ." ". $lang[$Label[$Ress]] ."! ";
			}
		}
		if ($Error == false) {
			if ($CheatTry == true) {
				doquery ( "UPDATE {{table}} SET `metal` = '0', `crystal` = '0', `deuterium` = '0' WHERE `id` = '". intval($CurrentPlanet['id']) ."';", 'planets');
				$CurrentPlanet['metal']      = 0;
				$CurrentPlanet['crystal']    = 0;
				$CurrentPlanet['deuterium']  = 0;
			}
			$Message = $lang['mod_ma_done'];
		}
		if ($Error == true) {
			$parse['title'] = $lang['mod_ma_error'];
		} else {
			$parse['title'] = $lang['mod_ma_donet'];
		}
		$parse['mes']   = $Message;
	} else {
		if (($_POST['action'] ?? null) != 2) {
			$PageTPL = gettemplate('marchand_main');
		} else {
			$parse['mod_ma_res']   = "1";
			switch (($_POST['choix'] ?? null)) {
				case 'metal':
					$PageTPL = gettemplate('marchand_metal');
					$parse['mod_ma_res_a'] = "2";
					$parse['mod_ma_res_b'] = "4";
					break;
				case 'cristal':
					$PageTPL = gettemplate('marchand_cristal');
					$parse['mod_ma_res_a'] = "0.5";
					$parse['mod_ma_res_b'] = "2";
					break;
				case 'deut':
					$PageTPL = gettemplate('marchand_deuterium');
					$parse['mod_ma_res_a'] = "0.25";
					$parse['mod_ma_res_b'] = "0.5";
					break;
				default:
					// Choix inconnu : retour au menu (page vide et avertissement PHP avant)
					$PageTPL = gettemplate('marchand_main');
			}
		}
	}

	$Page    = parsetemplate ( $PageTPL, $parse );
	return  $Page;
}

	$Page = ModuleMarchand ( $user, $planetrow );
	display ( $Page, $lang['mod_marchand'], true, '', false );

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Version originelle (Tom1991)
// 1.1 - Version 2.0 de Tom1991 ajout java
// 1.2 - Réécriture Chlorel passage aux template, optimisation des appels et des requetes SQL
?>