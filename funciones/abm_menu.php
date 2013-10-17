<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "SELECT * FROM sistema.grupos_menu g WHERE g.id_grupo = $_POST[grupo]";
$res = pg_query ($sql);

if (!$res) {
	die ("Ha ocurrido un error! " . pg_last_error());
} else {

	/**
		Guardo el menú del usuario en el array $menu
	*/
	while ($reg = pg_fetch_assoc ($res)) {
		$menu[] = $reg['id_modulo'];
	}

	/**
		Si no existe la variable $_POST[modulo], se da de baja el menú entero
	*/
	if (! (isset ($_POST['modulo']))) {
		$sql_destroy = "DELETE FROM sistema.grupos_menu g WHERE g.id_grupo = $_POST[grupo]";
		$res_destroy = pg_query ($sql_destroy);
		if ($res_destroy) {
			echo "Se ha dado de baja el men&uacute;";
		} else {
			die ("Ha ocurrido un error! " . pg_last_error());
		}
	} else if (isset ($menu)) {
		$bajas = implode (',' , array_diff ($menu , $_POST['modulo']));
		$altas = array_merge (array_diff ($_POST['modulo'] , $menu));
		
		if (strlen ($bajas)) {
			$sql_bajas = "DELETE FROM sistema.grupos_menu g WHERE g.id_grupo = $_POST[grupo] AND g.id_modulo IN ($bajas)";
			$res_baja = pg_query ($sql_bajas);
		}
		
		if (count ($altas)) {
			$sql_altas = '';
			for ($i = 0 ; $i < count ($altas) ; $i ++) {
				$sql_altas .= "INSERT INTO sistema.grupos_menu VALUES ($_POST[grupo] , $altas[$i]);";
			}
			$res_alta = pg_query ($sql_altas);
		}
		echo "Se ha modificado el men&uacute;";
	} else {
		$altas = array_merge ($_POST['modulo']);
		if (count ($altas)) {
			$sql_altas = '';
			for ($i = 0 ; $i < count ($altas) ; $i ++) {
				$sql_altas .= "INSERT INTO sistema.grupos_menu VALUES ($_POST[grupo] , $altas[$i]);";
			}
			$res_alta = pg_query ($sql_altas);
		echo "Se ha generado el men&uacute;";
		}
	}
}
?>