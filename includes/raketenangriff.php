<?php

/**
 * includes/raketenangriff.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original : -= MoF =- pour le Deutsches UGamela Forum (voir mentions d'origine ci-dessous)
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */
// Copyright (c) 2007 by -= MoF =- for Deutsches UGamela Forum
// Date N/A
// Open Source
function raketenangriff($verteidiger_panzerung, $angreifer_waffen, $iraks, $def, $primaerziel = '0') {
	// Variablen initialisieren
	$temp = '';
	$temp2 = '';

	$def[10] = $iraks;
	// Index 12 : Protecteur planetaire (0.9j), cible comme les autres defenses (dans OGame, toutes les defenses le sont)
	$def[12] = $def[12] ?? 0;

	$metall     = Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	$kristall   = Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	$deut       = Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	$verblieben = Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

	for($temp = 0; $temp < 11; $temp++) {
		$verblieben[$temp] = $def[$temp];
	}
	$verblieben[12] = $def[12];

	$kaputt = Array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

	$hull = Array();

	$hull[0] = 200 * (1 + $verteidiger_panzerung / 10);
	$hull[1] = $hull[0];
	$hull[2] = 800 * (1 + ($verteidiger_panzerung / 10));
	$hull[3] = 3500 * (1 + ($verteidiger_panzerung / 10));
	$hull[4] = $hull[2];
	$hull[5] = 10000 * (1 + ($verteidiger_panzerung / 10));
	$hull[6] = 2000 * (1 + ($verteidiger_panzerung / 10));
	$hull[7] = $hull[5];
	$hull[8] = 1500 * (1 + ($verteidiger_panzerung / 10));
	$hull[12] = 400000 * (1 + ($verteidiger_panzerung / 10));

	$metall_cost_tab   = Array( 2, 1.5, 6, 20, 2, 50, 10, 50, 12.5, 8);
	$kristall_cost_tab = Array( 0, 0.5, 2, 15, 6, 50, 10, 50,  2.5, 0);
	$deut_cost_tab     = Array( 0,   0, 0,  2, 0, 30,  0,  0, 10.0, 2);
	$metall_cost_tab[12]   = 2000;
	$kristall_cost_tab[12] = 2000;
	$deut_cost_tab[12]     = 1000;

	$schaden = floor(($def[10] - $def[9]) * (12000 * (1 + ($angreifer_waffen / 10))));
	if ($schaden < 0)
		$schaden = 0;

	// Ordre de tir : la cible choisie d'abord, puis les autres dans l'ordre (0 ou 8 : ordre normal, comme l'original) ;
	// le Protecteur planetaire (12) apres le Grand bouclier
	$beschussreihenfolge = Array(0, 1, 2, 3, 4, 5, 6, 7, 12, 8);
	if (in_array(intval($primaerziel), array(1, 2, 3, 4, 5, 6, 7, 12))) {
		$beschussreihenfolge = array_merge(array(intval($primaerziel)), array_values(array_diff($beschussreihenfolge, array(intval($primaerziel)))));
	}
	// Simulation
	// das Einfachste: I-Raks und Abfangraks ausrechnen...
	$verblieben[10] = 0;
	$kaputt[10] += $def[10];
	$metall[10] += $kaputt[10] * $metall_cost_tab[8];
	$kristall[10] += $kaputt[10] * $kristall_cost_tab[8];
	$deut[10] += $kaputt[10] * $deut_cost_tab[8];

	$verblieben[9] = ($def[9] - $def[10]);
	if ($verblieben[9] < 0)
		$verblieben[9] = 0;

	$kaputt[11] = $def[9] - $verblieben[9];
	$kaputt[9] += ($def[9] - $verblieben[9]);
	$metall[9] += $kaputt[9] * $metall_cost_tab[9];
	$kristall[9] += $kaputt[9] * $kristall_cost_tab[9];
	$deut[9] += $kaputt[9] * $deut_cost_tab[9];
	$metall[11] += $metall[9];
	$kristall[11] += $kristall[9];
	$deut[11] += $deut[9];
	// und jetzt der Reihe nach alles ABKNALLEN!!!
	for($temp = 0; $temp < count($beschussreihenfolge); $temp++) {
		if ($schaden >= ($hull[$beschussreihenfolge[$temp]] * $def[$beschussreihenfolge[$temp]])) {
			$kaputt[$beschussreihenfolge[$temp]] += $def[$beschussreihenfolge[$temp]];

			$verblieben[$beschussreihenfolge[$temp]] = 0;

			$schaden -= ($hull[$beschussreihenfolge[$temp]] * $kaputt[$beschussreihenfolge[$temp]]);
		} else {
			$kaputt[$beschussreihenfolge[$temp]] += floor($schaden / $hull[$beschussreihenfolge[$temp]]);

			$schaden -= $kaputt[$beschussreihenfolge[$temp]] * $hull[$beschussreihenfolge[$temp]];

			$verblieben[$beschussreihenfolge[$temp]] = ($def[$beschussreihenfolge[$temp]] - $kaputt[$beschussreihenfolge[$temp]]);
		}

		$metall[$beschussreihenfolge[$temp]] += $kaputt[$beschussreihenfolge[$temp]] * $metall_cost_tab[$beschussreihenfolge[$temp]];
		$kristall[$beschussreihenfolge[$temp]] += $kaputt[$beschussreihenfolge[$temp]] * $kristall_cost_tab[$beschussreihenfolge[$temp]];
		$deut[$beschussreihenfolge[$temp]] += $kaputt[$beschussreihenfolge[$temp]] * $deut_cost_tab[$beschussreihenfolge[$temp]];

		$verblieben[11] += $verblieben[$beschussreihenfolge[$temp]];
		$kaputt[11] += $kaputt[$beschussreihenfolge[$temp]];
		$metall[11] += $metall[$beschussreihenfolge[$temp]];
		$kristall[11] += $kristall[$beschussreihenfolge[$temp]];
		$deut[11] += $deut[$beschussreihenfolge[$temp]];
	}

	$return = array();

	$return['verbleibt'] = $verblieben; // Übrige Def
	$return['zerstoert'] = $kaputt; // Zerstörte Def
	$return['verluste_metall'] = $metall; // Gesamtverluste Metall
	$return['verluste_kristall'] = $kristall; // Gesamtverluste Kristall
	$return['verluste_deuterium'] = $deut; // Gesamtverluste Deuterium

	return $return;
}

// 11 => Immer gesamt
// Copyright (c) 2007 by -= MoF =- for Deutsches UGamela Forum
// Date N/A
// Open Source

?>