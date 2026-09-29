<?php

/**
 * install.mo
 *
 * @version 0.5
 * @copyright 2008
 */

$lang['ins_appname']      = "XNova";
$lang['ins_tx_state']     = 'Étape';
$lang['ins_tx_sys']       = "Gestion syst&egrave;me";
$lang['ins_btn_next']     = "Suivant";
$lang['ins_btn_inst']     = "Installer";
$lang['ins_btn_creat']    = "Cr&eacute;er";
$lang['ins_btn_login']    = "Connexion";
$lang['ins_btn_prev']     = 'Précédent';

$lang['ins_mnu_intro']    = "Introduction";
$lang['ins_mnu_inst']     = "Installer";
$lang['ins_mnu_goto']     = "Transfert";
$lang['ins_mnu_upgr']     = 'Mise à jour';
$lang['ins_mnu_quit']     = "Quitter";

$lang['ins_error']        = "Erreur";
$lang['ins_error1']       = 'La connexion à la base de données a échoué : vérifiez le serveur, le nom de la base, le préfixe des tables (lettres, chiffres et _) et les identifiants.';
$lang['ins_error2']       = 'Le fichier config.php n\'a pas pu être écrit : vérifiez que le serveur web a le droit de le modifier.';

$lang['ins_tx_welco']     = "Bienvenue dans l'installation de XNova";
$lang['ins_tx_intr1']     = 'Le projet XNova vous permettra d\'installer un clone d\'OGame quasi parfait.';
$lang['ins_tx_intr2']     = 'Le projet XNova est libre, gratuit et open source. Merci de ne pas en faire d\'utilisation commerciale.';
$lang['ins_tx_intr3']     = 'Par respect pour l\'équipe de développement de ce projet, vous êtes priés de ne pas supprimer les copyrights des fichiers source.';
$lang['ins_tx_inst1']     = 'Le fichier config.php doit être modifiable par le serveur web (droit d\'écriture, inutile de le mettre en CHMOD 777).';
$lang['ins_tx_inst2']     = 'Vous devez disposer d\'une base de données MySQL ou MariaDB.';
$lang['ins_tx_inst3']     = 'Remplissez correctement le formulaire suivant pour continuer l\'installation :';
$lang['ins_tx_acc1']      = 'Vous êtes sur le point de créer le compte administrateur.';
$lang['ins_tx_acc2']      = 'Remplissez le formulaire suivant avec les informations du compte :';
$lang['ins_tx_goto1']     = "Ce mode reprend une base XNova existante (XNova 0.8e d'origine ou XNova Renaissance) : il &eacute;crit le fichier config.php et met la base &agrave; jour.";
$lang['ins_tx_goto2']     = "Joueurs, plan&egrave;tes et flottes sont conserv&eacute;s (utile apr&egrave;s un changement de serveur ou une r&eacute;installation des fichiers).";
$lang['ins_tx_goto3']     = "Vous prenez un risque en transformant votre base de donn&eacute;es, faites une sauvegarde avant !";
$lang['ins_tx_goto4']     = "Vous devez d&eacute;j&agrave; avoir une base XNova 0.8e ou XNova Renaissance";
$lang['ins_tx_goto5']     = "Remplissez le formulaire suivant avec les informations exactes de cette base. Si vous vous trompez, le transfert &eacute;chouera :";
$lang['ins_tx_done1']     = "La base de donn&eacute;es a bien &eacute;t&eacute; install&eacute;e !";
$lang['ins_tx_done2']     = 'Le compte administrateur a bien été créé !';
$lang['ins_tx_done3']     = 'Il est conseillé de supprimer le dossier <i>install</i> si vous n\'avez plus besoin de l\'installeur !';
$lang['ins_tx_done4']     = "Le transfert a &eacute;t&eacute; correctement effectu&eacute; !";

$lang['ins_form_server']  = "Serveur SQL";
$lang['ins_form_db']      = "Base de donn&eacute;es";
$lang['ins_form_prefix']  = 'Préfixe des tables';
$lang['ins_form_login']   = "Identifiant";
$lang['ins_form_pass']    = 'Mot de passe';
$lang['ins_form_install'] = "Installer";

$lang['ins_acc_user']     = "Pseudo";
$lang['ins_acc_pass']     = 'Mot de passe';
$lang['ins_acc_email']    = 'Adresse e-mail';
$lang['ins_acc_planet']   = "Plan&egrave;te m&egrave;re";
$lang['ins_acc_sex']      = "Sexe";
$lang['ins_acc_sex0']     = '- indéfini -';
$lang['ins_acc_sex1']     = "Homme";
$lang['ins_acc_sex2']     = "Femme";


