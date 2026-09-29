<?php

/**
 * ResearchBuildingPage.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.2
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Page de Construction de niveau de Recherche
// $CurrentPlanet -> Planete sur laquelle la construction est lancée
//                   Parametre passé par adresse, cela permet de mettre les valeurs a jours
//                   dans le programme appelant
// $CurrentUser   -> Utilisateur qui a lancé la construction
// $InResearch    -> Indicateur qu'il y a une Recherche en cours
// $ThePlanet     -> Planete sur laquelle se realise la technologie eventuellement
function ResearchBuildingPage (&$CurrentPlanet, $CurrentUser, $InResearch, $ThePlanet) {
	global $lang, $resource, $reslist, $phpEx, $dpath, $game_config, $_GET;


	$NoResearchMessage = "";
	$bContinue         = true;
	// Deja est qu'il y a un laboratoire sur la planete ???
	if ($CurrentPlanet[$resource[31]] == 0) {
		message($lang['no_laboratory'], $lang['research_label']);
	}
	// Ensuite ... Est ce que la labo est en cours d'upgrade ?
	if (!CheckLabSettingsInQueue ( $CurrentPlanet )) {
		$NoResearchMessage = $lang['labo_on_update'];
		$bContinue         = false;
	}

	// Boucle d'interpretation des eventuelles commandes
	if (isset($_GET['cmd'])) {
		$TheCommand = ($_GET['cmd'] ?? null);
		$Techno     = ($_GET['tech'] ?? null);
		if ( is_numeric($Techno) ) {
			if ( in_array($Techno, $reslist['tech']) ) {
				// Bon quand on arrive ici ... On sait deja qu'on a une technologie valide
				// Numero entier (0.9k) : « 199.0 » ou « 1.99e2 » passaient la liste sans exister dans les tables des
				// prix et des prerequis (recherche gratuite, instantanee, sans technologies requises)
				$Techno = intval($Techno);
				if ( is_array ($ThePlanet) ) {
					$WorkingPlanet = $ThePlanet;
				} else {
					$WorkingPlanet = $CurrentPlanet;
				}
				switch($TheCommand){
					case 'cancel':
						// Pas de recherche en cours : $ThePlanet est vide (erreur fatale de PHP 8 avant la 0.9k)
						if (is_array($ThePlanet) && $ThePlanet['b_tech_id'] == $Techno) {
							$costs                        = GetBuildingPrice($CurrentUser, $WorkingPlanet, $Techno);
							// Remboursement en plus et sous condition (0.9k) : une seule fois, meme avec des requetes
							// simultanees (avant : ressources lues au debut de la page reecrites en valeurs absolues)
							$QryUpdatePlanet  = "UPDATE {{table}} SET ";
							$QryUpdatePlanet .= "`b_tech_id` = '0', ";
							$QryUpdatePlanet .= "`b_tech` = '0', ";
							$QryUpdatePlanet .= "`metal` = `metal` + '".         floatval($costs['metal'])     ."', ";
							$QryUpdatePlanet .= "`crystal` = `crystal` + '".     floatval($costs['crystal'])   ."', ";
							$QryUpdatePlanet .= "`deuterium` = `deuterium` + '". floatval($costs['deuterium']) ."' ";
							$QryUpdatePlanet .= "WHERE ";
							$QryUpdatePlanet .= "`id` = '".                      intval($WorkingPlanet['id'])  ."' AND ";
							$QryUpdatePlanet .= "`b_tech_id` = '".               intval($Techno)               ."';";
							doquery( $QryUpdatePlanet, 'planets');
							if (mysqli_affected_rows(DbConnect()) == 1) {
								$WorkingPlanet['metal']      += $costs['metal'];
								$WorkingPlanet['crystal']    += $costs['crystal'];
								$WorkingPlanet['deuterium']  += $costs['deuterium'];
								$WorkingPlanet['b_tech_id']   = 0;
								$WorkingPlanet["b_tech"]      = 0;
								$CurrentUser['b_tech_planet'] = 0;
								$InResearch                   = false;
								doquery("UPDATE {{table}} SET `b_tech_planet` = '0' WHERE `id` = '". intval($CurrentUser['id']) ."';", 'users');
								// Recherche sur la planete courante : sa copie en memoire est remboursee aussi (la barre des
								// ressources reecrivait ensuite les ressources d'avant le remboursement, perdu)
								if ($WorkingPlanet['id'] == $CurrentPlanet['id']) {
									$CurrentPlanet['metal']      += $costs['metal'];
									$CurrentPlanet['crystal']    += $costs['crystal'];
									$CurrentPlanet['deuterium']  += $costs['deuterium'];
									$CurrentPlanet['b_tech_id']   = 0;
									$CurrentPlanet["b_tech"]      = 0;
								}
							}
						}
						break;
					case 'search':
						// Memes regles que les liens de la page (0.9k) : aucune recherche en cours (elle etait remplacee et
						// son cout perdu), laboratoire pas en travaux (reglage BuildLabWhileRun)
						if ( $bContinue && !$InResearch &&
							 IsTechnologieAccessible($CurrentUser, $WorkingPlanet, $Techno) &&
							 IsElementBuyable($CurrentUser, $WorkingPlanet, $Techno) ) {
							$costs                        = GetBuildingPrice($CurrentUser, $WorkingPlanet, $Techno);
							$EndTime                      = time() + GetBuildingTime($CurrentUser, $WorkingPlanet, $Techno);
							// Recherche reservee sur le compte, puis debit atomique et conditionnel (0.9k) : deux requetes
							// simultanees ne lancent plus deux recherches, ni ne depensent deux fois le meme stock
							doquery("UPDATE {{table}} SET `b_tech_planet` = '". intval($WorkingPlanet['id']) ."' WHERE `id` = '". intval($CurrentUser['id']) ."' AND `b_tech_planet` = '0';", 'users');
							if (mysqli_affected_rows(DbConnect()) == 1) {
								$QryUpdatePlanet  = "UPDATE {{table}} SET ";
								$QryUpdatePlanet .= "`b_tech_id` = '".               intval($Techno)               ."', ";
								$QryUpdatePlanet .= "`b_tech` = '".                  floatval($EndTime)            ."', ";
								$QryUpdatePlanet .= "`metal` = `metal` - '".         floatval($costs['metal'])     ."', ";
								$QryUpdatePlanet .= "`crystal` = `crystal` - '".     floatval($costs['crystal'])   ."', ";
								$QryUpdatePlanet .= "`deuterium` = `deuterium` - '". floatval($costs['deuterium']) ."' ";
								$QryUpdatePlanet .= "WHERE ";
								$QryUpdatePlanet .= "`id` = '".                      intval($WorkingPlanet['id'])  ."' AND ";
								$QryUpdatePlanet .= "`b_tech_id` = '0' AND ";
								$QryUpdatePlanet .= "`metal` >= '".                  floatval($costs['metal'])     ."' AND ";
								$QryUpdatePlanet .= "`crystal` >= '".                floatval($costs['crystal'])   ."' AND ";
								$QryUpdatePlanet .= "`deuterium` >= '".              floatval($costs['deuterium']) ."';";
								doquery( $QryUpdatePlanet, 'planets');
								if (mysqli_affected_rows(DbConnect()) == 1) {
									$WorkingPlanet['metal']      -= $costs['metal'];
									$WorkingPlanet['crystal']    -= $costs['crystal'];
									$WorkingPlanet['deuterium']  -= $costs['deuterium'];
									$WorkingPlanet["b_tech_id"]   = $Techno;
									$WorkingPlanet["b_tech"]      = $EndTime;
									$CurrentUser["b_tech_planet"] = $WorkingPlanet["id"];
									$InResearch                   = true;
								} else {
									// Ressources depensees entre-temps : reservation rendue
									doquery("UPDATE {{table}} SET `b_tech_planet` = '0' WHERE `id` = '". intval($CurrentUser['id']) ."' AND `b_tech_planet` = '". intval($WorkingPlanet['id']) ."';", 'users');
									// Ressources relues : la barre du haut reecrivait sinon l'ancien stock (valeurs absolues)
									BuildingQueueReload ( $WorkingPlanet );
								}
							} else {
								// Recherche lancee entre-temps par une autre requete : ressources relues aussi
								BuildingQueueReload ( $WorkingPlanet );
							}
						}
						break;
				}
				if ( is_array ($ThePlanet) ) {
					$ThePlanet     = $WorkingPlanet;
				} else {
					$CurrentPlanet = $WorkingPlanet;
					if ($TheCommand == 'search') {
						$ThePlanet = $CurrentPlanet;
					}
				}
			}
		} else {
			$bContinue = false;
		}
	}

	$TechRowTPL = gettemplate('buildings_research_row');
	$TechScrTPL = gettemplate('buildings_research_script');

	foreach($lang['tech'] as $Tech => $TechName) {
		if ($Tech > 105 && $Tech <= 199) {
			if ( IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Tech)) {
				$RowParse                = $lang;
				$RowParse['dpath']       = $dpath;
				$RowParse['tech_id']     = $Tech;
				$building_level          = $CurrentUser[$resource[$Tech]];
				$RowParse['tech_level']  = ($building_level == 0) ? "" : "( ". $lang['level']. " ".$building_level." )";
				$RowParse['tech_name']   = $TechName;
				$RowParse['tech_descr']  = $lang['res']['descriptions'][$Tech];
				$RowParse['tech_price']  = GetElementPrice($CurrentUser, $CurrentPlanet, $Tech);
				$SearchTime              = GetBuildingTime($CurrentUser, $CurrentPlanet, $Tech);
				$RowParse['search_time'] = ShowBuildTime($SearchTime);
				$RowParse['tech_restp']  = $lang['rest_ress'] ." ". GetRestPrice ($CurrentUser, $CurrentPlanet, $Tech, true);
				$CanBeDone               = IsElementBuyable($CurrentUser, $CurrentPlanet, $Tech);

				// Arbre de decision de ce que l'on met dans la derniere case de la ligne
				if (!$InResearch) {
					$LevelToDo = 1 + $CurrentUser[$resource[$Tech]];
					if ($CanBeDone) {
						if (!CheckLabSettingsInQueue ( $CurrentPlanet )) {
							// Le laboratoire est cours de construction ou d'evolution
							// Et dans la config du systeme, on ne permet pas la recherche pendant
							// que le labo est en construction ou evolution !
							if ($LevelToDo == 1) {
								$TechnoLink  = "<font color=#FF0000>". $lang['rechercher'] ."</font>";
							} else {
								$TechnoLink  = "<font color=#FF0000>". $lang['rechercher'] ."<br>".$lang['level']." ".$LevelToDo."</font>";
							}
						} else {
							$TechnoLink  = "<a href=\"buildings.php?mode=research&cmd=search&tech=".$Tech."\">";
							if ($LevelToDo == 1) {
								$TechnoLink .= "<font color=#00FF00>". $lang['rechercher'] ."</font>";
							} else {
								$TechnoLink .= "<font color=#00FF00>". $lang['rechercher'] ."<br>".$lang['level']." ".$LevelToDo."</font>";
							}
							$TechnoLink  .= "</a>";
						}
					} else {
						if ($LevelToDo == 1) {
							$TechnoLink  = "<font color=#FF0000>". $lang['rechercher'] ."</font>";
						} else {
							$TechnoLink  = "<font color=#FF0000>". $lang['rechercher'] ."<br>".$lang['level']." ".$LevelToDo."</font>";
						}
					}

				} else {
					// Y a une construction en cours
					if ($ThePlanet["b_tech_id"] == $Tech) {
						// C'est le technologie en cours de recherche
						$bloc       = $lang;
						if ($ThePlanet['id'] != $CurrentPlanet['id']) {
							// Ca se passe sur une autre planete
							$bloc['tech_time']  = $ThePlanet["b_tech"] - time();
							$bloc['tech_name']  = $lang['on'] ."<br>". $ThePlanet["name"];
							$bloc['tech_home']  = $ThePlanet["id"];
							$bloc['tech_id']    = $ThePlanet["b_tech_id"];
						} else {
							// Ca se passe sur la planete actuelle
							$bloc['tech_time']  = $CurrentPlanet["b_tech"] - time();
							$bloc['tech_name']  = "";
							$bloc['tech_home']  = $CurrentPlanet["id"];
							$bloc['tech_id']    = $CurrentPlanet["b_tech_id"];
						}
						$TechnoLink  = parsetemplate($TechScrTPL, $bloc);
					} else {
						// Technologie pas en cours recherche
						$TechnoLink  = "<center>-</center>";
					}
				}
				$RowParse['tech_link']  = $TechnoLink;
				$TechnoList             = ($TechnoList ?? '') . parsetemplate($TechRowTPL, $RowParse);
			}
		}
	}

	$PageParse                = $lang;
	$PageParse['noresearch']  = $NoResearchMessage;
	$PageParse['technolist']  = $TechnoList ?? '';
	$Page                     = parsetemplate(gettemplate('buildings_research'), $PageParse);

	display( $Page, $lang['research_label'] );
}

// History revision
// 1.0 - Release initiale / modularisation / Reecriture / Commentaire / Mise en forme
// 1.1 - BUG affichage de la techno en cours
// 1.2 - Restructuration modification pour permettre d'annuller proprement une techno en cours
?>