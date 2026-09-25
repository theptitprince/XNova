<?php

/**
 * rak.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original : German UGamela (voir mentions d'origine ci-dessous)
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

/**
 * german UGamela
 *       OpenSource aslong as you don't remove this Copyright
 *       http://ugamela-forum.pheelgood.net
 *       UGamela basescripts from Perberos
 * 2006 (all rights reversed)
 *       I-Rak by MoF
 *       UPDATE
 * 18:28 23.12.2007
 */

if (!defined('INSIDE')) {
	die("Hacking attempt");
}

if (file_exists($xnova_root_path . "includes/raketenangriff.php")) {
	include($xnova_root_path . "includes/raketenangriff.php");
} elseif (file_exists("includes/raketenangriff.php")) {
	include("./includes/raketenangriff.php");
} elseif (file_exists("../includes/raketenangriff.php")) {
	include("../includes/raketenangriff.php");
} else
	die('Fatal error!');

if (isset($resource) && !empty($resource[401])) {
	$iraks = doquery("SELECT * FROM {{table}} WHERE zeit <= '" . time() . "'", 'iraks');

	while ($selected_row = mysqli_fetch_array($iraks)) {
		if ($selected_row['zeit'] != '' && $selected_row['galaxy'] != '' && $selected_row['system'] != '' && $selected_row['planet'] != '' && is_numeric($selected_row['owner']) && is_numeric($selected_row['zielid']) && is_numeric($selected_row['anzahl']) && !empty($selected_row['anzahl'])) {
			$planetrow = doquery("SELECT * FROM {{table}} WHERE
								galaxy = '" . $selected_row['galaxy'] . "' AND
								system = '" . $selected_row['system'] . "' AND
								planet = '" . $selected_row['planet'] . "' AND
								planet_type = '1'", 'planets');

			$select_ziel = doquery("SELECT defence_tech FROM {{table}} WHERE
								id = '" . $selected_row['zielid'] . "'", 'users');

			$select_owner = doquery("SELECT military_tech FROM {{table}} WHERE
								id = '" . $selected_row['owner'] . "'", 'users');

			if (mysqli_num_rows($planetrow) != 1 OR mysqli_num_rows($select_ziel) != 1) {
				doquery("DELETE FROM {{table}} WHERE id = '" . $selected_row['id'] . "'", 'iraks');
			} else {
				$verteidiger = mysqli_fetch_array($select_ziel);
				$angreifer = mysqli_fetch_array($select_owner);
				$planet = mysqli_fetch_array($planetrow);

				// Index 8 : missiles interplanetaires, 9 : missiles d'interception (comme $def et raketenangriff()).
				// 502 et 503 etaient inverses : une interception retirait les missiles interplanetaires de la cible
				// (et ses missiles d'interception n'etaient jamais consommes).
				$ids = array(0 => 401,
					1 => 402,
					2 => 403,
					3 => 404,
					4 => 405,
					5 => 406,
					6 => 407,
					7 => 408,
					8 => 503,
					9 => 502
					);

				$def =
				array(0 => $planet['misil_launcher'], // Raketenwerfer
					1 => $planet['small_laser'], // Leichtes Lasergeschütz
					2 => $planet['big_laser'], // Schweres Lasergeschütz
					3 => $planet['gauss_canyon'], // Gaußkanone
					4 => $planet['ionic_canyon'], // Ionengeschütz
					5 => $planet['buster_canyon'], // Plasmawerfer
					6 => $planet['small_protection_shield'], // Kleine Schildkuppel
					7 => $planet['big_protection_shield'], // Große Schildkuppel
					8 => $planet['interplanetary_misil'], // Interplanetarrakete
					9 => $planet['interceptor_misil'], // Abfangrakete
					);

				// Rapport : noms des defenses de tech.mo (l'ancienne liste en dur etait fausse : « Canon Magnetique »
				// pour l'artillerie laser legere...) et phrases de system.mo, dans la langue chargee
				$irak = raketenangriff($verteidiger['defence_tech'], $angreifer['military_tech'], $selected_row['anzahl'], $def, $selected_row['primaer']);

				$message = '';

				if ($planet['interceptor_misil'] >= $selected_row['anzahl']) {
					$message = $lang['sys_irak_all_intercepted'] . '<br>';

					$x = $resource[$ids[9]];

					doquery("UPDATE {{table}} SET " . $x . " = " . $x . "-" . $selected_row['anzahl'] . " WHERE id = " . $planet['id'], 'planets');
				} else {
					if ($planet['interceptor_misil'] > 0) {
						$x = $resource[$ids[9]];

						doquery("UPDATE {{table}} SET " . $x . " = '0' WHERE id = " . $planet['id'], 'planets');

						$message = sprintf($lang['sys_irak_some_intercepted'], intval($planet['interceptor_misil'])) . "<br>";
					}

					foreach ($irak['zerstoert'] as $id => $anzahl) {
						// Index 9 (missiles d'interception consommes) : deja mis a 0 juste au-dessus (ils etaient
						// retires une seconde fois : stock negatif)
						if (!empty($anzahl) && $id < 9) {
							$message .= $lang['tech'][$ids[$id]] . " (- " . $anzahl . ")<br>";

							$x = $resource[$ids[$id]];

							doquery("UPDATE {{table}} SET " . $x . " = GREATEST(" . $x . " - " . intval($anzahl) . ", 0) WHERE id = " . $planet['id'], 'planets');
						}
					}
				}

				$name        = '';
				$name_deffer = '';
				$planet_ = doquery("SELECT * FROM {{table}} WHERE
								galaxy = '" . $selected_row['galaxy_angreifer'] . "' AND
								system = '" . $selected_row['system_angreifer'] . "' AND
								planet = '" . $selected_row['planet_angreifer'] . "' AND
								planet_type = '1'", 'planets');

				if (mysqli_num_rows($planet_) == 1) {
					$array = mysqli_fetch_array($planet_);

					$name = $array['name'];
				}

				$planet_2 = doquery("SELECT * FROM {{table}} WHERE
								galaxy = '" . $selected_row['galaxy'] . "' AND
								system = '" . $selected_row['system'] . "' AND
								planet = '" . $selected_row['planet'] . "' AND
								planet_type = '1'", 'planets');

				if (mysqli_num_rows($planet_2) == 1) {
					$array = mysqli_fetch_array($planet_2);

					$name_deffer = $array['name'];
				}

				$FromLink = '<a href="galaxy.php?mode=3&galaxy=' . $selected_row['galaxy_angreifer'] . '&system=' . $selected_row['system_angreifer'] . '&planet=' . $selected_row['planet_angreifer'] . '">[' . $selected_row['galaxy_angreifer'] . ':' . $selected_row['system_angreifer'] . ':' . $selected_row['planet_angreifer'] . ']</a>';
				$ToLink   = '<a href="galaxy.php?mode=3&galaxy=' . $selected_row['galaxy'] . '&system=' . $selected_row['system'] . '&planet=' . $selected_row['planet'] . '">[' . $selected_row['galaxy'] . ':' . $selected_row['system'] . ':' . $selected_row['planet'] . ']</a>';
				$message_vorlage = sprintf($lang['sys_irak_report'], intval($selected_row['anzahl']), $name, $FromLink, $name_deffer, $ToLink) . '<br><br>';

				if (empty($message))
					$message = $lang['sys_irak_no_defense'];

				SendSimpleMessage ( $selected_row['zielid'], '', time(), 3, $lang['sys_irak_sender'], $lang['sys_irak_subject'], $message_vorlage . $message );

				doquery("DELETE FROM {{table}} WHERE id = '" . $selected_row['id'] . "'", 'iraks');
			}
		} else {
			doquery("DELETE FROM {{table}} WHERE id = '" . $selected_row['id'] . "'", 'iraks');
		}
	}
}

?>