<?php
session_start();
$nivel = 1;
require 'funciones.php';
require '../seguridad.php';
require '../sistema/benchmark.php';
require $ruta.'sistema/conectar_postgresql.php';

/**
	Cantidad de campos que deben venir en el archivo de prestaciones
*/
$campos_prestaciones = 21;

/**
	Quito los espacios en blanco que pueden llegar a venir en el nombre del archivo
*/
$file = trim ($_POST['file']);

/**
	Guardo el id_provincia del nombre del archivo
*/
$provincia = substr ($file , 5, 2);

/**
	Genero la ruta del archivo
*/
$archivo = "../upload/prestaciones/" . $file;


/**
	Inicializo variables
*/
$cantidad_rechazados = 0;
$cantidad_insertados = 0;
$linea_actual = 0;

$inmunizaciones = array ('MPU23','MEM07','NPE42');

/**
	Veo si existe el archivo con los registros rechazados, si existe lo elimino para generarlo nuevamente.
*/
if (file_exists ("upload/prestaciones/rec/rechazados-" . date("m") . "-P-$provincia.txt")) {
	unlink ("upload/prestaciones/rec/rechazados-" . date("m") . "-P-$provincia.txt");
}

if ($padron = fopen ($archivo , "rb")) {
	
	/**
		Inicio el proceso, con un número de lote
	*/
	$sql_start_lote = "
		insert into sistema.lotes (id_provincia , id_usuario_proceso , inicio , id_padron) 
		values ( '$provincia' , $_SESSION[id_usuario] , LOCALTIMESTAMP , 1 );
		select currval ('sistema.lotes_lote_seq') limit 1";
		
	$res_start_lote = pg_query ($sql_start_lote);
	
	$lote = pg_fetch_row ($res_start_lote);
	
	/**
		Guardo la cantidad de registros en el archivo
	*/
	$padron_lineas = count (file ($archivo));
	
	time_start();
	
	while (!feof ($padron)) {
		
		/**
			Inicializo variables
		*/
		$motivo = '';
		$rechazado = 0;
		$linea_actual += 1;
		
		/**
			Levanto una línea del archivo y la separo en campos por puntos y comas
		*/
		$linea = trim (fgets ($padron) , "\r\n");
		
		if ($linea_actual > 1) {
		
			$campos = explode (";" , $linea);
			
			//print_r ($campos); echo count ($campos); die();
			
			/**
				Si la cantidad de campos en el registro no es igual a la cantidad de campos definida en $campos_prestaciones, rechazo el registro
			*/
			if (count ($campos) <> $campos_prestaciones) {
				$rechazado = 1;
				$motivo .= 'No cumple con los campos minimos requeridos';
			} else {
			
				/**
					Campo 0 => OPERACION
				*/
				if ($campos[0] <> 'A' && $campos[0] <> 'M') {
					$rechazado = 1;
					$motivo = 'Valor invalido en campo OPERACION' ;
				}
				
				/**
					Campo 1 => ESTADO
				*/
				if ($campos[1] <> 'L' && $campos[1] <> 'D') {
					$rechazado = 1 ;
					$motivo = ' Valor invalido en campo ESTADO';
				}
				
				/**
					Campo 2 => NUMERO COMPROBANTE
				*/
				if (strlen ($campos[2]) == 0) {
					$rechazado = 1 ;
					$motivo = ' Registro sin numero de comprobante' ;
				}
				
				/**
					Campo 3 => Código Prestación
					CAMBIAR POR EXPRESION REGULAR
				*/
				$campos[3] = strtoupper (str_replace (' ' , '' , $campos[3]));
				
				if (strlen ($campos[3]) > 11) {
					$rechazado = 1;
					$motivo .= 'Prestacion invalida';
				} else if ( in_array ($campos[3] , $inmunizaciones) && $campos[6] >= '2013-01-01') {
					$rechazado = 1;
					$motivo .= 'El codigo ' .  $campos[3] . ' ya no es valido';
				}
				
				/**
					Campo 4 => Sub Código Prestación
				
				if (strlen ($campos[4]) > 0 && substr ($campos[3],0,3) <> 'LMI') {
					$rechazado = 1 ;
					$motivo = ' Los subcodigos solo son permitidos para las practicas de tipo LMIxx';
				}
				*/
				
				
				/**
					Campo 5 => Precio unitario
				*/
				if (! is_numeric ($campos[5]) || strlen ($campos[5]) == 0) {
					$rechazado = 1 ;
					$motivo = ' Precio unitario invalido';
				} else {
					$campos[5] = round ($campos[5] , 2);
				}
				
				/**
					Campo 6 => Fecha Prestación
				*/
				if (! validar_fecha_iso8601 ($campos[6]) || is_null ($campos[6])) {
					$rechazado = 1 ;
					$motivo = ' Fecha de prestacion invalida';
				}
				
				/**
					Campo 7 => Clave Beneficiario
				*/
				if (strlen ($campos[7]) <> 16 || $campos[7] == 0 || ! is_numeric ($campos[7])) {
					$rechazado = 1 ;
					$motivo = ' Clave de beneficiario incorrecta';
				} else if ($campos[7] == 9999999999999999 &&  substr ($campos[3],0,3) <> 'CMI' && substr ($campos[3],0,3) <> 'RCM' && substr ($campos[3],0,3) <> 'TAT' && substr ($campos[3],0,6) <> 'ROX001' && substr ($campos[3],0,6) <> 'ROX002') {
					$rechazado = 1 ;
					$motivo = ' El valor 9999999999999999 para la clave de beneficiario solo es valida para practicas de comunidad';
				}
				
				/**
					Campo 8 => Tipo de documento
					Validar contra la BDD
				*/
				
				/**
					Campo 9 => Clase de documento
					Valida contra la BDD (creo)
				*/
				
				/**
					Campo 10 => DNI
					Si el campo DNI está vacío, se inserta un 0
					Si no está vacío, quita el caracter '.' en caso de que exista
					También comprueba si es numérico
				*/
				if (strlen ($campos[10] == 0)) {
					$campos[10] = 0;
				} else {
					
					$campos[10] = trim ($campos[10]);
					
					if (! is_numeric ($campos[10])) {
						$rechazado = 1;
						$motivo .= 'DNI invalido';
					}
				}
				
				/**
					Campo 19 => ORDEN
				*/
				if (! is_numeric ($campos[19]) || strlen ($campos[19]) == 0 || $campos[19] == 0 ) {
					$rechazado = 1 ;
					$motivo = ' Campo ORDEN invalido ';
				}
				
				/**
					Campo 20 => CUIE
				*/
				if (strlen ($campos[20]) <> 6) {
					$rechazado = 1 ;
					$motivo = 'CUIE invalido';
				}
				 
			}
			
			/**
				Si el registro no pasó la validación, se rechaza
			*/
			if ($rechazado) {
				
				/**
					Incremento la cantidad de rechazos
				*/
				$cantidad_rechazados ++;
				
				$sql = "
					insert into	prestaciones.rechazados (id_provincia , motivos , registro_rechazado , lote)
					values ('$provincia' , '" . htmlentities ($motivo , ENT_HTML5 , 'ISO-8859-1') . "' , '" . implode (';' , $campos) . "' , $lote[0])";
				
				$res = pg_query ($sql);
				unset ($motivo);

			} else {
				
				switch ($campos[0]) {
					
					case 'A' :
						
						//echo '<pre>' , print_r ($campos) , '</pre><br />' ;
						
						/**
							Armo el array PRESTACION
						*/
						$prestacion = array (
							$campos[1]
							, strtoupper ($campos[20])
							, $campos[2]
							, 'FC'
							, $campos[3]
							, $campos[4]
							, $campos[5]
							, $campos[6]
							, $campos[7]
							, $campos[8]
							, $campos[9]
							, $campos[10]
							, $campos[19]
							, $lote[0]
						);
						
						//echo '<pre>' , print_r ($prestacion) , '</pre><br />' ;
						
						/**
							Armo el array de DATOS REPORTABLES
						*/
						$datos_reportables = array (
							$campos[3]
							, $campos[4]
							, $campos[6]
							, $campos[7]
							, $campos[19]
							, $campos[11]
							, $campos[12]
							, $campos[13]
							, $campos[14]
							, $campos[15]
							, $campos[16]
							, $campos[17]
							, $campos[18]
						);
						//echo '<pre>' , print_r ($datos_reportables) , '</pre><br />' ;
					
						$sql = "
							insert into prestaciones.p_" . $provincia . " 
							values ('" . implode ("','" , $prestacion) . "')";
							//echo $sql; die();
							$res = pg_query ($sql);
						break;
					
					case 'M' :
						$sql_existe = "
							select *
							from prestaciones.p_" . $provincia . "
							where
								numero_comprobante = '" . $campos[2] . "'
								and tipo_comprobante = 'FC'
								and codigo_prestacion = '" . $campos[3] . "'
								and subcodigo_prestacion = '" . $campos[4] . "'
								and fecha_prestacion = '" . $campos[6] . "'
								and clave_beneficiario = '" . $campos[7] . "'
								and orden = " . $campos[19];
						$res_existe = pg_query ($sql_existe);
						
						if (pg_num_rows ($res_existe) == 1) {
							$sql = "
								update prestaciones.p_" . $provincia . "
								set estado = '" . $campos[1] . "'
								where
									numero_comprobante = '" . $campos[2] . "'
									and tipo_comprobante = 'FC'
									and codigo_prestacion = '" . $campos[3] . "'
									and subcodigo_prestacion = '" . $campos[4] . "'
									and fecha_prestacion = '" . $campos[6] . "'
									and clave_beneficiario = '" . $campos[7] . "'
									and orden = " . $campos[19];
									
							$res = pg_query ($sql);
						} else {
							$rechazado = 1;
							$motivo = 'Se debe informar el registro original antes de realizar una modificacion o informar el alta de un debito directamente';
						}
						break;
					default : die ("ERROR EN ALGUN LADO LPM!");
				}
				
				
				
				if (!$res || $rechazado == 1) {
					
					if ($rechazado == 1) {
						$sql = "
						insert into prestaciones.rechazados (id_provincia , motivos , registro_rechazado , lote)
						values ('$provincia' , '" . $motivo . "' , '" . implode (';' , $campos) . "' , $lote[0])";	
					} else {
						$sql = "
						insert into prestaciones.rechazados (id_provincia , motivos , registro_rechazado , lote)
						values ('$provincia' , '" . pg_last_error() . "' , '" . implode (';' , $campos) . "' , $lote[0])";
					}
					$res = pg_query ($sql);
					$cantidad_rechazados ++;
				} else {
					$cantidad_insertados ++;
				}
			}
		}
	}

	fclose ($padron);
	rename ($archivo , "../upload/prestaciones/back/" . date("YmdHis") . "-" . date("m") . "-P-" . $provincia . ".txt");
	 
	echo "
		Total de registros 		: " , $padron_lineas , " <br />
		Registros rechazados 	: $cantidad_rechazados <br />
		Registros insertados 	: $cantidad_insertados <br />
		Lote de transacci&oacute;n 	: $lote[0] <br />
		Tiempo total: " . round ( time_end() , 4 );
		
	$sql_update_lote = "
		update sistema.lotes 
		set registros_insertados = $cantidad_insertados , registros_rechazados = $cantidad_rechazados , fin = LOCALTIMESTAMP
		where lote = $lote[0]";
	$res_update_lote = pg_query ($sql_update_lote);
	
	$sql_update_carga = "
		update sistema.cargas_archivos
		set procesado = 'S' , fecha_proceso = LOCALTIMESTAMP , lote = $lote[0]
		where nombre_actual = '$file'";
	$res_update_carga = pg_query ($sql_update_carga);
		
} else {
	die ("Error al abrir el archivo");
}
