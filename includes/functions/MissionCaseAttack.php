<?php

/**
 * MissionCaseAttack.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 By Chorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */
// ----------------------------------------------------------------------------------------------------------------
// Mission Case 1: -> Attaquer ; Mission Case 2: -> Attaque groupee (0.9i)
//
// A l'arrivee, un seul combat : la flotte seule, ou toutes les flottes de son groupe (attaque groupee), contre la
// planete (vaisseaux et defenses du proprietaire) et les flottes alliees qui y stationnent (defense groupee). Pour
// une attaque ordinaire, deroulement de l'original (memes requetes, memes tirages, meme butin, meme rapport). Le
// retour de chaque flotte est traite a un passage suivant, d'apres la flotte mise a jour : dans l'original, un combat
// et un retour traites au meme passage (joueurs absents) faisaient perdre le butin.
function MissionCaseAttack ($FleetRow)
{
    global $phpEx, $lang, $CombatCaps;

    if ($FleetRow['fleet_start_time'] <= time()) {
        if ($FleetRow['fleet_mess'] == 0) {
            if (!isset($CombatCaps[202]['sd'])) {
                message("<font color=\"red\">" . $lang['sys_no_vars'] . "</font>", $lang['sys_error'], "fleet." . $phpEx, 2);
            }
            MissionCaseAttackBattle($FleetRow);
        } elseif ($FleetRow['fleet_end_time'] <= time()) {
            // Retour de flotte : vaisseaux restants et chargement (butin compris)
            RestoreFleetToPlanet($FleetRow, true);
            doquery("DELETE FROM {{table}} WHERE `fleet_id` = '" . $FleetRow["fleet_id"] . "';", 'fleets');
        }
    }
}

