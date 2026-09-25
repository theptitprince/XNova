<?php

/**
 * install.mo
 *
 * @version 0.5
 * @copyright 2008
 */

$lang['ins_appname']      = "XNova";
$lang['ins_tx_state']     = "Etap";
$lang['ins_tx_sys']       = "Gestion syst&egrave;me";
$lang['ins_btn_next']     = "Suivant";
$lang['ins_btn_inst']     = "Installer";
$lang['ins_btn_creat']    = "Cr&eacute;er";
$lang['ins_btn_login']    = "Connexion";
$lang['ins_btn_prev']     = "Pr&eacute;c&eacute;dant";

$lang['ins_mnu_intro']    = "Introduction";
$lang['ins_mnu_inst']     = "Installer";
$lang['ins_mnu_goto']     = "Transfert";
$lang['ins_mnu_upgr']     = "Mise &agrave; Jour";
$lang['ins_mnu_quit']     = "Quitter";

$lang['ins_error']        = "Erreur";
$lang['ins_error1']       = "La connexion &agrave; la base de donn&eacute;es a &eacute;chou&eacute;";
$lang['ins_error2']       = "Le fichier config.php ne pas &ecirc;tre remplacer";

$lang['ins_tx_welco']     = "Bienvenue dans l'installation de XNova";
$lang['ins_tx_intr1']     = "Le projet XNova vous permettra d'installer un clone d'ogame quasi parfait";
$lang['ins_tx_intr2']     = "Le projet XNova est libre, gratuit et OpenSource. Merci de ne pas en faire d'utilisation commerciale";
$lang['ins_tx_intr3']     = "Par respect pour l'equipe de d&eacute;veloppement de ce projet, vous &ecirc;tes pri&eacute;s de ne pas supprimer les copyright des fichiers source.";
$lang['ins_tx_inst1']     = "Le fichier config.php doit &ecirc;tre en CHMOD 777";
$lang['ins_tx_inst2']     = "Vous devez poss&eacute;der une base de donn&eacute;es MySQL";
$lang['ins_tx_inst3']     = "Vous devez remplir le formulaire suivant correctement pour continuer l'installation:";
$lang['ins_tx_acc1']      = "Vous &ecirc;tes sur le point de cr&eacute;er un compte administrateur";
$lang['ins_tx_acc2']      = "Remplissez le formulaire suivant avec les informations du compte:";
$lang['ins_tx_goto1']     = "Ce mode reprend une base XNova existante (XNova 0.8e d'origine ou XNova Renaissance) : il &eacute;crit le fichier config.php et met la base &agrave; jour.";
$lang['ins_tx_goto2']     = "Joueurs, plan&egrave;tes et flottes sont conserv&eacute;s (utile apr&egrave;s un changement de serveur ou une r&eacute;installation des fichiers).";
$lang['ins_tx_goto3']     = "Vous prenez un risque en transformant votre base de donn&eacute;es, faites une sauvegarde avant !";
$lang['ins_tx_goto4']     = "Vous devez d&eacute;j&agrave; avoir une base XNova 0.8e ou XNova Renaissance";
$lang['ins_tx_goto5']     = "Remplissez le formulaire suivant avec les informations exactes de cette base. Si vous vous trompez, le transfert &eacute;chouera :";
$lang['ins_tx_done1']     = "La base de donn&eacute;es a bien &eacute;t&eacute; install&eacute;e !";
$lang['ins_tx_done2']     = "Le compte administrateur a correctement &eacute;t&eacute; cr&eacute;&eacute;!";
$lang['ins_tx_done3']     = "Il est conseill&eacute; de supprimer le dossier <i>install</i> si vous n'avez plus besoin de l'installateur!";
$lang['ins_tx_done4']     = "Le transfert a &eacute;t&eacute; correctement effectu&eacute; !";

$lang['ins_form_server']  = "Serveur SQL";
$lang['ins_form_db']      = "Base de donn&eacute;es";
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
$lang['ins_tx_goto1'] = 'Il trasferimento riprende una base esistente (XNova 0.8e originale o XNova Renaissance), per esempio dopo un cambio di server.';
$lang['ins_tx_goto2'] = 'Il file config.php viene riscritto e la base viene aggiornata se proviene da una versione precedente.';
$lang['ins_tx_goto3'] = 'Le basi UGamela, Legacies e degli altri derivati non sono supportate. Fate un backup della base prima!';
$lang['ins_tx_goto4'] = 'Dovete gi&agrave; disporre di una base XNova 0.8e o XNova Renaissance.';
$lang['ins_tx_goto5'] = 'Compilate il modulo con i dati esatti di questa base (server, nome, prefisso delle tabelle, credenziali).';
$lang['ins_goto_err_version'] = 'Questa base non &egrave; n&eacute; una XNova 0.8e originale n&eacute; una XNova Renaissance (controllate il nome della base e il prefisso).';
$lang['ins_goto_done_version'] = 'Base XNova Renaissance ripresa (versione di origine: %s) e file config.php scritto.';
$lang['ins_upg_intro1'] = 'L\'aggiornamento applica alla base del gioco le modifiche delle versioni pi&ugrave; recenti di XNova Renaissance.';
$lang['ins_upg_intro2'] = 'Usa i dati del file config.php esistente. Basi supportate: XNova 0.8e originale e XNova Renaissance.';
$lang['ins_upg_intro3'] = 'Fate un backup della base prima di continuare!';
$lang['ins_upg_from_version'] = 'Versione della base prima dell\'aggiornamento: %s';
$lang['ins_upg_applied'] = 'Aggiornamenti applicati: %s';
$lang['ins_upg_uptodate'] = 'La base &egrave; gi&agrave; aggiornata, nessuna modifica necessaria.';
$lang['ins_upg_noconfig'] = 'Il gioco non &egrave; ancora installato (config.php &egrave; vuoto): usate la modalit&agrave; Installa.';

// XNova Renaissance 0.9g : verrou de l'installeur
$lang['ins_locked'] = 'Il gioco &egrave; gi&agrave; installato: installazione e trasferimento sono bloccati (resta possibile solo l\'aggiornamento). Per reinstallare, svuotate prima il file config.php. Ricordate anche di eliminare la cartella install dal server.';

?>
