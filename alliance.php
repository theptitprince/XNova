<?php

/**
 * alliance.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

define('INSTALL' , false);

$mode = ($_GET['mode'] ?? null);
if (empty($mode))   { unset($mode); }
$a     = intval($_GET['a'] ?? 0);
if (empty($a))      { unset($a); }
$sort1 = intval($_GET['sort1'] ?? 0);
if (empty($sort1))  { unset($sort1); }
$sort2 = intval($_GET['sort2'] ?? 0);
$d = isset($_GET['d']) ? intval($_GET['d']) : null;
if ((!is_numeric($d)) || (empty($d) && $d != 0))
	unset($d);

$edit = ($_GET['edit'] ?? null);

if (empty($edit))
	unset($edit);

$rank = intval($_GET['rank'] ?? 0);

$kick = intval($_GET['kick'] ?? 0);
if (empty($kick))
	unset($kick);

$id = intval($_GET['id'] ?? 0);
if (empty($id))
	unset($id);

define('INSIDE', true);
$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.' . $phpEx);

$mode     = ($_GET['mode'] ?? null);
$yes      = ($_GET['yes'] ?? null);
$edit     = ($_GET['edit'] ?? null);
$allyid   = intval($_GET['allyid'] ?? 0);
$show     = intval($_GET['show'] ?? 0);
$sort     = intval(($_GET['sort'] ?? null));
$sendmail = intval(($_GET['sendmail'] ?? null));
$t        = ($_GET['t'] ?? null);
$a        = intval(($_GET['a'] ?? null));
$tag      = SqlEscape(($_GET['tag'] ?? null));

includeLang('alliance');

$page = '';
// Rangs de l'alliance : toujours un tableau. Alliance neuve : colonne vide, unserialize() rendait false
// et count(false) faisait planter la page des droits (erreur fatale en PHP 8).
function AllyRanks ( $Ally ) {
	$Ranks = @unserialize((string) ($Ally['ally_ranks'] ?? ''), array('allowed_classes' => false));
	return is_array($Ranks) ? $Ranks : array();
}

// Balises des textes d'alliance : [fc]couleur[/fc]texte[/f] et [img]adresse[/img].
// Images : adresses http(s) uniquement, une adresse du jeu declencherait une action chez le lecteur.
function AllyBBCode ( $Text ) {
	$Patterns = array(
		"#\[fc\]([a-z0-9\#]+)\[/fc\](.*?)\[/f\]#Ssi",
		'#\[img\](https?://[^\s"<>\[]+)\[/img\]#Smi',
		"#\[fc\]([a-z0-9\#\ \[\]]+)\[/fc\]#Ssi",
		"#\[/f\]#Ssi",
	);
	$Replacements = array(
		'<font color="\1">\2</font>',
		'<img src="\1" alt="" style="border:0px;" />',
		'<font color="\1">',
		'</font>',
	);
	return preg_replace($Patterns, $Replacements, (string) $Text);
}

// Membres d'une alliance avec leurs points, dans l'ordre choisi par les en-tetes de colonnes.
// $Sort1 : 1 nom, 2 rang, 3 points, 4 adhesion, 5 derniere connexion, sinon ordre d'arrivee. $Sort2 : 1 decroissant, 2 croissant.
function AllyMembersSorted ( $AllyId, $Sort1, $Sort2 ) {
	$Members = array();
	$Query   = doquery("SELECT * FROM {{table}} WHERE `ally_id`='" . intval($AllyId) . "'", 'users');
	while ($Row = mysqli_fetch_assoc($Query)) {
		$Points = doquery("SELECT `total_points` FROM {{table}} WHERE `stat_type`='1' AND `stat_code`='1' AND `id_owner`='" . intval($Row['id']) . "'", 'statpoints', true);
		$Row['total_points'] = $Points ? $Points['total_points'] : 0;
		$Members[] = $Row;
	}
	$Fields = array(1 => 'username', 2 => 'ally_rank_id', 3 => 'total_points', 4 => 'ally_register_time', 5 => 'onlinetime');
	$Field  = $Fields[$Sort1] ?? 'id';
	// Tri par rang : le fondateur (rang 0, comme un novice) etait classe parmi les novices. Ordre : fondateur,
	// rangs crees (dans leur ordre), puis novices.
	$Owner   = doquery("SELECT `ally_owner` FROM {{table}} WHERE `id`='" . intval($AllyId) . "'", 'alliance', true);
	$OwnerId = $Owner ? $Owner['ally_owner'] : 0;
	$RankKey = function ($M) use ($OwnerId) {
		if ($M['id'] == $OwnerId) {
			return -1;
		}
		return ($M['ally_rank_id'] == 0) ? PHP_INT_MAX : (int) $M['ally_rank_id'];
	};
	usort($Members, function ($A, $B) use ($Field, $RankKey) {
		if ($Field == 'ally_rank_id') {
			return $RankKey($A) <=> $RankKey($B);
		}
		return ($Field == 'username') ? strcasecmp($A[$Field], $B[$Field]) : ($A[$Field] <=> $B[$Field]);
	});
	return ($Sort2 == 1) ? array_reverse($Members) : $Members;
}

// Remet a zero l'appartenance a une alliance (depart, exclusion, dissolution). $Where : condition SQL sur la table users.
function AllyResetMembers ( $Where ) {
	doquery("UPDATE {{table}} SET `ally_id`='0', `ally_name`='', `ally_rank_id`='0', `ally_register_time`='0' WHERE {$Where}", 'users');
}


/*
  Alianza consiste en tres partes.
  La primera es la comun. Es decir, no se necesita comprobar si se esta en una alianza o no.
  La segunda, es sin alianza. Eso implica las solicitudes.
  La ultima, seria cuando ya se esta dentro de una.
*/
// Parte inicial.

