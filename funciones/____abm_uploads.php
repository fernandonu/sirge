<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$file = trim ($_POST['file']);

switch ($_POST['acc']) {
	case 'D':
		switch ($_POST['info']) {
			case 'P':
				if (unlink ("../upload/prestaciones/" . $file)) {
					echo "Archivo eliminado <br />";
					$sql = "update sistema.cargas_sirge set procesado = 'E' , fecha_baja = LOCALTIMESTAMP where nombre_actual = '$file'";
					$res = pg_query ($sql);
					if (! $res) {
						echo "No se ha podido actualizar la base de datos. Contacte al administrador del sistema.";
					}
				} else {
					echo "No se ha encontrado el archivo";
				}
			break;
		}
	break;
}



?>