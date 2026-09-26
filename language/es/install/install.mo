<?php
//é à ó ú ñ
/**
 * install.mo
 *
 * @version 0.5
 * @copyright 2008
 */

$lang['ins_appname']      = 'XNova';
$lang['ins_tx_state']     = "Etapa";
$lang['ins_tx_sys']       = "Gestión sistema";
$lang['ins_btn_next']     = 'Siguiente';
$lang['ins_btn_inst']     = "Instalar";
$lang['ins_btn_creat']    = "Crear";
$lang['ins_btn_prev']     = 'Anterior';

$lang['ins_mnu_intro']    = "Introducción";
$lang['ins_mnu_inst']     = "Instalar";
$lang['ins_mnu_upgr']     = 'Actualizar';
$lang['ins_mnu_quit']     = 'Salir';

$lang['ins_error']        = "Error";
$lang['ins_error1']       = 'La conexión a la base de datos ha fallado';
$lang['ins_error2']       = 'El archivo config.php no se puede sustituir';

$lang['ins_tx_welco']     = 'Bienvenido a la instalación de XNova';
$lang['ins_tx_intr1']     = 'El proyecto XNova le permitirá instalar un clon casi perfecto de OGame';
$lang['ins_tx_intr2']     = 'El proyecto XNova es libre, gratuito y de código abierto. Le rogamos que no haga un uso comercial de él';
$lang['ins_tx_intr3']     = 'Por respeto al equipo de desarrollo de este proyecto, le rogamos que no elimine el copyright de los archivos fuente.';
$lang['ins_tx_inst1']     = 'El servidor web debe poder escribir en el archivo config.php (basta con el permiso de escritura, no hace falta CHMOD 777)';
$lang['ins_tx_inst2']     = 'Debe disponer de una base de datos MySQL o MariaDB';
$lang['ins_tx_inst3']     = 'Debe rellenar correctamente el siguiente formulario para continuar con la instalación:';
$lang['ins_tx_acc1']      = 'Está a punto de crear una cuenta de administrador';
$lang['ins_tx_acc2']      = 'Rellene el siguiente formulario con los datos de la cuenta:';
$lang['ins_tx_done']      = "¡Enhorabuena, instaló correctamente a XNova!";

$lang['ins_form_server']  = 'Servidor SQL';
$lang['ins_form_db']      = 'Base de datos';
$lang['ins_form_prefix']  = 'Prefijo de las tablas';
$lang['ins_form_login']   = 'Usuario';
$lang['ins_form_pass']    = "Contraseña";
$lang['ins_form_install'] = "Instalar";

$lang['ins_acc_user']     = 'Nombre de usuario';
$lang['ins_acc_pass']     = "Contraseña";
$lang['ins_acc_email']    = 'Dirección de correo electrónico';
$lang['ins_acc_planet']   = "Planeta principal";
$lang['ins_acc_sex']      = "Sexo";
$lang['ins_acc_sex0']     = "-indefinido-";
$lang['ins_acc_sex1']     = "Hombre";
$lang['ins_acc_sex2']     = "Mujer";

// Traduction By Katsumi.All rights reversed (C) 2008


// XNova Renaissance : modes Transfere et Mise a jour
$lang['ins_tx_goto1'] = 'La transferencia recupera una base existente (XNova 0.8e original o XNova Renaissance), por ejemplo tras un cambio de servidor.';
$lang['ins_tx_goto2'] = 'El archivo config.php se reescribe y la base se actualiza si viene de una versi&oacute;n anterior.';
$lang['ins_tx_goto3'] = 'Las bases UGamela, Legacies y otros derivados no son compatibles. &iexcl;Haga una copia de seguridad antes!';
$lang['ins_tx_goto4'] = 'Debe disponer ya de una base XNova 0.8e o XNova Renaissance.';
$lang['ins_tx_goto5'] = 'Rellene el formulario con los datos exactos de esa base (servidor, nombre, prefijo de las tablas, identificadores).';
$lang['ins_goto_err_version'] = 'Esta base no es ni una XNova 0.8e original ni una XNova Renaissance (compruebe el nombre de la base y el prefijo).';
$lang['ins_goto_done_version'] = 'Base XNova Renaissance recuperada (versi&oacute;n de origen: %s) y archivo config.php escrito.';
$lang['ins_upg_intro1'] = 'La actualizaci&oacute;n aplica a la base del juego los cambios de las versiones m&aacute;s recientes de XNova Renaissance.';
$lang['ins_upg_intro2'] = 'Utiliza los datos del archivo config.php existente. Bases compatibles: XNova 0.8e original y XNova Renaissance.';
$lang['ins_upg_intro3'] = '&iexcl;Haga una copia de seguridad de su base antes de continuar!';
$lang['ins_upg_from_version'] = 'Versi&oacute;n de la base antes de la actualizaci&oacute;n: %s';
$lang['ins_upg_applied'] = 'Actualizaciones aplicadas: %s';
$lang['ins_upg_uptodate'] = 'La base ya est&aacute; actualizada, no hace falta ning&uacute;n cambio.';
$lang['ins_upg_noconfig'] = 'El juego a&uacute;n no est&aacute; instalado (config.php est&aacute; vac&iacute;o): utilice el modo Instalar.';

// XNova Renaissance 0.9g : verrou de l'installeur
$lang['ins_locked'] = 'El juego ya est&aacute; instalado: la instalaci&oacute;n y la transferencia est&aacute;n bloqueadas (solo queda la actualizaci&oacute;n). Para reinstalar, vac&iacute;e primero el archivo config.php. Recuerde tambi&eacute;n borrar la carpeta install del servidor.';

// XNova Renaissance 0.9g : traductions
$lang['ins_btn_login'] = 'Conectarse';
$lang['ins_mnu_goto'] = 'Transferencia';
$lang['ins_tx_done1'] = '¡La base de datos se ha instalado correctamente!';
$lang['ins_tx_done2'] = '¡La cuenta de administrador se ha creado correctamente!';
$lang['ins_tx_done3'] = '¡Se recomienda eliminar la carpeta <i>install</i> si ya no necesita el instalador!';
$lang['ins_tx_done4'] = '¡La transferencia se ha realizado correctamente!';
$lang['ins_error3'] = 'Todos los campos son obligatorios: nombre de usuario con letras, cifras, _ o -, contraseña de al menos 8 caracteres y dirección de correo válida.';

// XNova Renaissance 0.9g : titre de la page (« Installeur » ecrit en dur)
$lang['ins_page_title'] = 'Instalación de XNova';

?>