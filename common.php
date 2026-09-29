<?php

/**
 * common.php
 *
 * XNova Renaissance
 * Reprise et modernisation : theptitprince (2026)
 *
 * Travail original :
 * @version 1.0
 * @copyright 2008 by XNova Team (auteur non identifié) for XNova
 * @license GNU AGPL v3 ou ultérieure (voir NOTICE)
 */

// Fichier inclus par les pages du jeu : ouvert directement, il ecrivait un avertissement PHP dans le journal
if (!defined('INSIDE')) {
	die();
}

// Erreurs PHP jamais affichees aux visiteurs (chemins, traces, debut des hash), seulement ecrites dans le journal
// du serveur : sans php.ini, PHP les affiche. Avant tout include, pour couvrir aussi le chargement des fichiers
ini_set('display_errors', '0');
ini_set('log_errors', '1');

define('VERSION'     ,'0.9k');        // Version d'XNova utilisée...
define('VERSION_NAME','Renaissance'); // Nom de la version (0.9 et suivantes)

$phpEx = "php";

$game_config   = array();
$user          = array();
$lang          = array();
$link          = "";            // Lien pour liaison MySQL :)
$IsUserChecked = false;

define('DEFAULT_SKINPATH' , 'skins/xnova/');
define('TEMPLATE_DIR'     , 'templates/');
define('TEMPLATE_NAME'    , 'OpenGame');
define('DEFAULT_LANG'     , 'fr');

$HTTP_ACCEPT_LANGUAGE = DEFAULT_LANG;

include($xnova_root_path . 'includes/debug.class.'.$phpEx);
$debug = new debug();

include($xnova_root_path . 'includes/constants.'.$phpEx);
include($xnova_root_path . 'includes/functions.'.$phpEx);
include($xnova_root_path . 'includes/unlocalised.'.$phpEx);
include($xnova_root_path . 'includes/todofleetcontrol.'.$phpEx);
include($xnova_root_path . 'language/'. DEFAULT_LANG .'/lang_info.cfg');

// En-tetes de securite : l'adresse des pages (avec le jeton CSRF des liens) n'est jamais envoyee a un autre site,
// pas de devinette du type des fichiers, pages affichables seulement dans les cadres du jeu lui-meme
if (!headers_sent()) {
	header('Referrer-Policy: same-origin');
	header('X-Content-Type-Options: nosniff');
	header('X-Frame-Options: SAMEORIGIN');
}

// Jeu pas encore installe (config.php vide) : quelle que soit la page demandee, direction l'installeur
if (INSTALL != true && (!file_exists($xnova_root_path . 'config.php') || filesize($xnova_root_path . 'config.php') == 0)) {
	header('Location: ' . $xnova_root_path . 'install/');
	exit();
}

