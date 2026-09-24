<?php

/**
 * variables.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * overview.php
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU GPL v2
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = '../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

	if ($user['authlevel'] >= 1) {

				$parse['phpinfo'] = phpinfo();    

		$Page = parsetemplate($PageTPL, $parse);

		display ( $Page, "PhpInfo", false, '', true);
	} else {
		AdminMessage ( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>
