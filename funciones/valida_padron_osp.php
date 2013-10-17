<?php
session_start();
$nivel = 1;
require 'funciones.php';
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
require $ruta.'sistema/benchmark.php';

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
	'OTR', 'ET' , 'DE' , 'NE');

$nombre_archivo	= $_POST['archivo'];
$ruta_archivo	= '../upload/osp/' . $nombre_archivo;
$columna_pivot 	= mes_a_texto (date('m')) . '_' . date('Y');

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
$datos_cabecera = pg_fetch_row($res , 0);

$sql = "
	select column_name
	from information_schema.columns
	where 
		table_name = 'osp_" . $datos_cabecera[1]."'
		and column_name = '". $columna_pivot ."'";
$res = pg_query ($sql);

$existe_columna = pg_num_rows ($res);

$verificar_repetidos = 0;

$rep_fh = fopen ('../upload/osp/rep/REPETIDOS-' . $datos_cabecera[1] . '.txt' , 'w+');

if ($existe_columna) {
	
	/**
		Pongo el valor de la columna en "N" para prevenir inconvenientes
	*/
	$sql = "
		update osp.osp_" . $datos_cabecera[1] . "
		set $columna_pivot = 'N'";
	$res = pg_query ($sql);
	
} else {

	/**
		Si no existe la columna, la creo
	*/
	$sql = "
		alter table osp.osp_" . $datos_cabecera[1] . " add column $columna_pivot character(1);
		alter table osp.osp_" . $datos_cabecera[1] . " alter column $columna_pivot set default 'N'::bpchar";
	$res = pg_query ($sql);
	
	/**
		Aviso que la columna ya existe
	*/
	$res ? $existe_columna = 1 : die();
	
	$verificar_repetidos = $res ? 1 : 0 ;
	
	if ($existe_columna) {
		$sql = "
			update osp.osp_" . $datos_cabecera[1] . "
			set $columna_pivot = 'N'";
		$res = pg_query ($sql);
	}
}



