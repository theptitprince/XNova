<?php // debug.class.php ::  Clase Debug, maneja reporte de eventos

/**
 * includes/debug.class.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original : XNova Team, d'apres UGamela
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

if(!defined('INSIDE')){ die("attemp hacking");}
//
//  Experiment code!!!
//
/*vamos a experimentar >:)
  le veo futuro a las classes, ayudaria mucho a tener un codigo mas ordenado...
  que esperabas!!! soy newbie!!! D':<
*/

class debug
{
	var $log,$numqueries;

	function debug()
	{
		$this->vars = $this->log = '';
		$this->numqueries = 0;
	}

	function add($mes)
	{
		$this->log .= $mes;
		$this->numqueries++;
	}

	// Journal echappe au moment de l'afficher (pas a chaque requete : doquery() remplit le journal meme hors mode
	// debug). Le texte des requetes contient les textes des joueurs, affiches sinon tels quels a l'administrateur.
	// Une ligne qui n'a pas la forme ecrite par doquery() est echappee en entier.
	function log_html()
	{
		$Html = '';
		foreach (preg_split('#(?=<tr><th>Query [0-9]*: </th><th>)#', (string) $this->log, -1, PREG_SPLIT_NO_EMPTY) as $Row) {
			if (preg_match('#^<tr><th>(Query [0-9]*: )</th><th>(.*)</th><th>([^<]*)</th><th>([^<]*)</th></tr>$#s', $Row, $m)) {
				$Html .= "<tr><th>". $m[1] ."</th><th>". htmlspecialchars($m[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ."</th><th>". htmlspecialchars($m[3]) ."</th><th>". htmlspecialchars($m[4]) ."</th></tr>";
			} else {
				$Html .= "<tr><th colspan=4>". htmlspecialchars($Row, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ."</th></tr>";
			}
		}
		return $Html;
	}

	// Journal des requetes de la page, renvoye pour etre ajoute sous la page par display(). Avant : affiche seul puis
	// die(), en mode debug l'administrateur ne voyait plus aucune page, pas meme les parametres pour couper ce mode
	function echo_log()
	{	global $xnova_root_path;
		return "<br><table><tr><td class=k colspan=4><a href=".$xnova_root_path."admin/settings.php>Debug Log</a>:</td></tr>".$this->log_html()."</table>";
	}
	
	function error($message,$title)
	{
		global $link,$game_config;
		// Mode debug : erreur SQL complete et journal des requetes reserves aux administrateurs (avant : affiches a
		// n'importe quel visiteur, avec les requetes des flottes des autres joueurs). Les autres voient le message
		// d'erreur habituel, le detail reste enregistre dans la table errors.
		global $user;
		if(($game_config['debug'] ?? 0)==1 && is_array($user) && ($user['authlevel'] ?? 0) >= 3){
			$Detail = htmlspecialchars(str_replace('<br />', "\n", $message), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			echo "<h2>". htmlspecialchars($title) ."</h2><br><font color=red>". nl2br($Detail) ."</font><br><hr>";
			echo  "<table>".$this->log_html()."</table>";
		}
		//else{
			//A futuro, se creara una tabla especial, para almacenar
			//los errores que ocurran.
			global $user,$xnova_root_path,$phpEx;
			include($xnova_root_path . 'config.'.$phpEx);
			if(!$link) die('La base de donn&eacute;es MySQL est indisponible pour le moment, merci de r&eacute;essayer plus tard.');
			$query = "INSERT INTO {{table}} SET
				`error_sender` = '".intval($user['id'] ?? 0)."' ,
				`error_time` = '".time()."' ,
				`error_type` = '{$title}' ,
				`error_text` = '".SqlEscape($message)."';";
			$sqlquery = mysqli_query($link, str_replace("{{table}}", $dbsettings["prefix"].'errors',$query))
				or die('error fatal');
			$query = "explain select * from {{table}}";
			$q = mysqli_fetch_array(mysqli_query($link, str_replace("{{table}}", $dbsettings["prefix"].
				'errors', $query))) or die('error fatal: ');
				

			// Texte de la langue du joueur s'il est deja charge (base injoignable plus haut : langue par defaut, avant tout chargement)
			global $lang;
			$ErrorText = sprintf($lang['sys_sql_error'] ?? "Erreur, merci de contacter l'administrateur. Erreur n&deg; : <b>%d</b>", $q['rows']);
			if (!function_exists('message'))
				echo $ErrorText;
			else
				message($ErrorText, ($lang['sys_error'] ?? "Erreur"));
		//}
		
		die();
	}
	
	
}

// Created by Perberos. All rights reversed (C) 2006
?>
