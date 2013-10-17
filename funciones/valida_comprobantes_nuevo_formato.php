<?php
session_start();
$nivel = 1;
require 'funciones.php';
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

/**
	Cantidad de campos que deben venir en el archivo de comprobantes
*/
$campos_comprobantes = 12;

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
$archivo = "../upload/comprobantes/" . $file;

/**
	Inicializo variables
*/
$cantidad_rechazados = 0;
$cantidad_insertados = 0;
$linea_actual = 0;

/**
	Veo si existe el archivo con los registros rechazados, si existe lo elimino para generarlo nuevamente.
*/
if (file_exists ("upload/comprobantes/rec/rechazados-" . date("m") . "-C-$provincia.txt")) {
	unlink ("upload/comprobantes/rec/rechazados-" . date("m") . "-C-$provincia.txt");
}

if ($padron = fopen ($archivo , "rb")) {

/**
		Inicio el proceso, con un número de lote
	*/
	$sql_start_lote = "
		insert into sistema.lotes (id_provincia , id_usuario_proceso , inicio , id_padron) 
		values ( '$provincia' , $_SESSION[id_usuario] , LOCALTIMESTAMP , 3 );
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
			if (count ($campos) <> $campos_comprobantes) {
			
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
					Campo 1 => NUMERO COMPROBANTE
				*/
				if (strlen ($campos[1]) == 0) {
					$rechazado = 1 ;
					$motivo = ' Registro sin numero de comprobante' ;
				}
				
				/**
					Campo 2 => TIPO COMPROBANTE
				*/
				if ($campos[2] <> 'FC' && $campos[2] <> 'ND') {
					$rechazado = 1 ;
					$motivo = ' Tipo de comprobante invalido ';
				}
				
				/**
					Campo 3 => FECHA COMPROBANTE
				*/
				if (! validar_fecha_iso8601 ($campos[3])) {
					$rechazado = 1 ;
					$motivo = ' Fecha de comprobante invalida';
				}
				
				/**
					Campo 4 => FECHA RECEPCION
				*/
				if (! validar_fecha_iso8601 ($campos[4])) {
					$rechazado = 1 ;
					$motivo = ' Fecha de recepcion invalida';
				}
				
				/**
					Campo 5 => FECHA NOTIFICACION
				*/
				if (! validar_fecha_iso8601 ($campos[5])) {
					$rechazado = 1 ;
					$motivo = ' Fecha de notificacion invalida';
				}
				
				/**
					Campo 6 => FECHA LIQUIDACION
				*/
				if (! validar_fecha_iso8601 ($campos[6])) {
					$rechazado = 1 ;
					$motivo = ' Fecha de liquidacion invalida';
				}
				
				/**
					Campo 7 => FECHA DEBITO BANCARIO
				*/
				if (strlen (trim ($campos[7])) == 0) {
					$campos[7] = '1900-01-01';
				} else if (! validar_fecha_iso8601 ($campos[7])) {
					$rechazado = 1 ;
					$motivo = ' Fecha de debito invalida ';
				}
				
				/**
					Campo 8 => IMPORTE
				*/
				if (! is_numeric ($campos[8]) || $campos[8] == 0) {
					$rechazado = 1 ;
					$motivo = ' Valor invalido en campo IMPORTE ' ;
				} else {
					$campos[8] = round ($campos[8] , 2);
				}
				
				/**
					Campo 9 => IMPORTE PAGADO
				*/
				if (! is_numeric ($campos[9])) {
					$rechazado = 1 ;
					$motivo = ' Valor invalido en campo IMPORTE PAGADO' ;
				} else {
					$campos[9] = round ($campos[9] , 2);
				}
					
				/**
					Campo 11 => CONCEPTO
				*/
				$campos[11] = htmlentities ($campos[11] , ENT_QUOTES , 'UTF-8');
				
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
					insert into comprobantes.rechazados (id_provincia , motivos , registro_rechazado , lote)
					values ('$provincia' , '" . htmlentities ($motivo) . "' , '" . implode (';' , $campos) . "' , $lote[0])";
				$res = pg_query ($sql);
				unset ($motivo);
				
			} else {
				$sql = "
					insert into comprobantes.c_" . $provincia . "
					values ('" . implode ("','" , $campos) . "', $lote[0])";

				$res = pg_query ($sql);
				
				if (!$res) {
					
					$sql = "
						insert into comprobantes.rechazados (id_provincia , motivos , registro_rechazado , lote)
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
	rename ($archivo , "../upload/comprobantes/back/" . date("YmdHis") . "-" . date("m") . "-C-" . $provincia . ".txt");
	
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
