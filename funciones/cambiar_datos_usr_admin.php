<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$activo 	= $_POST['act'];
$usuario 	= $_POST['use'];
$email  	= pg_escape_string (htmlentities ($_POST['eml'] , ENT_QUOTES , "UTF-8"));
$nombre 	= pg_escape_string (htmlentities ($_POST['nom'] , ENT_QUOTES , "UTF-8"));
$grupo 		= pg_escape_string (htmlentities ($_POST['gru'] , ENT_QUOTES , "UTF-8"));
$juris 		= pg_escape_string (htmlentities ($_POST['jur'] , ENT_QUOTES , "UTF-8"));

$sql = "UPDATE sistema.usuarios SET descripcion = '$nombre' , email = '$email' , activo = '$activo' , grupo = $grupo , jurisdiccion = '$juris' WHERE usuario = '$usuario'";
$res = pg_query ($sql);
if ($res) {
	echo "Datos modificados";
} else {
	die ("Ha ocurrido un error");
}

?>