<?php

/**
 * SetNextQueueElementOnTop.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

function SetNextQueueElementOnTop ( &$CurrentPlanet, $CurrentUser ) {
	global $lang, $resource;

	// Garde fou ... Si le temps de construction n'est pas 0 on ne fait rien !!!
	if ($CurrentPlanet['b_building'] == 0) {
		$CurrentQueue  = $CurrentPlanet['b_building_id'];
		if (!empty($CurrentQueue)) {
			$QueueArray = explode ( ";", $CurrentQueue );
			$Loop       = true;
			while ($Loop == true) {
				$ListIDArray         = explode ( ",", $QueueArray[0] );
				$Element             = $ListIDArray[0];
				$Level               = $ListIDArray[1];
				$BuildTime           = $ListIDArray[2];
				$BuildEndTime        = $ListIDArray[3];
				$BuildMode           = $ListIDArray[4]; // pour savoir si on construit ou detruit
				$HaveNoMoreLevel     = false;

				if ($BuildMode == 'destroy') {
					$ForDestroy = true;
				} else {
					$ForDestroy = false;
				}
				$HaveRessources = IsElementBuyable ($CurrentUser, $CurrentPlanet, $Element, true, $ForDestroy);
				if ($ForDestroy) {
					if ($CurrentPlanet[$resource[$Element]] == 0) {
						$HaveRessources  = false;
						$HaveNoMoreLevel = true;
					}
				}
				if ( $HaveRessources == true ) {
					$Needed                        = GetBuildingPrice ($CurrentUser, $CurrentPlanet, $Element, true, $ForDestroy);
					$CurrentTime                   = time();
					$BuildEndTime                  = $BuildEndTime;
					// Arrondi comme la colonne entiere b_building (0.9k) : une demolition dure un temps divise par 2
					// (fin en ,5). La copie en memoire differait de la base et les ecritures conditionnelles qui
					// suivent (interruption, enregistrement de la page) ne trouvaient plus la ligne
					$BuildEndTime                  = round(floatval($BuildEndTime));
					$NewQueue                     = implode ( ";", $QueueArray );
					if ($NewQueue == "") {
						$NewQueue                      = '0';
					}
					// Debit atomique et conditionnel (0.9k) : l'element ne demarre que si aucun autre n'a demarre
					// entre-temps et si les ressources suffisent encore. Avant, les ressources lues au debut de la page
					// etaient reecrites en valeurs absolues : deux requetes simultanees depensaient deux fois le meme stock.
					$QryUpdatePlanet  = "UPDATE {{table}} SET ";
					$QryUpdatePlanet .= "`metal` = `metal` - '".         floatval($Needed['metal'])     ."', ";
					$QryUpdatePlanet .= "`crystal` = `crystal` - '".     floatval($Needed['crystal'])   ."', ";
					$QryUpdatePlanet .= "`deuterium` = `deuterium` - '". floatval($Needed['deuterium']) ."', ";
					$QryUpdatePlanet .= "`b_building` = '".              floatval($BuildEndTime)        ."', ";
					$QryUpdatePlanet .= "`b_building_id` = '".           SqlEscape($NewQueue)           ."' ";
					$QryUpdatePlanet .= "WHERE ";
					$QryUpdatePlanet .= "`id` = '".                      intval($CurrentPlanet['id'])   ."' AND ";
					$QryUpdatePlanet .= "`b_building` = '0' AND ";
					$QryUpdatePlanet .= "`metal` >= '".                  floatval($Needed['metal'])     ."' AND ";
					$QryUpdatePlanet .= "`crystal` >= '".                floatval($Needed['crystal'])   ."' AND ";
					$QryUpdatePlanet .= "`deuterium` >= '".              floatval($Needed['deuterium']) ."';";
					doquery( $QryUpdatePlanet, 'planets');
					if (mysqli_affected_rows(DbConnect()) == 1) {
						$CurrentPlanet['metal']         -= $Needed['metal'];
						$CurrentPlanet['crystal']       -= $Needed['crystal'];
						$CurrentPlanet['deuterium']     -= $Needed['deuterium'];
						$CurrentPlanet['b_building']     = $BuildEndTime;
						$CurrentPlanet['b_building_id']  = $NewQueue;
						return;
					}
					// Refuse : on reprend l'etat de la base. Planete disparue, ou file deja demarree par une autre requete :
					// rien a faire ; sinon les ressources ont ete depensees entre-temps et l'element est refuse (ci-dessous)
					if (!BuildingQueueReload ( $CurrentPlanet ) || $CurrentPlanet['b_building'] != 0) {
						return;
					}
					$HaveRessources = false;
				}
				if ( $HaveRessources == false ) {
					$ElementName = $lang['tech'][$Element];
					if ($HaveNoMoreLevel == true) {
						$Message     = sprintf ($lang['sys_nomore_level'], $ElementName );
					} else {
						$Needed      = GetBuildingPrice ($CurrentUser, $CurrentPlanet, $Element, true, $ForDestroy);
						$Message     = sprintf ($lang['sys_notenough_money'], $ElementName,
												pretty_number ($CurrentPlanet['metal']), $lang['metal_label'],
												pretty_number ($CurrentPlanet['crystal']), $lang['crystal_label'],
												pretty_number ($CurrentPlanet['deuterium']), $lang['deuterium_label'],
												pretty_number ($Needed['metal']), $lang['metal_label'],
												pretty_number ($Needed['crystal']), $lang['crystal_label'],
												pretty_number ($Needed['deuterium']), $lang['deuterium_label']);
					}

					SendSimpleMessage ( $CurrentUser['id'], '', '', 99, $lang['sys_buildlist'], $lang['sys_buildlist_fail'], $Message);

					array_shift( $QueueArray );
					$ActualCount         = count ( $QueueArray );
					if ( $ActualCount == 0 ) {
						$BuildEndTime  = '0';
						$NewQueue      = '0';
						$Loop          = false;
					}
				}
			} // while
		} else {
			$BuildEndTime  = '0';
			$NewQueue      = '0';
		}

		// Ecriture de la mise a jour dans la BDD : file videe, rien n'etait achetable. Les ressources n'ont pas change
		// et ne sont plus reecrites (valeurs absolues) ; rien ne s'ecrit si un element a demarre entre-temps
		$CurrentPlanet['b_building']    = $BuildEndTime;
		$CurrentPlanet['b_building_id'] = $NewQueue;

		$QryUpdatePlanet  = "UPDATE {{table}} SET ";
		$QryUpdatePlanet .= "`b_building` = '".    $CurrentPlanet['b_building']    ."' , ";
		$QryUpdatePlanet .= "`b_building_id` = '". $CurrentPlanet['b_building_id'] ."' ";
		$QryUpdatePlanet .= "WHERE ";
		$QryUpdatePlanet .= "`id` = '" .           $CurrentPlanet['id']            . "' AND `b_building` = '0';";
		doquery( $QryUpdatePlanet, 'planets');

	}

	return;
}

// Relit dans la base les ressources et la file de construction de la planete (0.9k), apres un debit ou un
// remboursement refuse parce qu'une autre requete est passee avant. Retourne faux si la planete n'existe plus.
function BuildingQueueReload ( &$CurrentPlanet ) {
	$Fresh = doquery("SELECT `metal`, `crystal`, `deuterium`, `b_building`, `b_building_id` FROM {{table}} WHERE `id` = '". intval($CurrentPlanet['id']) ."';", 'planets', true);
	if (!$Fresh) {
		return false;
	}
	foreach (array('metal', 'crystal', 'deuterium', 'b_building', 'b_building_id') as $Field) {
		$CurrentPlanet[$Field] = $Fresh[$Field];
	}
	return true;
}

?>