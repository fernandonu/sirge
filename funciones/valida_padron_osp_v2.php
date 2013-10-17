<?php
session_start();
$nivel = 1;
require 'funciones.php';
require '../seguridad.php';
require '../sistema/conectar_postgresql.php';
require '../sistema/benchmark.php';

function mes_a_texto ($mes_numerico) {
	switch ($mes_numerico) {
		case '01': return 'enero'; break;
		case '02': return 'febrero'; break;
		case '03': return 'marzo'; break;
		case '04': return 'abril'; break;
		case '05': return 'mayo'; break;
		case '06': return 'junio'; break;
		case '07': return 'julio'; break;
		case '08': return 'agosto'; break;
		case '09': return 'septiembre'; break;
		case '10': return 'octubre'; break;
		case '11': return 'noviembre'; break;
		case '12': return 'diciembre'; break;
		default: die("Error!!"); 
	}
}

function tipo_documento_provincia ($provincia) {
	return 'C' . $provincia;
}

$tipos_doc =  array (
	'DNI', 'LC' , 'LE' , 'CM' , 'PAS', 'C01', 
	'C02', 'C03', 'C04', 'C05', 'C06', 'C07',
	'C08', 'C09', 'C10', 'C11', 'C12', 'C13',
	'C14', 'C15', 'C16', 'C17', 'C18', 'C19',
	'C20', 'C21', 'C22', 'C23', 'C24', 'CI' ,
	'OTR', 'ET' , 'NE' , 'DU' );

$nombre_archivo	= $_POST['archivo'];
$ruta_archivo	= '../upload/osp/' . $nombre_archivo;

$sql = "
	select
		os.codigo_os
		, os.id_entidad
	from sistema.cargas_archivos c1
		left join sistema.cargas_archivos_osp c2 on c1.id_carga = c2.id_carga
		left join sistema.obras_sociales os on c2.codigo_os = os.codigo_os
	where
		procesado = 'N'
		and id_padron = 6
		and nombre_actual = '$nombre_archivo'";

$res = pg_query ($sql);
$datos_cabecera = pg_fetch_row ($res , 0);

$rep_fh = fopen ('../upload/osp/rep/REPETIDOS-' . $datos_cabecera[1] . '.txt' , 'w+');


if ($datos_cabecera[1] <> 26) {
	$sql = "truncate table osp.osp_" . $datos_cabecera[1] ;
	$res = pg_query ($sql);

	if (! $res) {
		die ("Error al truncar la base");
	}
}
/*
*/

