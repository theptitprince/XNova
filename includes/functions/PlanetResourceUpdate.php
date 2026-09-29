<?php

/**
 * PlanetResourceUpdate.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.1
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function PlanetResourceUpdate ( $CurrentUser, &$CurrentPlanet, $UpdateTime, $Simul = false ) {
	global $ProdGrid, $resource, $reslist, $game_config, $FleetHandlerLocked;
	// Unites ecrites par cette fonction pendant la page, par planete (voir l'enregistrement plus bas)
	static $WrittenUnits = array();

	// Planete telle qu'elle arrive (avant production et chantier)
	$PlanetBefore = $CurrentPlanet;

	// Mise a jour de l'espace de stockage
	$CurrentPlanet['metal_max']     = (floor (BASE_STORAGE_SIZE * pow (1.5, $CurrentPlanet[ $resource[22] ] ))) * (1 + ($CurrentUser['rpg_stockeur'] * 0.5));
	$CurrentPlanet['crystal_max']   = (floor (BASE_STORAGE_SIZE * pow (1.5, $CurrentPlanet[ $resource[23] ] ))) * (1 + ($CurrentUser['rpg_stockeur'] * 0.5));
	$CurrentPlanet['deuterium_max'] = (floor (BASE_STORAGE_SIZE * pow (1.5, $CurrentPlanet[ $resource[24] ] ))) * (1 + ($CurrentUser['rpg_stockeur'] * 0.5));

	// Calcul de l'espace de stockage (avec les debordements possibles)
	$MaxMetalStorage                = $CurrentPlanet['metal_max']     * MAX_OVERFLOW;
	$MaxCristalStorage              = $CurrentPlanet['crystal_max']   * MAX_OVERFLOW;
	$MaxDeuteriumStorage            = $CurrentPlanet['deuterium_max'] * MAX_OVERFLOW;

	// Calcul de production linéaire des divers types
	$Caps             = array('metal_perhour' => 0, 'crystal_perhour' => 0, 'deuterium_perhour' => 0, 'energy_used' => 0, 'energy_max' => 0);
	$BuildTemp        = $CurrentPlanet[ 'temp_max' ];

	for ( $ProdID = 0; $ProdID < 300; $ProdID++ ) {
		if ( in_array( $ProdID, $reslist['prod']) ) {
			$BuildLevelFactor = $CurrentPlanet[ $resource[$ProdID]."_porcent" ];
			$BuildLevel       = $CurrentPlanet[ $resource[$ProdID] ];
			$Caps['metal_perhour']     +=  floor( eval  ( $ProdGrid[$ProdID]['formule']['metal']     ) * ( $game_config['resource_multiplier'] ) * ( 1 + ( $CurrentUser['rpg_geologue']  * 0.05 ) ) );
			$Caps['crystal_perhour']   +=  floor( eval  ( $ProdGrid[$ProdID]['formule']['crystal']   ) * ( $game_config['resource_multiplier'] ) * ( 1 + ( $CurrentUser['rpg_geologue']  * 0.05 ) ) );
			$Caps['deuterium_perhour'] +=  floor( eval  ( $ProdGrid[$ProdID]['formule']['deuterium'] ) * ( $game_config['resource_multiplier'] ) * ( 1 + ( $CurrentUser['rpg_geologue']  * 0.05 ) ) );
			if ($ProdID < 4) {
				$Caps['energy_used']   +=  floor( eval  ( $ProdGrid[$ProdID]['formule']['energy']    ) * ( $game_config['resource_multiplier'] ) * ( 1 + ( $CurrentUser['rpg_ingenieur'] * 0.05 ) ) );
			} elseif ($ProdID >= 4 ) {
				$Caps['energy_max']    +=  floor( eval  ( $ProdGrid[$ProdID]['formule']['energy']    ) * ( $game_config['resource_multiplier'] ) * ( 1 + ( $CurrentUser['rpg_ingenieur'] * 0.05 ) ) );
			}
		}
	}

	// Il n'y a pas de production de base sur une lune (ni de production tout court d'ailleurs)
	// Revenu de base en variable locale : l'original le mettait a zero dans $game_config, et les planetes mises a
	// jour ensuite dans la meme page (vue generale, empire) perdaient aussi leur production de base
	$BasicIncome = array('metal'     => $game_config['metal_basic_income'],
	                     'crystal'   => $game_config['crystal_basic_income'],
	                     'deuterium' => $game_config['deuterium_basic_income']);
	if ($CurrentPlanet['planet_type'] == 3) {
		$BasicIncome                           = array('metal' => 0, 'crystal' => 0, 'deuterium' => 0);
		$CurrentPlanet['metal_perhour']        = 0;
		$CurrentPlanet['crystal_perhour']      = 0;
		$CurrentPlanet['deuterium_perhour']    = 0;
		$CurrentPlanet['energy_used']          = 0;
		$CurrentPlanet['energy_max']           = 0;
	} else {
		$CurrentPlanet['metal_perhour']        = $Caps['metal_perhour'];
		$CurrentPlanet['crystal_perhour']      = $Caps['crystal_perhour'];
		$CurrentPlanet['deuterium_perhour']    = $Caps['deuterium_perhour'];
		$CurrentPlanet['energy_used']          = $Caps['energy_used'];
		$CurrentPlanet['energy_max']           = $Caps['energy_max'];
	}

	// Depuis quand n'avons nous pas les infos ressources a jours ?
	$ProductionTime               = ($UpdateTime - $CurrentPlanet['last_update']);
	$CurrentPlanet['last_update'] = $UpdateTime;

	// XNova Renaissance : production des mines au prorata de l'energie disponible.
	// energy_used est negatif (consommation des mines), energy_max positif (production des centrales).
	// Le code d'origine comparait production et consommation sans tenir compte du signe : les mines
	// tournaient a 100 % des qu'une centrale existait, quel que soit le deficit, et sans aucune centrale
	// la production naturelle etait comptee deux fois. Elle est desormais ajoutee une seule fois (plus bas).
	$EnergyNeeded = abs($CurrentPlanet['energy_used']);
	if ($EnergyNeeded == 0 || $CurrentPlanet['energy_max'] >= $EnergyNeeded) {
		// Pas de consommation, ou assez d'energie : toutes les mines tournent a plein rendement
		$production_level            = 100;
	} else {
		// Il manque de l'energie (ou il n'y en a pas du tout) : production au prorata
		$production_level            = floor(($CurrentPlanet['energy_max'] / $EnergyNeeded) * 100);
	}
	// Mise a l'echele des valeurs
	if       ($production_level > 100) {
		$production_level = 100;
	} elseif ($production_level < 0) {
		$production_level = 0;
	}

	// Production par seconde : les *_perhour contiennent deja le multiplicateur de ressources. L'original le
	// reappliquait ici (gain reel = multiplicateur x la production affichee par la page Ressources ; sans effet
	// avec le reglage par defaut, 1). Gardee sur la planete pour le compteur en direct de la barre des ressources.
	foreach (array('metal', 'crystal', 'deuterium') as $Res) {
		$CurrentPlanet[$Res .'_persecond'] = (($CurrentPlanet[$Res .'_perhour'] / 3600) * (0.01 * $production_level))
		                                   + (($BasicIncome[$Res] / 3600) * $game_config['resource_multiplier']);
	}

	if ( $CurrentPlanet['metal'] <= $MaxMetalStorage ) {
		$MetalProduction = ($ProductionTime * ($CurrentPlanet['metal_perhour'] / 3600)) * (0.01 * $production_level);
		$MetalBaseProduc = (($ProductionTime * ($BasicIncome['metal'] / 3600 )) * $game_config['resource_multiplier']);
		$MetalTheorical  = $CurrentPlanet['metal'] + $MetalProduction  +  $MetalBaseProduc;
		if ( $MetalTheorical <= $MaxMetalStorage ) {
			$CurrentPlanet['metal']  = $MetalTheorical;
		} else {
			$CurrentPlanet['metal']  = $MaxMetalStorage;
		}
	}

	if ( $CurrentPlanet['crystal'] <= $MaxCristalStorage ) {
		$CristalProduction = ($ProductionTime * ($CurrentPlanet['crystal_perhour'] / 3600)) * (0.01 * $production_level);
		$CristalBaseProduc = (($ProductionTime * ($BasicIncome['crystal'] / 3600 )) * $game_config['resource_multiplier']);
		$CristalTheorical  = $CurrentPlanet['crystal'] + $CristalProduction  +  $CristalBaseProduc;
		if ( $CristalTheorical <= $MaxCristalStorage ) {
			$CurrentPlanet['crystal']  = $CristalTheorical;
		} else {
			$CurrentPlanet['crystal']  = $MaxCristalStorage;
		}
	}

	if ( $CurrentPlanet['deuterium'] <= $MaxDeuteriumStorage ) {
		$DeuteriumProduction = ($ProductionTime * ($CurrentPlanet['deuterium_perhour'] / 3600)) * (0.01 * $production_level);
		$DeuteriumBaseProduc = (($ProductionTime * ($BasicIncome['deuterium'] / 3600 )) * $game_config['resource_multiplier']);
		$DeuteriumTheorical  = $CurrentPlanet['deuterium'] + $DeuteriumProduction  +  $DeuteriumBaseProduc;
		if ( $DeuteriumTheorical <= $MaxDeuteriumStorage ) {
			$CurrentPlanet['deuterium']  = $DeuteriumTheorical;
		} else {
			$CurrentPlanet['deuterium']  = $MaxDeuteriumStorage;
		}
	}

	if ($Simul == false) {
		// Gestion de l'eventuelle queue de fabrication d'elements
		$Builded          = HandleElementBuildingQueue ( $CurrentUser, $CurrentPlanet, $ProductionTime );

		// XNova Renaissance (0.9k) : ressources et unites sont ecrites en valeurs absolues, calculees sur la planete lue
		// en debut de page. Une flotte traitee entre-temps par une autre page (livraison, pillage, retour, pertes d'un
		// combat, missiles) etait effacee par cette ecriture. Sous verrou, on relit la planete et on ajoute ces
		// changements aux valeurs ecrites ; sans changement, la meme ecriture qu'avant.
		// Pas de LOCK TABLE pendant le traitement des flottes : il libererait tout leur verrou (tables deja verrouillees)
		if (empty($FleetHandlerLocked)) {
			doquery("LOCK TABLE {{table}} WRITE", 'planets');
		}
		$FreshColumns = array();
		foreach (array('metal', 'crystal', 'deuterium') as $Res) {
			if (isset($CurrentPlanet[$Res .'_fleets'])) {
				$FreshColumns[] = "`". $Res ."_fleets`";
			}
		}
		if ( $Builded != '' ) {
			foreach ( $Builded as $Element => $Count ) {
				if ($Element <> '') {
					$FreshColumns[] = "`". $resource[$Element] ."`";
				}
			}
		}
		$FreshPlanet = (count($FreshColumns) > 0 && !empty($CurrentPlanet['id'])) ? doquery("SELECT ". implode(", ", $FreshColumns) ." FROM {{table}} WHERE `id` = '". intval($CurrentPlanet['id']) ."';", 'planets', true) : null;
		if ($FreshPlanet) {
			// Ressources : les flottes tiennent le compte de ce qu'elles apportent ou prennent (colonnes *_fleets) ;
			// la difference avec la valeur lue avec la planete est ce qui a change depuis
			foreach (array('metal', 'crystal', 'deuterium') as $Res) {
				if (isset($FreshPlanet[$Res .'_fleets'])) {
					$Moved = $FreshPlanet[$Res .'_fleets'] - $CurrentPlanet[$Res .'_fleets'];
					if ($Moved != 0) {
						$CurrentPlanet[$Res] = max(0, $CurrentPlanet[$Res] + $Moved);
					}
					$CurrentPlanet[$Res .'_fleets'] = $FreshPlanet[$Res .'_fleets'];
				}
			}
			// Unites terminees : difference avec la valeur lue (ou deja ecrite ici pendant la page : une copie plus
			// ancienne de la meme planete, comme celle de la vue generale, reecrit le meme total)
			if ( $Builded != '' ) {
				foreach ( $Builded as $Element => $Count ) {
					if ($Element <> '') {
						$Column = $resource[$Element];
						$Known  = $WrittenUnits[$CurrentPlanet['id']][$Column] ?? $PlanetBefore[$Column];
						$Moved  = $FreshPlanet[$Column] - $Known;
						if ($Moved != 0) {
							$CurrentPlanet[$Column] = max(0, $CurrentPlanet[$Column] + $Moved);
						}
					}
				}
			}
		}

		// On enregistre la planete !
		$QryUpdatePlanet  = "UPDATE {{table}} SET ";
		$QryUpdatePlanet .= "`metal` = '"            . $CurrentPlanet['metal']             ."', ";
		$QryUpdatePlanet .= "`crystal` = '"          . $CurrentPlanet['crystal']           ."', ";
		$QryUpdatePlanet .= "`deuterium` = '"        . $CurrentPlanet['deuterium']         ."', ";
		$QryUpdatePlanet .= "`last_update` = '"      . $CurrentPlanet['last_update']       ."', ";
		$QryUpdatePlanet .= "`b_hangar_id` = '"      . $CurrentPlanet['b_hangar_id']       ."', ";
		$QryUpdatePlanet .= "`metal_perhour` = '"    . $CurrentPlanet['metal_perhour']     ."', ";
		$QryUpdatePlanet .= "`crystal_perhour` = '"  . $CurrentPlanet['crystal_perhour']   ."', ";
		$QryUpdatePlanet .= "`deuterium_perhour` = '". $CurrentPlanet['deuterium_perhour'] ."', ";
		$QryUpdatePlanet .= "`energy_used` = '"      . $CurrentPlanet['energy_used']       ."', ";
		$QryUpdatePlanet .= "`energy_max` = '"       . $CurrentPlanet['energy_max']        ."', ";
		// Par hasard des elements etaient finis ....
		if ( $Builded != '' ) {
			foreach ( $Builded as $Element => $Count ) {
				if ($Element <> '') {
					$QryUpdatePlanet .= "`". $resource[$Element] ."` = '". $CurrentPlanet[$resource[$Element]] ."', ";
				}
			}
		}
		$QryUpdatePlanet .= "`b_hangar` = '". $CurrentPlanet['b_hangar'] ."' ";
		$QryUpdatePlanet .= "WHERE ";
		$QryUpdatePlanet .= "`id` = '". $CurrentPlanet['id'] ."';";

		doquery($QryUpdatePlanet, 'planets');
		if (empty($FleetHandlerLocked)) {
			doquery("UNLOCK TABLES", '');
		}
		if ( $Builded != '' ) {
			foreach ( $Builded as $Element => $Count ) {
				if ($Element <> '') {
					$WrittenUnits[$CurrentPlanet['id']][$resource[$Element]] = $CurrentPlanet[$resource[$Element]];
				}
			}
		}
	}

}

// Revision History
// - 1.0 Mise en module initiale
// - 1.1 Mise a jour automatique mines / silos / energie ...
?>