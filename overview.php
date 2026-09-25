<?php

/**
 * overview.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By Chlorel for XNova
 * @Copyright, bot de mapomme (Britania) modifié et adapté pour XNova by Bono ;)
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE' , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

$lunarow = doquery("SELECT * FROM {{table}} WHERE `id_owner` = '" . $planetrow['id_owner'] . "' AND `galaxy` = '" . $planetrow['galaxy'] . "' AND `system` = '" . $planetrow['system'] . "' AND `lunapos` = '" . $planetrow['planet'] . "';", 'lunas', true);

// (CheckPlanetUsedFields($lunarow) retire : $lunarow vient de la table des lunes, sans batiments ni cases)

$mode = ($_GET['mode'] ?? null);
$pl = SqlEscape(($_GET['pl'] ?? null));
$_POST['deleteid'] = intval(($_POST['deleteid'] ?? null));

includeLang('resources');
includeLang('overview');

if($game_config['enable_bot'] == 1){
	//robot anti multi -- debut --
$multi = $user['multi_validated'];
$ip = SqlEscape($user['user_lastip']);
$time = time();
$duree = $time + (intval($game_config['ban_duration']) * 86400);
$op = stripslashes($game_config['bot_name']);
$mail = stripslashes($game_config['bot_adress']);
$sql = doquery("SELECT * FROM {{table}} WHERE `user_lastip`='{$ip}'", 'users');
$boucle = 0;
$Names = array();
   while($m = mysqli_fetch_array($sql)){
      $Names[] = $m['username'];
      $boucle ++;
            }
$username = implode(', ', $Names);
if($boucle > 1 && $multi == 0){
	// Un seul bannissement pour le joueur courant (l'original l'inscrivait une fois par compte partageant l'IP)
      $QryBanMulti = "INSERT INTO {{table}} SET ";
        $QryBanMulti .= "`who` = '" . SqlEscape(strip_tags($user['username'])) . "', ";
        $QryBanMulti .= "`who2` = '" . SqlEscape(strip_tags($user['username'])) . "', ";
        $QryBanMulti .= "`theme` = 'Multi-Compte entre " . SqlEscape($username) . "', ";
        $QryBanMulti .= "`time` = '" . $time . "', ";
		$QryBanMulti .= "`longer` = '" . $duree . "', ";
		$QryBanMulti .= "`author` = '" . SqlEscape($op) . "', ";
		$QryBanMulti .= "`email`='" . SqlEscape($mail) . "';";
        doquery($QryBanMulti, 'banned');
		doquery("UPDATE {{table}} SET `bana` = '1', `banaday` = '". intval($duree) ."' WHERE `id` = '". intval($user['id']) ."';", "users");

      }
//robot anti multi -- FIN -- 
} else {}
switch ($mode) {
    case 'renameplanet':
        // -----------------------------------------------------------------------------------------------
        if (($_POST['action'] ?? null) == $lang['namer']) {
            // Reponse au changement de nom de la planete
            $UserPlanet = SafeName(($_POST['newname'] ?? ''), 32); // (l'ancien filtre changeait « Scriptopolis » en « *opolis »)
            $newname = SqlEscape(trim($UserPlanet));
            if ($newname != "") {
                // Deja on met jour la planete qu'on garde en memoire (pour le nom)
                $planetrow['name'] = $newname;
                // Ensuite, on enregistre dans la base de données
                doquery("UPDATE {{table}} SET `name` = '" . $newname . "' WHERE `id` = '" . $user['current_planet'] . "' LIMIT 1;", "planets");
                // Est ce qu'il sagit d'une lune ??
                if ($planetrow['planet_type'] == 3) {
                    // Oui ... alors y a plus qu'a changer son nom dans la table des lunes aussi !!!
                    doquery("UPDATE {{table}} SET `name` = '" . $newname . "' WHERE `galaxy` = '" . $planetrow['galaxy'] . "' AND `system` = '" . $planetrow['system'] . "' AND `lunapos` = '" . $planetrow['planet'] . "' LIMIT 1;", "lunas");
                }
            }
        } elseif (($_POST['action'] ?? null) == $lang['colony_abandon']) {
            // Cas d'abandon d'une colonie
            // Affichage de la forme d'abandon de colonie
            $parse = $lang;
            $parse['planet_id'] = $planetrow['id'];
            $parse['galaxy_galaxy'] = $planetrow['galaxy'];
            $parse['galaxy_system'] = $planetrow['system'];
            $parse['galaxy_planet'] = $planetrow['planet'];
            $parse['planet_name'] = $planetrow['name'];

            $page .= parsetemplate(gettemplate('overview_deleteplanet'), $parse);
            // On affiche la forme pour l'abandon de la colonie
            display($page, $lang['rename_and_abandon_planet']);
        } elseif (($_POST['kolonieloeschen'] ?? null) == 1 && ($_POST['deleteid'] ?? null) == $user['current_planet']) {
            // Controle du mot de passe pour abandon de colonie
            if (PasswordCheck(($_POST['pw'] ?? null), $user) && $user['id_planet'] != $user['current_planet']) {
                $destruyed = time() + 60 * 60 * 24;

                $QryUpdatePlanet = "UPDATE {{table}} SET ";
                $QryUpdatePlanet .= "`destruyed` = '" . $destruyed . "', ";
                $QryUpdatePlanet .= "`id_owner` = '0' ";
                $QryUpdatePlanet .= "WHERE ";
                $QryUpdatePlanet .= "`id` = '" . $user['current_planet'] . "' LIMIT 1;";
                doquery($QryUpdatePlanet , 'planets');

                $QryUpdateUser = "UPDATE {{table}} SET ";
                $QryUpdateUser .= "`current_planet` = `id_planet` ";
                $QryUpdateUser .= "WHERE ";
                $QryUpdateUser .= "`id` = '" . $user['id'] . "' LIMIT 1";
                doquery($QryUpdateUser, "users");
                // Tout s'est bien passé ! La colo a été effacée !!
                message($lang['deletemessage_ok'] , $lang['colony_abandon'], 'overview.php?mode=renameplanet');
            } elseif ($user['id_planet'] == $user["current_planet"]) {
                // Et puis quoi encore ??? On ne peut pas effacer la planete mere ..
                // Uniquement les colonies crées apres coup !!!
                message($lang['deletemessage_wrong'], $lang['colony_abandon'], 'overview.php?mode=renameplanet');
            } else {
                // Erreur de saisie du mot de passe je n'efface pas !!!
                message($lang['deletemessage_fail'] , $lang['colony_abandon'], 'overview.php?mode=renameplanet');
            }
        }

        $parse = $lang;

        $parse['planet_id'] = $planetrow['id'];
        $parse['galaxy_galaxy'] = $planetrow['galaxy'];
        $parse['galaxy_system'] = $planetrow['system'];
        $parse['galaxy_planet'] = $planetrow['planet'];
        $parse['planet_name'] = $planetrow['name'];

        $page = parsetemplate(gettemplate('overview_renameplanet'), $parse);
        // On affiche la page permettant d'abandonner OU de renomme une Colonie / Planete
        display($page, $lang['rename_and_abandon_planet']);
        break;

    default:
        if ($user['id'] != '') {
            // --- Gestion des messages ----------------------------------------------------------------------
            $Have_new_message = "";
            if ($user['new_message'] != 0) {
                $Have_new_message .= "<tr>";
                if ($user['new_message'] == 1) {
                    $Have_new_message .= "<th colspan=4><a href=messages.$phpEx>" . $lang['have_new_message'] . "</a></th>";
                } elseif ($user['new_message'] > 1) {
                    $Have_new_message .= "<th colspan=4><a href=messages.$phpEx>";
                    $m = pretty_number($user['new_message']);
                    $Have_new_message .= str_replace('%m', $m, $lang['have_new_messages']);
                    $Have_new_message .= "</a></th>";
                }
                $Have_new_message .= "</tr>";
            }
            // -----------------------------------------------------------------------------------------------
            // --- Gestion Officiers -------------------------------------------------------------------------
            // Passage au niveau suivant, ajout du point de compétence et affichage du passage au nouveau level
            $HaveNewLevelMineur = '';
            $HaveNewLevelRaid   = '';
            $XpMinierUp = $user['lvl_minier'] * 5000;
            $XpRaidUp = $user['lvl_raid'] * 10;
            $XpMinier = $user['xpminier'];
            $XPRaid = $user['xpraid'];

            $LvlUpMinier = $user['lvl_minier'] + 1;
            $LvlUpRaid = $user['lvl_raid'] + 1;

            if (($LvlUpMinier + $LvlUpRaid) <= 100) {
                if ($XpMinier >= $XpMinierUp) {
                    $QryUpdateUser = "UPDATE {{table}} SET ";
                    $QryUpdateUser .= "`lvl_minier` = '" . $LvlUpMinier . "', ";
                    $QryUpdateUser .= "`rpg_points` = `rpg_points` + 1 ";
                    $QryUpdateUser .= "WHERE ";
                    $QryUpdateUser .= "`id` = '" . $user['id'] . "';";
                    doquery($QryUpdateUser, 'users');
                    $HaveNewLevelMineur = "<tr>";
                    $HaveNewLevelMineur .= "<th colspan=4><a href=officier.$phpEx>" . $lang['have_new_level_mineur'] . "</a></th></tr>";
                }
                if ($XPRaid >= $XpRaidUp) {
                    $QryUpdateUser = "UPDATE {{table}} SET ";
                    $QryUpdateUser .= "`lvl_raid` = '" . $LvlUpRaid . "', ";
                    $QryUpdateUser .= "`rpg_points` = `rpg_points` + 1 ";
                    $QryUpdateUser .= "WHERE ";
                    $QryUpdateUser .= "`id` = '" . $user['id'] . "';";
                    doquery($QryUpdateUser, 'users');
                    $HaveNewLevelRaid = "<tr>";
                    $HaveNewLevelRaid .= "<th colspan=4><a href=officier.$phpEx>" . $lang['have_new_level_raid'] . "</a></th></tr>";
                }
            }
            // -----------------------------------------------------------------------------------------------
            // --- Gestion des flottes personnelles ---------------------------------------------------------
            // Toutes de vert vetues
            $OwnFleets = doquery("SELECT * FROM {{table}} WHERE `fleet_owner` = '" . $user['id'] . "';", 'fleets');
            $Record = 0;
            while ($FleetRow = mysqli_fetch_array($OwnFleets)) {
                $Record++;

                $StartTime = $FleetRow['fleet_start_time'];
                $StayTime = $FleetRow['fleet_end_stay'];
                $EndTime = $FleetRow['fleet_end_time'];
                // Flotte a l'aller
                $Label = "fs";
                if ($StartTime > time()) {
                    $fpage[$StartTime] = BuildFleetEventTable ($FleetRow, 0, true, $Label, $Record);
                }

                if ($FleetRow['fleet_mission'] <> 4) {
                    // Flotte en stationnement
                    $Label = "ft";
                    if ($StayTime > time()) {
                        $fpage[$StayTime] = BuildFleetEventTable ($FleetRow, 1, true, $Label, $Record);
                    }
                    // Flotte au retour
                    $Label = "fe";
                    if ($EndTime > time()) {
                        $fpage[$EndTime] = BuildFleetEventTable ($FleetRow, 2, true, $Label, $Record);
                    }
                }
            } // End While
            // -----------------------------------------------------------------------------------------------
            // --- Gestion des flottes autres que personnelles ----------------------------------------------
            // Flotte ennemies (ou amie) mais non personnelles
            $OtherFleets = doquery("SELECT * FROM {{table}} WHERE `fleet_target_owner` = '" . $user['id'] . "';", 'fleets');

            $Record = 2000;
            while ($FleetRow = mysqli_fetch_array($OtherFleets)) {
                if ($FleetRow['fleet_owner'] != $user['id']) {
                    if ($FleetRow['fleet_mission'] != 8) {
                        $Record++;
                        $StartTime = $FleetRow['fleet_start_time'];
                        $StayTime = $FleetRow['fleet_end_stay'];

                        if ($StartTime > time()) {
                            $Label = "ofs";
                            $fpage[$StartTime] = BuildFleetEventTable ($FleetRow, 0, false, $Label, $Record);
                        }
                        if ($FleetRow['fleet_mission'] == 5) {
                            // Flotte en stationnement
                            $Label = "oft";
                            if ($StayTime > time()) {
                                $fpage[$StayTime] = BuildFleetEventTable ($FleetRow, 1, false, $Label, $Record);
                            }
                        }
                    }
                }
            }
            // -----------------------------------------------------------------------------------------------
            // --- Gestion de la liste des planetes ----------------------------------------------------------
            // Planetes ...
            $Order = ($user['planet_sort_order'] == 1) ? "DESC" : "ASC" ;
            $Sort = $user['planet_sort'];

            $QryPlanets = "SELECT * FROM {{table}} WHERE `id_owner` = '" . $user['id'] . "' ORDER BY ";
            if ($Sort == 0) {
                $QryPlanets .= "`id` " . $Order;
            } elseif ($Sort == 1) {
                $QryPlanets .= "`galaxy`, `system`, `planet`, `planet_type` " . $Order;
            } elseif ($Sort == 2) {
                $QryPlanets .= "`name` " . $Order;
            }
            $planets_query = doquery ($QryPlanets, 'planets');
            // Autres planetes : disposition d'OGame classique (et de la 0.8e d'origine), a droite de la planete,
            // deux par ligne : nom, image, construction en cours. Largeurs figees (voir overview_body.tpl).
            $ColoCells = array();
            while ($UserPlanet = mysqli_fetch_array($planets_query)) {
                PlanetResourceUpdate ($user, $UserPlanet, time());
                if ($UserPlanet["id"] != $user["current_planet"] && $UserPlanet['planet_type'] != 3) {
                    $PlanetState = $lang['free'];
                    $PlanetStateTitle = $lang['free'];
                    if ($UserPlanet['b_building'] != 0) {
                        UpdatePlanetBatimentQueueList ($UserPlanet, $user);
                        if ($UserPlanet['b_building'] != 0) {
                            $QueueArray = explode (";", $UserPlanet['b_building_id']);
                            $CurrentBuild = explode (",", $QueueArray[0]);
                            $BuildRestTime = pretty_time($CurrentBuild[3] - time());
                            $PlanetState = $lang['tech'][$CurrentBuild[0]] . ' (' . $CurrentBuild[1] . ')';
                            $PlanetStateTitle = $PlanetState . ' - ' . $BuildRestTime;
                            $PlanetState .= "<br><font color=\"#7f7f7f\">" . $BuildRestTime . "</font>";
                        } else {
                            CheckPlanetUsedFields ($UserPlanet);
                        }
                    }
                    $PlanetTitle = $UserPlanet['name'] . " [" . $UserPlanet['galaxy'] . ":" . $UserPlanet['system'] . ":" . $UserPlanet['planet'] . "] : " . $PlanetStateTitle;
                    $Cell  = "<th class=\"ov_colo\" title=\"" . strip_tags($PlanetTitle) . "\">";
                    $Cell .= "<span class=\"ov_texte\">" . $UserPlanet['name'] . "</span>";
                    $Cell .= "<a href=\"?cp=" . $UserPlanet['id'] . "&re=0\"><img src=\"" . $dpath . "planeten/small/s_" . $UserPlanet['image'] . ".jpg\" height=\"50\" width=\"50\"></a>";
                    $Cell .= "<span class=\"ov_texte\">" . $PlanetState . "</span>";
                    $Cell .= "</th>";
                    $ColoCells[] = $Cell;
                }
            }
            $AllPlanets = "";
            if (count($ColoCells) > 0) {
                $AllPlanets = "<table class=\"s ov_colos\" border=\"0\">";
                foreach (array_chunk($ColoCells, 2) as $Pair) {
                    $AllPlanets .= "<tr>" . implode('', $Pair) . "</tr>";
                }
                $AllPlanets .= "</table>";
            }
            // -----------------------------------------------------------------------------------------------
            // --- Gestion des attaques missiles -------------------------------------------------------------
            $iraks_query = doquery("SELECT * FROM {{table}} WHERE owner = '" . $user['id'] . "'", 'iraks');
            $Record = 4000;
            while ($irak = mysqli_fetch_array($iraks_query)) {
                $Record++;
                $fpage[$irak['zeit']] = '';

                if ($irak['zeit'] > time()) {
                    $time = $irak['zeit'] - time();

                    $fpage[$irak['zeit']] .= InsertJavaScriptChronoApplet ("fm", $Record, $time, true);

                    $planet_start = doquery("SELECT * FROM {{table}} WHERE
						galaxy = '" . $irak['galaxy'] . "' AND
						system = '" . $irak['system'] . "' AND
						planet = '" . $irak['planet'] . "' AND
						planet_type = '1'", 'planets');

                    $user_planet = doquery("SELECT * FROM {{table}} WHERE
						galaxy = '" . $irak['galaxy_angreifer'] . "' AND
						system = '" . $irak['system_angreifer'] . "' AND
						planet = '" . $irak['planet_angreifer'] . "' AND
						planet_type = '1'", 'planets', true);

                    $planet = (mysqli_num_rows($planet_start) == 1) ? mysqli_fetch_array($planet_start) : array('name' => '');

                    // Phrase dans la langue du joueur (elle etait ecrite en dur en francais)
                    $FromLink = '<a href="galaxy.php?mode=3&galaxy=' . $irak["galaxy_angreifer"] . '&system=' . $irak["system_angreifer"] . '&planet=' . $irak["planet_angreifer"] . '">[' . $irak["galaxy_angreifer"] . ':' . $irak["system_angreifer"] . ':' . $irak["planet_angreifer"] . ']</a>';
                    $ToLink   = '<a href="galaxy.php?mode=3&galaxy=' . $irak["galaxy"] . '&system=' . $irak["system"] . '&planet=' . $irak["planet"] . '">[' . $irak["galaxy"] . ':' . $irak["system"] . ':' . $irak["planet"] . ']</a>';
                    $fpage[$irak['zeit']] .= "<tr><th><div id=\"bxxfs". $Record ."\" class=\"z\"></div><font color=\"lime\">" . date("H:i:s", $irak['zeit']) . "</font> </th><th colspan=\"3\"><font color=\"#0099FF\">";
                    $fpage[$irak['zeit']] .= sprintf($lang['ov_missile_attack'], intval($irak['anzahl']), ($user_planet['name'] ?? ''), $FromLink, $planet['name'], $ToLink);
                    $fpage[$irak['zeit']] .= '</font>';
                    $fpage[$irak['zeit']] .= InsertJavaScriptChronoApplet ("fm", $Record, $time, false);
                    $fpage[$irak['zeit']] .= "</th>";
                }
            }
            // -----------------------------------------------------------------------------------------------
            $parse = $lang;
            // -----------------------------------------------------------------------------------------------
            // News Frame ...
            // External Chat Frame ...
            // Banner ADS Google (meme si je suis contre cela)
            if ($game_config['OverviewNewsFrame'] == '1') {
                $parse['news_frame'] = "<tr><th>" . $lang['ov_news_title'] . "</th><th colspan=\"3\">" . stripslashes($game_config['OverviewNewsText']) . "</th></tr>";
            }
            if ($game_config['OverviewExternChat'] == '1') {
                $parse['external_tchat_frame'] = "<tr><th colspan=\"4\">" . stripslashes($game_config['OverviewExternChatCmd']) . "</th></tr>";
            }
            if ($game_config['OverviewClickBanner'] != '') {
                $parse['click_banner'] = stripslashes($game_config['OverviewClickBanner']);
            }
            if ($game_config['ForumBannerFrame'] == '1') {

                $BannerURL = "".dirname($_SERVER["HTTP_REFERER"])."/scripts/createbanner.php?id=".$user['id']."";

                $parse['bannerframe'] = "<th colspan=\"4\"><img src=\"scripts/createbanner.php?id=".$user['id']."\"><br>".$lang['info_banner']."<br><input name=\"bannerlink\" type=\"text\" id=\"bannerlink\" value=\"[img]".$BannerURL."[/img]\" size=\"62\"></th></tr>";
            }
            // --- Gestion de l'affichage d'une lune ---------------------------------------------------------
            if (!empty($lunarow['id'])) {
                if ($planetrow['planet_type'] == 1) {
                    $lune = doquery ("SELECT * FROM {{table}} WHERE `galaxy` = '" . $planetrow['galaxy'] . "' AND `system` = '" . $planetrow['system'] . "' AND `planet` = '" . $planetrow['planet'] . "' AND `planet_type` = '3'", 'planets', true);
                    $parse['moon_img'] = "<a href=\"?cp=" . $lune['id'] . "&re=0\" title=\"" . $lune['name'] . "\"><img src=\"" . $dpath . "planeten/" . $lune['image'] . ".jpg\" height=\"50\" width=\"50\"></a>";
                    $parse['moon'] = $lune['name'];
                } else {
                    $parse['moon_img'] = "";
                    $parse['moon'] = "";
                }
            } else {
                $parse['moon_img'] = "";
                $parse['moon'] = "";
            }
            // Moon END
            $parse['planet_name'] = $planetrow['name'];
            $parse['planet_diameter'] = pretty_number($planetrow['diameter']);
            $parse['planet_field_current'] = $planetrow['field_current'];
            $parse['planet_field_max'] = CalculateMaxPlanetFields($planetrow);
            $parse['planet_temp_min'] = $planetrow['temp_min'];
            $parse['planet_temp_max'] = $planetrow['temp_max'];
            $parse['galaxy_galaxy'] = $planetrow['galaxy'];
            $parse['galaxy_planet'] = $planetrow['planet'];
            $parse['galaxy_system'] = $planetrow['system'];
            $StatRecord = doquery("SELECT * FROM {{table}} WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $user['id'] . "';", 'statpoints', true);

            // Joueur pas encore classe (statistiques jamais calculees) : 0
            $StatRecord = $StatRecord ?: array('build_points' => 0, 'fleet_points' => 0, 'tech_points' => 0, 'total_points' => 0, 'total_rank' => 0, 'total_old_rank' => 0);
            $parse['user_points'] = pretty_number($StatRecord['build_points']);
            $parse['user_fleet'] = pretty_number($StatRecord['fleet_points']);
            $parse['player_points_tech'] = pretty_number($StatRecord['tech_points']);
            $parse['total_points'] = pretty_number($StatRecord['total_points']);

            $parse['user_rank'] = $StatRecord['total_rank'];
            $ile = $StatRecord['total_old_rank'] - $StatRecord['total_rank'];
            if ($ile >= 1) {
                $parse['ile'] = "<font color=lime>+" . $ile . "</font>";
            } elseif ($ile < 0) {
                $parse['ile'] = "<font color=red>-" . $ile . "</font>";
            } elseif ($ile == 0) {
                $parse['ile'] = "<font color=lightblue>" . $ile . "</font>";
            }
            $parse['u_user_rank'] = $StatRecord['total_rank'];
            $parse['user_username'] = $user['username'];

            $flotten = '';
            if (!empty($fpage)) {
                ksort($fpage);
                foreach ($fpage as $time => $content) {
                    $flotten .= $content . "\n";
                }
            }

            $parse['fleet_list'] = $flotten;
            $parse['energy_used'] = $planetrow["energy_max"] - $planetrow["energy_used"];

            $parse['have_new_message'] = $Have_new_message;
            $parse['have_new_level_mineur'] = $HaveNewLevelMineur;
            $parse['have_new_level_raid'] = $HaveNewLevelRaid;
            $parse['time'] = "<div id=\"dateheure\"></div>";
            // Horloge : heure locale du serveur (fuseau de php.ini), exprimee comme une date UTC en millisecondes pour le JavaScript
            $parse['server_clock'] = (time() + date('Z')) * 1000;
            $parse['dpath'] = $dpath;
            $parse['planet_image'] = $planetrow['image'];
            $parse['colonies_list'] = $AllPlanets;
            $parse['max_users'] = $game_config['users_amount'];

            $parse['metal_debris'] = pretty_number($galaxyrow['metal']);
            $parse['crystal_debris'] = pretty_number($galaxyrow['crystal']);
            if (($galaxyrow['metal'] != 0 || $galaxyrow['crystal'] != 0) && $planetrow[$resource[209]] != 0) {
                $parse['get_link'] = " (<a href=\"quickfleet.php?mode=8&g=" . $galaxyrow['galaxy'] . "&s=" . $galaxyrow['system'] . "&p=" . $galaxyrow['planet'] . "&t=2\">" . $lang['type_mission'][8] . "</a>)";
            } else {
                $parse['get_link'] = '';
            }

            if ($planetrow['b_building'] != 0) {
                UpdatePlanetBatimentQueueList ($planetrow, $user);
                if ($planetrow['b_building'] != 0) {
                    $BuildQueue = explode (";", $planetrow['b_building_id']);
                    $CurrBuild = explode (",", $BuildQueue[0]);
                    $RestTime = $planetrow['b_building'] - time();
                    $PlanetID = $planetrow['id'];
                    $Build = InsertBuildListScript ("overview");
                    $Build .= $lang['tech'][$CurrBuild[0]] . ' (' . ($CurrBuild[1]) . ')';
                    $Build .= "<br /><div id=\"blc\" class=\"z\">" . pretty_time($RestTime) . "</div>";
                    $Build .= "\n<script language=\"JavaScript\">";
                    $Build .= "\n	pp = \"" . $RestTime . "\";\n"; // temps necessaire (a compter de maintenant et sans ajouter time() )
                    $Build .= "\n	pk = \"" . 1 . "\";\n"; // id index (dans la liste de construction)
                    $Build .= "\n	pm = \"cancel\";\n"; // mot de controle
                    $Build .= "\n	pl = \"" . $PlanetID . "\";\n"; // id planete
                    $Build .= "\n	t();\n";
                    $Build .= "\n</script>\n";

                    $parse['building'] = $Build;
                } else {
                    $parse['building'] = $lang['free'];
                }
            } else {
                $parse['building'] = $lang['free'];
            }
            $query = doquery('SELECT username FROM {{table}} ORDER BY register_time DESC', 'users', true);
            $parse['last_user'] = $query['username'];
            $query = doquery("SELECT COUNT(DISTINCT(id)) FROM {{table}} WHERE onlinetime>" . (time()-900), 'users', true);
            $parse['online_users'] = $query[0];
            // $count = doquery(","users",true);
            $parse['users_amount'] = $game_config['users_amount'];
            // Rajout d'une barre pourcentage
            // Calcul du pourcentage de remplissage
            $parse['case_pourcentage'] = floor($planetrow["field_current"] / CalculateMaxPlanetFields($planetrow) * 100) . $lang['o_o'];
            // Barre de remplissage
            $parse['case_barre'] = floor($planetrow["field_current"] / CalculateMaxPlanetFields($planetrow) * 100) * 4.0;
            // Couleur de la barre de remplissage
            if ($parse['case_barre'] > (100 * 4.0)) {
                $parse['case_barre'] = 400;
                $parse['case_barre_barcolor'] = '#C00000';
            } elseif ($parse['case_barre'] > (80 * 4.0)) {
                $parse['case_barre_barcolor'] = '#C0C000';
            } else {
                $parse['case_barre_barcolor'] = '#00C000';
            }
            // Mode Améliorations
            $parse['xpminier'] = $user['xpminier'];
            $parse['xpraid'] = $user['xpraid'];
            $parse['lvl_minier'] = $user['lvl_minier'];
            $parse['lvl_raid'] = $user['lvl_raid'];

            $LvlMinier = $user['lvl_minier'];
            $LvlRaid = $user['lvl_raid'];

            $parse['lvl_up_minier'] = $LvlMinier * 5000;
            $parse['lvl_up_raid'] = $LvlRaid * 10;
            // Nombre de raids, pertes, etc ...
            $parse['raids_label'] = $lang['raids_label'];
            $parse['number_of_raids'] = $lang['number_of_raids'];
            $parse['raids_win'] = $lang['raids_win'];
            $parse['raids_loose'] = $lang['raids_loose'];

            $parse['raids'] = intval($user['raids']);
            $parse['raidswin'] = intval($user['raidswin']);
            $parse['raidsloose'] = intval($user['raidsloose']); // NULL tant qu'aucun raid n'est perdu
            // Compteur de Membres en ligne
            $OnlineUsers = doquery("SELECT COUNT(*) FROM {{table}} WHERE onlinetime>='" . (time()-15 * 60) . "'", 'users', 'true');
            $parse['number_members_online'] = $OnlineUsers[0];

            $page = parsetemplate(gettemplate('overview_body'), $parse);

            display($page, $lang['overview'] ?? '');
            break;
        }
}

?>
