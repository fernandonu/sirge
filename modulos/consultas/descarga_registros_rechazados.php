<?php
session_start();
require '../../sistema/conectar_postgresql.php';

if (isset ($_GET['lote'])) {
	
	$ruta = '/var/www/sirge/export/rechazados-lote' . $_GET['lote'] . '.csv';
	
	$expo = "E'$ruta'";

	switch ($_GET['padron']) {
		case 1 : $sql = "
			select
				case
					when motivos like '%pkey%' then 'Registro ya informado o campo ORDEN mal generado'
					when motivos like '%fkey_codigo_prestacion%' then 'Codigo de prestacion se encuentra fuera de los existentes'
					when motivos like '%NPE42%' or motivos like '%MEM07%' or motivos like '%MPU23%' then motivos || ', debe especificar A, B, etc, segun corresponda, dentro del campo CODIGO'
					else motivos
				end as motivo
			, registro_rechazado
			, lote
			from prestaciones.rechazados
			where 
				lote = $_GET[lote]";
		break;
		case 2 : $sql = "
			select
				case
					when motivos like '%fkey_subcodigo_gasto%' then 'Sub codigo de gasto invalido'
					when motivos like '%fkey_codigo_gasto%' then 'Codigo de gasto invalido'
					else motivos
				end as motivos
				, registro_rechazado
				, lote
			from aplicacion_fondos.rechazados
			where lote = $_GET[lote]";		
		break;
		case 3 : $sql = "
			select
				case
					when motivos like '%pkey%' then 'Tipo y numero de comprobantes ya existentes'
					else motivos
				end as motivo
				, registro_rechazado
				, lote
			from comprobantes.rechazados
			where
				lote = $_GET[lote]"; 
		break;
		default : die("ERROR");
	}
	
	$sql = "COPY ( " . $sql . " ) TO " . $expo . " WITH CSV HEADER DELIMITER ';'";
	$res = pg_query ($sql);
	
	$zip = new ZipArchive();
	$nombre = $_GET['lote'] . '.zip';
	
	if ($zip->open ('../../export/' . $nombre , ZIPARCHIVE::CREATE) === true) {
		$zip->addFile ('../../export/rechazados-lote' . $_GET['lote'] . '.csv' ,'rechazados-lote' . $_GET['lote'] . '.csv');
		$zip->close();
		
		if (! unlink ('../../export/rechazados-lote' . $_GET['lote'] . '.csv')) {
			die ("ERROR_2");
		}
		
		if (file_exists ('../../export/' . $nombre)){
			
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename('../../export/' . $nombre));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize('../../export/' . $nombre));
			ob_clean();
			ob_get_level();
			flush();
			readfile('../../export/' . $nombre);
			exit;
		} else {
			die ("ERROR_1");
		}
	}
} else {
	die ("Error");
}
?>