if (($_GET['mode'] ?? null) == 'ainfo') {
	$a = intval(($_GET['a'] ?? null));
	$tag = SqlEscape(($_GET['tag'] ?? null));
	$allyrow = false;

	if (isset($_GET['tag'])) {
		$allyrow = doquery("SELECT * FROM {{table}} WHERE ally_tag='{$tag}'", "alliance", true);
	} elseif ($a > 0) {
		$allyrow = doquery("SELECT * FROM {{table}} WHERE id='{$a}'", "alliance", true);
	}
	// Si no existe
	if (!$allyrow) {
		message($lang['ally_not_found'], $lang['alliance']);
	}
	extract($allyrow);
	$lang['alliance_information'] = str_replace('%s', $ally_name, $lang['info_of_alliance']);

	if ($ally_image != "") {
		$ally_image = "<tr><th colspan=2><img src=\"{$ally_image}\"></th></tr>";
	}

	if ($ally_description != "") {
		$ally_description = "<tr><th colspan=2 height=100>{$ally_description}</th></tr>";
	} else
		$ally_description = "<tr><th colspan=2 height=100>{$lang['no_description']}</th></tr>";

	if ($ally_web != "") {
		$ally_web = "<tr>
		<th>{$lang['initial_page']}</th>
		<th><a href=\"{$ally_web}\">{$ally_web}</a></th>
		</tr>";
	}

	$lang['ally_member_scount'] = $ally_members;
	$lang['ally_name'] = $ally_name;
	$lang['ally_tag'] = $ally_tag;
	$ally_description = AllyBBCode($ally_description);

	$lang['ally_description'] = nl2br($ally_description);
	$lang['ally_image'] = $ally_image;
	$lang['ally_web'] = $ally_web;

	// Lien de candidature : joueur sans alliance ni candidature en cours, alliance ouverte
	if ($user['ally_id'] == 0 && $user['ally_request'] == 0 && $ally_request_notallow == 0) {
		$lang['bewerbung'] = "<tr>
	  <th>{$lang['requests_label']}</th>
	  <th><a href=\"alliance.php?mode=apply&amp;allyid=" . $id . "\">{$lang['click_to_apply']}</a></th>
	</tr>";
	} else
		$lang['bewerbung'] = "";

	$page .= parsetemplate(gettemplate('alliance_ainfo'), $lang);
	display($page, str_replace('%s', $ally_name, $lang['info_of_alliance']));
}
// --[Comprobaciones de alianza]-------------------------
if ($user['ally_id'] == 0) { // Sin alianza
	if ($mode == 'make' && $user['ally_request'] == 0) { // Make alliance
		/*
	  Aca se crean las alianzas...
	*/
		if ($yes == 1 && $_POST) {
			/*
		  Por el momento solo estoy improvisando, luego se perfeccionara el sistema :)
		  Creo que aqui se realiza una query para comprovar el nombre, y luego le pregunta si es el tag correcto...
		*/
			// Tag et nom : texte brut, echappe dans chaque requete
			$_POST['atag']  = SafeName(($_POST['atag'] ?? null), 8);
			$_POST['aname'] = SafeName(($_POST['aname'] ?? null), 35);
			if (!($_POST['atag'] ?? null)) {
				message($lang['have_not_tag'], $lang['make_alliance']);
			}
			if (mb_strlen($_POST['atag']) < 3) {
				message($lang['tag_too_short'], $lang['make_alliance']);
			}
			if (!($_POST['aname'] ?? null)) {
				message($lang['have_not_name'], $lang['make_alliance']);
			}

			$tagquery = doquery("SELECT * FROM {{table}} WHERE ally_tag='". SqlEscape(($_POST['atag'] ?? null)) ."'", 'alliance', true);

			if ($tagquery) {
				message(str_replace('%s', ($_POST['atag'] ?? null), $lang['always_exist']), $lang['make_alliance']);
			}

			doquery("INSERT INTO {{table}} SET
			`ally_name`='". SqlEscape($_POST['aname']) ."',
			`ally_tag`='". SqlEscape($_POST['atag']) ."' ,
			`ally_owner`='{$user['id']}',
			`ally_owner_range`='',
			`ally_members`='1',
			`ally_register_time`=" . time() , "alliance");

			$allyquery = doquery("SELECT * FROM {{table}} WHERE ally_tag='". SqlEscape(($_POST['atag'] ?? null)) ."'", 'alliance', true);

			doquery("UPDATE {{table}} SET
			`ally_id`='". intval($allyquery['id']) ."',
			`ally_name`='". SqlEscape($allyquery['ally_name']) ."',
			`ally_rank_id`='0',
			`ally_register_time`='" . time() . "'
			WHERE `id`='{$user['id']}'", "users");

			$page = MessageForm(str_replace('%s', ($_POST['atag'] ?? null), $lang['ally_maked']),

				str_replace('%s', ($_POST['atag'] ?? null), $lang['alliance_has_been_maked']) . "<br><br>", "alliance.php", $lang['continue_label']);
		} else {
			$page .= parsetemplate(gettemplate('alliance_make'), $lang);
		}

		display($page, $lang['make_alliance']);
	}

	if ($mode == 'search' && $user['ally_request'] == 0) { // search one
		/*
	  Buscador de alianzas
	*/
		$parse = $lang;
		$lang['searchtext'] = SafeText(($_POST['searchtext'] ?? null));
		$page = parsetemplate(gettemplate('alliance_searchform'), $lang);

		if ($_POST) { // esta parte es igual que el buscador de search.php...
			// searchtext
			$SearchText = SqlEscape(addcslashes(($_POST['searchtext'] ?? null), '%_'));
			$search = doquery("SELECT * FROM {{table}} WHERE ally_name LIKE '%". $SearchText ."%' or ally_tag LIKE '%". $SearchText ."%' LIMIT 30", "alliance");

			$parse['result'] = '';
			if (mysqli_num_rows($search) == 0) {
				$parse['result'] = "<tr><th colspan=3>{$lang['no_alliance_found']}</th></tr>";
				$page .= parsetemplate(gettemplate('alliance_searchresult_table'), $parse);
			} else {
				$template = gettemplate('alliance_searchresult_row');

				while ($s = mysqli_fetch_array($search)) {
					$entry = array();
					$entry['ally_tag'] = "[<a href=\"alliance.php?mode=apply&allyid={$s['id']}\">{$s['ally_tag']}</a>]";
					$entry['ally_name'] = $s['ally_name'];
					$entry['ally_members'] = $s['ally_members'];

					$parse['result'] .= parsetemplate($template, $entry);
				}

				$page .= parsetemplate(gettemplate('alliance_searchresult_table'), $parse);
			}
		}

		display($page, $lang['search_alliance']);
	}

	if ($mode == 'apply' && $user['ally_request'] == 0) { // solicitudes
		if (!is_numeric(($_GET['allyid'] ?? null)) || !($_GET['allyid'] ?? null) || $user['ally_request'] != 0 || $user['ally_id'] != 0) {
			message($lang['it_is_not_posible_to_apply'], $lang['it_is_not_posible_to_apply']);
		}
		// pedimos la info de la alianza
		$allyrow = doquery("SELECT ally_tag,ally_request,ally_request_notallow FROM {{table}} WHERE id='" . intval(($_GET['allyid'] ?? null)) . "'", "alliance", true);

		if (!$allyrow) {
			message($lang['it_is_not_posible_to_apply'], $lang['it_is_not_posible_to_apply']);
		}

		extract($allyrow);
		// Alliance fermee aux candidatures
		if ($ally_request_notallow == 1) {
			message($lang['ally_closed'], $lang['your_apply'], 'alliance.php');
		}

		if (($_POST['further'] ?? null) == $lang['send_label']) { // esta parte es igual que el buscador de search.php...
			doquery("UPDATE {{table}} SET `ally_request`='" . intval($allyid) . "', ally_request_text='" . SqlEscape(SafeText(($_POST['text'] ?? null))) . "', ally_register_time='" . time() . "' WHERE `id`='" . $user['id'] . "'", "users");
			// mensaje de cuando se envia correctamente el mensaje
			message($lang['apply_registered'], $lang['your_apply']);
			// mensaje de cuando falla el envio
			// message($lang['apply_cantbeadded'], $lang['your_apply']);
		} else {
			// Modele de candidature de l'alliance ; sans modele le champ reste vide (l'indication s'affiche en filigrane)
			$text_apply = ($ally_request) ? $ally_request : '';
		}

		$parse = $lang;
		$parse['placeholder'] = ($ally_request) ? '' : $lang['there_is_no_a_text_apply'];
		$parse['allyid'] = intval(($_GET['allyid'] ?? null));
		$parse['chars_count'] = mb_strlen(html_entity_decode($text_apply, ENT_QUOTES, 'UTF-8'));
		$parse['text_apply'] = $text_apply;
		$parse['write_to_alliance'] = str_replace('%s', $ally_tag, $lang['write_to_alliance']);

		$page = parsetemplate(gettemplate('alliance_applyform'), $parse);

		display($page, $lang['write_to_alliance']);
	}

	if ($user['ally_request'] != 0) { // Esperando una respuesta
		// preguntamos por el ally_tag
		$allyquery = doquery("SELECT ally_tag FROM {{table}} WHERE id='" . intval($user['ally_request']) . "' ORDER BY `id`", "alliance", true);

		// Alliance dissoute pendant l'attente : la candidature tombe
		if (!$allyquery) {
			doquery("UPDATE {{table}} SET `ally_request`=0, `ally_request_text`='' WHERE `id`=" . intval($user['id']), "users");
			message($lang['ally_notexist'], $lang['your_apply'], 'alliance.php');
		}

		extract($allyquery);
		if (($_POST['bcancel'] ?? null)) {
			doquery("UPDATE {{table}} SET `ally_request`=0, `ally_request_text`='' WHERE `id`=" . intval($user['id']), "users");

			$lang['request_text'] = str_replace('%s', $ally_tag, $lang['canceled_a_request_text']);
			$lang['button_text'] = $lang['continue_label'];
			$page = parsetemplate(gettemplate('alliance_apply_waitform'), $lang);
		} else {
			$lang['request_text'] = str_replace('%s', $ally_tag, $lang['waiting_a_request_text']);
			$lang['button_text'] = $lang['delete_apply'];
			$page = parsetemplate(gettemplate('alliance_apply_waitform'), $lang);
		}
		display($page, $lang['your_apply']);
	} else { // Vista sin allianza
		/*
	  Vista normal de cuando no se tiene ni solicitud ni alianza
	*/
		$page = parsetemplate(gettemplate('alliance_defaultmenu'), $lang);
		display($page, $lang['alliance']);
	}
}

//---------------------------------------------------------------------------------------------------------------------------------------------------
// Parte de adentro de la alianza
elseif ($user['ally_id'] != 0 && $user['ally_request'] == 0) { // Con alianza
	// query para la allyrow
	/*
array(1 =>
	'name' => 'Co. Leader',
	'mails' => '1',
	'delete' => '0',
	'kick' => '1',
	'bewerbungen' => '1',
	'administrieren' => '1',
	'memberlist' => '1',
	'bewerbungenbearbeiten' => '1',
	'onlinestatus' => '1',
	'rechtehand' => '1'
	);

*/
	$ally = doquery("SELECT * FROM {{table}} WHERE id='{$user['ally_id']}'", "alliance", true);

	$ally_ranks = AllyRanks($ally);

	$allianz_raenge = $ally_ranks;

	// Droits du rang du joueur (aucun rang pour le fondateur : il a tous les droits)
	$MyRank  = $allianz_raenge[$user['ally_rank_id'] - 1] ?? array();
	$IsOwner = (($ally['ally_owner'] ?? 0) == $user['id']);
	$user_can_watch_memberlist_status = (($MyRank['onlinestatus'] ?? 0) == 1 || $IsOwner);
	$user_can_watch_memberlist = (($MyRank['memberlist'] ?? 0) == 1 || $IsOwner);
	$user_can_send_mails = (($MyRank['mails'] ?? 0) == 1 || $IsOwner);
	$user_can_kick = (($MyRank['kick'] ?? 0) == 1 || $IsOwner);
	$user_can_edit_rights = (($MyRank['rechtehand'] ?? 0) == 1 || $IsOwner);
	$user_can_exit_alliance = (($MyRank['delete'] ?? 0) == 1 || $IsOwner);
	$user_bewerbungen_einsehen = (($MyRank['bewerbungen'] ?? 0) == 1 || $IsOwner);
	$user_bewerbungen_bearbeiten = (($MyRank['bewerbungenbearbeiten'] ?? 0) == 1 || $IsOwner);
	$user_admin = (($MyRank['administrieren'] ?? 0) == 1 || $IsOwner);
	$user_onlinestatus = (($MyRank['onlinestatus'] ?? 0) == 1 || $IsOwner);

	if (!$ally) {
		AllyResetMembers("`id`='" . intval($user['id']) . "'");
		message($lang['ally_notexist'], $lang['your_alliance'], 'alliance.php');
	}

	if ($mode == 'exit') {
		if ($ally['ally_owner'] == $user['id']) {
			message($lang['owner_cant_go_out'], $lang['alliance']);
		}
		// se sale de la alianza
		if (($_GET['yes'] ?? null) == 1) {
			AllyResetMembers("`id`='" . intval($user['id']) . "'");
			doquery("UPDATE {{table}} SET `ally_members`=`ally_members`-1 WHERE `id`='{$ally['id']}' AND `ally_members`>0", 'alliance');
			$lang['go_out_welldone'] = str_replace("%s", $ally['ally_name'], $lang['go_out_welldone']);
			$page = MessageForm($lang['go_out_welldone'], "<br>", "alliance.php", $lang['continue_label']);
		} else {
			// se pregunta si se quiere salir
			$lang['want_go_out'] = str_replace("%s", $ally['ally_name'], $lang['want_go_out']);
			$page = MessageForm($lang['want_go_out'], "<br>", "?mode=exit&yes=1", $lang['ok']);
		}
		display($page, $lang['exit_of_this_alliance']);
	}

	if ($mode == 'memberslist') { // Lista de miembros.
		/*
	  Lista de miembros.
	  Por lo que parece solo se hace una query fijandose los usuarios con el mismo ally_id.
	  seguido del query del planeta principal de cada uno para sacarle la posicion, pero
	  voy a ver si tambien agrego las cordenadas en el id user...
	*/
		// obtenemos el array de los rangos
		// $ally_ranks = AllyRanks($ally);
		$allianz_raenge = AllyRanks($ally);
		// $user_can_watch_memberlist
		// comprobamos el permiso
		if ($ally['ally_owner'] != $user['id'] && !$user_can_watch_memberlist) {
			message($lang['denied_access'], $lang['members_list_label']);
		}
		// El orden de aparicion
		$listuser = AllyMembersSorted($user['ally_id'], $sort1 ?? 0, $sort2);
		// contamos la cantidad de usuarios.
		$i = 0;
		// Como es costumbre. un row template
		$template = gettemplate('alliance_memberslist_row');
		$page_list = '';
		foreach ($listuser as $u) {
			$i++;
			$u['i'] = $i;

			if ($u["onlinetime"] + 60 * 10 >= time() && $user_can_watch_memberlist_status) {
				$u["onlinetime"] = "lime>{$lang['on_label']}<";
			} elseif ($u["onlinetime"] + 60 * 20 >= time() && $user_can_watch_memberlist_status) {
				$u["onlinetime"] = "yellow>{$lang['15_min']}<";
			} elseif ($user_can_watch_memberlist_status) {
				$u["onlinetime"] = "red>{$lang['off']}<";
			} else $u["onlinetime"] = "orange>-<";
			// Nombre de rango (rangs numerotes a partir de 1, tableau a partir de 0)
			if ($ally['ally_owner'] == $u['id']) {
				$u["ally_range"] = ($ally['ally_owner_range'] == '') ? $lang['founder'] : $ally['ally_owner_range'];
			} elseif (isset($allianz_raenge[$u['ally_rank_id'] - 1]['name'])) {
				$u["ally_range"] = $allianz_raenge[$u['ally_rank_id'] - 1]['name'];
			} else {
				$u["ally_range"] = $lang['novate'];
			}

			$u["dpath"]  = $dpath;
			$u['points'] = pretty_number($u['total_points']);

			if ($u['ally_register_time'] > 0)
				$u['ally_register_time'] = date("d/m/Y H:i:s", $u['ally_register_time']);
			else
				$u['ally_register_time'] = "-";

			$page_list .= parsetemplate($template, $u);
		}
		// para cambiar el link de ordenar.
		if ($sort2 == 1) {
			$s = 2;
		} elseif ($sort2 == 2) {
			$s = 1;
		} else {
			$s = 1;
		}

		if ($i != $ally['ally_members']) {
			doquery("UPDATE {{table}} SET `ally_members`='{$i}' WHERE `id`='{$ally['id']}'", 'alliance');
		}

		$parse = $lang;
		$parse['i'] = $i;
		$parse['s'] = $s;
		$parse['list'] = $page_list;

		$page .= parsetemplate(gettemplate('alliance_memberslist_table'), $parse);

		display($page, $lang['members_list_label']);
	}

	if ($mode == 'circular') { // Correo circular
		/*
	  Mandar un correo circular.
	  creo que aqui tendria que ver yo como crear el sistema de mensajes...
	*/
		// un loop para mostrar losrangos
		$allianz_raenge = AllyRanks($ally);
		// comprobamos el permiso
		if ($ally['ally_owner'] != $user['id'] && !$user_can_send_mails) {
			message($lang['denied_access'], $lang['send_circular_mail_label']);
		}

		if ($sendmail == 1) {
			$_POST['r'] = intval(($_POST['r'] ?? null));
			$_POST['text'] = SqlEscape(SafeText(($_POST['text'] ?? null)));

			// Destinataires : tous les membres, ou ceux d'un rang
			$Where = "ally_id='" . intval($user['ally_id']) . "'" . ((($_POST['r'] ?? null) == 0) ? '' : " AND ally_rank_id='{$_POST['r']}'");
			$sq = doquery("SELECT id,username FROM {{table}} WHERE {$Where}", "users");
			// looooooop
			$list = '';
			while ($u = mysqli_fetch_array($sq)) {
				doquery("INSERT INTO {{table}} SET
				`message_owner`='{$u['id']}',
				`message_sender`='{$user['id']}' ,
				`message_time`='" . time() . "',
				`message_type`='2',
				`message_from`='" . SqlEscape($ally['ally_tag']) . "',
				`message_subject`='" . SqlEscape($user['username']) . "',
				`message_text`='{$_POST['text']}'
				", "messages");
				$list .= "<br>{$u['username']} ";
			}
			// Compteurs de nouveaux messages : les memes destinataires
			doquery("UPDATE {{table}} SET `new_message`=new_message+1, `mnl_alliance`=mnl_alliance+1 WHERE {$Where}", "users");
			/*
		  Aca un mensajito diciendo que a quien se mando.
		*/
			$page = MessageForm($lang['circular_sended'], $list, "alliance.php", $lang['continue_label'], true);
			display($page, $lang['send_circular_mail_label']);
		}

		$lang['r_list'] = "<option value=\"0\">{$lang['all_players']}</option>";
		if ($allianz_raenge) {
			foreach($allianz_raenge as $id => $array) {
				$lang['r_list'] .= "<option value=\"" . ($id + 1) . "\">" . $array['name'] . "</option>";
			}
		}

		$page .= parsetemplate(gettemplate('alliance_circular'), $lang);

		display($page, $lang['send_circular_mail_label']);
	}

	if ($mode == 'admin' && $edit == 'rights') { // Administrar leyes
		$allianz_raenge = AllyRanks($ally);

		if ($ally['ally_owner'] != $user['id'] && !$user_can_edit_rights) {
			message($lang['denied_access'], $lang['law_settings']);
		} elseif (!empty($_POST['newrangname'])) {
			$name = SafeName(($_POST['newrangname'] ?? null), 32);

			$allianz_raenge[] = array('name' => $name,
				'mails' => 0,
				'delete' => 0,
				'kick' => 0,
				'bewerbungen' => 0,
				'administrieren' => 0,
				'bewerbungenbearbeiten' => 0,
				'memberlist' => 0,
				'onlinestatus' => 0,
				'rechtehand' => 0
				);

			$ranks = serialize($allianz_raenge);

			doquery("UPDATE {{table}} SET `ally_ranks`='" . SqlEscape($ranks) . "' WHERE `id`=" . intval($ally['id']), "alliance");

			$goto = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];

			header("Location: " . $goto);
			exit();
		} elseif (($_POST['id'] ?? null) != '' && is_array(($_POST['id'] ?? null))) {
			$ally_ranks_new = array();

			foreach (($_POST['id'] ?? null) as $id) {
				$id = intval($id);
				if (!isset($allianz_raenge[$id])) {
					continue;
				}
				$name = $allianz_raenge[$id]['name'];

				$ally_ranks_new[$id]['name'] = $name;

				// Dissoudre et exclure : droits que seul le fondateur accorde (les autres gardent la valeur en place)
				if ($IsOwner) {
					$ally_ranks_new[$id]['delete'] = isset($_POST['u' . $id . 'r0']) ? 1 : 0;
					$ally_ranks_new[$id]['kick']   = isset($_POST['u' . $id . 'r1']) ? 1 : 0;
				} else {
					$ally_ranks_new[$id]['delete'] = intval($allianz_raenge[$id]['delete'] ?? 0);
					$ally_ranks_new[$id]['kick']   = intval($allianz_raenge[$id]['kick'] ?? 0);
				}

				if (isset($_POST['u' . $id . 'r2'])) {
					$ally_ranks_new[$id]['bewerbungen'] = 1;
				} else {
					$ally_ranks_new[$id]['bewerbungen'] = 0;
				}

				if (isset($_POST['u' . $id . 'r3'])) {
					$ally_ranks_new[$id]['memberlist'] = 1;
				} else {
					$ally_ranks_new[$id]['memberlist'] = 0;
				}

				if (isset($_POST['u' . $id . 'r4'])) {
					$ally_ranks_new[$id]['bewerbungenbearbeiten'] = 1;
				} else {
					$ally_ranks_new[$id]['bewerbungenbearbeiten'] = 0;
				}

				if (isset($_POST['u' . $id . 'r5'])) {
					$ally_ranks_new[$id]['administrieren'] = 1;
				} else {
					$ally_ranks_new[$id]['administrieren'] = 0;
				}

				if (isset($_POST['u' . $id . 'r6'])) {
					$ally_ranks_new[$id]['onlinestatus'] = 1;
				} else {
					$ally_ranks_new[$id]['onlinestatus'] = 0;
				}

				if (isset($_POST['u' . $id . 'r7'])) {
					$ally_ranks_new[$id]['mails'] = 1;
				} else {
					$ally_ranks_new[$id]['mails'] = 0;
				}

				if (isset($_POST['u' . $id . 'r8'])) {
					$ally_ranks_new[$id]['rechtehand'] = 1;
				} else {
					$ally_ranks_new[$id]['rechtehand'] = 0;
				}
			}

			$ranks = serialize($ally_ranks_new);

			doquery("UPDATE {{table}} SET `ally_ranks`='" . SqlEscape($ranks) . "' WHERE `id`=" . intval($ally['id']), "alliance");

			$goto = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];

			header("Location: " . $goto);
			exit();
		}
		// borrar una entrada
		elseif (isset($d) && isset($ally_ranks[$d])) {
			unset($ally_ranks[$d]);
			$ally['ally_rank'] = serialize($ally_ranks);

			doquery("UPDATE {{table}} SET `ally_ranks`='". SqlEscape($ally['ally_rank']) ."' WHERE `id`=". intval($ally['id']), "alliance");
			// Les membres de ce rang redeviennent novices (sinon un futur rang au meme numero leur donnerait ses droits)
			doquery("UPDATE {{table}} SET `ally_rank_id`='0' WHERE `ally_id`='" . intval($ally['id']) . "' AND `ally_rank_id`='" . ($d + 1) . "'", 'users');
		}

		if (count($ally_ranks) == 0 || $ally_ranks == '') { // si no hay rangos
			$list = "<th>{$lang['there_is_not_range']}</th>";
		} else { // Si hay rangos
			// cargamos la template de tabla
			$list = parsetemplate(gettemplate('alliance_admin_laws_head'), $lang);
			$template = gettemplate('alliance_admin_laws_row');
			// Creamos la lista de rangos
			$i = 0;

			foreach($ally_ranks as $a => $b) {
				if ($ally['ally_owner'] == $user['id']) {
					// $i++;u2r5
					$lang['id'] = $a;
					$lang['delete'] = "<a href=\"alliance.php?mode=admin&edit=rights&d={$a}\"><img src=\"{$dpath}pic/abort.gif\" alt=\"{$lang['delete_range']}\" border=0></a>";
					$lang['r0'] = $b['name'];
					$lang['a'] = $a;
					$lang['r1'] = "<input type=checkbox name=\"u{$a}r0\"" . (($b['delete'] == 1)?' checked="checked"':'') . ">"; //{$b[1]}
					$lang['r2'] = "<input type=checkbox name=\"u{$a}r1\"" . (($b['kick'] == 1)?' checked="checked"':'') . ">";
					$lang['r3'] = "<input type=checkbox name=\"u{$a}r2\"" . (($b['bewerbungen'] == 1)?' checked="checked"':'') . ">";
					$lang['r4'] = "<input type=checkbox name=\"u{$a}r3\"" . (($b['memberlist'] == 1)?' checked="checked"':'') . ">";
					$lang['r5'] = "<input type=checkbox name=\"u{$a}r4\"" . (($b['bewerbungenbearbeiten'] == 1)?' checked="checked"':'') . ">";
					$lang['r6'] = "<input type=checkbox name=\"u{$a}r5\"" . (($b['administrieren'] == 1)?' checked="checked"':'') . ">";
					$lang['r7'] = "<input type=checkbox name=\"u{$a}r6\"" . (($b['onlinestatus'] == 1)?' checked="checked"':'') . ">";
					$lang['r8'] = "<input type=checkbox name=\"u{$a}r7\"" . (($b['mails'] == 1)?' checked="checked"':'') . ">";
					$lang['r9'] = "<input type=checkbox name=\"u{$a}r8\"" . (($b['rechtehand'] == 1)?' checked="checked"':'') . ">";

					$list .= parsetemplate($template, $lang);
				} else {
					$lang['id'] = $a;
					$lang['r0'] = $b['name'];
					$lang['delete'] = "<a href=\"alliance.php?mode=admin&edit=rights&d={$a}\"><img src=\"{$dpath}pic/abort.gif\" alt=\"{$lang['delete_range']}\" border=0></a>";
					$lang['a'] = $a;
					// Dissoudre et exclure : visibles, mais seul le fondateur peut les changer
					$lang['r1'] = "<input type=checkbox disabled" . (($b['delete'] == 1)?' checked="checked"':'') . ">";
					$lang['r2'] = "<input type=checkbox disabled" . (($b['kick'] == 1)?' checked="checked"':'') . ">";
					$lang['r3'] = "<input type=checkbox name=\"u{$a}r2\"" . (($b['bewerbungen'] == 1)?' checked="checked"':'') . ">";
					$lang['r4'] = "<input type=checkbox name=\"u{$a}r3\"" . (($b['memberlist'] == 1)?' checked="checked"':'') . ">";
					$lang['r5'] = "<input type=checkbox name=\"u{$a}r4\"" . (($b['bewerbungenbearbeiten'] == 1)?' checked="checked"':'') . ">";
					$lang['r6'] = "<input type=checkbox name=\"u{$a}r5\"" . (($b['administrieren'] == 1)?' checked="checked"':'') . ">";
					$lang['r7'] = "<input type=checkbox name=\"u{$a}r6\"" . (($b['onlinestatus'] == 1)?' checked="checked"':'') . ">";
					$lang['r8'] = "<input type=checkbox name=\"u{$a}r7\"" . (($b['mails'] == 1)?' checked="checked"':'') . ">";
					$lang['r9'] = "<input type=checkbox name=\"u{$a}r8\"" . (($b['rechtehand'] == 1)?' checked="checked"':'') . ">";

					$list .= parsetemplate($template, $lang);
				}
			}

			if (count($ally_ranks) != 0) {
				$list .= parsetemplate(gettemplate('alliance_admin_laws_feet'), $lang);
			}
		}

		$lang['list'] = $list;
		$lang['dpath'] = $dpath;
		$page .= parsetemplate(gettemplate('alliance_admin_laws'), $lang);

		display($page, $lang['law_settings']);
	}

	if ($mode == 'admin' && $edit == 'ally') { // Administrar la alianza *pendiente urgente*
		// Droit "Administrer l'alliance" (ou fondateur)
		if (!$user_admin) {
			message($lang['denied_access'], $lang['alliance_admin_label']);
		}
		if ($t != 1 && $t != 2 && $t != 3) {
			$t = 1;
		}
		// post! (plus de stripslashes : sans magic_quotes, il effacait les barres obliques inverses tapees par le joueur)
		if (($_POST['options'] ?? null)) {
			$ally['ally_owner_range'] = SqlEscape(SafeName(($_POST['owner_range'] ?? null), 32));

			$ally['ally_web'] = SqlEscape(SafeUrl(($_POST['web'] ?? null)));

			$ally['ally_image'] = SqlEscape(SafeUrl(($_POST['image'] ?? null)));

			$ally['ally_request_notallow'] = (intval(($_POST['request_notallow'] ?? null)) == 1) ? 1 : 0;

			doquery("UPDATE {{table}} SET
			`ally_owner_range`='{$ally['ally_owner_range']}',
			`ally_image`='{$ally['ally_image']}',
			`ally_web`='{$ally['ally_web']}',
			`ally_request_notallow`='{$ally['ally_request_notallow']}'
			WHERE `id`='{$ally['id']}'", "alliance");
		} elseif (($_POST['t'] ?? null)) {
			if ($t == 3) {
				$ally['ally_request'] = SqlEscape(SafeText(($_POST['text'] ?? null)));

				doquery("UPDATE {{table}} SET
				`ally_request`='{$ally['ally_request']}'
				WHERE `id`='{$ally['id']}'", "alliance");
			} elseif ($t == 2) {
				$ally['ally_text'] = SqlEscape(SafeText(($_POST['text'] ?? null)));
				doquery("UPDATE {{table}} SET
				`ally_text`='{$ally['ally_text']}'
				WHERE `id`='{$ally['id']}'", "alliance");
			} else {
				$ally['ally_description'] = SqlEscape(SafeText(($_POST['text'] ?? null)));

				doquery("UPDATE {{table}} SET
				`ally_description`='" . $ally['ally_description'] . "'
				WHERE `id`='{$ally['id']}'", "alliance");
			}
		}
		$lang['dpath'] = $dpath;
		/*
	  Depende del $t, muestra el formulario para cada tipo de texto.
	*/
		if ($t == 3) {
			$lang['request_type'] = $lang['show_of_request_text'];
			$lang['text'] = $ally['ally_request'];
		} elseif ($t == 2) {
			$lang['request_type'] = $lang['internal_text_of_alliance'];
			$lang['text'] = $ally['ally_text'];
		} else {
			$lang['request_type'] = $lang['public_text_of_alliance'];
			$lang['text'] = $ally['ally_description'];
		}
		$lang['t'] = $t;

		$lang['ally_web'] = $ally['ally_web'];
		$lang['ally_image'] = $ally['ally_image'];
		$lang['ally_request_notallow_0'] = (($ally['ally_request_notallow'] == 1) ? ' SELECTED' : '');
		$lang['ally_request_notallow_1'] = (($ally['ally_request_notallow'] == 0) ? ' SELECTED' : '');
		$lang['ally_owner_range'] = $ally['ally_owner_range'];
		// Ceder : fondateur uniquement. Dissoudre : fondateur ou droit "Dissoudre l'alliance" (confirmation ensuite).
		$lang['transfer_alliance'] = $IsOwner ? MessageForm($lang['transfer_alliance'], "", "?mode=admin&edit=give", $lang['continue_label']) : '';
		$lang['disolve_alliance'] = $user_can_exit_alliance ? MessageForm($lang['alliance_dissolve'], "", "?mode=admin&edit=exit", $lang['continue_label']) : '';

		$page .= parsetemplate(gettemplate('alliance_admin'), $lang);
		display($page, $lang['alliance_admin_label']);
	}

	if ($mode == 'admin' && $edit == 'members') { // Administrar a los miembros
		/*
	  En la administrar a los miembros se pueden establecer los rangos
	  para dar los diferentes derechos "Leyes"
	*/
		// comprobamos el permiso
		if ($ally['ally_owner'] != $user['id'] && !$user_can_kick) {
			message($lang['denied_access'], $lang['members_administrate']);
		}

		/*
	  Kickear usuarios requiere el permiso numero 1
	*/
		if (isset($kick)) {
			if ($ally['ally_owner'] != $user['id'] && !$user_can_kick) {
				message($lang['denied_access'], $lang['members_administrate']);
			}

			$u = doquery("SELECT * FROM {{table}} WHERE id='{$kick}' LIMIT 1", 'users', true);
			// kickeamos!
			if ($u && $u['ally_id'] == $ally['id'] && $u['id'] != $ally['ally_owner']) {
				AllyResetMembers("`id`='" . intval($u['id']) . "'");
				doquery("UPDATE {{table}} SET `ally_members`=`ally_members`-1 WHERE `id`='{$ally['id']}' AND `ally_members`>0", 'alliance');
				SendSimpleMessage($u['id'], $user['id'], time(), 2, $ally['ally_tag'], $lang['kicked_subject'], str_replace('%s', $ally['ally_name'], $lang['kicked_text']));
			}
		} elseif (isset($_POST['newrang']) && isset($id)) {
			// Nouveau rang : membre de cette alliance, jamais le fondateur, rang existant ou novice (0)
			$NewRank = intval($_POST['newrang']);
			$q = doquery("SELECT id,ally_id FROM {{table}} WHERE id='" . intval($id) . "' LIMIT 1", 'users', true);

			if ($q && $q['ally_id'] == $ally['id'] && $q['id'] != $ally['ally_owner'] && ($NewRank == 0 || isset($ally_ranks[$NewRank - 1]))) {
				doquery("UPDATE {{table}} SET `ally_rank_id`='{$NewRank}' WHERE `id`='" . intval($id) . "'", 'users');
			}
		}
		// obtenemos las template row
		$template = gettemplate('alliance_admin_members_row');
		$f_template = gettemplate('alliance_admin_members_function');
		// El orden de aparicion
		$listuser = AllyMembersSorted($user['ally_id'], $sort1 ?? 0, $sort2);
		// contamos la cantidad de usuarios.
		$i = 0;
		// Como es costumbre. un row template
		$page_list = '';
		$lang['memberzahl'] = count($listuser);

		foreach ($listuser as $u) {
			$i++;
			$u['i'] = $i;
			// Dias de inactivos
			$u['points'] = pretty_number($u['total_points']);
			$days = floor(max(0, time() - $u["onlinetime"]) / 86400);
			$u["onlinetime"] = $days . ' ' . $lang['days_short'];
			// Nombre de rango
			if ($ally['ally_owner'] == $u['id']) {
				$ally_range = ($ally['ally_owner_range'] == '')?$lang['founder']:$ally['ally_owner_range'];
			} elseif ($u['ally_rank_id'] == 0 || !isset($ally_ranks[$u['ally_rank_id']-1]['name'])) {
				$ally_range = $lang['novate'];
			} else {
				$ally_range = $ally_ranks[$u['ally_rank_id']-1]['name'];
			}

			/*
		  Aca viene la parte jodida...
		*/
			if ($ally['ally_owner'] == $u['id'] || $rank == $u['id']) {
				$u["functions"] = '';
			} elseif ($user_can_kick) {
				// Textes places dans du JavaScript (infobulle, confirmation) : apostrophes echappees
				$f['dpath'] = $dpath;
				$f['expel_user'] = htmlspecialchars(addslashes($lang['expel_user']), ENT_QUOTES, 'UTF-8', false);
				$f['set_range'] = htmlspecialchars(addslashes($lang['set_range']), ENT_QUOTES, 'UTF-8', false);
				$f['you_are_sure_want_kick_to'] = htmlspecialchars(addslashes(str_replace("%s", $u['username'], $lang['you_are_sure_want_kick_to'])), ENT_QUOTES, 'UTF-8', false);
				$f['id'] = $u['id'];
				$u["functions"] = parsetemplate($f_template, $f);
			} else {
				$u["functions"] = '';
			}
			$u["dpath"] = $dpath;
			// por el formulario...
			if ($rank != $u['id']) {
				$u['ally_range'] = $ally_range;
			} else {
				$u['ally_range'] = '';
			}
			$u['ally_register_time'] = ($u['ally_register_time'] > 0) ? date("d/m/Y H:i:s", $u['ally_register_time']) : '-';
			$page_list .= parsetemplate($template, $u);
			if ($rank == $u['id']) {
				$r = array();
				$r['rank_for'] = str_replace("%s", $u['username'], $lang['rank_for']);
				$r['options'] = "<option value=\"0\">{$lang['novate']}</option>";

				foreach($ally_ranks as $a => $b) {
					$r['options'] .= "<option value=\"" . ($a + 1) . "\"";
					if ($u['ally_rank_id']-1 == $a) {
						$r['options'] .= ' selected=selected';
					}
					$r['options'] .= ">{$b['name']}</option>";
				}
				$r['id'] = $u['id'];
				$r['save'] = $lang['save'];
				$page_list .= parsetemplate(gettemplate('alliance_admin_members_row_edit'), $r);
			}
		}
		// para cambiar el link de ordenar.
		if ($sort2 == 1) {
			$s = 2;
		} elseif ($sort2 == 2) {
			$s = 1;
		} else {
			$s = 1;
		}

		if ($i != $ally['ally_members']) {
			doquery("UPDATE {{table}} SET `ally_members`='{$i}' WHERE `id`='{$ally['id']}'", 'alliance');
		}

		$lang['memberslist'] = $page_list;
		$lang['s'] = $s;
		$page .= parsetemplate(gettemplate('alliance_admin_members_table'), $lang);

		display($page, $lang['members_administrate']);
		// a=9 es para cambiar la etiqueta de la etiqueta.
		// a=10 es para cambiarle el nombre de la alianza
	}


	if ($mode == 'admin' && $edit == 'requests') { // Administrar solicitudes
		if ($ally['ally_owner'] != $user['id'] && !$user_bewerbungen_bearbeiten) {
			message($lang['denied_access'], $lang['check_the_requests']);
		}

		if (isset($_POST['accept']) || isset($_POST['refuse'])) {
			// Uniquement un joueur qui a vraiment postule chez nous (sinon : enrolement force ou exclusion d'un autre joueur)
			$Candidate = doquery("SELECT id FROM {{table}} WHERE id='{$show}' AND ally_request='{$ally['id']}' AND ally_id='0' LIMIT 1", 'users', true);
			if (!$Candidate) {
				message($lang['request_not_found'], $lang['check_the_requests'], 'alliance.php?mode=admin&edit=requests');
			}
			// Mot facultatif de l'alliance, ajoute au message envoye au candidat
			$Text = SafeText(($_POST['text'] ?? null));
			$Text = ($Text != '') ? "<br><br>{$lang['request_answer_message']}<br>" . nl2br($Text) : '';

			if (isset($_POST['accept'])) {
				doquery("UPDATE {{table}} SET
				ally_members=ally_members+1
				WHERE id='{$ally['id']}'", 'alliance');

				doquery("UPDATE {{table}} SET
				ally_name='" . SqlEscape($ally['ally_name']) . "',
				ally_request_text='',
				ally_request='0',
				ally_id='{$ally['id']}',
				ally_rank_id='0',
				ally_register_time='" . time() . "'
				WHERE id='{$show}'", 'users');

				SendSimpleMessage($show, $user['id'], time(), 2, $ally['ally_tag'], $lang['request_accepted_subject'], str_replace('%s', $ally['ally_name'], $lang['request_accepted_text']) . $Text);
			} else {
				doquery("UPDATE {{table}} SET ally_request_text='', ally_request='0' WHERE id='{$show}'", 'users');

				SendSimpleMessage($show, $user['id'], time(), 2, $ally['ally_tag'], $lang['request_refused_subject'], str_replace('%s', $ally['ally_name'], $lang['request_refused_text']) . $Text);
			}

			header('Location:alliance.php?mode=admin&edit=requests');
			die();
		}

		$row = gettemplate('alliance_admin_request_row');
		$i = 0;
		$parse = $lang;
		$parse['list'] = '';
		$s = array();
		// Tri : par nom (sort=1) ou par date de candidature
		$Order = ($sort == 1) ? '`username`' : '`ally_register_time`';
		$query = doquery("SELECT id,username,ally_request_text,ally_register_time FROM {{table}} WHERE ally_request='{$ally['id']}' ORDER BY {$Order}", 'users');
		while ($r = mysqli_fetch_array($query)) {
			// recolectamos los datos del que se eligio.
			if (isset($show) && $r['id'] == $show) {
				$s['username'] = $r['username'];
				$s['ally_request_text'] = nl2br($r['ally_request_text']);
				$s['id'] = $r['id'];
			}
			// la fecha de cuando se envio la solicitud
			$r['time'] = date("d/m/Y H:i:s", $r['ally_register_time']);
			$parse['list'] .= parsetemplate($row, $r);
			$i++;
		}
		if ($parse['list'] == '') {
			$parse['list'] = "<tr><th colspan=2>{$lang['no_requests']}</th></tr>";
		}
		// Con $show (candidature choisie dans la liste)
		if (isset($s['id'])) {
			// Los datos de la solicitud
			$s['request_from'] = str_replace('%s', $s['username'], $lang['request_from']);
			// el formulario (un seul passage : un second effacait les libelles, vides depuis la 0.8e)
			$parse['request'] = parsetemplate(gettemplate('alliance_admin_request_form'), array_merge($lang, $s));
		} else {
			$parse['request'] = '';
		}

		$parse['ally_tag'] = $ally['ally_tag'];
		$parse['back'] = $lang['back'];

		$parse['there_is_hanging_request'] = str_replace('%n', $i, $lang['there_is_hanging_request']);
		// $parse['list'] = $lang['return_to_overview'];
		$page = parsetemplate(gettemplate('alliance_admin_request_table'), $parse);
		display($page, $lang['check_the_requests']);
	}

	if ($mode == 'admin' && $edit == 'name') {
		 // Changer le nom de l'alliance

		$ally_ranks = AllyRanks($ally);
		// comprobamos el permiso
		if ($ally['ally_owner'] != $user['id'] && !$user_admin) {
			message($lang['denied_access'], $lang['change_the_ally_name']);
		}

		if (($_POST['newname'] ?? null)) {
			// Y a le nouveau Nom
			$NewName = SafeName(($_POST['newname'] ?? null), 35);
			if ($NewName == '') {
				message($lang['have_not_name'], $lang['change_the_ally_name'], 'alliance.php?mode=admin&edit=name');
			}
			$ally['ally_name'] = SqlEscape($NewName);
			doquery("UPDATE {{table}} SET `ally_name` = '". $ally['ally_name'] ."' WHERE `id` = '". $user['ally_id'] ."';", 'alliance');
			doquery("UPDATE {{table}} SET `ally_name` = '". $ally['ally_name'] ."' WHERE `ally_id` = '". $ally['id'] ."';", 'users');
		}

		$parse['question']           = str_replace('%s', $ally['ally_name'], $lang['how_you_will_call_the_alliance_in_the_future']);
		$parse['new_name']           = $lang['new_name'];
		$parse['change']             = $lang['change'];
		$parse['name']               = 'newname';
		$parse['return_to_overview'] = $lang['return_to_overview'];
		$page .= parsetemplate(gettemplate('alliance_admin_rename'), $parse);
		display($page, $lang['alliance_admin_label']);

	}

	if ($mode == 'admin' && $edit == 'tag') {
		// Changer le TAG l'alliance
		$ally_ranks = AllyRanks($ally);

		// Bon si on verifiait les autorisation ?
		if ($ally['ally_owner'] != $user['id'] && !$user_admin) {
			message($lang['denied_access'], $lang['change_the_ally_tag']);
		}

		if (($_POST['newtag'] ?? null)) {
			// Y a le nouveau TAG : 3 caracteres minimum, pas deja pris par une autre alliance
			$NewTag = SafeName(($_POST['newtag'] ?? null), 8);
			if (mb_strlen($NewTag) < 3) {
				message($lang['tag_too_short'], $lang['change_the_ally_tag'], 'alliance.php?mode=admin&edit=tag');
			}
			if (doquery("SELECT id FROM {{table}} WHERE `ally_tag`='" . SqlEscape($NewTag) . "' AND `id`<>'{$ally['id']}'", 'alliance', true)) {
				message(str_replace('%s', $NewTag, $lang['always_exist']), $lang['change_the_ally_tag'], 'alliance.php?mode=admin&edit=tag');
			}
			$ally['ally_tag'] = SqlEscape($NewTag);
			doquery("UPDATE {{table}} SET `ally_tag` = '". $ally['ally_tag'] ."' WHERE `id` = '". $user['ally_id'] ."';", 'alliance');
		}

		$parse['question']           = str_replace('%s', $ally['ally_tag'], $lang['new_tag_question']);
		$parse['new_name']           = $lang['new_tag'];
		$parse['change']             = $lang['change'];
		$parse['name']               = 'newtag';
		$parse['return_to_overview'] = $lang['return_to_overview'];
		$page .= parsetemplate(gettemplate('alliance_admin_rename'), $parse);
		display($page, $lang['alliance_admin_label']);
	}

	if ($mode == 'admin' && $edit == 'exit') { // disolver una alianza
		// comprobamos el permiso
		if (!$user_can_exit_alliance) {
			message($lang['denied_access'], $lang['alliance_dissolve']);
		}
		// Confirmation d'abord : le parametre yes exige le jeton anti-CSRF (un simple lien ou une image ne suffit plus)
		if ($yes != 1) {
			$Question = str_replace('%s', $ally['ally_name'], $lang['dissolve_confirm']) . "<br><br><a href=\"alliance.php?mode=admin&edit=ally\">{$lang['return_to_overview']}</a><br><br>";
			$page = MessageForm($lang['alliance_dissolve'], $Question, "?mode=admin&edit=exit&yes=1", $lang['ok'], true);
			display($page, $lang['alliance_dissolve']);
		}
		// Membres prevenus puis liberes, candidatures en attente annulees, classement de l'alliance retire
		$Members = doquery("SELECT id FROM {{table}} WHERE `ally_id`='{$ally['id']}' AND `id`<>'{$user['id']}'", 'users');
		while ($Member = mysqli_fetch_assoc($Members)) {
			SendSimpleMessage($Member['id'], $user['id'], time(), 2, $ally['ally_tag'], $lang['alliance_dissolve'], str_replace('%s', $ally['ally_name'], $lang['dissolved']));
		}
		AllyResetMembers("`ally_id`='{$ally['id']}'");
		doquery("UPDATE {{table}} SET `ally_request`='0', `ally_request_text`='' WHERE `ally_request`='{$ally['id']}'", 'users');
		doquery("DELETE FROM {{table}} WHERE `stat_type`='2' AND `id_owner`='{$ally['id']}'", 'statpoints');
		doquery("DELETE FROM {{table}} WHERE id='{$ally['id']}'", "alliance");
		message(str_replace('%s', $ally['ally_name'], $lang['dissolved']), $lang['alliance_dissolve'], 'alliance.php');
	}

	if ($mode == 'admin' && $edit == 'give') { // Ceder l'alliance
		if (!$IsOwner) {
			message($lang['denied_access'], $lang['transfer_alliance']);
		}
		// Seul un membre dont le rang a le droit "Main droite" peut devenir fondateur
		$Candidates = array();
		$Query = doquery("SELECT id,username,ally_rank_id FROM {{table}} WHERE `ally_id`='{$ally['id']}' AND `id`<>'{$user['id']}' ORDER BY `username`", 'users');
		while ($Row = mysqli_fetch_assoc($Query)) {
			if (($ally_ranks[$Row['ally_rank_id'] - 1]['rechtehand'] ?? 0) == 1) {
				$Candidates[$Row['id']] = $Row;
			}
		}
		if (count($Candidates) == 0) {
			message($lang['transfer_none'], $lang['transfer_alliance'], 'alliance.php?mode=admin&edit=ally');
		}

		$NewOwner = intval($_POST['newleader'] ?? 0);
		if (isset($Candidates[$NewOwner])) {
			// Le nouveau fondateur n'a plus besoin de rang ; l'ancien prend le sien et garde donc la "Main droite"
			doquery("UPDATE {{table}} SET `ally_owner`='{$NewOwner}' WHERE `id`='{$ally['id']}'", 'alliance');
			doquery("UPDATE {{table}} SET `ally_rank_id`='" . intval($Candidates[$NewOwner]['ally_rank_id']) . "' WHERE `id`='{$user['id']}'", 'users');
			doquery("UPDATE {{table}} SET `ally_rank_id`='0' WHERE `id`='{$NewOwner}'", 'users');
			SendSimpleMessage($NewOwner, $user['id'], time(), 2, $ally['ally_tag'], $lang['transfer_alliance'], str_replace(array('%s', '%a'), array($user['username'], $ally['ally_name']), $lang['transfer_received']));
			message(str_replace('%s', $Candidates[$NewOwner]['username'], $lang['transfer_done']), $lang['transfer_alliance'], 'alliance.php');
		}

		$parse = $lang;
		$parse['options'] = '';
		foreach ($Candidates as $Row) {
			$parse['options'] .= "<option value=\"{$Row['id']}\">{$Row['username']}</option>";
		}
		$page .= parsetemplate(gettemplate('alliance_admin_transfer'), $parse);
		display($page, $lang['transfer_alliance']);
	}
	{
	 // Default *falta revisar...*
		// Rang affiche : titre du fondateur (par defaut "Fondateur"), nom du rang, ou novice
		if ($ally['ally_owner'] == $user['id']) {
			$range = ($ally['ally_owner_range'] != '') ? $ally['ally_owner_range'] : $lang['founder'];
		} elseif ($user['ally_rank_id'] != 0 && isset($ally_ranks[$user['ally_rank_id']-1]['name'])) {
			$range = $ally_ranks[$user['ally_rank_id']-1]['name'];
		} else {
			$range = $lang['novate'];
		}
		// Link de la lista de miembros
		if ($user_can_watch_memberlist) {
			$lang['members_list'] = " (<a href=\"?mode=memberslist\">{$lang['members_list_label']}</a>)";
		} else {
			$lang['members_list'] = '';
		}
		// El link de adminstrar la allianza
		if ($user_admin) {
			$lang['alliance_admin'] = " (<a href=\"?mode=admin&edit=ally\">{$lang['alliance_admin_label']}</a>)";
		} else {
			$lang['alliance_admin'] = '';
		}
		// El link de enviar correo circular
		if ($user_can_send_mails) {
			$lang['send_circular_mail'] = "<tr><th>{$lang['circular_message']}</th><th><a href=\"?mode=circular\">{$lang['send_circular_mail_label']}</a></th></tr>";
		} else {
			$lang['send_circular_mail'] = '';
		}
		// El link para ver las solicitudes
		$lang['requests'] = '';
		$request = doquery("SELECT id FROM {{table}} WHERE ally_request='{$ally['id']}'", 'users');
		$request_count = mysqli_num_rows($request);
		if ($request_count != 0) {
			if ($user_bewerbungen_einsehen)
				$lang['requests'] ="<tr><th>{$lang['requests_label']}</th><th><a href=\"alliance.php?mode=admin&edit=requests\">{$request_count} {$lang['xrequests']}</a></th></tr>";
		}
		if ($ally['ally_owner'] != $user['id']) {
			$lang['ally_owner'] = MessageForm($lang['exit_of_this_alliance'], "", "?mode=exit", $lang['continue_label']);
		} else {
			$lang['ally_owner'] = '';
		}
		// La imagen de logotipo
		$lang['ally_image'] = ($ally['ally_image'] != '')?
		"<tr><th colspan=2><img src=\"{$ally['ally_image']}\"></th></tr>":'';
		$lang['range'] = $range;
		$lang['ally_description'] = nl2br(AllyBBCode($ally['ally_description'] ?? ''));
		$lang['ally_text'] = nl2br(AllyBBCode($ally['ally_text'] ?? ''));

		$lang['ally_web'] = $ally['ally_web'];
		$lang['ally_tag'] = $ally['ally_tag'];
		$lang['ally_members'] = $ally['ally_members'];
		$lang['ally_name'] = $ally['ally_name'];

		$page .= parsetemplate(gettemplate('alliance_frontpage'), $lang);
		display($page, $lang['your_alliance']);
	}
}

?>
