<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

switch ($_POST['campo']) {
	case 'cuie':
		$sql = "select cuie from sistema.efectores where cuie like '$_POST[valor]%' order by cuie asc";
		$res = pg_query ($sql);
		if (pg_num_rows ($res)) {
			while ($reg = pg_fetch_assoc ($res)) {
				$arr[] = $reg['cuie'];
			}
			echo json_encode ($arr);
		} else {
			echo '["No hay sugerencias"]';
		}
	break;
	default: break;
}





?>