switch ($datos_cabecera[1]) {
	
	/**
		Entidad 26 - SSS
	*/
	case 26:
		if ($file = fopen ($ruta_archivo , "rb")) {
			
			
			time_start();
			
			$total_registros 		= count (file ($ruta_archivo));
			$linea_actual 			= 0;
			$registros_rechazados 	= 0;
			$registros_insertados 	= 0;
			$motivo 				= '' ;	
		
			$sql = "insert into osp.osp_26 values ";
			$sql_ex = $sql;
		
			while (! feof ($file)) {
				
				$rechazado = 0;
				
				$data = explode ("," , pg_escape_string (str_replace ("\r\n" , '' , fgets ($file)))) ;
				$linea_actual ++ ;
				
				if (count ($data) > 1) {
					if (count ($data) == 16) {
						unset ($data[4]);
						$data = array_merge ($data);
					} else if (count ($data) > 16) {
						$rechazado = 1 ;
					}
					
					if (count ($data) == 15) {
						
						/**
							Campo 0 -> Cuil del beneficiario
						*/
						if (! (is_numeric ($data[0]))) {
							$rechazado = 1 ;
						}
						
						/**
							Campo 1 -> Tipo de documento
						*/
						if ($data[1] > 0 && $data[1] < 25) {
							$data[1] = tipo_documento_provincia ($data[1]);
						} else if ($data[1] == 'DU') {
							$data[1] = 'DNI';
						} else if ($data[1] == 'PA') {
							$data[1] = 'PAS';
						}
						
						if (! in_array (trim ($data[1]) , $tipos_doc)) {
							$rechazado = 1 ;
						}
						
						/**
							Campo 2 -> Número de documento
						*/
						if (! (is_numeric ($data[2])) || $data[2] == 0 || strlen ((int)$data[2]) < 4) {
							$rechazado = 1 ;
						}
						
						/**
							Campo 3 -> Apellido y nombre
						*/
						
						/**
							Campo 4 -> Sexo
						*/
						if ($data[4] <> 'M' && $data[4] <> 'F') {
							$rechazado = 1 ;
						}
						
						/**
							Campo 5 -> Fecha de nacimiento
						*/
						$dia = substr ($data[5] , 0 , 2);
						$mes = substr ($data[5] , 2 , 2);
						$anio = substr ($data[5] , 4 , 4);
						
						if (checkdate ($mes , $dia , $anio)) {
							$data[5] = $anio . '-' . $mes . '-' . $dia;
						} else {
							$rechazado = 1 ;
						}
						
						/**
							Campo 6 -> Tipo de Beneficiario
							Campo 7 -> Código de parentesco
							Campo 8 -> Código postal
						*/
						$data[8] = trim ($data[8]);
						
						/**
							Campo 9 -> Provincia
						*/
						if ($data[9] < 1 || $data[9] > 24) {
							$rechazado = 1 ;
						}
						
						/**
							Campo 10 -> Cuil del titular
						*/
						if (! (is_numeric ($data[10]))) {
							$rechazado = 1 ;
						}
						
						/**
							Campo 11 -> Código OSP (Según RNOS)
							Validar contra tabla
						*/
						
						/**
							Campo 12 -> Ultimo período de aporte
						if ($data[12] == 0) {
							$rechazado = 1 ;
						}
						*/
						
					} else {
						$rechazado = 1 ;
					}
					
					if ($rechazado <> 1) {
					
						$sql_ex .= "('" . implode ("','" , $data) . "'),";
					
						if (($linea_actual % 10000) == 0 || $linea_actual == $total_registros) {
							$res = pg_query (trim ($sql_ex , ','));
							if (! $res ) {
								echo $sql_ex; die();
							}
							
							$sql_ex = $sql ;
							
							$registros_insertados += pg_affected_rows ($res) ;
						}
					} else {
						$registros_rechazados ++ ;
					}
				}
			}
		}
	break;
	
	case 27 :
		time_start();
		
		if ($file = fopen ($ruta_archivo , "rb")) {
			
			$total_registros 		= count (file ($ruta_archivo));
			$linea_actual 			= 0;
			$registros_rechazados 	= 0;
			$registros_insertados 	= 0;
			$motivo					= '';
		
			$sql = "insert into osp.osp_27 values ";
			$sql_ex = $sql;
		
			while (! feof ($file)) {
				
				$rechazado = 0;
				
				$data = explode ("\t" , pg_escape_string (str_replace ("\r\n" , '' , fgets ($file)))) ;
				$linea_actual ++ ;
				
				if (count ($data) > 1) {

					/**
						Campo 0 -> Tipo de documento
					*/
					$data[0] = $data[0] == 'DU' ? 'DNI' : $data[0] ;
					
					if (! in_array ($data[0] , $tipos_doc)) {
						$rechazado = 1 ;
						$motivo .= 'Tipo de documento inválido ' ;
					}
					
					/**
						Campo 1 -> Número de documento
					*/
					if (! is_numeric ($data[1]) || $data[1] == 0) {
						$rechazado = 1 ;
						$motivo .= 'Número de documento inválido ' ;
					}
					
					/**
						Campo 2 -> Nombre y apellido
						Campo 3 -> Sexo
						Campo 4 -> Fecha de nacimiento
					*/
					$data[4] = substr ($data[4] , 0 , 10) ;
					
					/**
						Campo 5 -> Fecha alta
					*/
					$data[5] = substr ($data[5] , 0 , 10) ;
					
					/**
						Campo 9 -> Fecha desde
					*/
					$data[9] = substr ($data[9] , 0 , 10) ;
					
					/**
						Campo 10 -> Fecha desde
					*/
					$data[10] = substr ($data[10] , 0 , 10) ;
					
					
					if ($rechazado <> 1) {
						$sql_ex .= "('" . implode ("','" , $data) . "'),";
						
						if (($linea_actual % 10000) == 0 || $linea_actual == $total_registros) {
							$res = pg_query (trim ($sql_ex , ','));
							if (! $res ) {
								echo $sql_ex; die();
							}
							
							$sql_ex = $sql ;
							
							$registros_insertados += pg_affected_rows ($res) ;
						}
					} else {
						$registros_rechazados ++ ;
					}
				}
			}
		}
	break;
	
	default:

		if ($file = fopen ($ruta_archivo , "rb")) {
			time_start();
			
			$total_registros 		= count (file ($ruta_archivo));
			$registros_rechazados 	= 0;
			$registros_insertados 	= 0;
			$linea_actual 			= 0;
			$motivo 				= '' ;
			
			if ($datos_cabecera[1] == 18) {
				$sql = "insert into osp.osp_" . $datos_cabecera[1] . " ( tipo_documento , numero_documento , nombre_apellido , sexo , codigo_os , codigo_postal , tipo_afiliado ) values ";
			} else {
				$sql = "insert into osp.osp_" . $datos_cabecera[1] . " ( tipo_documento , numero_documento , nombre_apellido , sexo , codigo_postal , tipo_afiliado ) values ";
			}
			
			$sql_ex = $sql;
			
			while (!feof ($file)) {

				$rechazado = 0;
				
				if ($datos_cabecera[1] == 18) {
					$data = explode ("||" , pg_escape_string (str_replace ("\r\n" , '' , fgets ($file))));
					
				} else {
					$data = explode ("||" , pg_escape_string (str_replace ("\r\n" , '' , fgets ($file)))) ;
				}
				$linea_actual ++;
				
				if (count ($data) > 1) {

					if ($data[4] == $datos_cabecera[0] && $data[6] == $datos_cabecera[1]) {
						
						if ($datos_cabecera[1] <> 18) {
							array_splice ($data , 4 , -2);
						} 
						
						array_splice ($data , 6 , -1);
						
						//echo "<pre>" , print_r ($data) , "</pre>"; die();
						
						/**
							Campo 0 -> Tipo Doc
							Controlo que se encuentre entre los permitidos
						*/
						$data[0] = $data[0] == 'DU' ? 'DNI' : $data[0] ;
						
						if (! in_array (trim ($data[0]) , $tipos_doc)) {
							$rechazado = 1;
							$motivo .= ' Tipo de documento inválido';
						}
						
						/**
							Campo 1 -> DNI
							Controlo que sea numérico
						*/
						if ((! (is_numeric ($data[1]))) || $data[1] == 0 || strlen ((int)$data[1]) < 5) {
							$rechazado = 1;
							$motivo .= ' Campo número de documento inválido';
						} else if (strlen ((int)$data[1]) > 8 && $data[0] == 'LC') {
							$rechazado = 1;
							$motivo .= ' Numero de documento muy largo';
						}
					
						/**
							Campo 2 -> Nombre y apellido
							Saco las comas u otros caracteres -> Ver de hacer con una expresión regular
						*/
						$busqueda = array (',');
						$data[2] = trim (str_replace ($busqueda , '' , strtoupper ($data[2])));
						
						if (strlen ($data[2]) > 50) {
							$rechazado = 1 ;
							$motivo .= ' Nombre demasiado largo';
						}
						
						/**
							Campo 3 -> Sexo
							Controlo que sea M o F
						if ($data[3] <> 'F' && $data[3] <> 'M') {
							 $rechazado = 1;
							 $motivo .= ' Campo sexo no es M o F';
						}
						*/
						
						/**
							Campo 5 -> Tipo socio
							Controlo que sea T o A
						*/
						if ($data[5] <> 'T' && $data[5] <> 'A') {
							 $data[5] = 'A';
						}
						
					} else {
						$rechazado = 1 ;
						$motivo = ' Código de OS o ID provincia inválidos ';
					}
					
					if ($rechazado <> 1) {
					
						
					
						$sql_ex .= "('" . implode ("','" , $data) . "'),";

						if (($linea_actual % 10000) == 0 || $linea_actual == $total_registros) {
							
							//echo $sql_ex ; die();
							
							$res = pg_query (trim ($sql_ex , ','));
							if (! $res ) {
								echo $sql_ex; die();
							}
							
							$sql_ex = $sql ;
							
							$registros_insertados += pg_affected_rows ($res) ;
						}
					} else {
						$registros_rechazados ++ ;
						file_put_contents ("../upload/osp/rec/RECHAZADOS-" . $datos_cabecera[1] . ".txt" , $motivo . " en línea $linea_actual ||" .  implode ("||" , $data) . "\r\n" , FILE_APPEND);
						$motivo = '' ;
					}
				} else {
					
					if (strlen ($sql) <> strlen ($sql_ex)) {
						$res = pg_query (trim ($sql_ex , ','));
						if (! $res ) {
							echo $sql_ex; die();
						}
						
						$sql_ex = $sql ;
						
						$registros_insertados += pg_affected_rows ($res) ;
					}
				}
			}
			fclose ($rep_fh);
		} else {
			echo "Error al abrir el archivo" ;
		}
	break;
}

