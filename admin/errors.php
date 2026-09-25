<?php

/**
 * errors.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * erreurs.php
 * @version 1.0
 * @copyright 2008 by e-Zobar for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

$xnova_root_path = '../';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

includeLang('admin');
$parse = $lang;

	if ($user['authlevel'] >= 3) {

		// Supprimer les erreurs
		// extract($_GET) remplace par des lectures explicites (il permettait d'ecraser n'importe quelle variable)
		$delete    = isset($_GET['delete']) ? intval($_GET['delete']) : null;
		$deleteall = isset($_GET['deleteall']) ? ($_GET['deleteall'] ?? null) : '';
		if (isset($delete)) {
			doquery("DELETE FROM {{table}} WHERE `error_id`=$delete", 'errors');
		} elseif ($deleteall == 'yes') {
			doquery("TRUNCATE TABLE {{table}}", 'errors');
		}

		// Afficher les erreurs
		$query = doquery("SELECT * FROM {{table}}", 'errors');
		$i = 0;
		$parse['errors_list'] = '';
		while ($u = mysqli_fetch_array($query)) {
			$i++;
			$parse['errors_list'] .= "
			<tr><td width=\"25\" class=n>". $u['error_id'] ."</td>
			<td width=\"170\" class=n>". $u['error_type'] ."</td>
			<td width=\"230\" class=n>". date('d/m/Y H:i:s', $u['error_time']) ."</td>
			<td width=\"95\" class=n><a href=\"?delete=". $u['error_id'] ."\"><img src=\"../images/r1.png\"></a></td></tr>
			<tr><td colspan=\"4\" class=b>".  nl2br($u['error_text'])."</td></tr>";
		}
		$parse['errors_list'] .= "<tr>
			<th class=b colspan=5>". $i ." ". $lang['adm_er_nbs'] ."</th>
		</tr>";

		display(parsetemplate(gettemplate('admin/errors_body'), $parse), "Bledy", false, '', true);
	} else {
		message( $lang['sys_noalloaw'], $lang['sys_noaccess'] );
	}

?>
