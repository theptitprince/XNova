<?php

/**
 * ElementQueueFixer.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1
 * @copyright 2008 By Chlorel for XNova
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

		// Nettoyage : formulaire POST seulement (jeton verifie par common.php). Il partait a la simple ouverture de la
		// page, donc aussi depuis une image placee ailleurs et vue par un membre du staff
		if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['run'])) {
			$Page  = "<br><br><h2>". $lang['adm_cleaner_title'] ."</h2>";
			$Page .= "<form action=\"ElementQueueFixer.php\" method=\"post\"><input type=\"hidden\" name=\"run\" value=\"1\">";
			$Page .= "<table width=\"519\"><tr><td class=\"c\">". $lang['adm_cleaner_title'] ."</td></tr>";
			$Page .= "<tr><th>". $lang['adm_cleaner_intro'] ."</th></tr>";
			$Page .= "<tr><th><input type=\"submit\" value=\"". $lang['adm_cleaner_run'] ."\"></th></tr></table></form>";
			display ($Page, $lang['adm_cleaner_title'], false, '', true);
		}

		$QrySelectPlanet  = "SELECT `id`, `id_owner`, `b_hangar`, `b_hangar_id` ";
		$QrySelectPlanet .= "FROM {{table}} ";
		$QrySelectPlanet .= "WHERE ";
		$QrySelectPlanet .= "`b_hangar_id` != '0' AND `b_hangar_id` != '';";
		$AffectedPlanets  = doquery ($QrySelectPlanet, 'planets');
		$DeletedQueues    = 0;
		while ( $ActualPlanet = mysqli_fetch_assoc($AffectedPlanets) ) {
			$HangarQueue = explode (";", $ActualPlanet['b_hangar_id']);
			$bDelQueue   = false;
			if (count($HangarQueue)) {
				for ( $Queue = 0; $Queue < count($HangarQueue); $Queue++) {
					// (file vide ou terminee par « ; » : element vide, avertissements PHP auparavant)
					$InQueue = explode (",", $HangarQueue[$Queue]);
					// Au-dela de la borne technique seulement : une ligne commandee avant une baisse du reglage
					// reste valable (l'effacer ferait perdre les ressources payees)
					if (isset($InQueue[1]) && $InQueue[1] > MAX_ORDER_UNITS_LIMIT) {
						$bDelQueue = true;
					}
				}
			}
			if ($bDelQueue) {
				$QryUpdatePlanet  = "UPDATE {{table}} ";
				$QryUpdatePlanet .= "SET ";
				$QryUpdatePlanet .= "`b_hangar` = '0', ";
				$QryUpdatePlanet .= "`b_hangar_id` = '0' ";
				$QryUpdatePlanet .= "WHERE ";
				$QryUpdatePlanet .= "`id` = '".$ActualPlanet['id']."';";
				doquery ($QryUpdatePlanet, 'planets');
				$DeletedQueues += 1;
			}
		}
		if ($DeletedQueues > 0) {
			$QuitMessage = $lang['adm_cleaned']." ". $DeletedQueues;
		} else {
			$QuitMessage = $lang['adm_done'];
		}

		AdminMessage ($QuitMessage, $lang['adm_cleaner_title']);

	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>