// Combat a l'arrivee d'une attaque ou d'une attaque groupee
function MissionCaseAttackBattle ( $FleetRow ) {
    global $pricelist, $lang, $resource;

    // Flottes qui attaquent : la flotte seule, ou tout son groupe arrive (le chef d'abord, puis dans l'ordre d'envoi)
    $GroupId    = intval($FleetRow['fleet_group']);
    $AttackRows = array();
    if ($GroupId > 0) {
        $Query = doquery("SELECT * FROM {{table}} WHERE `fleet_group` = '" . $GroupId . "' AND `fleet_mess` = '0' AND `fleet_start_time` <= '" . time() . "' ORDER BY `fleet_mission` ASC, `fleet_id` ASC;", 'fleets');
        while ($Row = mysqli_fetch_assoc($Query)) {
            $AttackRows[] = $Row;
        }
    }
    if (count($AttackRows) == 0) {
        $AttackRows[] = $FleetRow;
    }
    $Arrival = $FleetRow['fleet_start_time'];

    $QryTargetPlanet = "SELECT * FROM {{table}} ";
    $QryTargetPlanet .= "WHERE ";
    $QryTargetPlanet .= "`galaxy` = '" . $FleetRow['fleet_end_galaxy'] . "' AND ";
    $QryTargetPlanet .= "`system` = '" . $FleetRow['fleet_end_system'] . "' AND ";
    $QryTargetPlanet .= "`planet` = '" . $FleetRow['fleet_end_planet'] . "' AND ";
    $QryTargetPlanet .= "`planet_type` = '" . $FleetRow['fleet_end_type'] . "';";
    $TargetPlanet = doquery($QryTargetPlanet, 'planets', true);
    if (!$TargetPlanet) {
        // Cible disparue (colonie abandonnee, compte supprime) : demi-tour sans combat
        foreach ($AttackRows as $Row) {
            doquery("UPDATE {{table}} SET `fleet_mess` = '1', `fleet_group` = '0' WHERE `fleet_id` = '" . $Row['fleet_id'] . "';", 'fleets');
        }
        if ($GroupId > 0) {
            doquery("DELETE FROM {{table}} WHERE `id` = '" . $GroupId . "';", 'aks');
        }
        return;
    }
    $TargetUserID = $TargetPlanet['id_owner'];
    $TargetUser   = doquery("SELECT * FROM {{table}} WHERE `id` = '" . $TargetUserID . "';", 'users', true);

    // Participants : chaque flotte qui attaque, avec les technologies de son joueur
    $Users     = array();
    $Attackers = array();
    $AttInfo   = array();
    foreach ($AttackRows as $i => $Row) {
        if (!isset($Users[$Row['fleet_owner']])) {
            $Users[$Row['fleet_owner']] = doquery("SELECT * FROM {{table}} WHERE `id` = '" . $Row['fleet_owner'] . "';", 'users', true);
        }
        $Owner = $Users[$Row['fleet_owner']];
        $Attackers[$i] = array('fleet' => MissionCaseAttackFleet($Row['fleet_array']), 'techno' => $Owner);
        $AttInfo[$i]   = array('name' => $Owner['username'], 'galaxy' => $Row['fleet_start_galaxy'], 'system' => $Row['fleet_start_system'], 'planet' => $Row['fleet_start_planet'], 'techno' => $Owner);
    }

    // Defenseurs : le proprietaire (vaisseaux et defenses de la planete), puis les flottes alliees en stationnement
    // pendant leur duree (defense groupee, 0.9i) ; un stationnement arrive mais pas encore traite compte aussi
    $TargetFleet = array();
    for ($SetItem = 200; $SetItem < 500; $SetItem++) {
        if (isset($resource[$SetItem]) && $TargetPlanet[$resource[$SetItem]] > 0) {
            $TargetFleet[$SetItem] = $TargetPlanet[$resource[$SetItem]];
        }
    }
    $Defenders  = array(0 => array('fleet' => $TargetFleet, 'techno' => $TargetUser));
    $DefInfo    = array(0 => array('name' => $TargetUser['username'], 'galaxy' => $FleetRow['fleet_end_galaxy'], 'system' => $FleetRow['fleet_end_system'], 'planet' => $FleetRow['fleet_end_planet'], 'techno' => $TargetUser));
    $HoldRows   = array();
    foreach (AcsHoldingFleets($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $FleetRow['fleet_end_type'], $Arrival) as $Row) {
        if (!isset($Users[$Row['fleet_owner']])) {
            $Users[$Row['fleet_owner']] = doquery("SELECT * FROM {{table}} WHERE `id` = '" . $Row['fleet_owner'] . "';", 'users', true);
        }
        $Owner = $Users[$Row['fleet_owner']];
        $i = count($Defenders);
        $HoldRows[$i]  = $Row;
        $Defenders[$i] = array('fleet' => MissionCaseAttackFleet($Row['fleet_array']), 'techno' => $Owner);
        $DefInfo[$i]   = array('name' => $Owner['username'], 'galaxy' => $Row['fleet_start_galaxy'], 'system' => $Row['fleet_start_system'], 'planet' => $Row['fleet_start_planet'], 'techno' => $Owner);
    }

    // Calcul de la duree de traitement (initialisation)
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    // Moteur de combat de Renaissance (0.9i) : memes resultats que l'original (includes/ataki.php, retire)
    $Combat = CombatEngine($Attackers, $Defenders);
    // Calcul de la duree de traitement (calcul)
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = ($endtime - $starttime);
    // Le resultat de la bataille
    $FleetResult = $Combat['result'];
    // Rapport court (cdr + unités perdues)
    $zlom = array('metal' => $Combat['debris']['metal'], 'crystal' => $Combat['debris']['crystal'],
                  'atakujacy' => $Combat['lost']['att'], 'wrog' => $Combat['lost']['def']);

    // Place libre dans chaque flotte qui attaque (vaisseaux restants, moins ce qu'elle transporte deja)
    $Storage      = array();
    $FleetStorage = 0;
    foreach ($AttackRows as $i => $Row) {
        $Space = 0;
        foreach ($Combat['attackers'][$i] as $Ship => $Count) {
            $Space += $pricelist[$Ship]["capacity"] * $Count;
        }
        // Au cas ou le p'tit rigolo qu'a envoyé la flotte y avait mis des ressources ...
        $Space -= $Row["fleet_resource_metal"];
        $Space -= $Row["fleet_resource_crystal"];
        $Space -= $Row["fleet_resource_deuterium"];
        $Storage[$i]   = max(0, $Space);
        $FleetStorage += $Storage[$i];
    }

    // Determination des ressources pillées (place de tout le groupe), puis partage au prorata de la place de chaque
    // flotte (le reste de l'arrondi a la flotte qui a le plus de place)
    $Mining['metal'] = 0;
    $Mining['crystal'] = 0;
    $Mining['deuter'] = 0;
    if ($FleetResult == "a") {
        if ($FleetStorage > 0) {
            $metal = $TargetPlanet['metal'] / 2;
            $crystal = $TargetPlanet['crystal'] / 2;
            $deuter = $TargetPlanet["deuterium"] / 2;
            if (($metal) > $FleetStorage / 3) {
                $Mining['metal'] = $FleetStorage / 3;
                $FleetStorage = $FleetStorage - $Mining['metal'];
            } else {
                $Mining['metal'] = $metal;
                $FleetStorage = $FleetStorage - $Mining['metal'];
            }

            if (($crystal) > $FleetStorage / 2) {
                $Mining['crystal'] = $FleetStorage / 2;
                $FleetStorage = $FleetStorage - $Mining['crystal'];
            } else {
                $Mining['crystal'] = $crystal;
                $FleetStorage = $FleetStorage - $Mining['crystal'];
            }

            if (($deuter) > $FleetStorage) {
                $Mining['deuter'] = $FleetStorage;
                $FleetStorage = $FleetStorage - $Mining['deuter'];
            } else {
                $Mining['deuter'] = $deuter;
                $FleetStorage = $FleetStorage - $Mining['deuter'];
            }
        }
    }
    $Mining['metal'] = round($Mining['metal']);
    $Mining['crystal'] = round($Mining['crystal']);
    $Mining['deuter'] = round($Mining['deuter']);
    $TotalSpace = array_sum($Storage);
    $Biggest    = array_search(max($Storage), $Storage);
    $Share      = array();
    foreach (array('metal', 'crystal', 'deuter') as $Res) {
        $Left = $Mining[$Res];
        foreach ($AttackRows as $i => $Row) {
            $Share[$i][$Res] = ($i != $Biggest && $TotalSpace > 0) ? floor($Mining[$Res] * $Storage[$i] / $TotalSpace) : 0;
            $Left -= $Share[$i][$Res];
        }
        $Share[$Biggest][$Res] = $Left;
    }

    // Mise a jour de l'enregistrement de la planete attaquée (defenses reconstruites en partie)
    $TargetPlanetUpd = "";
    foreach($Combat['defenders'][0] as $Ship => $Count) {
        $TargetPlanetUpd .= "`" . $resource[$Ship] . "` = '" . $Count . "', ";
    }
    $QryUpdateTarget = "UPDATE {{table}} SET ";
    $QryUpdateTarget .= $TargetPlanetUpd;
    $QryUpdateTarget .= "`metal` = `metal` - '" . $Mining['metal'] . "', ";
    $QryUpdateTarget .= "`crystal` = `crystal` - '" . $Mining['crystal'] . "', ";
    $QryUpdateTarget .= "`deuterium` = `deuterium` - '" . $Mining['deuter'] . "', ";
    // Compte des flottes (0.9k, voir PlanetResourceUpdate)
    $QryUpdateTarget .= "`metal_fleets` = `metal_fleets` - '" . $Mining['metal'] . "', ";
    $QryUpdateTarget .= "`crystal_fleets` = `crystal_fleets` - '" . $Mining['crystal'] . "', ";
    $QryUpdateTarget .= "`deuterium_fleets` = `deuterium_fleets` - '" . $Mining['deuter'] . "' ";
    $QryUpdateTarget .= "WHERE ";
    $QryUpdateTarget .= "`galaxy` = '" . $FleetRow['fleet_end_galaxy'] . "' AND ";
    $QryUpdateTarget .= "`system` = '" . $FleetRow['fleet_end_system'] . "' AND ";
    $QryUpdateTarget .= "`planet` = '" . $FleetRow['fleet_end_planet'] . "' AND ";
    $QryUpdateTarget .= "`planet_type` = '" . $FleetRow['fleet_end_type'] . "' ";
    $QryUpdateTarget .= "LIMIT 1;";
    doquery($QryUpdateTarget , 'planets');

    // Flottes alliees en stationnement : pertes (une flotte detruite disparait, les autres continuent de stationner)
    AcsUpdateHoldingFleets($HoldRows, $Combat['defenders']);

    // Mise a jour du champ de ruine devant la planete attaquée
    $QryUpdateGalaxy = "UPDATE {{table}} SET ";
    $QryUpdateGalaxy .= "`metal` = `metal` + '" . $zlom['metal'] . "', ";
    $QryUpdateGalaxy .= "`crystal` = `crystal` + '" . $zlom['crystal'] . "' ";
    $QryUpdateGalaxy .= "WHERE ";
    $QryUpdateGalaxy .= "`galaxy` = '" . $FleetRow['fleet_end_galaxy'] . "' AND ";
    $QryUpdateGalaxy .= "`system` = '" . $FleetRow['fleet_end_system'] . "' AND ";
    $QryUpdateGalaxy .= "`planet` = '" . $FleetRow['fleet_end_planet'] . "' ";
    $QryUpdateGalaxy .= "LIMIT 1;";
    doquery($QryUpdateGalaxy , 'galaxy');
    // Là on va discuter le bout de gras pour voir s'il y a moyen d'avoir une Lune !
    $FleetDebris = $zlom['metal'] + $zlom['crystal'];
    $StrAttackerUnits = sprintf ($lang['sys_attacker_lostunits'], pretty_number ($zlom["atakujacy"]));
    $StrDefenderUnits = sprintf ($lang['sys_defender_lostunits'], pretty_number ($zlom["wrog"]));
    $StrRuins = sprintf ($lang['sys_gcdrunits'], pretty_number ($zlom["metal"]), $lang['metal_label'], pretty_number ($zlom['crystal']), $lang['crystal_label']);
    $DebrisField = $StrAttackerUnits . "<br />" . $StrDefenderUnits . "<br />" . $StrRuins;
    $MoonChance = $FleetDebris / 100000;
    if ($FleetDebris > 2000000) {
        $MoonChance = 20;
    }
    if ($FleetDebris < 100000) {
        $UserChance = 0;
        $ChanceMoon = "";
    } elseif ($FleetDebris >= 100000) {
        $UserChance = mt_rand(1, 100);
        $ChanceMoon = sprintf ($lang['sys_moonproba'], $MoonChance);
    }

    // Lune deja presente ? ($galenemyrow n'etait jamais defini dans l'original : le test passait toujours)
    $galenemyrow = doquery("SELECT `id_luna` FROM {{table}} WHERE `galaxy` = '". intval($FleetRow['fleet_end_galaxy']) ."' AND `system` = '". intval($FleetRow['fleet_end_system']) ."' AND `planet` = '". intval($FleetRow['fleet_end_planet']) ."';", 'galaxy', true);
    if (($UserChance > 0) and ($UserChance <= $MoonChance) and empty($galenemyrow['id_luna'])) {
        $TargetPlanetName = CreateOneMoonRecord ($FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet'], $TargetUserID, $FleetRow['fleet_start_time'], '', $MoonChance);
        $GottenMoon = sprintf ($lang['sys_moonbuilt'], $TargetPlanetName, $FleetRow['fleet_end_galaxy'], $FleetRow['fleet_end_system'], $FleetRow['fleet_end_planet']);
    } else {
        // Aussi quand la lune existe deja (variable non definie avant : avertissement PHP, meme rapport)
        $GottenMoon = "";
    }

    $AttackDate = date("d/m/Y H:i:s", $FleetRow["fleet_start_time"]);
    $title = sprintf ($lang['sys_attack_title'], $AttackDate);
    $raport = "<center><table><tr><td>" . $title . "<br />";
    // Tours du rapport (un cadre par joueur, voir CombatReport.php)
    $Rounds = CombatReportRounds($Combat, $AttInfo, $DefInfo);
    $raport .= $Rounds['html'];
    $a_zestrzelona = $Rounds['first_round'];

    switch ($FleetResult) {
        case "a":
            $Pillage = sprintf ($lang['sys_stealed_ressources'], pretty_number ($Mining['metal']), $lang['metal_label'], pretty_number ($Mining['crystal']), $lang['crystal_label'], pretty_number ($Mining['deuter']), $lang['deuterium_label']);
            $raport .= $lang['sys_attacker_won'] . "<br />" . $Pillage . "<br />";
            $raport .= $DebrisField . "<br />";
            $raport .= $ChanceMoon . "<br />";
            $raport .= $GottenMoon . "<br />";
            break;
        case "r":
            $raport .= $lang['sys_both_won'] . "<br />";
            $raport .= $DebrisField . "<br />";
            $raport .= $ChanceMoon . "<br />";
            $raport .= $GottenMoon . "<br />";
            break;
        case "w":
            $raport .= $lang['sys_defender_won'] . "<br />";
            $raport .= $DebrisField . "<br />";
            $raport .= $ChanceMoon . "<br />";
            $raport .= $GottenMoon . "<br />";
            break;
        default:
            break;
    }
    $SimMessage = sprintf ($lang['sys_rapport_build_time'], number_format($totaltime, 5, ',', ''));
    $raport .= $SimMessage . "</table>";

    // Rapport enregistre pour chaque joueur qui attaque (son lien a lui : un attaquant detruit des le premier tour ne
    // voit pas le detail), le premier servant aussi aux defenseurs
    $Rids = array();
    foreach ($AttackRows as $i => $Row) {
        if (isset($Rids[$Row['fleet_owner']])) {
            continue;
        }
        $rid = (count($Rids) == 0) ? md5($raport) : md5($raport . '-' . $Row['fleet_owner']);
        $Rids[$Row['fleet_owner']] = $rid;
        $QryInsertRapport = "INSERT INTO {{table}} SET ";
        $QryInsertRapport .= "`time` = UNIX_TIMESTAMP(), ";
        $QryInsertRapport .= "`id_owner1` = '" . $Row['fleet_owner'] . "', ";
        $QryInsertRapport .= "`id_owner2` = '" . $TargetUserID . "', ";
        $QryInsertRapport .= "`rid` = '" . $rid . "', ";
        $QryInsertRapport .= "`a_zestrzelona` = '" . $a_zestrzelona . "', ";
        $QryInsertRapport .= "`raport` = '" . addslashes ($raport) . "';";
        doquery($QryInsertRapport , 'rw');
    }
    $FirstRid = reset($Rids);
    $Color = array("a" => "green", "r" => "orange", "w" => "red");
    $Coords = " [" . $FleetRow['fleet_end_galaxy'] . ":" . $FleetRow['fleet_end_system'] . ":" . $FleetRow['fleet_end_planet'] . "] ";

    // Flottes qui attaquent : vaisseaux restants, butin, retour ; flotte detruite supprimee
    $Loot = array();
    foreach ($AttackRows as $i => $Row) {
        $FleetArray  = "";
        $FleetAmount = 0;
        foreach ($Combat['attackers'][$i] as $Ship => $Count) {
            $FleetArray  .= $Ship . "," . $Count . ";";
            $FleetAmount += $Count;
        }
        foreach (array('metal', 'crystal', 'deuter') as $Res) {
            $Loot[$Row['fleet_owner']][$Res] = ($Loot[$Row['fleet_owner']][$Res] ?? 0) + $Share[$i][$Res];
        }
        if ($FleetResult == "w" || $FleetAmount <= 0) {
            doquery("DELETE FROM {{table}} WHERE `fleet_id` = '" . $Row["fleet_id"] . "';", 'fleets');
            continue;
        }
        $QryUpdateFleet = "UPDATE {{table}} SET ";
        $QryUpdateFleet .= "`fleet_amount` = '" . $FleetAmount . "', ";
        $QryUpdateFleet .= "`fleet_array` = '" . $FleetArray . "', ";
        $QryUpdateFleet .= "`fleet_mess` = '1', ";
        $QryUpdateFleet .= "`fleet_group` = '0', ";
        $QryUpdateFleet .= "`fleet_resource_metal` = '" . ($Share[$i]['metal'] + $Row["fleet_resource_metal"]) . "', ";
        $QryUpdateFleet .= "`fleet_resource_crystal` = '" . ($Share[$i]['crystal'] + $Row["fleet_resource_crystal"]) . "', ";
        $QryUpdateFleet .= "`fleet_resource_deuterium` = '" . ($Share[$i]['deuter'] + $Row["fleet_resource_deuterium"]) . "' ";
        $QryUpdateFleet .= "WHERE fleet_id = '" . $Row['fleet_id'] . "' ";
        $QryUpdateFleet .= "LIMIT 1 ;";
        doquery($QryUpdateFleet , 'fleets');
    }
    if ($GroupId > 0) {
        doquery("DELETE FROM {{table}} WHERE `id` = '" . $GroupId . "';", 'aks');
    }

    // Chaque joueur qui attaque : resume (son butin), point de Raideur, compteur de raids
    foreach ($Rids as $OwnerId => $rid) {
        $CurrentUser = $Users[$OwnerId];
        // Colorisation du résumé de rapport pour l'attaquant
        $Summary = "<center><a href=\"rw.php?raport=". $rid ."\">"; // <center> avant le lien (HTML valide)
        $Summary .= "<font color=\"" . $Color[$FleetResult] . "\">";
        $Summary .= $lang['sys_mess_attack_report'] . $Coords . "</font></a><br /><br />";
        $Summary .= "<font color=\"red\">" . $lang['sys_perte_attaquant'] . ": " . pretty_number ($zlom["atakujacy"]) . "</font>";
        $Summary .= "<font color=\"green\">   " . $lang['sys_perte_defenseur'] . ":" . pretty_number ($zlom["wrog"]) . "</font><br />" ;
        $Summary .= $lang['sys_gain'] . " " . $lang['metal_label'] . ":<font color=\"#adaead\">" . pretty_number ($Loot[$OwnerId]['metal']) . "</font>   " . $lang['crystal_label'] . ":<font color=\"#ef51ef\">" . pretty_number ($Loot[$OwnerId]['crystal']) . "</font>   " . $lang['deuterium_label'] . ":<font color=\"#f77542\">" . pretty_number ($Loot[$OwnerId]['deuter']) . "</font><br />";
        $Summary .= $lang['sys_debris'] . " " . $lang['metal_label'] . ":<font color=\"#adaead\">" . pretty_number ($zlom['metal']) . "</font>   " . $lang['crystal_label'] . ":<font color=\"#ef51ef\">" . pretty_number ($zlom['crystal']) . "</font><br /></center>";
        SendSimpleMessage ($OwnerId, '', $FleetRow['fleet_start_time'], 3, $lang['sys_mess_tower'], $lang['sys_mess_attack_report'], $Summary);

        // Ajout du petit point raideur
        doquery("UPDATE {{table}} SET `xpraid` = '" . ($CurrentUser['xpraid'] + 1) . "' WHERE id = '" . $OwnerId . "' LIMIT 1 ;", 'users');
        // Ajout d'un point au compteur de raids (l'original ecrivait les raids perdus dans `raidswin`)
        $RaidsTotal = $CurrentUser['raids'] + 1;
        if ($FleetResult == "a") {
            doquery("UPDATE {{table}} SET `raidswin` ='" . ($CurrentUser['raidswin'] + 1) . "', `raids` ='" . $RaidsTotal . "' WHERE id = '" . $OwnerId . "' LIMIT 1 ;", 'users');
        } elseif ($FleetResult == "r" || $FleetResult == "w") {
            doquery("UPDATE {{table}} SET `raidsloose` ='" . ($CurrentUser['raidsloose'] + 1) . "', `raids` ='" . $RaidsTotal . "' WHERE id = '" . $OwnerId . "' LIMIT 1 ;", 'users');
        }
    }

    // Defenseurs : le proprietaire, puis chaque joueur qui stationnait (une fois chacun)
    $Summary = "<center><a href=\"rw.php?raport=". $FirstRid ."\">"; // <center> avant le lien (HTML valide)
    $Summary .= "<font color=\"" . $Color[$FleetResult] . "\">";
    $Summary .= $lang['sys_mess_attack_report'] . $Coords . "</font></a><br /><br />";
    $Warned = array($TargetUserID => true);
    SendSimpleMessage ($TargetUserID, '', $FleetRow['fleet_start_time'], 3, $lang['sys_mess_tower'], $lang['sys_mess_attack_report'], $Summary);
    foreach ($HoldRows as $Row) {
        if (!isset($Warned[$Row['fleet_owner']])) {
            $Warned[$Row['fleet_owner']] = true;
            SendSimpleMessage ($Row['fleet_owner'], '', $FleetRow['fleet_start_time'], 3, $lang['sys_mess_tower'], $lang['sys_mess_attack_report'], $Summary);
        }
    }
}

// Vaisseaux d'une flotte (champ fleet_array : « type,nombre; ... »), nombres tels qu'enregistres
function MissionCaseAttackFleet ( $FleetArray ) {
    $Fleet = array();
    foreach (explode(";", $FleetArray) as $Item) {
        if ($Item != '') {
            $Parts = explode(",", $Item);
            $Fleet[$Parts[0]] = $Parts[1];
        }
    }
    return $Fleet;
}

?>
