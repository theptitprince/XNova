<?php

/**
 * install.mo
 *
 * @version 0.5
 * @copyright 2008
 */

$lang['ins_appname']      = "XNova";
$lang['ins_tx_state']     = "Etape";
$lang['ins_tx_sys']       = "Gestion syst&egrave;me";
$lang['ins_btn_next']     = "Suivant";
$lang['ins_btn_inst']     = "Installer";
$lang['ins_btn_creat']    = "Cr&eacute;er";
$lang['ins_btn_login']    = "Connexion";
$lang['ins_btn_prev']     = "Pr&eacute;c&eacute;dant";

$lang['ins_mnu_intro']    = "Introduction";
$lang['ins_mnu_inst']     = "Installer";
$lang['ins_mnu_goto']     = "Transf&egrave;re";
$lang['ins_mnu_upgr']     = "Mise &agrave; Jour";
$lang['ins_mnu_quit']     = "Quitter";

$lang['ins_error']        = "Erreur";
$lang['ins_error1']       = "La connexion &agrave; la base de donn&eacute;e a &eacute;chou&eacute;";
$lang['ins_error2']       = "Le fichier config.php ne pas &ecirc;tre remplacer";

$lang['ins_tx_welco']     = "Bienvenue dans l'installation de XNova";
$lang['ins_tx_intr1']     = "Le projet XNova vous permettra d'installer un clone d'ogame quasi parfait";
$lang['ins_tx_intr2']     = "Le projet XNova est libre, gratuit et OpenSource. Merci de ne pas en faire d'utilisation commerciale";
$lang['ins_tx_intr3']     = "Par respect pour l'equipe de d&eacute;veloppement de ce projet, vous &ecirc;tes pri&eacute;s de ne pas supprimer les copyright des fichiers source.";
$lang['ins_tx_inst1']     = "Le fichier config.php doit &ecirc;tre en CHMOD 777";
$lang['ins_tx_inst2']     = "Vous devez poss&eacute;der une base de donn&eacute;e MySQL";
$lang['ins_tx_inst3']     = "Vous devez remplir le formulaire suivant correctement pour continuer l'installation:";
$lang['ins_tx_acc1']      = "Vous &ecirc;tes sur le point de cr&eacute;er un compte administrateur";
$lang['ins_tx_acc2']      = "Remplissez le formulaire suivant avec les informations du compte:";
$lang['ins_tx_goto1']     = "En choisissant cette m&eacute;tode d'installation, vous allez transformer une base de donn&eacute;e UGamela en base de donn&eacute;e XNova.";
$lang['ins_tx_goto2']     = "Cette option fonctionne, mais il reste pr&eacute;f&eacute;rable de faire une installation compl&egrave;te d'XNova.";
$lang['ins_tx_goto3']     = "Vous prenez un risque en transformant votre base de donn&eacute;e, faites une sauvegarde avant!";
$lang['ins_tx_goto4']     = "Vous devez d&eacute;j&agrave; avoir install&eacute; UGamela";
$lang['ins_tx_goto5']     = "Remplissez le formulaire suivant avec les informations exactes de la base de donn&eacute;e o&ugrave; a &eacute;t&eacute; install&eacute; UGamela. Si vous vous trompez, le transfert &eacute;choura:";
$lang['ins_tx_done1']     = "La base de donn&eacute;e a bien &eacute;t&eacute; install&eacute;e!";
$lang['ins_tx_done2']     = "Le compte administrateur a correctement &eacute;t&eacute; cr&eacute;&eacute;!";
$lang['ins_tx_done3']     = "Il est conseill&eacute; de supprimer le dossier <i>install</i> si vous n'avez plus besoin de l'installateur!";
$lang['ins_tx_done4']     = "Le transf&egrave;re a &eacute;t&eacute; correctement effectu&eacute;!";

$lang['ins_form_server']  = "Serveur SQL";
$lang['ins_form_db']      = "Base de donn&eacute;e";
$lang['ins_form_prefix']  = "Pr&eacute;fix des tables";
$lang['ins_form_login']   = "Identifiant";
$lang['ins_form_pass']    = "Mot-de-passe";
$lang['ins_form_install'] = "Installer";

$lang['ins_acc_user']     = "Pseudo";
$lang['ins_acc_pass']     = "Mot-de-passe";
$lang['ins_acc_email']    = "Adresse e-Mail";
$lang['ins_acc_planet']   = "Plan&egrave;te m&egrave;re";
$lang['ins_acc_sex']      = "Sexe";
$lang['ins_acc_sex0']     = "-ind&eacute;fini-";
$lang['ins_acc_sex1']     = "Homme";
$lang['ins_acc_sex2']     = "Femme";


// XNova Renaissance : modes Transfere et Mise a jour
$lang['ins_tx_goto1'] = 'Le transfert reprend une base XNova Renaissance existante (version 0.9d ou plus r&eacute;cente), par exemple apr&egrave;s un changement de serveur.';
$lang['ins_tx_goto2'] = 'Le fichier config.php est r&eacute;&eacute;crit, puis la base est mise &agrave; jour si elle vient d\'une version plus ancienne.';
$lang['ins_tx_goto3'] = 'Les bases UGamela, XNova 0.8 et Legacies ne sont pas prises en charge. Faites une sauvegarde de votre base avant !';
$lang['ins_tx_goto4'] = 'Vous devez d&eacute;j&agrave; disposer d\'une base XNova Renaissance.';
$lang['ins_tx_goto5'] = 'Remplissez le formulaire avec les informations exactes de cette base (serveur, nom, pr&eacute;fixe des tables, identifiants).';
$lang['ins_goto_err_version'] = 'Cette base n\'est pas une base XNova Renaissance 0.9d ou plus r&eacute;cente (v&eacute;rifiez le nom de la base et le pr&eacute;fixe des tables).';
$lang['ins_goto_done_version'] = 'Base XNova Renaissance reprise (version d\'origine : %s) et fichier config.php &eacute;crit.';
$lang['ins_upg_intro1'] = 'La mise &agrave; jour applique &agrave; la base du jeu les modifications apport&eacute;es par les versions plus r&eacute;centes d\'XNova Renaissance.';
$lang['ins_upg_intro2'] = 'Elle utilise les informations du fichier config.php existant. Seules les bases 0.9d et plus r&eacute;centes sont prises en charge.';
$lang['ins_upg_intro3'] = 'Faites une sauvegarde de votre base avant de continuer !';
$lang['ins_upg_from_version'] = 'Version de la base avant la mise &agrave; jour : %s';
$lang['ins_upg_applied'] = 'Mises &agrave; jour appliqu&eacute;es : %s';
$lang['ins_upg_uptodate'] = 'La base est d&eacute;j&agrave; &agrave; jour, aucune modification n&eacute;cessaire.';
$lang['ins_upg_noconfig'] = 'Le jeu n\'est pas encore install&eacute; (config.php est vide) : utilisez le mode Installer.';

?>