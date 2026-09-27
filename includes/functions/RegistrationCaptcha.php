<?php

/**
 * RegistrationCaptcha.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Captcha a l'inscription : mod de theptitprince (d'apres son tutoriel sur Britania), reecrit pour XNova Renaissance.
 * Code de 6 caracteres tire au hasard (random_int), garde en session PHP (seulement pour l'inscription), valable
 * 10 minutes et une seule fois ; image dessinee par GD (caracteres tournes, agrandis et decales, brouillage).
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Caracteres sans ambiguite a la lecture (ni 0 / O, ni 1 / I / L)
define('CAPTCHA_CHARS'   , 'ABCDEFGHJKMNPQRSTUVWXYZ23456789');
define('CAPTCHA_LENGTH'  , 6);
define('CAPTCHA_LIFETIME', 600);

// Captcha actif : reglage de l'administration, et extension GD presente pour dessiner l'image
function CaptchaEnabled () {
	global $game_config;
	return (!empty($game_config['reg_captcha']) && function_exists('imagecreatetruecolor'));
}

// Session reservee au captcha (le jeu n'utilise pas de session PHP ailleurs)
function CaptchaSession () {
	if (session_status() != PHP_SESSION_ACTIVE) {
		session_start(array('name' => 'XNOVA_CAPTCHA', 'cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true));
	}
}

// Nouveau code (remplace le precedent)
function CaptchaNewCode () {
	CaptchaSession();
	$Code = '';
	for ($i = 0; $i < CAPTCHA_LENGTH; $i++) {
		$Code .= CAPTCHA_CHARS[random_int(0, strlen(CAPTCHA_CHARS) - 1)];
	}
	$_SESSION['reg_captcha'] = array('code' => $Code, 'time' => time());
	return $Code;
}

// Code saisi correct ? Le code est efface dans tous les cas : il ne sert qu'une fois (un robot qui l'aurait lu ne peut
// pas le rejouer)
function CaptchaCheck ( $Given ) {
	CaptchaSession();
	$Expected = $_SESSION['reg_captcha'] ?? null;
	unset($_SESSION['reg_captcha']);
	if (!is_array($Expected) || (time() - intval($Expected['time'])) > CAPTCHA_LIFETIME) {
		return false;
	}
	$Given = strtoupper(trim((string) $Given));
	return ($Given != '' && hash_equals((string) $Expected['code'], $Given));
}

// Image PNG du code, 150 x 50
function CaptchaImage ( $Code ) {
	$Width  = 150;
	$Height = 50;
	$Image  = imagecreatetruecolor($Width, $Height);
	imagefill($Image, 0, 0, imagecolorallocate($Image, random_int(15, 35), random_int(15, 35), random_int(35, 60)));

	// Brouillage du fond : points et lignes
	for ($i = 0; $i < 350; $i++) {
		imagesetpixel($Image, random_int(0, $Width - 1), random_int(0, $Height - 1),
		              imagecolorallocate($Image, random_int(60, 150), random_int(60, 150), random_int(60, 150)));
	}
	for ($i = 0; $i < 6; $i++) {
		imageline($Image, random_int(0, $Width), random_int(0, $Height), random_int(0, $Width), random_int(0, $Height),
		          imagecolorallocate($Image, random_int(50, 110), random_int(50, 110), random_int(50, 120)));
	}

	// Caracteres : dessines en petit, tournes, agrandis et decales
	$Step = ($Width - 16) / strlen($Code);
	for ($i = 0; $i < strlen($Code); $i++) {
		$Char = imagecreatetruecolor(14, 20);
		imagealphablending($Char, false);
		imagesavealpha($Char, true);
		$Clear = imagecolorallocatealpha($Char, 0, 0, 0, 127);
		imagefill($Char, 0, 0, $Clear);
		imagechar($Char, 5, 3, 2, $Code[$i], imagecolorallocate($Char, random_int(190, 255), random_int(190, 255), random_int(140, 255)));
		$Turned = imagerotate($Char, random_int(-28, 28), $Clear);
		imagesavealpha($Turned, true);
		$High   = random_int(30, 38);
		$Wide   = intval($High * imagesx($Turned) / imagesy($Turned));
		imagecopyresampled($Image, $Turned, intval(8 + $i * $Step + random_int(-2, 2)), random_int(1, max(1, $Height - $High - 1)),
		                   0, 0, $Wide, $High, imagesx($Turned), imagesy($Turned));
		imagedestroy($Char);
		imagedestroy($Turned);
	}

	// Deux lignes par-dessus le texte
	for ($i = 0; $i < 2; $i++) {
		imageline($Image, 0, random_int(10, $Height - 10), $Width, random_int(10, $Height - 10),
		          imagecolorallocate($Image, random_int(120, 190), random_int(120, 190), random_int(120, 190)));
	}

	header('Content-Type: image/png');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	imagepng($Image);
	imagedestroy($Image);
}

?>
