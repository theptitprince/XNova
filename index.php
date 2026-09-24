<?php

/**
 * index.php
 *
 * XNova 0.9 Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by e-Zobar for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

if (filesize('config.php') == 0) {
	header('location: install/');
	exit();
}

header('location: login.php');

// -----------------------------------------------------------------------------------------------------------
// History version
// 1.0 - Creation avec redirection vers l'installeur si pas de config.php
?>