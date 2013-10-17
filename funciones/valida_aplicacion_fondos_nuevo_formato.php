<?php
session_start();
$nivel = 1;
require 'funciones.php';
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

/**
	Cantidad de campos que deben venir en el archivo de aplicacion de fondos
*/
$campos_fondos = 8;

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
$archivo = "../upload/fondos/" . $file;

/**
	Inicializo variables
*/
$cantidad_rechazados = 0;
$cantidad_insertados = 0;
$linea_actual = 0;

/**
	Veo si existe el archivo con los registros rechazados, si existe lo elimino para generarlo nuevamente.
*/
if (file_exists ("upload/fondos/rec/rechazados-" . date("m") . "-F-$provincia.txt")) {
	unlink ("upload/fondos/rec/rechazados-" . date("m") . "-F-$provincia.txt");
}

if ($padron = fopen ($archivo , "rb")) {

/**
		Inicio el proceso, con un número de lote
	*/
	$sql_start_lote = "
		insert into sistema.lotes (id_provincia , id_usuario_proceso , inicio , id_padron) 
		values ( '$provincia' , $_SESSION[id_usuario] , LOCALTIMESTAMP , 2 );
		select currval ('sistema.lotes_lote_seq') limit 1";
	
	$res_start_lote = pg_query ($sql_start_lote);
	
	$lote = pg_fetch_row ($res_start_lote);
	
	/**
		Guardo la cantidad de registros en el archivo
	*/
	$padron_lineas = count (file ($archivo));
	
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
			
			/**
				Si la cantidad de campos en el registro no es igual a la cantidad de campos definida en $campos_prestaciones, rechazo el registro
			*/
			if (count ($campos) <> $campos_fondos) {
			
				$rechazado = 1;
				$motivo .= 'No cumple con los campos minimos requeridos';
				
			} else {
				
				/**
					Campo 0 => EFECTOR
				*/
				if (strlen ($campos[0]) <> 6) {
					$rechazado = 1 ;
					$motivo = 'Efector invalido ';
				}
				
				/**
					Campo 1 => FECHA GASTO
				*/
				if (! validar_fecha_iso8601 ($campos[1])) {
					$rechazado = 1 ;
					$motivo = ' Fecha de gasto invalida';
				}
				
				/**
					Campo 2 => PERIODO
				*/
				$fechas = explode ("-" , $campos[2]);
				
				if (strlen ($fechas[0]) <> 4 || $fechas[0] < 2004 || $fechas[0] > date('Y')) {
					$rechazado = 1 ;
					$motivo = 'Año de periodo fuera de rango permitido';
				}
				
				if (strlen ($fechas[1]) > 2 || (int)$fechas[1] < 1 || (int)$fechas[1] > 12) {
					$rechazado = 1 ;
					$motivo = 'Mes de periodo fuera de rango permitido';
				}
				
				/**
					Campo 3 => NUMERO COMPROBANTE GASTO
				*/
				$campos[3] = htmlentities (pg_escape_string ($campos[3]) , ENT_QUOTES , 'UTF-8');
				
				/**
					Campo 4 => CODIGO GASTO
				*/
				$codigos = explode ("." , $campos[4]);
				
				if (count ($codigos) <> 2) {
					$rechazado = 1 ;
					$motivo = 'Codigo de gasto mal generado';
				}
				
				/**
					Campo 5 => EFECTOR CESION
				*/
				if ($codigos[0] == 7 && strlen ($campos[5]) <> 6) {
					$rechazado = 1 ;
					$motivo = "Efector cesión incorrecto";
				}
				
				/**
					Campo 6 => MONTO
				*/
				if (! is_numeric ($campos[6]) || $campos[6] == 0) {
					$rechazado = 1 ;
					$motivo = ' Valor invalido en campo IMPORTE ' ;
				} else {
					$campos[6] = round ($campos[6] , 2);
				}
				
				/**
					Campo 7 => CONCEPTO
				*/
				$campos[7] = htmlentities (pg_escape_string ($campos[7]) , ENT_QUOTES , 'UTF-8');
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
					insert into aplicacion_fondos.rechazados (id_provincia , motivos , registro_rechazado , lote)
					values ('$provincia' , '" . htmlentities ($motivo) . "' , '" . implode (';' , $campos) . "' , $lote[0])";
				$res = pg_query ($sql);
				unset ($motivo);
				
			} else {
				
				$fondos = array (
					$campos[0]
					, $campos[1]
					, $campos[2]
					, $campos[3]
					, $codigos[0]
					, $codigos[1]
					, $campos[5]
					, $campos[6]
					, $campos[7]
				);
				
				$sql = "
					insert into aplicacion_fondos.a_" . $provincia . "
					values ('" . implode ("','" , $fondos) . "', $lote[0])";
					
				$res = pg_query ($sql);
				
				if (!$res) {
					
					$sql = "
						insert into aplicacion_fondos.rechazados (id_provincia , motivos , registro_rechazado , lote)
						values ('$provincia' , '" . pg_last_error() . "' , '" . implode (';' , $campos) . "' , $lote[0])";
					$res = pg_query ($sql);
					$cantidad_rechazados ++;
					
				} else {
					$cantidad_insertados ++;
				}
			}
		}
	}
	
	fclose ($padron);
	rename ($archivo , "../upload/fondos/back/" . date("YmdHis") . "-" . date("m") . "-F-" . $provincia . ".txt");
	
	echo "
		Total de registros 		: " , $padron_lineas , " <br />
		Registros rechazados 	: $cantidad_rechazados <br />
		Registros insertados 	: $cantidad_insertados <br />
		Lote de transacci&oacute;n 	: $lote[0]";
		
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
	die ("Error al abrir el archivo" . pg_last_error());
}
?>
