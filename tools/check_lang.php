<?php

/**
 * tools/check_lang.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Verificateur des cles de langue (outil de developpement, en ligne de commande uniquement) :
 *  1. cles $lang['...'] utilisees dans le code mais definies dans aucun fichier de langue (texte vide a l'ecran) ;
 *  2. cles definies en francais mais absentes d'une autre langue (traduction manquante) ;
 *  3. (--noms) cles de langue et balises {x} des templates hors convention : a-z, 0-9 et _ uniquement (depuis la 0.9g).
 *
 * Usage, depuis la racine du jeu :  php tools/check_lang.php [--langues] [--noms]
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

if (PHP_SAPI !== 'cli') { die('Outil en ligne de commande uniquement.'); }

$Root = dirname(__DIR__);
chdir($Root);

// Cles definies : $lang['cle'] = ... (on ne garde que le premier niveau)
function DefinedKeys ( $Dir ) {
	$Keys = array();
	$Files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Dir, FilesystemIterator::SKIP_DOTS));
	foreach ($Files as $File) {
		if (!preg_match('/\.(mo|cfg|php)$/', $File->getFilename())) continue;
		$Src = file_get_contents($File->getPathname());
		if (preg_match_all('/\$lang\s*\[\s*[\'"]([^\'"]+)[\'"]\s*\]/', $Src, $M)) {
			foreach ($M[1] as $K) $Keys[$K][] = substr($File->getPathname(), strlen($Dir) + 1);
		}
	}
	return $Keys;
}

// Cles utilisees dans le code PHP (hors dossier language et hors outils)
$Used = array();
$Files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Root, FilesystemIterator::SKIP_DOTS));
foreach ($Files as $File) {
	$Path = str_replace('\\', '/', substr($File->getPathname(), strlen($Root) + 1));
	if (!preg_match('/\.php$/', $Path) || preg_match('#^(language|tools|\.git)/#', $Path)) continue;
	$Lines = file($File->getPathname());
	foreach ($Lines as $N => $Line) {
		// Cle remplie par le code lui-meme ($lang sert parfois de tableau de valeurs pour un template)
		if (preg_match_all('/\$lang\s*\[\s*[\'"]([A-Za-z0-9_\-]+)[\'"]\s*\](\s*\[[^\]]*\])*\s*\.?=(?!=)/', $Line, $A)) {
			foreach ($A[1] as $K) $Assigned[$K] = true;
		}
		if (preg_match_all('/\$lang\s*\[\s*[\'"]([A-Za-z0-9_\-]+)[\'"]\s*\]/', $Line, $M)) {
			foreach ($M[1] as $K) $Used[$K][] = $Path . ':' . ($N + 1);
		}
	}
}

$Fr = DefinedKeys($Root . '/language/fr') + (isset($Assigned) ? $Assigned : array());
$Missing = array_diff_key($Used, $Fr);
ksort($Missing);

echo "=== Cles utilisees dans le code mais absentes du francais : " . count($Missing) . "\n";
foreach ($Missing as $K => $Where) {
	echo str_pad($K, 32) . ' ' . implode(', ', array_slice(array_unique($Where), 0, 3)) . (count($Where) > 3 ? ' ...' : '') . "\n";
}

if (in_array('--langues', $argv)) {
	// Comparaison fichier a fichier des langues : uniquement les cles definies dans language/fr
	// (pas celles que le code remplit lui-meme, qui n'ont rien a traduire)
	$FrFiles = DefinedKeys($Root . '/language/fr');
	foreach (array('de', 'es', 'it') as $L) {
		if (!is_dir($Root . '/language/' . $L)) continue;
		$Other = DefinedKeys($Root . '/language/' . $L);
		$Absent = array_diff_key($FrFiles, $Other);
		echo "\n=== Cles du francais absentes de '$L' : " . count($Absent) . "\n";
		echo wordwrap(implode(', ', array_keys($Absent)), 110) . "\n";
	}
}
$BadNames = array();
if (in_array('--noms', $argv)) {
	// Convention de nommage : cles de langue (toutes les langues) et balises des templates
	foreach (array('language' => '/\.(mo|cfg)$/', 'templates' => '/\.tpl$/') as $Dir => $Ext) {
		$Files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($Root . '/' . $Dir, FilesystemIterator::SKIP_DOTS));
		foreach ($Files as $File) {
			if (!preg_match($Ext, $File->getFilename())) continue;
			$Src  = file_get_contents($File->getPathname());
			$Path = str_replace('\\', '/', substr($File->getPathname(), strlen($Root) + 1));
			$Pattern = ($Dir == 'language') ? '/\$lang\s*\[\s*[\'"]([^\'"]+)[\'"]\s*\]/' : '/\{([A-Za-z0-9\-_]+)\}/';
			if (preg_match_all($Pattern, $Src, $M)) {
				foreach ($M[1] as $K) {
					if (!preg_match('/^[a-z0-9_]+$/', $K)) $BadNames[$K][$Path] = true;
				}
			}
		}
	}
	echo "\n=== Noms hors convention (a-z, 0-9, _) : " . count($BadNames) . "\n";
	foreach ($BadNames as $K => $Where) {
		echo str_pad($K, 32) . ' ' . implode(', ', array_slice(array_keys($Where), 0, 3)) . "\n";
	}
}
exit((count($Missing) > 0 || count($BadNames) > 0) ? 1 : 0);
