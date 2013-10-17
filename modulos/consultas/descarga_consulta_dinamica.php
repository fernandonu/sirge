<?php
session_start();
require '../../sistema/conectar_postgresql.php';

if (isset ($_SESSION['consulta'])) {
	$sql = $_SESSION['consulta'];
	
	$id_unico = uniqid();
	
	$ruta = '/var/www/sirge/export/consulta_' . $id_unico . '.csv';
	
	$expo = "E'$ruta'";

	$sql = "COPY ( " . $sql . " ) TO " . $expo . " WITH CSV HEADER DELIMITER ';'";
	//echo $sql; die();
	
	$res = pg_query ($sql);
	
	$zip = new ZipArchive();
	$nombre = $id_unico . '.zip';
	
	if ($zip->open ('../../export/' . $nombre , ZIPARCHIVE::CREATE) === true) {
		$zip->addFile ('../../export/consulta_' . $id_unico . '.csv' ,'consulta.csv');
		$zip->close();
		
		if (! unlink ('../../export/consulta_' . $id_unico . '.csv')) {
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
	die ("ERROR_0");
}
?>
