<?php

/**
 * DefensesBuildingPage.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.2
 * @copyright 2008 By Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Page de Construction d'Elements de Defense
// $CurrentPlanet -> Planete sur laquelle la construction est lancée
//                   Parametre passé par adresse, cela permet de mettre les valeurs a jours
//                   dans le programme appelant
// $CurrentUser   -> Utilisateur qui a lancé la construction
//
// Petit et grand bouclier : un seul exemplaire (ni deja construit, ni deja dans la file). L'original testait le petit
// bouclier pour les deux : grand bouclier bloque des que le petit existait, et constructible a volonte sinon.
function DefenseShieldBuildable ( $CurrentPlanet, $Element ) {
	global $resource;
	if ($Element != 407 && $Element != 408) {
		return true;
	}
	$InQueue = (strpos(';'. $CurrentPlanet['b_hangar_id'], ';'. $Element .',') !== false);
	return (!$InQueue && $CurrentPlanet[$resource[$Element]] < 1);
}

// Missiles du silo, stock et file de fabrication. Chaque entree de la file vaut « element,nombre » : l'original lisait
// les cases [502] et [503] (inexistantes), les missiles en attente n'etaient jamais comptes et le silo pouvait deborder.
function DefenseMissilesInSilo ( $CurrentPlanet ) {
	global $resource;
	$Missiles = array(502 => $CurrentPlanet[ $resource[502] ], 503 => $CurrentPlanet[ $resource[503] ]);
	foreach (explode(';', $CurrentPlanet['b_hangar_id']) as $QueueItem) {
		$ElmentArray = explode(',', $QueueItem);
		if (count($ElmentArray) >= 2 && ($ElmentArray[0] == 502 || $ElmentArray[0] == 503)) {
			$Missiles[intval($ElmentArray[0])] += intval($ElmentArray[1]);
		}
	}
	return $Missiles;
}

// Maximum commandable d'une defense (lien « max. N ») : memes limites que la commande
function DefenseMaxElements ( $CurrentPlanet, $Element, $Missiles ) {
	global $resource;
	$Max = min(GetMaxConstructibleElements($Element, $CurrentPlanet), OrderUnitsMax());
	if ($Element == 407 || $Element == 408) {
		$Max = min($Max, 1);
	} elseif ($Element == 502 || $Element == 503) {
		// Un missile interplanetaire prend la place de deux missiles d'interception
		$Space = ($CurrentPlanet[ $resource[44] ] * 10) - $Missiles[502] - (2 * $Missiles[503]);
		$Max   = min($Max, ($Element == 502) ? $Space : floor($Space / 2));
	}
	return max(0, $Max);
}

function DefensesBuildingPage ( &$CurrentPlanet, $CurrentUser ) {
 	global $lang, $resource, $phpEx, $dpath, $_POST;

	if (isset($_POST['fmenge'])) {
		// On vient de Cliquer ' Construire '

		// Et y a une liste de doléances
		// Ici, on sait precisement ce qu'on aimerait bien construire ...

		// Gestion de la place disponible dans les silos (missiles en stock et deja dans la file)
		$Missiles      = DefenseMissilesInSilo($CurrentPlanet);
		$SiloSize      = $CurrentPlanet[ $resource[44] ];
		$MaxMissiles   = $SiloSize * 10;
		foreach(($_POST['fmenge'] ?? null) as $Element => $Count) {
			// Construction d'Element recuperés sur la page de Flotte ...
			// ATTENTION ! La file d'attente Flotte est Commune a celle des Defenses
			// Dans fmenge, on devrait trouver un tableau des elements constructibles etdu nombre d'elements souhaités

			$Element = intval($Element);
			$Count   = max(0, intval($Count)); // pas de quantite negative (sinon remboursement de ressources)
			if ($Count > OrderUnitsMax()) {
				$Count = OrderUnitsMax();
			}


			if ($Count != 0) {
				// Petit et grand bouclier : un seul exemplaire (l'original laissait la quantite demandee quand le
				// bouclier existait deja)
				if ($Element == 407 || $Element == 408) {
					$Count = DefenseShieldBuildable($CurrentPlanet, $Element) ? 1 : 0;
				}

				// On verifie si on a les technologies necessaires a la construction de l'element
				if ( IsTechnologieAccessible ($CurrentUser, $CurrentPlanet, $Element) ) {
					// On verifie combien on sait faire de cet element au max
					$MaxElements   = GetMaxConstructibleElements ( $Element, $CurrentPlanet );

					// Testons si on a de la place pour ces nouveaux missiles !
					if ($Element == 502 || $Element == 503) {
						// Cas particulier des missiles
						$ActuMissiles  = $Missiles[502] + ( 2 * $Missiles[503] );
						$MissilesSpace = $MaxMissiles - $ActuMissiles;
						if ($Element == 502) {
							if ( $Count > $MissilesSpace ) {
								$Count = $MissilesSpace;
							}
						} else {
							if ( $Count > floor( $MissilesSpace / 2 ) ) {
								$Count = floor( $MissilesSpace / 2 );
							}
						}
						if ($Count > $MaxElements) {
							$Count = $MaxElements;
						}
						$Missiles[$Element] += $Count;
					} else {
						// Si pas assez de ressources, on ajuste le nombre d'elements
						if ($Count > $MaxElements) {
							$Count = $MaxElements;
						}
					}

					$Ressource = GetElementRessources ( $Element, $Count );
					$BuildTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $Element);
					if ($Count >= 1) {
						$CurrentPlanet['metal']           -= $Ressource['metal'];
						$CurrentPlanet['crystal']         -= $Ressource['crystal'];
						$CurrentPlanet['deuterium']       -= $Ressource['deuterium'];
						$CurrentPlanet['b_hangar_id']     .= "". $Element .",". $Count .";";
					}
				}
			}
		}
	}

	// -------------------------------------------------------------------------------------------------------
	// S'il n'y a pas de Chantier ...
	if ($CurrentPlanet[$resource[21]] == 0) {
		// Veuillez avoir l'obligeance de construire le Chantier Spacial !!
		message($lang['need_hangar'], $lang['tech'][21]);
	}

	// -------------------------------------------------------------------------------------------------------
	// Construction de la page du Chantier (car si j'arrive ici ... c'est que j'ai tout ce qu'il faut pour ...
	$TabIndex  = 0;
	$PageTable = "";
	$Missiles  = DefenseMissilesInSilo($CurrentPlanet);
	foreach($lang['tech'] as $Element => $ElementName) {
		if ($Element > 400 && $Element <= 599) {
			if (IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element)) {
				// Disponible à la construction

				// On regarde si on peut en acheter au moins 1
				$CanBuildOne         = IsElementBuyable($CurrentUser, $CurrentPlanet, $Element, false);
				// On regarde combien de temps il faut pour construire l'element
				$BuildOneElementTime = GetBuildingTime($CurrentUser, $CurrentPlanet, $Element);
				// Disponibilité actuelle
				$ElementCount        = $CurrentPlanet[$resource[$Element]];
				$ElementNbre         = ($ElementCount == 0) ? "" : " (".$lang['dispo'].": " . pretty_number($ElementCount) . ")";

				// Construction des 3 cases de la ligne d'un element dans la page d'achat !
				// Début de ligne
				$PageTable .= "\n<tr>";

				// Imagette + Link vers la page d'info
				$PageTable .= "<th class=l>";
				$PageTable .= "<a href=infos.".$phpEx."?gid=".$Element.">";
				$PageTable .= "<img border=0 src=\"".$dpath."gebaeude/".$Element.".gif\" align=top width=120 height=120></a>";
				$PageTable .= "</th>";

				// Description
				$PageTable .= "<td class=l>";
				$PageTable .= "<a href=infos.".$phpEx."?gid=".$Element.">".$ElementName."</a> ".$ElementNbre."<br>";
				$PageTable .= "".$lang['res']['descriptions'][$Element]."<br>";
				// On affiche le 'prix' avec eventuellement ce qui manque en ressource
				$PageTable .= GetElementPrice($CurrentUser, $CurrentPlanet, $Element, false);
				// On affiche le temps de construction (c'est toujours tellement plus joli)
				$PageTable .= ShowBuildTime($BuildOneElementTime);
				$PageTable .= "</td>";

				// Case nombre d'elements a construire
				$PageTable .= "<th class=k>";
				// Si ... Et Seulement si je peux construire je mets la p'tite zone de saisie
				if ($CanBuildOne) {
					if ( !DefenseShieldBuildable($CurrentPlanet, $Element) ) {
						$PageTable .= "<font color=\"red\">".$lang['only_one']."</font>";
					} else {
						$TabIndex++;
						$PageTable .= "<input type=text name=fmenge[".$Element."] alt='".$lang['tech'][$Element]."' size=5 maxlength=".strlen(OrderUnitsMax())." value=0 tabindex=".$TabIndex.">";
						$PageTable .= ElementMaxLink($Element, DefenseMaxElements($CurrentPlanet, $Element, $Missiles));
					}
				}
				// (la case restait ouverte quand le bouclier etait deja construit)
				$PageTable .= "</th>";

				// Fin de ligne (les 3 cases sont construites !!
				$PageTable .= "</tr>";
			}
		}
	}

	// Liste des constructions en cours (la file brute « 502,10;503,10; » s'y ajoutait apres un envoi du formulaire)
	$BuildQueue = '';
	if ($CurrentPlanet['b_hangar_id'] != '') {
		$BuildQueue = ElementBuildListBox( $CurrentUser, $CurrentPlanet );
	}

	$parse = $lang;
	// La page se trouve dans $PageTable;
	$parse['buildlist']    = $PageTable;
	// Et la liste de constructions en cours dans $BuildQueue;
	$parse['buildinglist'] = $BuildQueue;
	// fragmento de template
	$page = parsetemplate(gettemplate('buildings_defense'), $parse);

	display($page, $lang['defense_label']);

}
// Version History
// - 1.0 Modularisation
// - 1.1 Correction mise en place d'une limite max d'elements constructibles par ligne
// - 1.2 Correction limitation bouclier meme si en queue de fabrication
//
?>