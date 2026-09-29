<?php

/**
 * records.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.4
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	includeLang('records');

	$RecordTpl = gettemplate('records_body');
	$HeaderTpl = gettemplate('records_section_header');
	$TableRows = gettemplate('records_section_rows');

	$parse['rec_title'] = $lang['rec_title'];

	$bloc['section']    = $lang['rec_build'];
	$bloc['player']     = $lang['rec_playe'];
	$bloc['level']      = $lang['rec_level'];
	$parse['building']  = parsetemplate( $HeaderTpl, $bloc);

	$bloc['section']    = $lang['rec_specb'];
	$bloc['player']     = $lang['rec_playe'];
	$bloc['level']      = $lang['rec_level'];
	$parse['buildspe']  = parsetemplate( $HeaderTpl, $bloc);

	$bloc['section']    = $lang['rec_techn'];
	$bloc['player']     = $lang['rec_playe'];
	$bloc['level']      = $lang['rec_level'];
	$parse['research']  = parsetemplate( $HeaderTpl, $bloc);

	$bloc['section']    = $lang['rec_fleet'];
	$bloc['player']     = $lang['rec_playe'];
	$bloc['level']      = $lang['rec_nbre'];
	$parse['fleet']     = parsetemplate( $HeaderTpl, $bloc);

	$bloc['section']    = $lang['rec_defes'];
	$bloc['player']     = $lang['rec_playe'];
	$bloc['level']      = $lang['rec_nbre'];
	$parse['defenses']  = parsetemplate( $HeaderTpl, $bloc);

	if ( SHOW_ADMIN_IN_RECORDS == 0 ) {
		// XNova Renaissance : comptes d'administration exclus d'apres leur niveau. id_level (protection des planetes
		// d'un administrateur) est vide pour tous les joueurs : aucun record ne s'affichait jamais.
		$AdminIds = array(0);
		$Admins   = doquery("SELECT `id` FROM {{table}} WHERE `authlevel` > '0';", 'users');
		while ($Admin = mysqli_fetch_array($Admins)) {
			$AdminIds[] = intval($Admin['id']);
		}
		$RecConditionP       = " WHERE `id_owner` NOT IN (". implode(',', $AdminIds) .")";
		$RecConditionU       = " WHERE `authlevel` = '0'";
		// Meme condition sur la ligne retenue (a egalite, un administrateur pouvait etre affiche)
		$RecAndP             = " AND `id_owner` NOT IN (". implode(',', $AdminIds) .")";
		$RecAndU             = " AND `authlevel` = '0'";
	} else {
		$RecConditionP       = "";
		$RecConditionU       = "";
		$RecAndP             = "";
		$RecAndU             = "";
	}

	// Records lus en un seul parcours de planets et un seul de users (0.9k ; avant : deux parcours complets de la
	// table par element, une centaine par affichage). Meme detenteur qu'avant : quand une seule ligne a le maximum,
	// c'est forcement elle ; a egalite (plusieurs lignes au maximum), la requete d'origine est relancee pour cet
	// element, car elle seule sait quelle ligne MySQL rend en premier
	$RecCols = array('planets' => array(), 'users' => array());
	foreach($lang['tech'] as $Element => $ElementName) {
		if ($ElementName != "" && !empty($resource[$Element])) {
			if (($Element >= 1 && $Element <= 39) || $Element == 44 || ($Element >= 41 && $Element <= 99) ||
			    ($Element >= 201 && $Element <= 399) || ($Element >= 401 && $Element <= 599)) {
				$RecCols['planets'][$resource[$Element]] = true;
			} elseif ($Element >= 101 && $Element <= 199) {
				$RecCols['users'][$resource[$Element]] = true;
			}
		}
	}
	$RecBest  = array('planets' => array(), 'users' => array());
	$RecScans = array('planets' => array('id_owner', $RecConditionP), 'users' => array('username', $RecConditionU));
	foreach ($RecScans as $Table => $Scan) {
		if (count($RecCols[$Table]) > 0) {
			$ScanQuery = doquery("SELECT `". $Scan[0] ."`, `". implode("`, `", array_keys($RecCols[$Table])) ."` FROM {{table}}". $Scan[1] .";", $Table);
			while ($ScanRow = mysqli_fetch_assoc($ScanQuery)) {
				foreach ($RecCols[$Table] as $Column => $Dummy) {
					// Meme regle que MAX() : valeurs NULL ignorees ; [maximum, lignes a ce maximum, detenteur]
					$Value = $ScanRow[$Column];
					if ($Value === null) {
						continue;
					}
					if (!isset($RecBest[$Table][$Column]) || $Value > $RecBest[$Table][$Column][0]) {
						$RecBest[$Table][$Column] = array($Value, 1, $ScanRow[$Scan[0]]);
					} elseif ($Value == $RecBest[$Table][$Column][0]) {
						$RecBest[$Table][$Column][1]++;
					}
				}
			}
		}
	}
	// Pseudos des detenteurs des records de planets, en une requete
	$RecNames = array();
	$Holders  = array();
	foreach ($RecBest['planets'] as $Best) {
		if ($Best[1] == 1 && $Best[0] != 0 && ctype_digit((string) $Best[2])) {
			$Holders[] = $Best[2];
		}
	}
	if (count($Holders) > 0) {
		$Names = doquery("SELECT `id`, `username` FROM {{table}} WHERE `id` IN ('". implode("','", $Holders) ."');", 'users');
		while ($Name = mysqli_fetch_assoc($Names)) {
			$RecNames[$Name['id']] = $Name;
		}
	}

	// Ligne du record d'une colonne de planets et son detenteur : (id_owner, current) et (username)
	function RecordsPlanetRows ( $Column ) {
		global $RecBest, $RecNames, $RecConditionP, $RecAndP;

		$Best = $RecBest['planets'][$Column] ?? null;
		if ($Best === null || $Best[0] == 0) {
			// Aucune ligne, ou maximum nul : « rien » s'affiche, quelle que soit la ligne
			return array(array('id_owner' => 0, 'current' => 0), array('username' => '', 'current' => 0));
		}
		if ($Best[1] > 1 || !ctype_digit((string) $Best[2])) {
			$PlanetRow          = doquery ("SELECT `id_owner`, `". $Column ."` AS `current` FROM {{table}} WHERE `". $Column. "` = (SELECT MAX(`". $Column ."`) FROM {{table}}". $RecConditionP .")". $RecAndP ." LIMIT 1;", 'planets', true);
			$PlanetRow          = $PlanetRow ?: array('id_owner' => 0, 'current' => 0); // aucun detenteur
			$UserRow            = doquery ("SELECT `username` FROM {{table}} WHERE `id` = '".$PlanetRow['id_owner']."';", 'users', true);
		} else {
			$PlanetRow          = array('id_owner' => $Best[2], 'current' => $Best[0]);
			$UserRow            = $RecNames[$Best[2]] ?? null;
		}
		$UserRow            = $UserRow ?: array('username' => '', 'current' => 0);
		return array($PlanetRow, $UserRow);
	}

	foreach($lang['tech'] as $Element => $ElementName) {
		if ($ElementName != "") {
			if (!empty($resource[$Element])) {
				// Je sais bien qu'il n'y a aucune raison de blinder ce test ...
				// Mais avec les zozos qui vont le pomper ... Mieux vaut prevoir que guerir !!
				if       ($Element >=   1 && $Element <=  39 || $Element == 44) {
					// Batiment
					list($PlanetRow, $UserRow) = RecordsPlanetRows ( $resource[$Element] );
					$Row['element']     = $ElementName;
					$Row['winner']      = ($PlanetRow['current'] != 0) ? $UserRow['username'] : $lang['rec_rien'];
					$Row['count']       = ($PlanetRow['current'] != 0) ? pretty_number( $PlanetRow['current'] ) : $lang['rec_rien'];
					$parse['building'] .= parsetemplate( $TableRows, $Row);
				} elseif ($Element >=  41 && $Element <=  99 && $Element != 44) {
					// Batiment spéciaux
					list($PlanetRow, $UserRow) = RecordsPlanetRows ( $resource[$Element] );
					$Row['element']     = $ElementName;
					$Row['winner']      = ($PlanetRow['current'] != 0) ? $UserRow['username'] : $lang['rec_rien'];
					$Row['count']       = ($PlanetRow['current'] != 0) ? pretty_number( $PlanetRow['current'] ) : $lang['rec_rien'];
					$parse['buildspe'] .= parsetemplate( $TableRows, $Row);
				} elseif ($Element >= 101 && $Element <= 199) {
					// Techno
					$Best               = $RecBest['users'][$resource[$Element]] ?? null;
					if ($Best === null || $Best[0] == 0) {
						$UserRow        = array('username' => '', 'current' => 0);
					} elseif ($Best[1] > 1) {
						$UserRow        = doquery ("SELECT `username`, `". $resource[$Element] ."` AS `current` FROM {{table}} WHERE `". $resource[$Element] ."` = (SELECT MAX(`". $resource[$Element] ."`) FROM {{table}}". $RecConditionU .")". $RecAndU ." LIMIT 1;", 'users', true);
						$UserRow        = $UserRow ?: array('username' => '', 'current' => 0);
					} else {
						$UserRow        = array('username' => $Best[2], 'current' => $Best[0]);
					}
					$Row['element']     = $ElementName;
					$Row['winner']      = ($UserRow['current'] != 0) ? $UserRow['username'] : $lang['rec_rien'];
					$Row['count']       = ($UserRow['current'] != 0) ? pretty_number( $UserRow['current'] ) : $lang['rec_rien'];
					$parse['research'] .= parsetemplate( $TableRows, $Row);
				} elseif ($Element >= 201 && $Element <= 399) {
					// Flotte
					list($PlanetRow, $UserRow) = RecordsPlanetRows ( $resource[$Element] );
					$Row['element']     = $ElementName;
					$Row['winner']      = ($PlanetRow['current'] != 0) ? $UserRow['username'] : $lang['rec_rien'];
					$Row['count']       = ($PlanetRow['current'] != 0) ? pretty_number( $PlanetRow['current'] ) : $lang['rec_rien'];
					$parse['fleet']    .= parsetemplate( $TableRows, $Row);
				} elseif ($Element >= 401 && $Element <= 599) {
					// Défenses
					list($PlanetRow, $UserRow) = RecordsPlanetRows ( $resource[$Element] );
					$Row['element']     = $ElementName;
					$Row['winner']      = ($PlanetRow['current'] != 0) ? $UserRow['username'] : $lang['rec_rien'];
					$Row['count']       = ($PlanetRow['current'] != 0) ? pretty_number( $PlanetRow['current'] ) : $lang['rec_rien'];
					$parse['defenses'] .= parsetemplate( $TableRows, $Row);
				}
			}
		}
	}

	$page = parsetemplate( $RecordTpl, $parse );
	display($page, $lang['rec_title']);

// -----------------------------------------------------------------------------------------------------------
// History version
// - 1.0 Réécriture
// - 1.1 Ajout du test de presence d'un chmap de la base de données ... Si apres ca ca plante c'est
//       que l'utilisateur de ce module est vraiment trop con et devrait arreter l'informatique pour aller
//       vendre des frittes chez Mc Do ou autre FastFood
// - 1.2 Separateur de chiffres ... qu'ils soient comme partout ailleur dans le jeu
// - 1.3 Remplacement des 0 par un texte ou un '-' (suggestion matdu57)
// - 1.4 Non prise en compte des planetes protégées
?>