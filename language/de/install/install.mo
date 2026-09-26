<?php

// XNova Renaissance 0.9g : traductions
$lang['ins_appname'] = 'XNova';
$lang['ins_tx_state'] = 'Schritt';
$lang['ins_tx_sys'] = 'Systemverwaltung';
$lang['ins_btn_next'] = 'Weiter';
$lang['ins_btn_inst'] = 'Installieren';
$lang['ins_btn_creat'] = 'Erstellen';
$lang['ins_btn_login'] = 'Anmelden';
$lang['ins_btn_prev'] = 'Zurück';
$lang['ins_mnu_intro'] = 'Einführung';
$lang['ins_mnu_inst'] = 'Installieren';
$lang['ins_mnu_goto'] = 'Transfer';
$lang['ins_mnu_upgr'] = 'Update';
$lang['ins_mnu_quit'] = 'Beenden';
$lang['ins_error'] = 'Fehler';
$lang['ins_error1'] = 'Die Verbindung zur Datenbank ist fehlgeschlagen';
$lang['ins_error2'] = 'Die Datei config.php konnte nicht überschrieben werden';
$lang['ins_tx_welco'] = 'Willkommen bei der Installation von XNova';
$lang['ins_tx_intr1'] = 'Mit dem Projekt XNova lässt sich ein nahezu perfekter OGame-Klon installieren';
$lang['ins_tx_intr2'] = 'Das Projekt XNova ist frei, kostenlos und Open Source. Bitte nicht kommerziell nutzen';
$lang['ins_tx_intr3'] = 'Aus Respekt vor dem Entwicklerteam dieses Projekts bitte die Copyright-Hinweise in den Quelldateien nicht entfernen.';
$lang['ins_tx_inst1'] = 'Die Datei config.php muss für den Webserver beschreibbar sein (Schreibrecht genügt, CHMOD 777 ist nicht nötig)';
$lang['ins_tx_inst2'] = 'Eine MySQL- oder MariaDB-Datenbank wird benötigt';
$lang['ins_tx_inst3'] = 'Um die Installation fortzusetzen, muss das folgende Formular korrekt ausgefüllt werden:';
$lang['ins_tx_acc1'] = 'Es wird nun ein Administrator-Account erstellt';
$lang['ins_tx_acc2'] = 'Bitte das folgende Formular mit den Account-Daten ausfüllen:';
$lang['ins_tx_goto1'] = 'Der Transfer übernimmt eine bestehende Datenbank (Original-XNova 0.8e oder XNova Renaissance), zum Beispiel nach einem Serverwechsel.';
$lang['ins_tx_goto2'] = 'Die Datei config.php wird neu geschrieben, danach wird die Datenbank aktualisiert, falls sie von einer älteren Version stammt.';
$lang['ins_tx_goto3'] = 'Datenbanken von UGamela, Legacies und anderen Ablegern werden nicht unterstützt. Vorher unbedingt eine Sicherung der Datenbank anlegen!';
$lang['ins_tx_goto4'] = 'Eine bestehende Datenbank von XNova 0.8e oder XNova Renaissance wird vorausgesetzt.';
$lang['ins_tx_goto5'] = 'Bitte das Formular mit den genauen Daten dieser Datenbank ausfüllen (Server, Name, Tabellenpräfix, Zugangsdaten).';
$lang['ins_tx_done1'] = 'Die Datenbank wurde erfolgreich installiert!';
$lang['ins_tx_done2'] = 'Der Administrator-Account wurde erfolgreich erstellt!';
$lang['ins_tx_done3'] = 'Es wird empfohlen, den Ordner <i>install</i> zu löschen, wenn das Installationsprogramm nicht mehr benötigt wird!';
$lang['ins_tx_done4'] = 'Der Transfer wurde erfolgreich durchgeführt!';
$lang['ins_form_server'] = 'SQL-Server';
$lang['ins_form_db'] = 'Datenbank';
$lang['ins_form_prefix'] = 'Tabellenpräfix';
$lang['ins_form_login'] = 'Benutzername';
$lang['ins_form_pass'] = 'Passwort';
$lang['ins_form_install'] = 'Installieren';
$lang['ins_acc_user'] = 'Benutzername';
$lang['ins_acc_pass'] = 'Passwort';
$lang['ins_acc_email'] = 'E-Mail-Adresse';
$lang['ins_acc_planet'] = 'Hauptplanet';
$lang['ins_acc_sex'] = 'Geschlecht';
$lang['ins_acc_sex0'] = '-keine Angabe-';
$lang['ins_acc_sex1'] = 'Mann';
$lang['ins_acc_sex2'] = 'Frau';
$lang['ins_goto_err_version'] = 'Diese Datenbank ist weder eine Original-XNova 0.8e noch eine XNova Renaissance (Datenbankname und Tabellenpräfix überprüfen).';
$lang['ins_goto_done_version'] = 'XNova-Renaissance-Datenbank übernommen (ursprüngliche Version: %s) und Datei config.php geschrieben.';
$lang['ins_upg_intro1'] = 'Das Update überträgt die Änderungen neuerer Versionen von XNova Renaissance auf die Datenbank des Spiels.';
$lang['ins_upg_intro2'] = 'Es verwendet die Angaben der vorhandenen Datei config.php. Unterstützte Datenbanken: Original-XNova 0.8e und XNova Renaissance.';
$lang['ins_upg_intro3'] = 'Vor dem Fortfahren unbedingt eine Sicherung der Datenbank anlegen!';
$lang['ins_upg_from_version'] = 'Version der Datenbank vor dem Update: %s';
$lang['ins_upg_applied'] = 'Angewendete Updates: %s';
$lang['ins_upg_uptodate'] = 'Die Datenbank ist bereits auf dem neuesten Stand, keine Änderungen nötig.';
$lang['ins_upg_noconfig'] = 'Das Spiel ist noch nicht installiert (config.php ist leer): Bitte den Modus „Installieren“ verwenden.';
$lang['ins_error3'] = 'Alle Felder sind Pflichtfelder: Benutzername nur aus Buchstaben, Ziffern, _ oder -, Passwort mit mindestens 8 Zeichen und gültige E-Mail-Adresse.';
$lang['ins_locked'] = 'Das Spiel ist bereits installiert: Installation und Transfer sind gesperrt (nur das Update ist noch möglich). Für eine Neuinstallation zuerst die Datei config.php leeren. Außerdem sollte der Ordner install vom Server gelöscht werden.';

// XNova Renaissance 0.9g : titre de la page (« Installeur » ecrit en dur)
$lang['ins_page_title'] = 'XNova-Installation';

?>