<?php

/**
 * BatimentBuildingPage.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.1
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function BatimentBuildingPage (&$CurrentPlanet, $CurrentUser) {
	global $lang, $resource, $reslist, $phpEx, $dpath, $game_config, $_GET;

	CheckPlanetUsedFields ( $CurrentPlanet );

	// Tables des batiments possibles par type de planete
	$Allowed['1'] = array(  1,  2,  3,  4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 44);
	$Allowed['3'] = array( 12, 14, 21, 22, 23, 24, 34, 41, 42, 43);
	// Type de planete inconnu (planete absente) : aucun batiment, au lieu d'une erreur fatale
	$PlanetAllowed = $Allowed[$CurrentPlanet['planet_type'] ?? 0] ?? array();

	// Boucle d'interpretation des eventuelles commandes
	if (isset($_GET['cmd'])) {
		// On passe une commande
		$bThisIsCheated = false;
		$bDoItNow       = false;
		$TheCommand     = ($_GET['cmd'] ?? null);
		$Element        = ($_GET['building'] ?? null);
		$ListID         = ($_GET['listid'] ?? null);
		if       ( isset ( $Element )) {
			if ( is_string ( $Element ) && !strchr ( $Element, " ") ) {
				if ( !strchr ( $Element, ",") ) {
					if (in_array( trim($Element), $PlanetAllowed)) {
						$bDoItNow = true;
						$Element  = intval(trim($Element)); // « 1.0 » ou « 1e0 » passaient la liste et faussaient la file
					} else {
						$bThisIsCheated = true;
					}
				} else {
					$bThisIsCheated = true;
				}
			} else {
				$bThisIsCheated = true;
			}
		} elseif ( isset ( $ListID )) {
			$bDoItNow = true;
		}
		// Lien d'interruption ou de retrait venu d'une autre planete (autre onglet) : ignore, au lieu d'agir sur la file
		// de la planete courante
		if (isset($ListID) && isset($_GET['planet']) && intval($_GET['planet']) != $CurrentPlanet['id']) {
			$bDoItNow = false;
		}
		if ($bDoItNow == true) {
			switch($TheCommand){
				case 'cancel':
					// Interrompre le premier batiment de la queue
					// Remboursement enregistre tout de suite, en plus et sous condition (0.9k) : il passait par
					// l'ecriture des ressources en valeurs absolues de SetNextQueueElementOnTop, qui ne les ecrit plus
					$Before = $CurrentPlanet;
					CancelBuildingFromQueue ( $CurrentPlanet, $CurrentUser );
					$QryUpdatePlanet  = "UPDATE {{table}} SET ";
					$QryUpdatePlanet .= "`metal` = `metal` + '".         floatval($CurrentPlanet['metal'] - $Before['metal'])         ."', ";
					$QryUpdatePlanet .= "`crystal` = `crystal` + '".     floatval($CurrentPlanet['crystal'] - $Before['crystal'])     ."', ";
					$QryUpdatePlanet .= "`deuterium` = `deuterium` + '". floatval($CurrentPlanet['deuterium'] - $Before['deuterium']) ."', ";
					$QryUpdatePlanet .= "`b_building` = '".              floatval($CurrentPlanet['b_building'])                        ."', ";
					$QryUpdatePlanet .= "`b_building_id` = '".           SqlEscape($CurrentPlanet['b_building_id'])                    ."' ";
					$QryUpdatePlanet .= "WHERE ";
					$QryUpdatePlanet .= "`id` = '".                      intval($CurrentPlanet['id'])                                  ."' AND ";
					$QryUpdatePlanet .= "`b_building` = '".              floatval($Before['b_building'])                               ."' AND ";
					$QryUpdatePlanet .= "`b_building_id` = '".           SqlEscape($Before['b_building_id'])                           ."';";
					doquery( $QryUpdatePlanet, 'planets');
					if (mysqli_affected_rows(DbConnect()) != 1) {
						// Deja interrompu par une autre requete (ou file changee) : pas de second remboursement
						BuildingQueueReload ( $CurrentPlanet );
					}
					break;
				case 'remove':
					// Supprimer un element de la queue (mais pas le premier)
					// $RemID -> element de la liste a supprimer
					RemoveBuildingFromQueue ( $CurrentPlanet, $CurrentUser, $ListID );
					break;
				case 'insert':
					// Insere un element dans la queue
					// Memes regles que les liens de la page (0.9k) : technologies requises, case libre (file comprise),
					// laboratoire pendant une recherche. La commande forgee passait outre.
					// Batiment verifie obligatoire : « listid » sans « building » mettait un element vide dans la file
					if (!is_int($Element)) {
						break;
					}
					$QueueLength = (!empty($CurrentPlanet['b_building_id'])) ? count(explode(';', $CurrentPlanet['b_building_id'])) : 0;
					$RoomIsOk    = ($CurrentPlanet['field_current'] < (CalculateMaxPlanetFields($CurrentPlanet) - $QueueLength));
					$LabIsBusy   = ($Element == 31 && $CurrentUser['b_tech_planet'] != 0 && $game_config['BuildLabWhileRun'] != 1);
					if (IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element) && $RoomIsOk && !$LabIsBusy) {
						AddBuildingToQueue ( $CurrentPlanet, $CurrentUser, $Element, true );
					}
					break;
				case 'destroy':
					// Detruit un batiment deja construit sur la planete !
					// Terraformeur et base lunaire : jamais detruits (comme OGame ; la page d'info ne le propose pas)
					// Batiment verifie obligatoire, comme pour « insert »
					if (is_int($Element) && !in_array($Element, array(33, 41))) {
						AddBuildingToQueue ( $CurrentPlanet, $CurrentUser, $Element, false );
					}
					break;
				default:
					break;
			} // switch
		} elseif ($bThisIsCheated == true) {
			// Batiment impossible sur ce type de planete : simple refus (0.9k, decision de theptitprince). L'original
			// effacait et recreait tout le compte (ResetThisFuckingCheater), meme pour un lien perime d'un autre
			// onglet ouvert sur une autre planete.
			message ($lang['bld_not_allowed'], $lang['builds'], "buildings.php", 3);
		}
	}

	SetNextQueueElementOnTop ( $CurrentPlanet, $CurrentUser );

	$Queue = ShowBuildingQueue ( $CurrentPlanet, $CurrentUser );

	// On enregistre ce que l'on a modifié dans planet !
	// Sous condition (0.9k, a la place de BuildingSavePlanetRecord) : l'element en cours dans la base est toujours
	// celui que cette page connait. Une file perimee (une autre requete a interrompu et rembourse l'element) n'est plus
	// reecrite par-dessus, ce qui relancait gratuitement l'element rembourse.
	$QryUpdatePlanet  = "UPDATE {{table}} SET ";
	$QryUpdatePlanet .= "`b_building_id` = '". SqlEscape($CurrentPlanet['b_building_id']) ."', ";
	$QryUpdatePlanet .= "`b_building` = '".    floatval($CurrentPlanet['b_building'])     ."' ";
	$QryUpdatePlanet .= "WHERE ";
	$QryUpdatePlanet .= "`id` = '".            intval($CurrentPlanet['id'])               ."' AND ";
	$QryUpdatePlanet .= "`b_building` = '".    floatval($CurrentPlanet['b_building'])     ."';";
	doquery( $QryUpdatePlanet, 'planets');
	// On enregistre ce que l'on a eventuellement modifié dans users
	BuildingSaveUserRecord ( $CurrentUser );

	if ($Queue['lenght'] < BuildingQueueSize()) {
		$CanBuildElement = true;
	} else {
		$CanBuildElement = false;
	}

	$SubTemplate         = gettemplate('buildings_builds_row');
	$BuildingPage        = "";
	foreach($lang['tech'] as $Element => $ElementName) {
		if (in_array($Element, $PlanetAllowed)) {
			$CurrentMaxFields      = CalculateMaxPlanetFields($CurrentPlanet);
			if ($CurrentPlanet["field_current"] < ($CurrentMaxFields - $Queue['lenght'])) {
				$RoomIsOk = true;
			} else {
				$RoomIsOk = false;
			}

			if (IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element)) {
				$HaveRessources        = IsElementBuyable ($CurrentUser, $CurrentPlanet, $Element, true, false);
				$parse                 = array();
				$parse['dpath']        = $dpath;
				$parse['i']            = $Element;
				$BuildingLevel         = $CurrentPlanet[$resource[$Element]];
				$parse['nivel']        = ($BuildingLevel == 0) ? "" : " (". $lang['level'] ." ". $BuildingLevel .")";
				$parse['n']            = $ElementName;
				$parse['descriptions'] = $lang['res']['descriptions'][$Element];
				$ElementBuildTime      = GetBuildingTime($CurrentUser, $CurrentPlanet, $Element);
				$parse['time']         = ShowBuildTime($ElementBuildTime);
				$parse['price']        = GetElementPrice($CurrentUser, $CurrentPlanet, $Element);
				$parse['rest_price']   = GetRestPrice($CurrentUser, $CurrentPlanet, $Element);
				$parse['click']        = '';
				$NextBuildLevel        = $CurrentPlanet[$resource[$Element]] + 1;

				if ($Element == 31) {
					// Spécial Laboratoire
					if ($CurrentUser["b_tech_planet"] != 0 &&     // Si pas 0 y a une recherche en cours
						$game_config['BuildLabWhileRun'] != 1) {  // Variable qui contient le parametre
						// On verifie si on a le droit d'evoluer pendant les recherches (Setting dans config)
						$parse['click'] = "<font color=#FF0000>". $lang['in_working'] ."</font>";
					}
				}
				if       ($parse['click'] != '') {
					// Bin on ne fait rien, vu que l'on l'a deja fait au dessus !!
				} elseif ($RoomIsOk && $CanBuildElement) {
					if ($Queue['lenght'] == 0) {
						if ($NextBuildLevel == 1) {
							if ( $HaveRessources == true ) {
								$parse['click'] = "<a href=\"?cmd=insert&building=". $Element ."\"><font color=#00FF00>". $lang['build_first_level'] ."</font></a>";
							} else {
								$parse['click'] = "<font color=#FF0000>". $lang['build_first_level'] ."</font>";
							}
						} else {
							if ( $HaveRessources == true ) {
								$parse['click'] = "<a href=\"?cmd=insert&building=". $Element ."\"><font color=#00FF00>". $lang['build_next_level'] ." ". $NextBuildLevel ."</font></a>";
							} else {
								$parse['click'] = "<font color=#FF0000>". $lang['build_next_level'] ." ". $NextBuildLevel ."</font>";
							}
						}
					} else {
						$parse['click'] = "<a href=\"?cmd=insert&building=". $Element ."\"><font color=#00FF00>". $lang['in_build_queue'] ."</font></a>";
					}
				} elseif ($RoomIsOk && !$CanBuildElement) {
					if ($NextBuildLevel == 1) {
						$parse['click'] = "<font color=#FF0000>". $lang['build_first_level'] ."</font>";
					} else {
						$parse['click'] = "<font color=#FF0000>". $lang['build_next_level'] ." ". $NextBuildLevel ."</font>";
					}
				} else {
					$parse['click'] = "<font color=#FF0000>". $lang['no_more_space'] ."</font>";
				}

				$BuildingPage .= parsetemplate($SubTemplate, $parse);
			}
		}
	}

	$parse                         = $lang;

	// Faut il afficher la liste de construction ??
	if ($Queue['lenght'] > 0) {
		$parse['build_list_script']  = InsertBuildListScript ( "buildings" );
		$parse['build_list']        = $Queue['buildlist'];
	} else {
		$parse['build_list_script']  = "";
		$parse['build_list']        = "";
	}

    $parse['planet_field_current'] = $CurrentPlanet["field_current"];
    $parse['planet_field_max']     = $CurrentPlanet['field_max'] + ($CurrentPlanet[$resource[33]] * 5);
    $parse['field_libre']          = $parse['planet_field_max']  - $CurrentPlanet['field_current'];
	// Accord : « Il reste 1 case libre » / « Il reste 5 cases libres »
	if (abs($parse['field_libre']) <= 1) {
		$parse['bld_theyare']  = $lang['bld_theyare_one'];
		$parse['bld_cellfree'] = $lang['bld_cellfree_one'];
	}

	$parse['buildings_list']        = $BuildingPage;

	$page                          = parsetemplate(gettemplate('buildings_builds'), $parse);

	display($page, $lang['builds']);
}

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 Mise en module initiale (creation)
// 1.1 FIX interception cheat +1
// 1.2 FIX interception cheat destruction a -1

?>
