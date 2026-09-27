<?php

/**
 * paneladmina.php
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
define('IN_ADMIN', true);

$xnova_root_path = './../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	if ($user['authlevel'] >= "1") {
		includeLang('admin/adminpanel');

		$PanelMainTPL = gettemplate('admin/admin_panel_main');

		$parse                  = $lang;
		$parse['adm_sub_form1'] = "";
		$parse['adm_sub_form2'] = "";
		$parse['adm_sub_form3'] = "";

		// Afficher les templates
		if (isset($_GET['result'])) {
			switch (($_GET['result'] ?? null)){
				case 'usr_search':
					$Pattern = SqlEscape(addcslashes((string) ($_GET['player'] ?? ''), '%_'));
					$SelUser = doquery("SELECT * FROM {{table}} WHERE `username` LIKE '%". $Pattern ."%' LIMIT 1;", 'users', true);
					if (!$SelUser) {
						$parse['adm_sub_form2'] = "<table width=\"519\"><tr><th class=\"errormessage\">". $lang['adm_usr_notfound'] ."</th></tr></table>";
						break;
					}
					$UsrMain = doquery("SELECT `name` FROM {{table}} WHERE `id` = '". $SelUser['id_planet'] ."';", 'planets', true);

					$bloc                   = $lang;
					$bloc['answer1']        = $SelUser['id'];
					$bloc['answer2']        = $SelUser['username'];
					$bloc['answer3']        = $SelUser['user_lastip'];
					$bloc['answer4']        = $SelUser['email'];
					$bloc['answer5']        = $lang['adm_usr_level'][ $SelUser['authlevel'] ];
					$bloc['answer6']        = $lang['adm_usr_genre'][ $SelUser['sex'] ];
					$bloc['answer7']        = "[".$SelUser['id_planet']."] ".$UsrMain['name'];
					$bloc['answer8']        = "[".$SelUser['galaxy'].":".$SelUser['system'].":".$SelUser['planet']."] ";
					$SubPanelTPL            = gettemplate('admin/admin_panel_asw1');
					$parse['adm_sub_form2'] = parsetemplate( $SubPanelTPL, $bloc );
					break;

				case 'usr_data':
					// Par ID exact (lien « Fiche » de la liste des joueurs) ou par nom ; le nom LIKE '%...%' prenait le
					// premier pseudo contenant le texte (« admin » trouvait aussi « admin2 »)
					if (intval($_GET['id'] ?? 0) > 0) {
						$SelUser = doquery("SELECT * FROM {{table}} WHERE `id` = '". intval($_GET['id']) ."';", 'users', true);
					} else {
						$Pattern = SqlEscape(addcslashes((string) ($_GET['player'] ?? ''), '%_'));
						$SelUser = doquery("SELECT * FROM {{table}} WHERE `username` = '". SqlEscape((string) ($_GET['player'] ?? '')) ."' LIMIT 1;", 'users', true)
						        ?: doquery("SELECT * FROM {{table}} WHERE `username` LIKE '%". $Pattern ."%' LIMIT 1;", 'users', true);
					}
					if (!$SelUser) {
						$parse['adm_sub_form1'] = "<table width=\"519\"><tr><th class=\"errormessage\">". $lang['adm_usr_notfound'] ."</th></tr></table>";
						break;
					}
					$UsrMain = doquery("SELECT `name` FROM {{table}} WHERE `id` = '". $SelUser['id_planet'] ."';", 'planets', true);

					$bloc                    = $lang;
					$bloc['answer1']         = $SelUser['id'];
					$bloc['answer2']         = $SelUser['username'];
					$bloc['answer3']         = $SelUser['user_lastip'];
					$bloc['answer4']         = $SelUser['email'];
					$bloc['answer5']         = $lang['adm_usr_level'][ $SelUser['authlevel'] ];
					$bloc['answer6']         = $lang['adm_usr_genre'][ $SelUser['sex'] ];
					$bloc['answer7']         = "[".$SelUser['id_planet']."] ".$UsrMain['name'];
					$bloc['answer8']         = "[".$SelUser['galaxy'].":".$SelUser['system'].":".$SelUser['planet']."] ";
					$SubPanelTPL             = gettemplate('admin/admin_panel_asw1');
					$parse['adm_sub_form1']  = parsetemplate( $SubPanelTPL, $bloc );

					$parse['adm_sub_form2']  = "<table><tbody>";
					$parse['adm_sub_form2'] .= "<tr><td colspan=\"4\" class=\"c\">".$lang['adm_colony']."</td></tr>";
					$UsrColo = doquery("SELECT * FROM {{table}} WHERE `id_owner` = '". intval($SelUser['id']) ."' ORDER BY `galaxy` ASC, `system` ASC, `planet` ASC, `planet_type` ASC;", 'planets');
					while ( $Colo = mysqli_fetch_assoc($UsrColo) ) {
						if ($Colo['id'] != $SelUser['id_planet']) {
							$parse['adm_sub_form2'] .= "<tr><th>".$Colo['id']."</th>";
							$parse['adm_sub_form2'] .= "<th>". (($Colo['planet_type'] == 1) ? $lang['adm_planet'] : $lang['adm_moon'] ) ."</th>";
							$parse['adm_sub_form2'] .= "<th>[".$Colo['galaxy'].":".$Colo['system'].":".$Colo['planet']."]</th>";
							$parse['adm_sub_form2'] .= "<th>".$Colo['name']."</th></tr>";
						}
					}
					$parse['adm_sub_form2'] .= "</tbody></table>";

					$parse['adm_sub_form3']  = "<table><tbody>";
					$parse['adm_sub_form3'] .= "<tr><td colspan=\"4\" class=\"c\">".$lang['adm_technos']."</td></tr>";
					for ($Item = 100; $Item <= 199; $Item++) {
						if (!empty($resource[$Item])) {
							$parse['adm_sub_form3'] .= "<tr><th>".$lang['tech'][$Item]."</th>";
							$parse['adm_sub_form3'] .= "<th>".$SelUser[$resource[$Item]]."</th></tr>";
						}
					}
					$parse['adm_sub_form3'] .= "</tbody></table>";
					break;

				case 'usr_level':
					// Seul un administrateur (niveau 3) peut changer le niveau d'un compte (un moderateur pouvait se promouvoir)
					if ($user['authlevel'] < 3) {
						message($lang['sys_noalloaw'], $lang['sys_noaccess']);
					}
					$Player     = SqlEscape(($_GET['player'] ?? null));
					$NewLvl     = max(0, min(3, intval(($_GET['authlvl'] ?? null))));
					// Compte introuvable : message (le changement etait annonce quand meme) ; pas son propre acces
					$Target     = doquery("SELECT `id` FROM {{table}} WHERE `username` = '".$Player."' LIMIT 1;", 'users', true);
					if (!$Target) {
						AdminMessage ( $lang['adm_usr_notfound'], $lang['adm_mod_level'] );
					}
					if ($Target['id'] == $user['id']) {
						AdminMessage ( $lang['adm_usr_ownlevel'], $lang['adm_mod_level'] );
					}

					$QryUpdate  = doquery("UPDATE {{table}} SET `authlevel` = '".$NewLvl."' WHERE `id` = '". intval($Target['id']) ."';", 'users');
					// Protection des planetes reservee aux administrateurs : retiree si le compte ne l'est plus
					if ($NewLvl < 3) {
						doquery("UPDATE {{table}} SET `id_level` = '0' WHERE `id_owner` = '". intval($Target['id']) ."';", 'planets');
					}
					$Message    = $lang['adm_mess_lvl1']. " ". htmlspecialchars((string) ($_GET['player'] ?? ''), ENT_QUOTES, 'UTF-8') ." ".$lang['adm_mess_lvl2'];
					$Message   .= "<font color=\"red\">".$lang['adm_usr_level'][ $NewLvl ]."</font>!";

					AdminMessage ( $Message, $lang['adm_mod_level'] );
					break;

				case 'ip_search':
					// L'adresse cherchee etait une variable inexistante ($ip) : la recherche ne trouvait jamais personne
					$Pattern    = SqlEscape(trim((string) ($_GET['ip'] ?? '')));
					$SelUser    = doquery("SELECT * FROM {{table}} WHERE `user_lastip` = '". $Pattern ."' OR `ip_at_reg` = '". $Pattern ."' LIMIT 50;", 'users');
					$bloc                   = $lang;
					$bloc['adm_this_ip']    = htmlspecialchars((string) ($_GET['ip'] ?? ''), ENT_QUOTES, 'UTF-8');
					$bloc['adm_plyer_lst']  = '';
					while ( $Usr = mysqli_fetch_assoc($SelUser) ) {
						$UsrMain = doquery("SELECT `name` FROM {{table}} WHERE `id` = '". $Usr['id_planet'] ."';", 'planets', true);
						$bloc['adm_plyer_lst'] .= "<tr><th>".$Usr['username']."</th><th>[".$Usr['galaxy'].":".$Usr['system'].":".$Usr['planet']."] ".$UsrMain['name']."</th></tr>";
					}
					if ($bloc['adm_plyer_lst'] == '') {
						$bloc['adm_plyer_lst'] = "<tr><th colspan=\"2\">". $lang['adm_usr_notfound'] ."</th></tr>";
					}
					$SubPanelTPL            = gettemplate('admin/admin_panel_asw2');
					$parse['adm_sub_form2'] = parsetemplate( $SubPanelTPL, $bloc );
					break;
				default:
					break;
			}
		}

		// Traiter les reponses aux formulaires
		if (isset($_GET['action'])) {
			$bloc                   = $lang;
			switch (($_GET['action'] ?? null)){
				case 'usr_search':
					$SubPanelTPL            = gettemplate('admin/admin_panel_frm1');
					break;

				case 'usr_data':
					$SubPanelTPL            = gettemplate('admin/admin_panel_frm4');
					break;

				case 'usr_level':
					$bloc['adm_level_lst'] = '';
					for ($Lvl = 0; $Lvl < 4; $Lvl++) {
						$bloc['adm_level_lst'] .= "<option value=\"". $Lvl ."\">". $lang['adm_usr_level'][ $Lvl ] ."</option>";
					}
					$SubPanelTPL            = gettemplate('admin/admin_panel_frm3');
					break;

				case 'ip_search':
					$SubPanelTPL            = gettemplate('admin/admin_panel_frm2');
					break;

				default:
					break;
			}
			$parse['adm_sub_form2'] = parsetemplate( $SubPanelTPL, $bloc );
		}

		$page = parsetemplate( $PanelMainTPL, $parse );
		display( $page, $lang['panel_mainttl'], false, '', true );
	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>