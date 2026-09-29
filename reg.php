<?php

/**
 * reg.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.1
 * @copyright 2008 by Chlorel for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE' , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

includeLang('reg');

// Mail de bienvenue : rappelle le pseudo, jamais le mot de passe (avant : envoye en clair)
function sendpassemail($emailaddress, $username)
{
    global $lang;

    // Adresse du jeu reglee (game_url), pas l'en-tete Host envoye par le visiteur
    $parse['gameurl']  = GameUrl();
    $parse['username'] = $username;
    $email = parsetemplate($lang['mail_welcome'], $parse);
    // Mail en texte brut : vrais retours a la ligne (les \n des textes etaient envoyes tels quels) et accents decodes
    $email = html_entity_decode(str_replace('\n', "\n", $email), ENT_QUOTES, 'UTF-8');
    $status = mymail($emailaddress, $lang['mail_title'], $email);
    return $status;
}

// (mymail() est dans includes/functions/SendGameMail.php, commune avec le mot de passe oublie)

if ($_POST) {
    // Captcha (mod de theptitprince), verifie avant tout le reste : sans code valide, rien n'est revele (pseudo ou
    // adresse deja pris) et un robot ne peut pas envoyer le formulaire en masse
    if (CaptchaEnabled() && !CaptchaCheck(is_string($_POST['captcha'] ?? null) ? $_POST['captcha'] : '')) {
        message($lang['error_captcha'], $lang['register']);
    }

    $errors = 0;
    $errorlist = "";
    // Champs recus en texte seulement (un tableau faisait une erreur fatale dans strip_tags, preg_match...)
    foreach (array('character', 'email', 'passwrd', 'planet', 'hplanet', 'sex', 'rgt') as $Field) {
        $_POST[$Field] = is_string($_POST[$Field] ?? null) ? $_POST[$Field] : '';
    }

    $_POST['email'] = strip_tags(($_POST['email'] ?? null));
    if (!is_email(($_POST['email'] ?? null))) {
        $errorlist .= "\"" . ($_POST['email'] ?? null) . "\" " . $lang['error_mail'];
        $errors++;
    }

    if (!($_POST['planet'] ?? null)) {
        $errorlist .= $lang['error_planet'];
        $errors++;
    }

    if (preg_match("/[^A-Za-z0-9_\-]/", (string) ($_POST['hplanet'] ?? '')) == 1) {
        $errorlist .= $lang['error_planetnum'];
        $errors++;
    }

    if (!($_POST['character'] ?? null)) {
        $errorlist .= $lang['error_character'];
        $errors++;
    }

    // 8 caracteres au moins (4 dans l'original), meme regle que le changement de mot de passe des options
    if (mb_strlen((string) ($_POST['passwrd'] ?? '')) < 8) {
        $errorlist .= $lang['error_password'];
        $errors++;
    }

    if (preg_match("/[^A-Za-z0-9_\-]/", ($_POST['character'] ?? null)) == 1) {
        $errorlist .= $lang['error_charalpha'];
        $errors++;
    }

    // Longueur maximale : celle de la colonne username (au-dela, pseudo tronque, compte sans planete mere)
    if (strlen($_POST['character']) > USERNAME_MAX_LENGTH) {
        $errorlist .= $lang['error_charlength'];
        $errors++;
    }

    if (($_POST['rgt'] ?? null) != 'on') {
        $errorlist .= $lang['error_rgt'];
        $errors++;
    }
    // Deux inscriptions simultanees du meme pseudo : verrou nomme (propre a cette base) du controle jusqu'a la
    // creation du compte, libere a la fin de la page ; l'index unique de la 0.9k protege aussi la table
    doquery("SELECT GET_LOCK(CONCAT(DATABASE(), '.{{table}}.reg'), 10);", 'users');
    // Le meilleur moyen de voir si un nom d'utilisateur est pris c'est d'essayer de l'appeler !!
    $ExistUser = doquery("SELECT `username` FROM {{table}} WHERE `username` = '" . SqlEscape(($_POST['character'] ?? null)) . "' LIMIT 1;", 'users', true);
    if ($ExistUser) {
        $errorlist .= $lang['error_userexist'];
        $errors++;
    }
    // Si l'on verifiait que l'adresse email n'existe pas encore ???
    $ExistMail = doquery("SELECT `email` FROM {{table}} WHERE `email` = '" . SqlEscape(($_POST['email'] ?? null)) . "' LIMIT 1;", 'users', true);
    if ($ExistMail) {
        $errorlist .= $lang['error_emailexist'];
        $errors++;
    }

    if (($_POST['sex'] ?? null) != '' && ($_POST['sex'] ?? null) != 'F' && ($_POST['sex'] ?? null) != 'M') {
        $errorlist .= $lang['error_sex'];
        $errors++;
    }

    if ($errors != 0) {
        message ($errorlist, $lang['register']);
    } else {
        $newpass = ($_POST['passwrd'] ?? null);
        // Pseudo et adresse deja valides plus haut (A-Z a-z 0-9 _ - / is_email). L'ancien filtre de mots remplacait
        // « script », « http »... par « * » : « scripttest » devenait « *test », compte impossible a utiliser
        // (connexion avec le nom tape, planete creee sans proprietaire) et doublons possibles.
        $UserName = ($_POST['character'] ?? '');
        $UserEmail = trim($_POST['email'] ?? '');
        $UserPlanet = SqlEscape(SafeName(($_POST['planet'] ?? ''), 32));

        $md5newpass = PasswordHash($newpass);
        // Creation de l'utilisateur
        // INSERT IGNORE : pseudo pris entre-temps (index unique) = aucune ligne creee, refus normal
        $QryInsertUser = "INSERT IGNORE INTO {{table}} SET ";
        $QryInsertUser .= "`username` = '" . SqlEscape(strip_tags($UserName)) . "', ";
        $QryInsertUser .= "`email` = '" . SqlEscape($UserEmail) . "', ";
        $QryInsertUser .= "`email_2` = '" . SqlEscape($UserEmail) . "', ";
        $QryInsertUser .= "`sex` = '" . SqlEscape(($_POST['sex'] ?? null)) . "', ";
		$QryInsertUser .= "`ip_at_reg` = '" . SqlEscape($_SERVER["REMOTE_ADDR"] ?? '') . "', ";
        $QryInsertUser .= "`id_planet` = '0', ";
        $QryInsertUser .= "`register_time` = '" . time() . "', ";
        $QryInsertUser .= "`password`='" . SqlEscape($md5newpass) . "';";
        doquery($QryInsertUser, 'users');
        if (mysqli_affected_rows(DbConnect()) != 1) {
            message ($lang['error_userexist'], $lang['register']);
        }
        // Numero d'enregistrement de l'utilisateur fraichement créé (avant : relu par le pseudo, rien trouve si le
        // pseudo avait ete tronque, et la planete etait creee sans proprietaire)
        $NewUser = array('id' => mysqli_insert_id(DbConnect()));
        $iduser = $NewUser['id'];
        doquery("SELECT RELEASE_LOCK(CONCAT(DATABASE(), '.{{table}}.reg'));", 'users');
        // Recherche d'une place libre !
        $LastSettedGalaxyPos = $game_config['LastSettedGalaxyPos'];
        $LastSettedSystemPos = $game_config['LastSettedSystemPos'];
        $LastSettedPlanetPos = $game_config['LastSettedPlanetPos'];
        while (!isset($newpos_checked)) {
            for ($Galaxy = $LastSettedGalaxyPos; $Galaxy <= MAX_GALAXY_IN_WORLD; $Galaxy++) {
                for ($System = $LastSettedSystemPos; $System <= MAX_SYSTEM_IN_GALAXY; $System++) {
                    for ($Posit = $LastSettedPlanetPos; $Posit <= 4; $Posit++) {
                        $Planet = round (rand (4, 12));

                        switch ($LastSettedPlanetPos) {
                            case 1:
                                $LastSettedPlanetPos += 1;
                                break;
                            case 2:
                                $LastSettedPlanetPos += 1;
                                break;
                            case 3:
                                if ($LastSettedSystemPos == MAX_SYSTEM_IN_GALAXY) {
                                    $LastSettedGalaxyPos += 1;
                                    $LastSettedSystemPos = 1;
                                    $LastSettedPlanetPos = 1;
                                    break;
                                } else {
                                    $LastSettedPlanetPos = 1;
                                }
                                $LastSettedSystemPos += 1;
                                break;
                        }
                        break;
                    }
                    break;
                }
                break;
            }

            $QrySelectGalaxy = "SELECT * ";
            $QrySelectGalaxy .= "FROM {{table}} ";
            $QrySelectGalaxy .= "WHERE ";
            $QrySelectGalaxy .= "`galaxy` = '" . $Galaxy . "' AND ";
            $QrySelectGalaxy .= "`system` = '" . $System . "' AND ";
            $QrySelectGalaxy .= "`planet` = '" . $Planet . "' ";
            $QrySelectGalaxy .= "LIMIT 1;";
            $GalaxyRow = doquery($QrySelectGalaxy, 'galaxy', true);

            // Place libre : pas de ligne dans la galaxie, ou une ligne sans planete (id_planet = 0). Dans ce second
            // cas l'original validait la place sans creer de planete (compte sans planete mere). Place prise :
            // on passe a la suivante ($newpos_checked non defini, avertissement PHP auparavant).
            if (!$GalaxyRow || $GalaxyRow["id_planet"] == "0") {
                if (CreateOnePlanetRecord ($Galaxy, $System, $Planet, $NewUser['id'], $UserPlanet, true)) {
                    $newpos_checked = true;
                }
            }
            if (!empty($newpos_checked)) {
                doquery("UPDATE {{table}} SET `config_value` = '" . $LastSettedGalaxyPos . "' WHERE `config_name` = 'LastSettedGalaxyPos';", 'config');
                doquery("UPDATE {{table}} SET `config_value` = '" . $LastSettedSystemPos . "' WHERE `config_name` = 'LastSettedSystemPos';", 'config');
                doquery("UPDATE {{table}} SET `config_value` = '" . $LastSettedPlanetPos . "' WHERE `config_name` = 'LastSettedPlanetPos';", 'config');
            }
        }
        // Recherche de la reference de la nouvelle planete (qui est unique normalement !
        $PlanetID = doquery("SELECT `id` FROM {{table}} WHERE `id_owner` = '" . $NewUser['id'] . "' LIMIT 1;", 'planets', true);
        // Mise a jour de l'enregistrement utilisateur avec les infos de sa planete mere
        $QryUpdateUser = "UPDATE {{table}} SET ";
        $QryUpdateUser .= "`id_planet` = '" . $PlanetID['id'] . "', ";
        $QryUpdateUser .= "`current_planet` = '" . $PlanetID['id'] . "', ";
        $QryUpdateUser .= "`galaxy` = '" . $Galaxy . "', ";
        $QryUpdateUser .= "`system` = '" . $System . "', ";
        $QryUpdateUser .= "`planet` = '" . $Planet . "' ";
        $QryUpdateUser .= "WHERE ";
        $QryUpdateUser .= "`id` = '" . $NewUser['id'] . "' ";
        $QryUpdateUser .= "LIMIT 1;";
        doquery($QryUpdateUser, 'users');
        // Envois d'un message in-game sympa ^^
        $from = $lang['sender_message_ig'];
        $sender = "Admin";
        $Subject = $lang['subject_message_ig'];
        $message = $lang['text_message_ig'];
        SendSimpleMessage($iduser, $sender, time(), 1, $from, $Subject, $message);

        // Mise a jour du nombre de joueurs inscripts
        doquery("UPDATE {{table}} SET `config_value` = `config_value` + '1' WHERE `config_name` = 'users_amount' LIMIT 1;", 'config');

        $Message = $lang['thanksforregistry'];
        if (sendpassemail(($_POST['email'] ?? null), $UserName)) {
            $Message .= " (" . htmlentities(($_POST["email"] ?? null)) . ")";
        } else {
            $Message .= " (" . htmlentities(($_POST["email"] ?? null)) . ")";
            $Message .= "<br><br>" . $lang['error_mailsend']; // le mot de passe n'est plus affiche en clair
        }
        $Message .= "<br><br><a href=\"login.php\">". $lang['reg_go_login'] ."</a>";
        message($Message, $lang['reg_welldone'], 'login.php', 10);
    }
} else {
    // Afficher le formulaire d'enregistrement
    $parse = $lang;
    $parse['servername'] = $game_config['game_name'];
    // Captcha (mod de theptitprince) : image tiree a chaque affichage
    $parse['captcha_row'] = '';
    if (CaptchaEnabled()) {
        $parse['captcha_rnd'] = time();
        $parse['captcha_row'] = parsetemplate(gettemplate('registry_captcha'), $parse);
    }
    $page = parsetemplate(gettemplate('registry_form'), $parse);

    display ($page, $lang['registry'], false);
}
// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Version originelle
// 1.1 - Menage + rangement + utilisation fonction de creation planete nouvelle generation
?>
