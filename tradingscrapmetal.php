<?php

/**
 * tradingscrapmetal.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Le negociant intergalactique : rachete les sondes d'espionnage de la planete contre du cristal.
 * Gabarit et textes livres avec la 0.8e, page jamais ecrite.
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

function ShowTradingScrapMetal ( $CurrentUser, &$CurrentPlanet ) {
	global $lang, $game_config, $resource, $pricelist, $dpath, $link;

	includeLang('tradingscrapmetal');

	// Meme reglage que le marchand (page et lien du menu)
	if ($game_config['enable_marchand'] != 1) {
		message($lang['sys_page_disabled'], $lang['intergalactic_merchant']);
	}

	$Rate  = $pricelist[210]['crystal'];   // une sonde est rachetee a son prix en cristal (1k)
	$Probe = $resource[210];

	if (isset($_POST['number_of_probes'])) {
		$Count = intval($_POST['number_of_probes']);
		if ($Count < 1) {
			message($lang['merchant_no_probe'], $lang['intergalactic_merchant'], 'tradingscrapmetal.php', 2);
		}
		// Vente en une requete : refusee si les sondes ne sont plus sur la planete (double envoi, flotte partie)
		$Gain  = $Count * $Rate;
		doquery("UPDATE {{table}} SET `". $Probe ."` = `". $Probe ."` - ". $Count .", `crystal` = `crystal` + ". $Gain ." WHERE `id` = '". intval($CurrentPlanet['id']) ."' AND `". $Probe ."` >= ". $Count .";", 'planets');
		if (mysqli_affected_rows($link) < 1) {
			message($lang['merchant_not_enough'], $lang['intergalactic_merchant'], 'tradingscrapmetal.php', 2);
		}
		$CurrentPlanet[$Probe]    -= $Count;
		$CurrentPlanet['crystal'] += $Gain;
		message(sprintf($lang['merchant_done'], pretty_number($Count), pretty_number($Gain)), $lang['intergalactic_merchant'], 'tradingscrapmetal.php', 3);
	}

	$parse                      = $lang;
	$parse['dpath']             = $dpath;
	$parse['crystal']           = $Rate;
	$parse['max_spy_probe']     = intval($CurrentPlanet[$Probe]);
	$parse['merchant_give_you'] = str_replace('%n', parsetemplate(gettemplate('tradingscrapmetal_n'), array()), $lang['merchant_give_you']);

	return parsetemplate(gettemplate('tradingscrapmetal'), $parse);
}

	$page = ShowTradingScrapMetal($user, $planetrow);
	display($page, $lang['intergalactic_merchant']);

?>
