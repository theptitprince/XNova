<?php
//é à ó ú ñ
/**
 * install.mo
 *
 * @version 0.5
 * @copyright 2008
 */

$lang['ins_appname']      = "XNova España";
$lang['ins_tx_state']     = "Etapa";
$lang['ins_tx_sys']       = "Gestión sistema";
$lang['ins_btn_next']     = "Sigiente";
$lang['ins_btn_inst']     = "Instalar";
$lang['ins_btn_creat']    = "Crear";
$lang['ins_btn_prev']     = "Precedente";

$lang['ins_mnu_intro']    = "Introducción";
$lang['ins_mnu_inst']     = "Instalar";
$lang['ins_mnu_upgr']     = "Update";
$lang['ins_mnu_quit']     = "Dejar";

$lang['ins_error']        = "Error";
$lang['ins_error1']       = "La conexión a la base de datos a fallado;";
$lang['ins_error2']       = "El fichero config.php no puede ser sustituir";

$lang['ins_tx_welco']     = "Bienvenido en la instalación de XNova";
$lang['ins_tx_intr1']     = "El proyecto XNova le permitirá instalar un clon de ogame casi perfecto";
$lang['ins_tx_intr2']     = "El proyecto XNova es libre, gratuito y OpenSource. Gracias de no hacer utilización comercial";
$lang['ins_tx_intr3']     = "Por respeto para el equipo de desarrollo de este proyecto, se les ruega no suprimir el copyright de los ficheros fuente.";
$lang['ins_tx_inst1']     = "El fichero config.php debe ser en CHMOD 777";
$lang['ins_tx_inst2']     = "Debe poseder una base de datos MySQL";
$lang['ins_tx_inst3']     = "Debe llenar el formulario siguiente correctamente para seguir la instalación:";
$lang['ins_tx_acc1']      = "Está a punto de crear una cuenta administrador";
$lang['ins_tx_acc2']      = "Llene el formulario siguiente con la información de la cuenta:";
$lang['ins_tx_done']      = "¡Enhorabuena, instaló correctamente a XNova!";

$lang['ins_form_server']  = "Servor SQL";
$lang['ins_form_db']      = "Base de dato";
$lang['ins_form_prefix']  = "Préfix de las tablas";
$lang['ins_form_login']   = "Identifiente";
$lang['ins_form_pass']    = "Contraseña";
$lang['ins_form_install'] = "Instalar";

$lang['ins_acc_user']     = "Identifiente";
$lang['ins_acc_pass']     = "Contraseña";
$lang['ins_acc_email']    = "Dirección correo electrónico";
$lang['ins_acc_planet']   = "Planeta principal";
$lang['ins_acc_sex']      = "Sexo";
$lang['ins_acc_sex0']     = "-indefinido-";
$lang['ins_acc_sex1']     = "Hombre";
$lang['ins_acc_sex2']     = "Mujer";

// Traduction By Katsumi.All rights reversed (C) 2008


// XNova Renaissance : modes Transfere et Mise a jour
$lang['ins_tx_goto1'] = 'La transferencia recupera una base XNova Renaissance existente (versi&oacute;n 0.9d o m&aacute;s reciente), por ejemplo tras un cambio de servidor.';
$lang['ins_tx_goto2'] = 'El archivo config.php se reescribe y la base se actualiza si viene de una versi&oacute;n anterior.';
$lang['ins_tx_goto3'] = 'Las bases UGamela, XNova 0.8 y Legacies no son compatibles. &iexcl;Haga una copia de seguridad antes!';
$lang['ins_tx_goto4'] = 'Debe disponer ya de una base XNova Renaissance.';
$lang['ins_tx_goto5'] = 'Rellene el formulario con los datos exactos de esa base (servidor, nombre, prefijo de las tablas, identificadores).';
$lang['ins_goto_err_version'] = 'Esta base no es una base XNova Renaissance 0.9d o m&aacute;s reciente (compruebe el nombre de la base y el prefijo).';
$lang['ins_goto_done_version'] = 'Base XNova Renaissance recuperada (versi&oacute;n de origen: %s) y archivo config.php escrito.';
$lang['ins_upg_intro1'] = 'La actualizaci&oacute;n aplica a la base del juego los cambios de las versiones m&aacute;s recientes de XNova Renaissance.';
$lang['ins_upg_intro2'] = 'Utiliza los datos del archivo config.php existente. Solo se admiten bases 0.9d o m&aacute;s recientes.';
$lang['ins_upg_intro3'] = '&iexcl;Haga una copia de seguridad de su base antes de continuar!';
$lang['ins_upg_from_version'] = 'Versi&oacute;n de la base antes de la actualizaci&oacute;n: %s';
$lang['ins_upg_applied'] = 'Actualizaciones aplicadas: %s';
$lang['ins_upg_uptodate'] = 'La base ya est&aacute; actualizada, no hace falta ning&uacute;n cambio.';
$lang['ins_upg_noconfig'] = 'El juego a&uacute;n no est&aacute; instalado (config.php est&aacute; vac&iacute;o): utilice el modo Instalar.';

?>