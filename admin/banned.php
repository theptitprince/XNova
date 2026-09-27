<?php

/**
 * banned.php
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

	if ($user['authlevel'] >= 1) {
		includeLang('admin');

		$mode      = ($_POST['mode'] ?? null);

		$PageTpl   = gettemplate("admin/banned");

		$parse     = $lang;
		// Pseudo pre-rempli (lien « Bannir » de la liste des multi-comptes)
		$parse['adm_bn_prefill'] = htmlspecialchars((string) ($_GET['name'] ?? ''), ENT_QUOTES, 'UTF-8');
		if ($mode == 'banit') {
			// Compte a bannir : il doit exister, ni soi-meme ni un compte de rang egal ou superieur (l'original bannissait
			// n'importe quel nom tape, meme inexistant : entree au pilori pour personne)
			$Target            = doquery("SELECT `id`, `username`, `authlevel` FROM {{table}} WHERE `username` = '". SqlEscape(trim((string) ($_POST['name'] ?? ''))) ."' LIMIT 1;", 'users', true);
			if (!$Target) {
				AdminMessage ($lang['adm_bn_notfound'], $lang['adm_bn_ttle']);
			}
			if ($Target['id'] == $user['id']) {
				AdminMessage ($lang['adm_bn_self'], $lang['adm_bn_ttle']);
			}
			if ($Target['authlevel'] >= $user['authlevel']) {
				AdminMessage ($lang['adm_bn_rank'], $lang['adm_bn_ttle']);
			}
			$name              = SqlEscape($Target['username']);
			$reas              = SqlEscape(SafeText(($_POST['why'] ?? null))); // affiche dans le pilori public
			$days              = max(0, intval(($_POST['days'] ?? null)));
			$hour              = max(0, intval(($_POST['hour'] ?? null)));
			$mins              = max(0, intval(($_POST['mins'] ?? null)));
			$secs              = max(0, intval(($_POST['secs'] ?? null)));

			$admin             = $user['username'];
			$mail              = $user['email'];

			$Now               = time();
			$BanTime           = $days * 86400;
			$BanTime          += $hour * 3600;
			$BanTime          += $mins * 60;
			$BanTime          += $secs;
			// Duree nulle : bannissement definitif (0, comme le lit ChekUser). L'original enregistrait « maintenant » :
			// le bannissement etait leve des la connexion suivante, aucun bannissement definitif n'etait possible
			$BannedUntil       = ($BanTime > 0) ? $Now + $BanTime : 0;

			// Une seule entree par joueur au pilori : un nouveau bannissement remplace le precedent
			doquery("DELETE FROM {{table}} WHERE `who2` = '". $name ."';", 'banned');
			$QryInsertBan      = "INSERT INTO {{table}} SET ";
			$QryInsertBan     .= "`who` = \"". $name ."\", ";
			$QryInsertBan     .= "`theme` = '". $reas ."', ";
			$QryInsertBan     .= "`who2` = '". $name ."', ";
			$QryInsertBan     .= "`time` = '". $Now ."', ";
			$QryInsertBan     .= "`longer` = '". $BannedUntil ."', ";
			$QryInsertBan     .= "`author` = '". $admin ."', ";
			$QryInsertBan     .= "`email` = '". $mail ."';";
			doquery( $QryInsertBan, 'banned');

			$QryUpdateUser     = "UPDATE {{table}} SET ";
			$QryUpdateUser    .= "`bana` = '1', ";
			$QryUpdateUser    .= "`banaday` = '". $BannedUntil ."' ";
			$QryUpdateUser    .= "WHERE ";
			$QryUpdateUser    .= "`id` = '". intval($Target['id']) ."';";
			doquery( $QryUpdateUser, 'users');

			$DoneMessage       = $lang['adm_bn_thpl'] ." ". htmlspecialchars($Target['username'], ENT_QUOTES, 'UTF-8') ." ". $lang['adm_bn_isbn'] ." "
			                   . (($BannedUntil > 0) ? sprintf($lang['adm_bn_until'], date('d/m/Y H:i', $BannedUntil)) : $lang['adm_bn_forever']);
			AdminMessage ($DoneMessage, $lang['adm_bn_ttle']);
		}

		$Page = parsetemplate($PageTpl, $parse);
		display( $Page, $lang['adm_bn_ttle'], false, '', true);
	} else {
		AdminMessage ($lang['sys_noalloaw'], $lang['sys_noaccess']);
	}

?>