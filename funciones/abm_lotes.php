<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$provincia = $_POST['provincia'];
$lote = $_POST['lote'];
$acc = $_POST['acc'];
$padron = $_POST['padron'];

switch ($padron) {
	case 'comprobantes': $sigla = 'c' ; break;
	case 'prestaciones': $sigla = 'p' ; break;
	case 'aplicacion_fondos': $sigla = 'a' ; break;
}

$prov = strlen ($provincia) == 2 ? $provincia : "0" . $provincia;

switch ($acc) {
	case 'B':
		$sql_back = "
			insert into $padron.lotes_eliminados
			select * from " . $padron . "." . $sigla . "_$prov
			where lote = $lote";

		$res_back = pg_query ($sql_back);

		if ($res_back) {
			$sql = "
				delete from " . $padron . "." . $sigla . "_$prov
				where lote = $lote";
			$res = pg_query ($sql);
			if ($res) {
				$filas_borradas = pg_affected_rows ($res);
				$sql = "
					update sistema.lotes
					set 
						id_estado = 3, 
						id_usuario_baja_lote = '$_SESSION[id_usuario]' ,
						fecha_baja_lote = LOCALTIMESTAMP
					where 
						id_provincia = '$provincia'
						and lote = '$lote'";
				$res = pg_query ($sql);
				if ($res) {
					echo "
						Se ha dado de baja el lote $lote, <br />
						eliminando un total de $filas_borradas registros";
				} else {
					die("Error " . pg_last_error());
				}
			} else {
				die("Error " . pg_last_error());
			}
		} else {
			die ("Error " . pg_last_error());
		}
	break;
	
	
	case 'C':
		$sql_cierre = "
			update sistema.lotes
			set 
				id_estado = 1 ,
				fecha_cierre_lote = LOCALTIMESTAMP ,
				id_usuario_cierre_lote = '$_SESSION[id_usuario]'
			where lote = $lote";
		$res_cierre = pg_query ($sql_cierre);
		if ($res_cierre) {
			echo "Se ha cerrado el lote $lote";
		} else {
			die (pg_last_error());
		}
	break;
}


?>
