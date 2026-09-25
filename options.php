<?php

    /**
    * options.php
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
    include($xnova_root_path . 'common.' . $phpEx);

    includeLang('options');

    $lang['PHP_SELF'] = 'options.' . $phpEx;

    $dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
    $mode = ($_GET['mode'] ?? null);

    if ($_POST && $mode == "exit") { // Array ( [db_character]
       if (isset($_POST["exit_modus"]) && ($_POST["exit_modus"] ?? null) == 'on' and $user['urlaubs_until'] <= time()){
          $urlaubs_modus = "0";
          doquery("UPDATE {{table}} SET   
             `urlaubs_modus` = '0',
             `urlaubs_until` = '0'
             WHERE `id` = '".$user['id']."' LIMIT 1", "users");   
          $dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
          message($lang['succeful_save'], $lang['Options'],"options.php",1);
       }else{
       $urlaubs_modus = "1";
       $dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
       message($lang['You_cant_exit_vmode'], $lan['Error'] ,"options.php",1);
       }
    }
    if ($_POST && $mode == "change") { // Array ( [db_character]
       $iduser = $user["id"];
       $avatar = SqlEscape(SafeUrl(($_POST["avatar"] ?? null)));
       $dpath = SqlEscape(SafePath(($_POST["dpath"] ?? null)));

       // Gestion des options speciales pour les admins
       if ($user['authlevel'] > 0) {
          if (($_POST['adm_pl_prot'] ?? null) == 'on') {
             doquery ("UPDATE {{table}} SET `id_level` = '".$user['authlevel']."' WHERE `id_owner` = '".$user['id']."';", 'planets');
          } else {
             doquery ("UPDATE {{table}} SET `id_level` = '0' WHERE `id_owner` = '".$user['id']."';", 'planets');
          }
       }

       // Mostrar skin
       if (isset($_POST["design"]) && ($_POST["design"] ?? null) == 'on') {
          $design = "1";
       } else {
          $design = "0";
       }
       // Desactivar comprobaci? de IP
       if (isset($_POST["noipcheck"]) && ($_POST["noipcheck"] ?? null) == 'on') {
          $noipcheck = "1";
       } else {
          $noipcheck = "0";
       }
       // Nombre de usuario
       if (isset($_POST["db_character"]) && ($_POST["db_character"] ?? null) != '') {
          // Meme regle qu'a l'inscription : lettres, chiffres, _ et - uniquement
          $username = (preg_match("/[^A-Za-z0-9_\-]/", $_POST['db_character']) == 1) ? $user['username'] : $_POST['db_character'];
       } else {
          $username = $user['username'];
       }
       // Adresse e-Mail
       if (isset($_POST["db_email"]) && ($_POST["db_email"] ?? null) != '') {
          $db_email = SqlEscape(is_email($_POST['db_email']) ? ($_POST['db_email'] ?? null) : $user['email']); // adresse valide uniquement
       } else {
          $db_email = SqlEscape($user['email']);
       }
       // Cantidad de sondas de espionaje
       if (isset($_POST["spio_anz"]) && is_numeric($_POST["spio_anz"])) {
          $spio_anz = intval(($_POST["spio_anz"] ?? null));
       } else {
          $spio_anz = "1";
       }
       // Mostrar tooltip durante
       if (isset($_POST["settings_tooltiptime"]) && is_numeric($_POST["settings_tooltiptime"])) {
          $settings_tooltiptime = intval(($_POST["settings_tooltiptime"] ?? null));
       } else {
          $settings_tooltiptime = "1";
       }
       // Maximo mensajes de flotas
       if (isset($_POST["settings_fleetactions"]) && is_numeric($_POST["settings_fleetactions"])) {
          $settings_fleetactions = intval(($_POST["settings_fleetactions"] ?? null));
       } else {
          $settings_fleetactions = "1";
       } //
       // Mostrar logos de los aliados
       if (isset($_POST["settings_allylogo"]) && ($_POST["settings_allylogo"] ?? null) == 'on') {
          $settings_allylogo = "1";
       } else {
          $settings_allylogo = "0";
       }
       // Espionaje
       if (isset($_POST["settings_esp"]) && ($_POST["settings_esp"] ?? null) == 'on') {
          $settings_esp = "1";
       } else {
          $settings_esp = "0";
       }
       // Escribir mensaje
       if (isset($_POST["settings_wri"]) && ($_POST["settings_wri"] ?? null) == 'on') {
          $settings_wri = "1";
       } else {
          $settings_wri = "0";
       }
       // A?dir a lista de amigos
       if (isset($_POST["settings_bud"]) && ($_POST["settings_bud"] ?? null) == 'on') {
          $settings_bud = "1";
       } else {
          $settings_bud = "0";
       }
       // Ataque con misiles
       if (isset($_POST["settings_mis"]) && ($_POST["settings_mis"] ?? null) == 'on') {
          $settings_mis = "1";
       } else {
          $settings_mis = "0";
       }
       // Ver reporte
       if (isset($_POST["settings_rep"]) && ($_POST["settings_rep"] ?? null) == 'on') {
          $settings_rep = "1";
       } else {
          $settings_rep = "0";
       }
       // Modo vacaciones
       if (isset($_POST["urlaubs_modus"]) && ($_POST["urlaubs_modus"] ?? null) == 'on') {
          $urlaubs_modus = "1";
          $time = time() + 172800;
          doquery("UPDATE {{table}} SET   
             `urlaubs_modus` = '$urlaubs_modus',
             `urlaubs_until` = '$time'
             WHERE `id` = '$iduser' LIMIT 1", "users");

          $query = doquery("SELECT * FROM {{table}} WHERE id_owner = '{$user['id']}'", 'planets');
          while($id = mysqli_fetch_array($query)){
             doquery("UPDATE {{table}} SET
                   metal_perhour = '".$game_config['metal_basic_income']."',
                   crystal_perhour = '".$game_config['metal_basic_income']."',
                   deuterium_perhour = '".$game_config['metal_basic_income']."',
                   energy_used = '0',
                   energy_max = '0',
                   metal_mine_porcent = '0',
                   crystal_mine_porcent = '0',
                   deuterium_sintetizer_porcent = '0',
                   solar_plant_porcent = '0',
                   fusion_plant_porcent = '0',
                   solar_satelit_porcent = '0'
                 WHERE id = '{$id['id']}' AND `planet_type` = 1 ", 'planets');
          }
       } else {
          $urlaubs_modus = "0";
       }

       // Borrar cuenta
       if (isset($_POST["db_deaktjava"]) && ($_POST["db_deaktjava"] ?? null) == 'on') {
          $db_deaktjava = "1";
       } else {
          $db_deaktjava = "0";
       }
       $SetSort  = intval(($_POST['settings_sort'] ?? null));
       $SetOrder = intval(($_POST['settings_order'] ?? null));
       // Couleurs : pas de champ dans le formulaire, on conserve les valeurs existantes (elles etaient effacees)
       $kolorminus  = SqlEscape($user['kolorminus']);
       $kolorplus   = SqlEscape($user['kolorplus']);
       $kolorpoziom = SqlEscape($user['kolorpoziom']);
       $iduser      = intval($iduser);

       doquery("UPDATE {{table}} SET
       `email` = '$db_email',
       `avatar` = '$avatar',
       `dpath` = '$dpath',
       `design` = '$design',
       `noipcheck` = '$noipcheck',
       `planet_sort` = '$SetSort',
       `planet_sort_order` = '$SetOrder',
       `spio_anz` = '$spio_anz',
       `settings_tooltiptime` = '$settings_tooltiptime',
       `settings_fleetactions` = '$settings_fleetactions',
       `settings_allylogo` = '$settings_allylogo',
       `settings_esp` = '$settings_esp',
       `settings_wri` = '$settings_wri',
       `settings_bud` = '$settings_bud',
       `settings_mis` = '$settings_mis',
       `settings_rep` = '$settings_rep',
       `urlaubs_modus` = '$urlaubs_modus',
       `db_deaktjava` = '$db_deaktjava',
       `kolorminus` = '$kolorminus',
       `kolorplus` = '$kolorplus',
       `kolorpoziom` = '$kolorpoziom'
       WHERE `id` = '$iduser' LIMIT 1", "users");

       // La page de confirmation doit garder une skin, meme si le champ skin a ete vide
       $dpath = ($dpath == '') ? DEFAULT_SKINPATH : $dpath;

       if (isset($_POST["db_password"]) && ($_POST["db_password"] ?? null) != '' && PasswordCheck($_POST["db_password"], $user)) {
          if (($_POST["newpass1"] ?? null) != '' && ($_POST["newpass1"] ?? null) == ($_POST["newpass2"] ?? null)) {
             $newpass = PasswordHash(($_POST["newpass1"] ?? null));
             doquery("UPDATE {{table}} SET `password` = '". SqlEscape($newpass) ."' WHERE `id` = '". intval($user['id']) ."' LIMIT 1", "users");
             SetAuthCookie("", time()-100000); //le da el expire
             message($lang['succeful_changepass'], $lang['changue_pass'],"login.php",1);
          }
       }
       if ($user['username'] != $username) {
          $query = doquery("SELECT id FROM {{table}} WHERE username='". SqlEscape($username) ."'", 'users', true);
          if (!$query) {
             doquery("UPDATE {{table}} SET username='". SqlEscape($username) ."' WHERE id='". intval($user['id']) ."' LIMIT 1", "users");
             SetAuthCookie("", time()-100000); //le da el expire
             message($lang['succeful_changename'], $lang['changue_name'],"login.php",1);
          }
       }
       message($lang['succeful_save'], $lang['Options'],"options.php",1);
    } else {
       $parse = $lang;

       $parse['dpath'] = $dpath;
       $parse['opt_lst_skin_data']  = "<option value =\"skins/xnova/\">skins/xnova/</option>";
       $parse['opt_lst_ord_data']   = "<option value =\"0\"". (($user['planet_sort'] == 0) ? " selected": "") .">". $lang['opt_lst_ord0'] ."</option>";
       $parse['opt_lst_ord_data']  .= "<option value =\"1\"". (($user['planet_sort'] == 1) ? " selected": "") .">". $lang['opt_lst_ord1'] ."</option>";
       $parse['opt_lst_ord_data']  .= "<option value =\"2\"". (($user['planet_sort'] == 2) ? " selected": "") .">". $lang['opt_lst_ord2'] ."</option>";

       $parse['opt_lst_cla_data']   = "<option value =\"0\"". (($user['planet_sort_order'] == 0) ? " selected": "") .">". $lang['opt_lst_cla0'] ."</option>";
       $parse['opt_lst_cla_data']  .= "<option value =\"1\"". (($user['planet_sort_order'] == 1) ? " selected": "") .">". $lang['opt_lst_cla1'] ."</option>";

       if ($user['authlevel'] > 0) {
          $FrameTPL = gettemplate('options_admadd');
          $IsProtOn = doquery ("SELECT `id_level` FROM {{table}} WHERE `id_owner` = '".$user['id']."' LIMIT 1;", 'planets', true);
          $bloc['opt_adm_title']       = $lang['opt_adm_title'];
          $bloc['opt_adm_planet_prot'] = $lang['opt_adm_planet_prot'];
          $bloc['adm_pl_prot_data']    = ($IsProtOn['id_level'] > 0) ? " checked='checked'/":'';
          $parse['opt_adm_frame']      = parsetemplate($FrameTPL, $bloc);
       }

       $parse['opt_usern_data'] = $user['username'];
       $parse['opt_mail1_data'] = $user['email'];
       $parse['opt_mail2_data'] = $user['email_2'];
       $parse['opt_dpath_data'] = $user['dpath'];
       $parse['opt_avata_data'] = $user['avatar'];
       $parse['opt_probe_data'] = $user['spio_anz'];
       $parse['opt_toolt_data'] = $user['settings_tooltiptime'];
       $parse['opt_fleet_data'] = $user['settings_fleetactions'];
       $parse['opt_sskin_data'] = ($user['design'] == 1) ? " checked='checked'":'';
       $parse['opt_noipc_data'] = ($user['noipcheck'] == 1) ? " checked='checked'":'';
       $parse['opt_allyl_data'] = ($user['settings_allylogo'] == 1) ? " checked='checked'/":'';
       $parse['opt_delac_data'] = ($user['db_deaktjava'] == 1) ? " checked='checked'/":'';
       $parse['opt_modev_data'] = ($user['urlaubs_modus'] == 1)?" checked='checked'/":'';
       $parse['opt_modev_exit'] = ($user['urlaubs_modus'] == 0)?" checked='1'/":'';
       $parse['Vaccation_mode'] = $lang['Vaccation_mode'];
       $parse['vacation_until'] = date("d/m/Y H:i:s",$user['urlaubs_until']);
       $parse['user_settings_rep'] = ($user['settings_rep'] == 1) ? " checked='checked'/":'';
       $parse['user_settings_esp'] = ($user['settings_esp'] == 1) ? " checked='checked'/":'';
       $parse['user_settings_wri'] = ($user['settings_wri'] == 1) ? " checked='checked'/":'';
       $parse['user_settings_mis'] = ($user['settings_mis'] == 1) ? " checked='checked'/":'';
       $parse['user_settings_bud'] = ($user['settings_bud'] == 1) ? " checked='checked'/":'';
       $parse['kolorminus']  = $user['kolorminus'];
       $parse['kolorplus']   = $user['kolorplus'];
       $parse['kolorpoziom'] = $user['kolorpoziom'];

       if($user['urlaubs_modus']){

          display(parsetemplate(gettemplate('options_body_vmode'), $parse), 'Options', false);
       }else{
       display(parsetemplate(gettemplate('options_body'), $parse), 'Options', false);
       }
       die();
    }

    ?>
