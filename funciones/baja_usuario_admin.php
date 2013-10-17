<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "DELETE FROM sistema.usuarios WHERE usuario = '$_POST[usuario]'";
$res = pg_query ($sql);
if ($res) {
	echo "Usuario eliminado";
} else {
	die ("Ha ocurrido un error al eliminar el usuario solicitado");
}

?>