if (INSTALL != true) {
    include($xnova_root_path . 'includes/vars.'.$phpEx);
    include($xnova_root_path . 'includes/db.'.$phpEx);
    include($xnova_root_path . 'includes/strings.'.$phpEx);

    // Lecture de la table de configuration
    $query = doquery("SELECT * FROM {{table}}",'config');
    while ( $row = mysqli_fetch_assoc($query) ) {
	    $game_config[$row['config_name']] = $row['config_value'];
    }

	if (empty($InLogin)) {
		$Result        = CheckTheUser ( $IsUserChecked );
		$IsUserChecked = $Result['state'];
		$user          = $Result['record'];
	}

	// Pages d'un meme joueur traitees l'une apres l'autre (0.9k, decide par theptitprince) : deux pages envoyees en
	// meme temps lisaient le meme etat et ecrivaient chacune le leur (ressources, vaisseaux, missiles, officiers
	// dupliques). Verrou nomme de MySQL, libere a la fermeture de la connexion en fin de page ; nom avec le prefixe des
	// tables et la base (deux jeux sur un meme serveur MySQL). Sans verrou : les pages en lecture seule (sondage du
	// chat toutes les 3 secondes, cadres et menu charges avec la vue generale) et l'administration (pages longues comme
	// le calcul des statistiques, qui bloqueraient le jeu de l'administrateur).
	if (is_array($user) && !empty($user['id']) && !defined('IN_ADMIN') && !in_array(basename($_SERVER['SCRIPT_NAME']), array('chat_msg.php', 'frames.php', 'leftmenu.php'))) {
		// {{table}} devient ici le prefixe des tables suivi de « user_ »
		$UserLock = doquery("SELECT GET_LOCK(LEFT(CONCAT('{{table}}". intval($user['id']) ."@', IFNULL(DATABASE(), '')), 64), 15) AS `ok`;", 'user_', true);
		if (empty($UserLock['ok'])) {
			// Toujours occupe apres 15 secondes : on n'attend pas plus, le joueur recommence
			includeLang('system');
			$dpath = empty($user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
			message($lang['sys_user_busy'], $lang['sys_user_busy_title']);
		}
		// Le joueur a pu changer pendant l'attente (page precedente du meme joueur) : relu
		$user = doquery("SELECT * FROM {{table}} WHERE `id` = '". intval($user['id']) ."';", 'users', true);
		if (!$user) {
			$user = array();
		}
	}

	includeLang ("system");
	includeLang ('tech');

	// Jeu en mode 'clos' (reglage de l'admin) : seuls les administrateurs peuvent jouer.
	// Dans l'original, ce test etait dans une branche jamais atteinte : le jeu restait toujours ouvert.
	if (!empty($game_config['game_disable']) && empty($InLogin) && is_array($user) && !empty($user['id']) &&
	    $user['authlevel'] < 1 && basename($_SERVER['SCRIPT_NAME']) != 'logout.php') {
		message ( stripslashes ( $game_config['close_reason'] ), $game_config['game_name'] );
	}

	// Visiteur non connecte : seules les pages publiques s'affichent, les autres renvoient vers la connexion.
	// (Dans l'original, n'importe qui pouvait ouvrir frames.php, overview.php, l'admin... avec un joueur vide.)
	$PublicPages = array('index.php', 'login.php', 'reg.php', 'check_registration.php', 'captcha.php', 'lostpassword.php', 'contact.php',
	                     'credit.php', 'rules.php', 'changelog.php', 'banned.php', 'logout.php');
	if (empty($user['id']) && !defined('LOGIN') &&
	    (defined('IN_ADMIN') || !in_array(basename($_SERVER['SCRIPT_NAME']), $PublicPages))) {
		$LoginUrl = $xnova_root_path . 'login.php';
		// top.location : on sort des frames (sinon la page de connexion s'afficherait dans le cadre du jeu)
		echo "<html><head><meta http-equiv=\"refresh\" content=\"0;URL=". $LoginUrl ."\">".
		     "<script type=\"text/javascript\">top.location.href = '". $LoginUrl ."';</script></head><body></body></html>";
		exit();
	}

	// Protection CSRF : tout formulaire envoye par un joueur connecte, et toute action declenchee par un lien,
	// doivent porter le jeton de ce joueur (un site exterieur ne peut pas le connaitre)
	if (is_array($user) && !empty($user['id']) && !defined('LOGIN')) {
		if (($_SERVER['REQUEST_METHOD'] == 'POST' || CsrfGetAction()) && !CsrfValid()) {
			message($lang['sys_csrf_error'], $lang['sys_noaccess']);
		}
	}

	if ( isset ($user) ) {
		// Pages de sondage (chat, captcha, verification de l'inscription) et cadres (frames, menu) : ni flottes ni
		// missiles, laisses a la page suivante (0.9k, performances)
		if (!defined('NO_FLEET_PASS')) {
			// Une seule lecture legere quand aucune flotte n'a rien a faire (0.9k, performances) : les boucles appelaient
			// le gestionnaire pour chaque flotte arrivee, retours et stationnements compris. Sinon, boucles d'origine
			// (parcours de toute la table dans son ordre physique, qui decide de l'ordre de traitement).
			$FleetPassNow = time();
			$FleetPassDue = false;
			$_fleets = doquery("SELECT `fleet_mission`, `fleet_mess`, `fleet_start_time`, `fleet_end_stay`, `fleet_end_time` FROM {{table}} WHERE `fleet_start_time` < '". $FleetPassNow ."' OR `fleet_end_time` < '". $FleetPassNow ."';", 'fleets');
			while (!$FleetPassDue && ($FleetPassRow = mysqli_fetch_assoc($_fleets))) {
				$FleetPassDue = FleetRowIsDue($FleetPassRow, $FleetPassNow);
			}
			unset($FleetPassNow, $FleetPassRow);

			if ($FleetPassDue) {
				$_fleets = doquery("SELECT `fleet_start_galaxy`, `fleet_start_system`, `fleet_start_planet`, `fleet_start_type` FROM {{table}} USE INDEX () WHERE `fleet_start_time` <= '".time()."';", 'fleets'); //  OR fleet_end_time <= ".time()
				while ($row = mysqli_fetch_array($_fleets)) {
					$array                = array();
					$array['galaxy']      = $row['fleet_start_galaxy'];
					$array['system']      = $row['fleet_start_system'];
					$array['planet']      = $row['fleet_start_planet'];
					$array['planet_type'] = $row['fleet_start_type'];

					$temp = FlyingFleetHandler ($array);
				}

				$_fleets = doquery("SELECT `fleet_end_galaxy`, `fleet_end_system`, `fleet_end_planet`, `fleet_end_type` FROM {{table}} USE INDEX () WHERE `fleet_end_time` <= '".time()."';", 'fleets'); //  OR fleet_end_time <= ".time()
				while ($row = mysqli_fetch_array($_fleets)) {
					$array                = array();
					$array['galaxy']      = $row['fleet_end_galaxy'];
					$array['system']      = $row['fleet_end_system'];
					$array['planet']      = $row['fleet_end_planet'];
					$array['planet_type'] = $row['fleet_end_type'];

					$temp = FlyingFleetHandler ($array);
				}
			}
			unset($_fleets, $FleetPassDue);
		}

		// Comptes dont la suppression demandee dans les Options arrive a echeance (ACCOUNT_DELETE_DELAY apres la demande)
		$Expired = doquery("SELECT `id` FROM {{table}} WHERE `db_deaktjava` > 0 AND `db_deaktjava` <= '". time() ."' LIMIT 10;", 'users');
		$SelfDeleted = false;
		while ($ExpiredRow = mysqli_fetch_assoc($Expired)) {
			DeleteSelectedUser(intval($ExpiredRow['id']));
			$SelfDeleted = $SelfDeleted || (!empty($user['id']) && $ExpiredRow['id'] == $user['id']);
		}
		if ($SelfDeleted) {
			SetAuthCookie("", time() - 100000);
			message($lang['sys_account_deleted'], $lang['sys_account_deleted_title'], 'login.php', 5);
		}

		if (!defined('NO_FLEET_PASS')) {
			include($xnova_root_path . 'rak.'.$phpEx);
		}
		if ( defined('IN_ADMIN') ) {
			$UserSkin  = $user['dpath'] ?? '';
			$local     = stristr ( $UserSkin, "http:");
			if ($local === false) {
				if (!$UserSkin) {
					$dpath     = "../". DEFAULT_SKINPATH  ;
				} else {
					$dpath     = "../". $UserSkin;
				}
			} else {
				$dpath     = $UserSkin;
			}
		} else {
			$dpath     = empty($user["dpath"]) ? DEFAULT_SKINPATH : $user["dpath"];
		}

		// Planete courante : seulement pour un joueur connecte ($user vaut un tableau vide pour un visiteur)
		if (!empty($user['id'])) {
			SetSelectedPlanet ( $user );

			$planetrow = doquery("SELECT * FROM {{table}} WHERE `id` = '". intval($user['current_planet']) ."';", 'planets', true);
			// Planete courante qui n'est pas (ou plus) au joueur : retour sur sa planete mere (la porte de saut permettait
			// de se placer sur la lune d'un autre joueur ; toutes les pages agissaient ensuite sur elle)
			if (!$planetrow || intval($planetrow['id_owner']) != intval($user['id'])) {
				$user['current_planet'] = intval($user['id_planet']);
				doquery("UPDATE {{table}} SET `current_planet` = '". intval($user['id_planet']) ."' WHERE `id` = '". intval($user['id']) ."' LIMIT 1;", 'users');
				$planetrow = doquery("SELECT * FROM {{table}} WHERE `id` = '". intval($user['id_planet']) ."';", 'planets', true);
			}
			// Ligne de galaxie par coordonnees : fonctionne aussi pour une lune (avant : par id de planete, rien pour une lune)
			$galaxyrow = doquery("SELECT * FROM {{table}} WHERE `galaxy` = '". intval($planetrow['galaxy'] ?? 0) ."' AND `system` = '". intval($planetrow['system'] ?? 0) ."' AND `planet` = '". intval($planetrow['planet'] ?? 0) ."';", 'galaxy', true);

			CheckPlanetUsedFields($planetrow);

			// Statistiques recalculees au passage d'un joueur quand le dernier calcul date de plus de N heures (reglages
			// stat_auto et stat_auto_hours de l'administration) ; verrou et date relue dans BuildStatistics. Calcul fait
			// apres la page, verrou du joueur rendu d'abord : ses autres pages n'attendent pas la fin du calcul (et avec
			// PHP-FPM, la page lui est envoyee avant le calcul)
			$StatAge = 3600 * max(1, intval($game_config['stat_auto_hours'] ?? 6));
			if (!empty($game_config['stat_auto']) && time() - intval($game_config['stat_last'] ?? 0) >= $StatAge) {
				// Dossier courant retenu : a la fin du script, il n'est plus forcement celui de la page (serveur integre de
				// PHP, Apache), et doquery lit config.php par un chemin relatif
				$StatCwd = getcwd();
				register_shutdown_function(function () use ($StatAge, $StatCwd) {
					global $link;
					if ($StatCwd !== false) {
						chdir($StatCwd);
					}
					// Connexion de la page fermee (display() la ferme deja ; sinon ici) : le verrou du joueur est rendu avec
					// elle ; le calcul ouvre sa propre connexion
					try {
						if ($link instanceof mysqli) {
							mysqli_close($link);
						}
					} catch (Throwable $e) {
					}
					$link = false;
					if (function_exists('fastcgi_finish_request')) {
						fastcgi_finish_request();
					}
					include_once(__DIR__ . '/admin/statfunctions.php');
					BuildStatistics($StatAge);
				});
			}
		} else {
			$planetrow = null;
			$galaxyrow = null;
		}
	} else {
		// Bah si déjà y a quelqu'un qui passe par là et qu'a rien a faire de pressé ...
		// On se sert de lui pour mettre a jour tout les retardataires !!

	}
} else {
	$dpath     = "../" . DEFAULT_SKINPATH;
}

?>
