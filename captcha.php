<?php

/**
 * captcha.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Image du captcha de l'inscription (mod de theptitprince), voir includes/functions/RegistrationCaptcha.php.
 * Chaque affichage tire un nouveau code.
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE' , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

if (!CaptchaEnabled()) {
	header('HTTP/1.0 404 Not Found');
	exit();
}
CaptchaImage(CaptchaNewCode());

?>
