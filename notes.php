<?php

/**
 * notes.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */


define('INSIDE'  , true);
define('INSTALL' , false);

$xnova_root_path = './';
include($xnova_root_path . 'extension.inc');
include($xnova_root_path . 'common.'.$phpEx);

$dpath = (!$user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];

$a = intval(($_GET['a'] ?? null));
$n = intval(($_GET['n'] ?? null));
$lang['please_wait_label'] = "Patientez...";

//lenguaje
includeLang('notes');

$lang['php_self'] = 'notes.'.$phpEx;

if(($_POST["s"] ?? null) == 1 || ($_POST["s"] ?? null) == 2){//Edicion y agregar notas

	$time = time();
	$priority = intval(($_POST["u"] ?? null));
	$title = (($_POST["title"] ?? null)) ? SqlEscape(SafeText(($_POST["title"] ?? null))) : $lang['no_title'];
	$text = (($_POST["text"] ?? null)) ? SqlEscape(SafeText(($_POST["text"] ?? null))) : $lang['no_text'];

	if(($_POST["s"] ?? null) ==1){
		doquery("INSERT INTO {{table}} SET owner={$user['id']}, time=$time, priority=$priority, title='$title', text='$text'","notes");
		message($lang['note_added'], $lang['please_wait_label'],'notes.'.$phpEx,"3");
	}elseif(($_POST["s"] ?? null) == 2){
		/*
		  pequeño query para averiguar si la nota que se edita es del propio jugador
		*/
		$id = intval(($_POST["n"] ?? null));
		$note_query = doquery("SELECT * FROM {{table}} WHERE id=$id AND owner=".$user["id"],"notes");

		if(!$note_query){ error($lang['notpossiblethisway'],$lang['notes']); }

		doquery("UPDATE {{table}} SET time=$time, priority=$priority, title='$title', text='$text' WHERE id=$id","notes");
		message($lang['note_updated'], $lang['please_wait_label'], 'notes.'.$phpEx, "3");
	}

}
elseif($_POST){//Borrar

	foreach($_POST as $a => $b){
		/*
		  Los checkbox marcados tienen la palabra delmes seguido del id.
		  Y cada array contiene el valor "y" para compro
		*/
		if(preg_match("/delmes/i",$a) && $b == "y"){

			$id = str_replace("delmes","",$a);
			$note_query = doquery("SELECT * FROM {{table}} WHERE id=$id AND owner={$user['id']}","notes");
			$deleted = $deleted ?? 0;
			//comprobamos,
			if($note_query){
				$deleted++;
				doquery("DELETE FROM {{table}} WHERE `id`=$id;","notes");// y borramos
			}
		}
	}
	if($deleted){
		$mes = ($deleted == 1) ? $lang['note_deleted'] : $lang['note_deleteds'];
		message($mes,$lang['please_wait_label'],'notes.'.$phpEx,"3");
	}else{header("Location: notes.$phpEx");}

}else{//sin post...
	if(($_GET["a"] ?? null) == 1){//crear una nueva nota.
		/*
		  Formulario para crear una nueva nota.
		*/

		$parse = $lang;

		$parse['c_options'] = "<option value=2 selected=selected>{$lang['important']}</option>
			  <option value=1>{$lang['normal']}</option>
			  <option value=0>{$lang['unimportant']}</option>";

		$parse['cnt_chars'] = '0';
		$parse['title_label'] = $lang['createnote'];
		$parse['text'] = '';
		$parse['title'] = '';
		$parse['inputs'] = '<input type=hidden name=s value=1>';

		$page = parsetemplate(gettemplate('notes_form'), $parse);

		display($page,$lang['notes'],false);

	}
	elseif(($_GET["a"] ?? null) == 2){//editar
		/*
		  Formulario donde se puestra la nota y se puede editar.
		*/
		$note = doquery("SELECT * FROM {{table}} WHERE owner={$user['id']} AND id=$n",'notes',true);

		if(!$note){ message($lang['notpossiblethisway'],$lang['error_label']); }

		$cntChars = mb_strlen(html_entity_decode($note['text'], ENT_QUOTES, 'UTF-8'), 'UTF-8');

		$SELECTED = array(0 => '', 1 => '', 2 => '');
		$SELECTED[$note['priority']] = ' selected="selected"';

		$parse = array_merge($note,$lang);

		$parse['c_options'] = "<option value=2{$SELECTED[2]}>{$lang['important']}</option>
			  <option value=1{$SELECTED[1]}>{$lang['normal']}</option>
			  <option value=0{$SELECTED[0]}>{$lang['unimportant']}</option>";

		$parse['cnt_chars'] = $cntChars;
		$parse['title_label'] = $lang['editnote'];
		$parse['inputs'] = '<input type=hidden name=s value=2><input type=hidden name=n value='.$note['id'].'>';

		$page = parsetemplate(gettemplate('notes_form'), $parse);

		display($page,$lang['notes'],false);

	}
	else{//default

		$notes_query = doquery("SELECT * FROM {{table}} WHERE owner={$user['id']} ORDER BY time DESC",'notes');
		//Loop para crear la lista de notas que el jugador tiene
		$count = 0;
		$list  = '';
		$parse=$lang;
		while($note = mysqli_fetch_array($notes_query)){
			$count++;
			//Colorea el titulo dependiendo de la prioridad
			if($note["priority"] == 0){ $parse['note_color'] = "lime";}//Importante
			elseif($note["priority"] == 1){ $parse['note_color'] = "yellow";}//Normal
			elseif($note["priority"] == 2){ $parse['note_color'] = "red";}//Sin importancia

			//fragmento de template
			$parse['note_id'] = $note['id'];
			$parse['note_time'] = date("d/m/Y H:i:s",$note["time"]);
			$parse['note_title'] = $note['title'];
			$parse['note_text'] = mb_strlen(html_entity_decode($note['text'], ENT_QUOTES, 'UTF-8'), 'UTF-8');

			$list .= parsetemplate(gettemplate('notes_body_entry'), $parse);

		}

		if($count == 0){
			$list .= "<tr><th colspan=4>{$lang['there_is_no_note']}</th>\n";
		}

		$parse = $lang;
		$parse['body_list'] = $list;
		//fragmento de template
		$page = parsetemplate(gettemplate('notes_body'), $parse);

		display($page,$lang['notes'],false);
	}
}
?>