fclose ($file);
rename ($ruta_archivo , '../upload/osp/back/' . $datos_cabecera[0] . '-' . date("Ymdhms") . '.txt');

$sql = "
	update sistema.cargas_archivos
	set procesado = 'S'
	where
		id_padron = 6
		and nombre_actual = '$nombre_archivo'
		and procesado = 'N'";
$res = pg_query ($sql);

$sql = "
	select *
	from sistema.procesos_obras_sociales
	where
		id_entidad = '" . $datos_cabecera[1] . "'
		and periodo = " . date("mY") . "
		and puco = 'N'";
$res = pg_query ($sql);

if (pg_num_rows ($res)) {
	switch ($datos_cabecera[1]) {
		case '26' :
			$reg = pg_fetch_assoc ($res);
			
			$sql = "
				update sistema.procesos_obras_sociales
				set
					registros_insertados = " . ($reg['registros_insertados'] + $registros_insertados) . "
					, registros_rechazados = " . ($reg['registros_rechazados'] + $registros_rechazados) . "
					, registros_totales = " . ($reg['registros_totales'] + $total_registros) . "
				where
					id_entidad = '26'
					and puco = 'N'
					and periodo = " . date('mY');
			$res = pg_query ($sql);
			
			if (! $res) {
				echo $sql; die();
			}
			break;
			
		default : 
			$sql = "
				update sistema.procesos_obras_sociales
				set
					registros_insertados = " . $registros_insertados . "
					, registros_rechazados = " . $registros_rechazados . "
					, registros_totales = " . $total_registros . "
				where
					id_entidad = '" . $datos_cabecera[1] . "'
					and puco = 'N'
					and periodo = " . date('mY') ;
			pg_query ($sql);
		
		
	}
} else {
	$sql = "
		insert into sistema.procesos_obras_sociales
		values ('" . $datos_cabecera[1] . "' , '" . date('mY') . "' , $registros_insertados , $registros_rechazados , $total_registros , 'N')";
	$res = pg_query ($sql);
}

echo 
	"
	Total de registros 		: $total_registros <br />
	Registros insertados 	: $registros_insertados <br />
	Registros rechazados 	: $registros_rechazados <br />
	Porcentaje de rechazos	: " . round (($registros_rechazados / $total_registros) * 100 , 2) . "% <br />
	Tiempo: " . round ( time_end() , 4 ) ;
?>
