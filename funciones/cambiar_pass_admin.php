<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$pass_new= md5 ($_POST['pass_2']);

$sql = "SELECT * FROM sistema.usuarios WHERE usuario = '$_SESSION[usr_ch_pass]'";
$res = pg_query ($sql);

if (pg_num_rows ($res) == 1) {
	$sql = "UPDATE sistema.usuarios SET password = '$pass_new' WHERE usuario = '$_SESSION[usr_ch_pass]'";
	$res = pg_query ($sql);
	if ($res) {
		echo "Contrase&ntilde;a cambiada con &eacute;xito";
	} else {
		die ("Ha ocurrido un error");
	}
} else {
	die ("Ha ocurrido un error");
}

unset ($_SESSION['usr_ch_pass']);
?>