switch ($datos_cabecera[1]) {
	
	/**
		Entidad 26 - SSS
	*/
	case 26:
		if ($file = fopen ($ruta_archivo , "rb")) {
			
			time_start();
			
			$linea_actual = 0;
			$registros_rechazados 	= 0;
			$registros_insertados 	= 0;
		
			$sql = "insert into osp.osp_26 values ";
			$sql_ext = $sql;
		
			while (!feof ($file)) {
				
				$rechazado = 0 ;
				
				$linea = fgets ($file);
				$linea_actual ++ ;
				
				$data = explode (',' , pg_escape_string (trim ($linea)));
				
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
				
				if ($rechazado != 1) {
					if (($linea_actual % 10000) <> 0 && !feof ($file)) {
						$sql_ext .= "('" . implode ("','" , $data) . "'),";
					} else {
						$sql_ext = trim ($sql_ext , ',');
						$res = pg_query ($sql_ext);
						if (!$res) {
							echo $sql_ext , '<br />';
							die();
						}
						$sql_ext = $sql;
					}
				}
			}
			
			$sql_ext = trim ($sql_ext , ',');
			$res = pg_query ($sql_ext);
			if (! $res) {
				echo $linea_actual , '<br />' , $sql_ext;
			}
		}
	echo round (time_end() , 4);
	break;
	
	case 27 :
		if ($file = fopen ($ruta_archivo , "rb")) {
			
			time_start();
			
			$linea_actual = 0;
			$registros_rechazados 	= 0;
			$registros_insertados 	= 0;
		
			$sql = "insert into osp.osp_26 values ";
			$sql_ext = $sql;
		
			while (! feof ($file)) {
				//$linea = fgets ($file);
				
				$data = explode ("\t" , trim (fgets ($file))) ;
				$linea_actual ++ ;
				
				/**
					Campo 0 -> Tipo de documento
				*/
				$data[0] = $data[0] == 'DU' ? 'DNI' : $data[0] ;
				
				if (! in_array ($data[0] , $tipos_doc)) {
					$rechazado = 1 ;
				}
				
				/**
					Campo 1 -> Número de documento
				*/
				if (! (is_numeric ($data[1])) || $data[1] == 0 || strlen ((int)$data[1]) < 4) {
					$rechazado = 1 ;
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
				
				//echo "<pre>" , print_r ($data) , "</ pre>";
				
				if (
				
				$sql = "insert into osp.osp_27 values ('" . implode ("','" , $data) . "')" ;
				
				echo $sql ;
			}
		}
	break;
	
	default:
	
		if ($file = fopen ($ruta_archivo , "rb")) {
			
			$registros_rechazados 	= 0;
			$registros_insertados 	= 0;
			$registros_actualizados	= 0;
			$linea_actual 			= 0;

			while (!feof ($file)) {

				$rechazado = 0;
				$motivo = '';
				$linea = fgets ($file);
				$linea_actual ++;
				
				$data = explode ("||" , pg_escape_string (trim ($linea)));
				
				if ($data[4] == $datos_cabecera[0]) {
					/**
						Saco el id y el código de la OSP de la provincia
					*/
					array_splice ($data , 6 , -1);
					array_splice ($data , 4 , -2);
					
					if (count ($data) == 6) {
					
						/**
							Campo 0 -> Tipo Doc
							Controlo que se encuentre entre los permitos
						*/
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
						}
					
						/**
							Campo 2 -> Nombre y apellido
							Saco las comas u otros caracteres -> Ver de hacer con una expresión regular
						*/
						$busqueda = array (',');
						$data[2] = trim (str_replace ($busqueda , '' , strtoupper ($data[2])));
							
						/**
							Campo 3 -> Sexo
							Controlo que sea M o F
						*/
						if ($data[3] <> 'F' && $data[3] <> 'M') {
							 $rechazado = 1;
							 $motivo .= ' Campo sexo no es M o F';
						}
						
						/**
							Campo 5 -> Tipo socio
							Controlo que sea T o A
						*/
						if ($data[5] <> 'T' && $data[5] <> 'A') {
							 $data[5] = 'A';
							 $motivo .= ' Campo Tipo afiliado no es T o A';
						}
						
						
						if ($rechazado == 1) {
							$registros_rechazados ++;
							
							file_put_contents (
								'../upload/osp/rec/RECHAZADOS-' . $datos_cabecera[1] . '.txt' 
								, $motivo . " en línea $linea_actual ||" . implode ("||" , $data) . "\r\n" 
								, FILE_APPEND);
							
						} else {
						
							/**
								Intento actualizar registro de persona
							*/
							
							$sql = "
								update osp.osp_" . $datos_cabecera[1] . "
								set $columna_pivot = 'S'
								where 
									tipo_documento 			= '$data[0]'
									and numero_documento 	= $data[1]
									and nombre_apellido 	= '$data[2]'
									and sexo 				= '$data[3]'
									and tipo_afiliado 		= '$data[5]'";
							$res = pg_query ($sql);

							$actualizado = pg_affected_rows ($res);
							
							if (! $actualizado) {
								$campos =  implode ("','" , $data);
								$sql = "
									insert into osp.osp_" . $datos_cabecera[1] . "
										( tipo_documento , numero_documento , nombre_apellido , sexo , codigo_postal , tipo_afiliado , $columna_pivot)
									values ('" . $campos . "' , 'S')";
								$res = pg_query ($sql);
								
								$registros_insertados ++ ;
							} else {
								$registros_actualizados ++ ;
								if ($verificar_repetidos) {
									fwrite ($rep_fh , implode ('||' , $data) . "\r\n");
								}
							}
						}
					} else {
						$registros_rechazados ++ ;
						
						$motivo .= "No cumple con el mínimo de campos requeridos o el delimitador de campos es incorrecto, debe usar --> || <br />";
						file_put_contents ("../upload/osp/rec/RECHAZADOS-" . $datos_cabecera[1] . ".txt" , $motivo . " en línea $linea_actual ||" .  implode ("||" , $data) . "\r\n" , FILE_APPEND);
					}
					unset ($motivo);
					
				} else {
					$rechazado = 1;
					$motivo .= ' codigo_os no corresponde a la provincia que se procesa';
				}
			}

			fclose ($file);
			fclose ($rep_fh);
			
			rename ($ruta_archivo , '../upload/osp/back/' . $datos_cabecera[0] . '-' . date("Ymdhms") . '.txt');
			
			$sql = "
				update sistema.cargas_archivos
				set procesado = 'S'
				where
					id_padron = 6
					and nombre_actual = '$nombre_archivo'
					and procesado = 'N'";
			$res = pg_query ($sql);
			
			echo 
				"Registros insertados : $registros_insertados <br />
				Registros actualizados : $registros_actualizados <br />
				Registros rechazados : $registros_rechazados";
		}
	break;
}
?>