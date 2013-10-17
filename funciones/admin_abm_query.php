<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

switch ($_POST['accion']) {

	case 'A':
		$nombre = pg_escape_string (htmlentities (str_replace ("'" , "''" , trim ($_POST['nombre_query'])) , ENT_QUOTES , 'UTF-8'));
		$descripcion = pg_escape_string (htmlentities (str_replace ("'" , "''" , trim ($_POST['descr_query'])) , ENT_QUOTES , 'UTF-8'));
		$area = $_POST['area_query'];
		$grafico = $_POST['grafico_query'];
		$sentencia = pg_escape_string (trim ($_POST['sql_query']));
	
		$sql = "
			INSERT INTO sistema.queries ( nombre , descripcion , area , consulta , grafico )
			VALUES ( '$nombre' , '$descripcion' , '$area' , '$sentencia' , '$grafico' )" ;
		$res = pg_query ($sql);
		
		if ($res) {
			echo "Query insertado";
		} else {
			die ("Ha ocurrido un error " . pg_last_error());
		}
		break;
		
	case 'V':
		$sql = "SELECT * FROM sistema.queries WHERE id = $_POST[id]";
		$res = pg_query ($sql);
		if (pg_num_rows ($res) > 0) {
			$reg = pg_fetch_assoc ($res);
			echo "<span style=\"font-size: 12px;\">" , nl2br ($reg['consulta']) , "<span>";
		}
		break;
		
	case 'E':
		$nombre = pg_escape_string (htmlentities (str_replace ("'" , "''" , trim ($_POST['nombre_query'])) , ENT_QUOTES , 'UTF-8'));
		$descripcion = pg_escape_string (htmlentities (str_replace ("'" , "''" , trim ($_POST['descr_query'])) , ENT_QUOTES , 'UTF-8'));
		$area = $_POST['area_query'];
		$grafico = $_POST['grafico_query'];
		$sentencia = pg_escape_string (trim ($_POST['sql_query']));
	
		$sql = "
			UPDATE sistema.queries 
			SET nombre = '$nombre' , descripcion = '$descripcion' , area = '$area' , grafico = '$grafico' , consulta = '$sentencia'
			WHERE id = $_POST[id]";
		$res = pg_query ($sql);
		if ($res) {
			echo "Query modificado";
		} else {
			die ("Ha ocurrido un error " . pg_last_error());
		}
		break;
	case 'D':
		$sql = "DELETE FROM sistema.queries WHERE id = $_POST[id]";
		$res = pg_query ($sql);
		if ($res) {
			echo "Se ha eliminado el query";
		} else {
			die ("Ha ocurrido un error " . pg_last_error());
		}
		break;
	default: break;
}
?>