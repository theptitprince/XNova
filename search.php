<?php

/**
 * search.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

$searchtext = SqlEscape(($_POST['searchtext'] ?? null));
$type = ($_POST['type'] ?? null);

$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

includeLang('search');
$i = 0;
$search_results = '';
//creamos la query
$searchtext = SqlEscape(($_POST["searchtext"] ?? null));
switch($type){
	case "playername":
		$table = gettemplate('search_user_table');
		$row = gettemplate('search_user_row');
		$search = doquery("SELECT * FROM {{table}} WHERE username LIKE '%{$searchtext}%' LIMIT 30;","users");
	break;
	case "planetname":
		$table = gettemplate('search_user_table');
		$row = gettemplate('search_user_row');
		$search = doquery("SELECT * FROM {{table}} WHERE name LIKE '%{$searchtext}%' LIMIT 30",'planets');
	break;
	case "allytag":
		$table = gettemplate('search_ally_table');
		$row = gettemplate('search_ally_row');
		$search = doquery("SELECT * FROM {{table}} WHERE ally_tag LIKE '%{$searchtext}%' LIMIT 30","alliance");
	break;
	case "allyname":
		$table = gettemplate('search_ally_table');
		$row = gettemplate('search_ally_row');
		$search = doquery("SELECT * FROM {{table}} WHERE ally_name LIKE '%{$searchtext}%' LIMIT 30","alliance");
	break;
	default:
		$table = gettemplate('search_user_table');
		$row = gettemplate('search_user_row');
		$search = doquery("SELECT * FROM {{table}} WHERE username LIKE '%{$searchtext}%' LIMIT 30","users");
}
/*
  Esta es la tecnica de, "el ahorro de queries".
  Inventada por Perberos :3
  ...pero ahora no... porque tengo sueño ;P
*/
if(isset($searchtext) && isset($type)){

	$result_list = '';
	// Resultats lus d'abord, puis les joueurs, planetes, alliances et rangs de tous les resultats en une requete de
	// chaque sorte (0.9k ; avant : 2 a 3 requetes par resultat). Lectures par identifiant (cle primaire) : memes
	// lignes. Relus un par un comme avant : un identifiant qui n'est pas un entier (meme erreur SQL qu'avant) et un
	// joueur ou une alliance qui a plusieurs lignes de points (la premiere depend alors de l'ordre de lecture de MySQL)
	$SearchRows = array();
	while($r = mysqli_fetch_array($search, MYSQLI_BOTH)){
		$SearchRows[] = $r;
	}
	$WantOwners  = array();
	$WantPlanets = array();
	$WantAllys   = array();
	$WantStats   = array();
	foreach ($SearchRows as $r) {
		if ($type == 'planetname') {
			if (ctype_digit((string) $r['id_owner'])) {
				$WantOwners[] = $r['id_owner'];
			}
			$WantStats[] = intval($r['id_owner']);
		} elseif ($type == 'playername') {
			if (ctype_digit((string) $r['id_planet'])) {
				$WantPlanets[] = $r['id_planet'];
			}
			if(($r['ally_id'] ?? 0)!=0&&($r['ally_request'] ?? 0)==0){
				$WantAllys[] = intval($r['ally_id']);
			}
			$WantStats[] = intval($r['id']);
		} elseif ($type == 'allytag' || $type == 'allyname') {
			$WantStats[] = intval($r['id']);
		}
	}
	$OwnerRows  = array();
	$PlanetRows = array();
	$AllyRows   = array();
	$StatRows   = array();
	if (count($WantOwners) > 0) {
		$Found = doquery("SELECT * FROM {{table}} WHERE id IN (". implode(',', $WantOwners) .")", "users");
		while ($FoundRow = mysqli_fetch_array($Found)) {
			$OwnerRows[$FoundRow['id']] = $FoundRow;
		}
	}
	if (count($WantPlanets) > 0) {
		$Found = doquery("SELECT id, name FROM {{table}} WHERE id IN (". implode(',', $WantPlanets) .")", "planets");
		while ($FoundRow = mysqli_fetch_array($Found)) {
			$PlanetRows[$FoundRow['id']] = $FoundRow;
		}
	}
	if (count($WantAllys) > 0) {
		$Found = doquery("SELECT id, ally_name FROM {{table}} WHERE id IN (". implode(',', $WantAllys) .")", "alliance");
		while ($FoundRow = mysqli_fetch_array($Found)) {
			$AllyRows[$FoundRow['id']] = $FoundRow;
		}
	}
	if (count($WantStats) > 0) {
		$StatType  = ($type == 'allytag' || $type == 'allyname') ? 2 : 1;
		$StatField = ($StatType == 2) ? 'total_points' : 'total_rank';
		$Found = doquery("SELECT `id_owner`, `". $StatField ."` FROM {{table}} WHERE `stat_type` = '". $StatType ."' AND `stat_code` = '1' AND `id_owner` IN ('". implode("','", $WantStats) ."');", 'statpoints');
		while ($FoundRow = mysqli_fetch_array($Found)) {
			$StatRows[intval($FoundRow['id_owner'])][] = $FoundRow[$StatField];
		}
	}

	foreach ($SearchRows as $r) {

		if($type=='playername'||$type=='planetname'){
			$s=$r;
			//para obtener el nombre del planeta
			if ($type == "planetname")
			{
			if (ctype_digit((string) $s['id_owner'])) {
				$pquery = $OwnerRows[$s['id_owner']] ?? null;
			} else {
				$pquery = doquery("SELECT * FROM {{table}} WHERE id = {$s['id_owner']}","users",true);
			}
/*			$farray = mysqli_fetch_array($pquery);*/
			$s['planet_name'] = $s['name'];
			$s['username'] = $pquery['username'];
			$s['id'] = intval($pquery['id']);
			$s['ally_name'] = (($pquery['ally_id'] ?? 0) > 0 && $pquery['ally_name'] != '') ? "<a href=\"alliance.php?mode=ainfo&amp;a=". intval($pquery['ally_id']) ."\">". htmlspecialchars($pquery['ally_name'], ENT_QUOTES, 'UTF-8') ."</a>" : '';
			}else{
			if (ctype_digit((string) $s['id_planet'])) {
				$pquery = $PlanetRows[$s['id_planet']] ?? null;
			} else {
				$pquery = doquery("SELECT name FROM {{table}} WHERE id = {$s['id_planet']}","planets",true);
			}
			$s['planet_name'] = $pquery['name'] ?? '';
			// Alliance du joueur : lue avant d'etre affichee (l'original affichait celle du joueur precedent)
			$aquery = array();
			if(($s['ally_id'] ?? 0)!=0&&($s['ally_request'] ?? 0)==0){
				$aquery = $AllyRows[intval($s['ally_id'])] ?? null;
			}
			$s['ally_name'] = (($aquery['ally_name'] ?? '')!='') ? "<a href=\"alliance.php?mode=ainfo&amp;a=". intval($s['ally_id']) ."\">". htmlspecialchars($aquery['ally_name'], ENT_QUOTES, 'UTF-8') ."</a>" : '';
			}

			// Rang au classement general (la table des joueurs n'a pas de colonne rank : la position etait vide)
			$OwnerId  = ($type == "planetname") ? intval($s['id_owner']) : intval($s['id']);
			$Found    = $StatRows[$OwnerId] ?? array();
			if (count($Found) > 1) {
				$RankRow  = doquery("SELECT `total_rank` FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '". $OwnerId ."';", 'statpoints', true);
			} else {
				$RankRow  = (count($Found) == 1) ? array('total_rank' => $Found[0]) : null;
			}
			$s['rank'] = $RankRow['total_rank'] ?? '';

			$s['position'] = "<a href=\"stat.php?range=".$s['rank']."\">".$s['rank']."</a>";
			$s['dpath'] = $dpath;
			$s['coordinated'] = "{$s['galaxy']}:{$s['system']}:{$s['planet']}";
			$s['buddy_request'] = $lang['buddy_request'];
			$s['write_a_messege'] = $lang['write_a_messege'];
			$result_list .= parsetemplate($row, $s);
		}elseif($type=='allytag'||$type=='allyname'){
			$s=$r;

			// Points de l'alliance : classement (statpoints, type 2) ; la table alliance n'a pas de colonne ally_points
			$Found = $StatRows[intval($s['id'])] ?? array();
			if (count($Found) > 1) {
				$PointsRow = doquery("SELECT `total_points` FROM {{table}} WHERE `stat_type` = '2' AND `stat_code` = '1' AND `id_owner` = '". intval($s['id']) ."';", 'statpoints', true);
			} else {
				$PointsRow = (count($Found) == 1) ? array('total_points' => $Found[0]) : null;
			}
			$s['ally_points'] = pretty_number($PointsRow['total_points'] ?? 0);

			$s['ally_tag'] = "<a href=\"alliance.php?mode=ainfo&amp;a=". intval($s['id']) ."\">". htmlspecialchars($s['ally_tag'], ENT_QUOTES, 'UTF-8') ."</a>";
			$s['ally_name'] = htmlspecialchars($s['ally_name'], ENT_QUOTES, 'UTF-8');
			$result_list .= parsetemplate($row, $s);
		}
	}
	if($result_list!=''){
		$lang['result_list'] = $result_list;
		$search_results = parsetemplate($table, $lang);
	}
}

//el resto...
$lang['type_playername'] = (($_POST["type"] ?? null) == "playername") ? " SELECTED" : "";
$lang['type_planetname'] = (($_POST["type"] ?? null) == "planetname") ? " SELECTED" : "";
$lang['type_allytag'] = (($_POST["type"] ?? null) == "allytag") ? " SELECTED" : "";
$lang['type_allyname'] = (($_POST["type"] ?? null) == "allyname") ? " SELECTED" : "";
$lang['searchtext'] = SafeText(($_POST['searchtext'] ?? null));
$lang['search_results'] = $search_results;
//esto es algo repetitivo ... w
$page = parsetemplate(gettemplate('search_body'), $lang);
display($page,$lang['search']);
?>
