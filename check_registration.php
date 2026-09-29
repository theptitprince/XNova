<?php

/**
 * check_registration.php
 *
 * XNova Renaissance
 * Écrit par theptitprince (2026)
 *
 * Verification en direct du pseudo et de l'adresse e-mail pendant l'inscription (scripts/registration.js).
 * Page appelee par le script mais jamais ecrite dans l'original. Memes regles que reg.php, qui reste seul juge.
 * Reponse : "1|ok|message" (pseudo) ou "2|ok|message" (e-mail), ok = 1 si la valeur est acceptee.
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE' , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

includeLang('reg');

header('Content-Type: text/plain; charset=UTF-8');

// Messages de reg.php, sans le retour a la ligne final
function CheckRegistrationAnswer ( $Field, $Ok, $Message ) {
	echo $Field . '|' . ($Ok ? '1' : '0') . '|' . str_replace('<br />', '', $Message);
	exit();
}

// Champs recus en texte seulement (un tableau ecrivait « Array to string conversion » dans le journal)
$Action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';

if ($Action == 'check_username') {
	$Name = is_string($_POST['username'] ?? null) ? $_POST['username'] : '';
	if ($Name == '') {
		CheckRegistrationAnswer(1, false, $lang['error_character']);
	}
	if (preg_match("/[^A-Za-z0-9_\-]/", $Name) == 1) {
		CheckRegistrationAnswer(1, false, $lang['error_charalpha']);
	}
	if (strlen($Name) > USERNAME_MAX_LENGTH) {
		CheckRegistrationAnswer(1, false, $lang['error_charlength']);
	}
	$ExistUser = doquery("SELECT `username` FROM {{table}} WHERE `username` = '" . SqlEscape($Name) . "' LIMIT 1;", 'users', true);
	if ($ExistUser) {
		CheckRegistrationAnswer(1, false, $lang['error_userexist']);
	}
	CheckRegistrationAnswer(1, true, $lang['reg_check_user_ok']);
} elseif ($Action == 'check_email') {
	$Mail = strip_tags(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
	if (!is_email($Mail)) {
		CheckRegistrationAnswer(2, false, $lang['error_mail']);
	}
	// Syntaxe seulement (0.9k) : cette page publique, sans captcha ni limite, disait si une adresse etait inscrite.
	// reg.php refuse toujours une adresse deja utilisee, apres le captcha.
	CheckRegistrationAnswer(2, true, $lang['reg_check_mail_ok']);
}

?>