// XNova Renaissance : modes Transfere et Mise a jour
$lang['ins_tx_goto1'] = 'Le transfert reprend une base existante (XNova 0.8e d\'origine ou XNova Renaissance), par exemple apr&egrave;s un changement de serveur.';
$lang['ins_tx_goto2'] = 'Le fichier config.php est r&eacute;&eacute;crit, puis la base est mise &agrave; jour si elle vient d\'une version plus ancienne.';
$lang['ins_tx_goto3'] = 'Les bases UGamela, Legacies et des autres d&eacute;riv&eacute;s ne sont pas prises en charge. Faites une sauvegarde de votre base avant !';
$lang['ins_tx_goto4'] = 'Vous devez d&eacute;j&agrave; disposer d\'une base XNova 0.8e ou XNova Renaissance.';
$lang['ins_tx_goto5'] = 'Remplissez le formulaire avec les informations exactes de cette base (serveur, nom, pr&eacute;fixe des tables, identifiants).';
$lang['ins_goto_err_version'] = 'Cette base n\'est ni une XNova 0.8e d\'origine, ni une XNova Renaissance (v&eacute;rifiez le nom de la base et le pr&eacute;fixe des tables).';
$lang['ins_goto_done_version'] = 'Base XNova Renaissance reprise (version d\'origine : %s) et fichier config.php &eacute;crit.';
$lang['ins_upg_intro1'] = 'La mise &agrave; jour applique &agrave; la base du jeu les modifications apport&eacute;es par les versions plus r&eacute;centes d\'XNova Renaissance.';
$lang['ins_upg_intro2'] = 'Elle utilise les informations du fichier config.php existant. Bases prises en charge : XNova 0.8e d\'origine et XNova Renaissance.';
$lang['ins_upg_intro3'] = 'Faites une sauvegarde de votre base avant de continuer !';
$lang['ins_upg_from_version'] = 'Version de la base avant la mise &agrave; jour : %s';
$lang['ins_upg_applied'] = 'Mises &agrave; jour appliqu&eacute;es : %s';
$lang['ins_upg_uptodate'] = 'La base est d&eacute;j&agrave; &agrave; jour, aucune modification n&eacute;cessaire.';
$lang['ins_upg_noconfig'] = 'Le jeu n\'est pas encore install&eacute; (config.php est vide) : utilisez le mode Installer.';


// XNova Renaissance : textes manquants (ils s'affichaient vides)
$lang['ins_error3'] = 'Tous les champs sont obligatoires : pseudo en lettres, chiffres, _ ou -, mot de passe d\'au moins 8 caractères et adresse e-mail valide.';

// XNova Renaissance 0.9g : verrou de l'installeur
$lang['ins_locked'] = 'Le jeu est d&eacute;j&agrave; install&eacute; : l\'installation est verrouill&eacute;e (la mise &agrave; jour et le transfert sont r&eacute;serv&eacute;s &agrave; un administrateur du jeu, qui saisit son pseudo et son mot de passe). Pour r&eacute;installer, videz d\'abord le fichier config.php. Pensez aussi &agrave; supprimer le dossier install du serveur.';

// XNova Renaissance 0.9g : titre de la page (« Installeur » ecrit en dur)
$lang['ins_page_title'] = 'Installation de XNova';

// 0.9k (Administration)
$lang['ins_admin_only'] = 'Installeur verrouill&eacute; : le jeu est install&eacute;, la mise &agrave; jour et le transfert sont r&eacute;serv&eacute;s &agrave; un administrateur du jeu. Pseudo ou mot de passe incorrect, ou ce compte n\'est pas administrateur.';
$lang['ins_adm_auth'] = 'Administrateur du jeu';
$lang['ins_adm_auth_txt'] = 'Le jeu est install&eacute; : seul un administrateur peut lancer cette op&eacute;ration. Saisissez le pseudo et le mot de passe de votre compte administrateur (inutile de vous connecter au jeu : ouvrez ses pages seulement apr&egrave;s la mise &agrave; jour de la base).';
$lang['ins_upg_running'] = 'Une mise &agrave; jour est d&eacute;j&agrave; en cours : patientez quelques instants, puis rechargez la page.';
$lang['ins_upg_secret'] = 'Le mot secret de config.php venait d\'une ancienne version (0.8e / 0.9d) et &eacute;tait trop faible : il a &eacute;t&eacute; remplac&eacute; par un mot secret al&eacute;atoire. Tous les joueurs, vous compris, doivent se reconnecter une fois.';
$lang['ins_upg_secret_fail'] = 'Le mot secret de config.php vient d\'une ancienne version (0.8e / 0.9d) et il est trop faible, mais le fichier n\'a pas pu &ecirc;tre r&eacute;&eacute;crit : rendez config.php modifiable par le serveur web, puis relancez la mise &agrave; jour.';
$lang['ins_admin_nodb'] = 'Impossible de v&eacute;rifier le compte administrateur : la base de donn&eacute;es indiqu&eacute;e dans config.php est injoignable. V&eacute;rifiez config.php. Pour transf&eacute;rer le jeu vers un nouveau serveur, videz d\'abord config.php : le transfert est alors ouvert, comme l\'installation.';
$lang['ins_admin_blocked'] = 'Trop d\'essais rat&eacute;s pour ce pseudo depuis votre adresse : r&eacute;essayez dans 15 minutes.';

?>