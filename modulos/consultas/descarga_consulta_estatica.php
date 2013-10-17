<?php
session_start();
require '../../sistema/conectar_postgresql.php';

/**
	Guardo el query a generar en $id
*/
if (isset ($_GET['id']) && $_GET['id'] <> 0){
	$id = $_GET['id'];
	$sql = "SELECT q.consulta FROM sistema.queries q WHERE q.id = " . $id;
	echo $sql;
	$rs = pg_query ($sql);
	$rg = pg_fetch_assoc ($rs);
	$sql = $rg['consulta'];
} else if (isset ($_SESSION['consulta'])) {
	$sql = $_SESSION['consulta'];
} else {
	die ("Error");
}

$random = rand();
$semi_path = 'C:/xampp/htdocs/sirge/export/Consulta_' . $_SESSION['id_usuario'] . $random . '.csv';

/**
	Ruta para la sentencia COPY del Postgres
*/
$path = "E'$semi_path'";

/**
	Ruta final del archivo
*/
$fichero = "C:/xampp/htdocs/sirge/export/Consulta_" . $_SESSION['id_usuario'] . $random . ".csv";

$sql = "COPY ( " . $sql . " ) TO " . $path . " WITH CSV HEADER DELIMITER ';'";

$rs = pg_query ($sql);

if (file_exists ($fichero)){
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename($fichero));
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($fichero));
	ob_clean();
	ob_get_level();
	flush();
	readfile($fichero);
	exit;
} else {
	die ($_SERVER['PATH_INFO']);
}
?>