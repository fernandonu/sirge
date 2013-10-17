<?php
session_start();
require '../sistema/conectar_postgresql.php';

function descarga_datos ($fichero) {
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
		flush();
		readfile($fichero);
		exit;
	} else {
		die ($_SERVER['PATH_INFO']);
	}
}


if (isset ($_GET['p'] , $_GET['a'])) {
	switch ($_GET['a']) {
		case 'FECHAS':
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_FECHAS.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_FECHAS.csv";
			$sql = "
			copy(
				select *
				from prestaciones.p_" . $_GET['p'] . "
				where fecha_prestacion not between '2004-01-01' and current_date )
			to " . $ruta . "
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
		case 'CB-DNI':
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_CB-DNI.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_CB-DNI.csv";
			$sql = "
			copy (
				select * 
				from prestaciones.p_" . $_GET['p'] ." 
				where clave_beneficiario = '0' and numero_documento = 0	)
			to " . $ruta ."
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
		case 'CB-NOT16':
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_CB-NOT16.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_CB-NOT16.csv";
			$sql = "
			copy (
				select *
				from prestaciones.p_" . $_GET['p'] ." 
				where 
					length (clave_beneficiario) <> 16
					and codigo_prestacion not like 'CMI%')
			to " . $ruta ."
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
		case 'CODIGOS':
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_CODIGOS.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_CODIGOS.csv";
			$sql = "
			copy (
				select *
				from prestaciones.p_" . $_GET['p'] ." 
				where length (codigo_prestacion) = 0 )
			to " . $ruta . "
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
			
		case 'SUBCODIGOS':
			break;
		case 'MPU23';	
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_MPU23.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_MPU23.csv";
			$sql = "
			copy (
				select *
				from prestaciones.p_" . $_GET['p'] ."
				where 
					codigo_prestacion = 'MPU23'
					and subcodigo_prestacion not in ('A','B')
					and fecha_prestacion >= '2011-01-01' )
			to " . $ruta . "
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
			
		case 'MEM07';
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_MEM07.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_MEM07.csv";
			$sql = "
			copy (
				select *
				from prestaciones.p_" . $_GET['p'] ."
				where 
					codigo_prestacion = 'MEM07'
					and subcodigo_prestacion not in ('A','B')
					and fecha_prestacion >= '2011-01-01' )
			to " . $ruta . "
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
			
		case 'NPE42':
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_NPE42.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_NPE42.csv";
			$sql = "
			copy (
				select 
					*
				from
					prestaciones.p_" . $_GET['p'] ." 
				where
					codigo_prestacion = 'NPE42'
					and length (cast (subcodigo_prestacion as character)) = 0
					and fecha_prestacion between '2010-01-01' and current_date
				order by fecha_prestacion )
			to " . $ruta . "
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
		
		case 'CLASEDOC':
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_CLASEDOC.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_CLASEDOC.csv";
			$sql = "
			copy (
				select *
				from prestaciones.p_" . $_GET['p']  . "
				where clase_documento not in ('A','P'))
			to " . $ruta . "
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
		
		case 'REPETIDOS':
			echo "Ud ha elegido la opcion $_GET[a] de la provincia $_GET[p]";
			break;
		
		case 'EFECTORES':
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_EFECTORES.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_EFECTORES.csv";
			$sql = "
			copy (
				select * 
				from prestaciones.p_" . $_GET['p']  . "
				where cuie not in (select cuie from sistema.efectores where provincia = '" . $_GET['p']  . "'))
			to " . $ruta . "
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
		case 'SUBCODIGO':
			$ruta = "E'C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_SUBCODIGOS.csv'";
			$fichero = "C:/xampp/htdocs/sirge/export/INCONSISTENCIAS_" . $_GET['p'] . "_SUBCODIGOS.csv";
			$sql = "
			copy (
				select *
				from prestaciones.p_" . $_GET['p'] ." 
				where 
					length (cast (subcodigo_prestacion as character)) <> 0
					and codigo_prestacion not in ('LMI46','LMI47','LMI48','LMI49','CMI65','CMI66','CMI67','NPE42','MEM07','MPU23'))
			to " . $ruta . "
			with csv header delimiter ';'";
			$res = pg_query ($sql);
			descarga_datos($fichero);
			break;
		default: die("Error!! D:");
	}
	
} else {
	die ("Error :)");
}


	