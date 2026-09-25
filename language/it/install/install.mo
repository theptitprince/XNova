<?php

/**
 * install.mo
 *
 * @version 0.5
 * @copyright 2008
 */

$lang['ins_appname']      = "XNova";
$lang['ins_tx_state']     = 'Fase';
$lang['ins_tx_sys']       = 'Gestione del sistema';
$lang['ins_btn_next']     = 'Avanti';
$lang['ins_btn_inst']     = 'Installa';
$lang['ins_btn_creat']    = 'Crea';
$lang['ins_btn_login']    = 'Accedi';
$lang['ins_btn_prev']     = 'Indietro';

$lang['ins_mnu_intro']    = 'Introduzione';
$lang['ins_mnu_inst']     = 'Installa';
$lang['ins_mnu_goto']     = 'Trasferimento';
$lang['ins_mnu_upgr']     = 'Aggiornamento';
$lang['ins_mnu_quit']     = 'Esci';

$lang['ins_error']        = 'Errore';
$lang['ins_error1']       = 'La connessione al database non è riuscita';
$lang['ins_error2']       = 'Impossibile sovrascrivere il file config.php';

$lang['ins_tx_welco']     = 'Benvenuti nell\'installazione di XNova';
$lang['ins_tx_intr1']     = 'Il progetto XNova vi permetterà di installare un clone di OGame quasi perfetto';
$lang['ins_tx_intr2']     = 'Il progetto XNova è libero, gratuito e open source. Vi preghiamo di non farne un uso commerciale';
$lang['ins_tx_intr3']     = 'Per rispetto verso il team di sviluppo di questo progetto, siete pregati di non rimuovere i copyright dai file sorgente.';
$lang['ins_tx_inst1']     = 'Il file config.php deve avere i permessi CHMOD 777';
$lang['ins_tx_inst2']     = 'Dovete disporre di un database MySQL';
$lang['ins_tx_inst3']     = 'Dovete compilare correttamente il modulo seguente per continuare l\'installazione:';
$lang['ins_tx_acc1']      = 'State per creare un account amministratore';
$lang['ins_tx_acc2']      = 'Compilate il modulo seguente con i dati dell\'account:';
$lang['ins_tx_goto1']     = "Ce mode reprend une base XNova existante (XNova 0.8e d'origine ou XNova Renaissance) : il &eacute;crit le fichier config.php et met la base &agrave; jour.";
$lang['ins_tx_goto2']     = "Joueurs, plan&egrave;tes et flottes sont conserv&eacute;s (utile apr&egrave;s un changement de serveur ou une r&eacute;installation des fichiers).";
$lang['ins_tx_goto3']     = "Vous prenez un risque en transformant votre base de donn&eacute;es, faites une sauvegarde avant !";
$lang['ins_tx_goto4']     = "Vous devez d&eacute;j&agrave; avoir une base XNova 0.8e ou XNova Renaissance";
$lang['ins_tx_goto5']     = "Remplissez le formulaire suivant avec les informations exactes de cette base. Si vous vous trompez, le transfert &eacute;chouera :";
$lang['ins_tx_done1']     = 'Il database è stato installato correttamente!';
$lang['ins_tx_done2']     = 'L\'account amministratore è stato creato correttamente!';
$lang['ins_tx_done3']     = 'Si consiglia di eliminare la cartella <i>install</i> se non avete più bisogno del programma di installazione!';
$lang['ins_tx_done4']     = 'Il trasferimento è stato effettuato correttamente!';

$lang['ins_form_server']  = 'Server SQL';
$lang['ins_form_db']      = 'Database';
$lang['ins_form_prefix']  = 'Prefisso delle tabelle';
$lang['ins_form_login']   = 'Nome utente';
$lang['ins_form_pass']    = 'Password';
$lang['ins_form_install'] = 'Installa';

$lang['ins_acc_user']     = 'Nome utente';
$lang['ins_acc_pass']     = 'Password';
$lang['ins_acc_email']    = 'Indirizzo e-mail';
$lang['ins_acc_planet']   = 'Pianeta madre';
$lang['ins_acc_sex']      = 'Sesso';
$lang['ins_acc_sex0']     = '-indefinito-';
$lang['ins_acc_sex1']     = 'Uomo';
$lang['ins_acc_sex2']     = 'Donna';


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

// XNova Renaissance 0.9g : traductions
$lang['ins_error3'] = 'Tutti i campi sono obbligatori e il nome utente deve contenere solo lettere, cifre, _ o -.';

?>
