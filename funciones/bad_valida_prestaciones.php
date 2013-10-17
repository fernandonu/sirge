<?php
session_start();
$nivel = 1;
require 'funciones.php';
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

/**
	Cantidad de campos que deben venir en el archivo de prestaciones
*/
$campos_prestaciones = 16;

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
		values ( $provincia , $_SESSION[id_usuario] , LOCALTIMESTAMP , 1 );
		select currval ('sistema.lotes_lote_seq') limit 1";
		
	$res_start_lote = pg_query ($sql_start_lote);
	
	$lote = pg_fetch_row ($res_start_lote);
	
	/**
		Guardo la cantidad de registros en el archivo
	*/
	$padron_lineas = count (file ($archivo));
	
	/**
		Genero sentencias sql para ejecutar después
	*/
	$sql = 'insert into prestaciones.p_' . $provincia . 'values';
	$sql_ext = $sql;
	
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
		$linea = fgets ($padron);
		$campos = explode (";" , $linea);
		
		/**
			Si la cantidad de campos en el registro no es igual a la cantidad de campos definida en $campos_prestaciones, rechazo el registro
		*/
		if (count ($campos) <> $campos_prestaciones) {
			$rechazado = 1;
			$motivo .= 'No cumple con la cantidad de campos requeridos';
		} else {
		
			/**
				Campo 0 => CUIE
				Comprobar que sea no sea vacío
			*/
			if (strlen ($campos[0]) <> 6) {
				$rechazado = 1 ;
				$motivo .= 'CUIE inválido';
			}
			
			/**
				Campo 3 => Código Prestación
				Quito el espacio que separa el código de prestación, en caso de que exista
			*/
			$campos[3] = str_replace (" " , "" , $campos[3]);
			
			if (strlen ($campos[3]) > 9 || strlen ($campos[3]) == 0 ) {
				$rechazado = 1;
				$motivo .= 'Código de prestación inválida';
			}
			
			/**
				Campo 4 => Sub Código Prestación
				Si el subcódigo de prestación es 0, lo dejo vacío
			*/
			if ($campos[4] === '0' || strlen($campos[4]) == 0){
				$campos[4] = ' ';
			}
			
			/**
				Campo 6 => Fecha Prestación
				Valido la fecha de prestación
				Formato: AAAA-MM-DD
				Si no se puede validar la fecha, el registro se rechazadoaza
			*/
			$validacion_fecha = validar_fecha_v2 ($campos[5]);
			if ($validacion_fecha == 0) {
				$rechazado = 1;
				$motivo .= 'Fecha de comprobante inválida';
			} else if ($validacion_fecha == 2) {
				$campos[5] = '1900-01-01';
			} else {
				$campos[5] = $validacion_fecha;
			}
			
			/**
				Campo 6 => Clave Beneficiario
				Compruebo que no lo envíen vacío.
			*/
			if (strlen ($campos[6]) == 0) {
				$rechazado = 1 ;
			} else {
				if (! (is_numeric ($campos[6]))) {
					$rechazado = 1;
					$motivo .= 'Clave de beneficiario inválida';
				}
			}
									
			/**
				Campo 9 => DNI
				Si el campo DNI está vacío, se inserta un 0
				Si no está vacío, quita el caracter '.' en caso de que exista
				También comprueba si es numérico
			*/
			if (strlen($campos[9] == 0)) {
				$campos[9] = 0;
			} else {
				$campos[9] = str_replace (".","",$campos[9]);
				if (! (is_numeric ($campos[9]))) {
					$rechazado = 1;
					$motivo .= 'DNI inválido';
				}
			}
			
			/**
				Campo 10 => Precio Unitario
				Reemplazar el caracter ',' por '.' en caso de que exista
			*/
			if (strlen ($campos[10]) == 0){
				$campos[10] = 0;
			} else {
				$campos[10] = str_replace (",",".",$campos[10]);
			}
			
			/**
				Campo 13 => Peso
				Redondeo el valor a 2 decimales
			*/
			if (strlen ($campos[13]) < 1) {
				$campos[13] == '0';
			} else {
				$pos = strpos ($campos[13],".");
				if ($pos) {
					$pos += 5;
					$campos[13] = substr ($campos[13] , 0, $pos);
				}
			}
			
			/**
				Campo 14 => Tensión arterial
			*/
			if (strlen ($campos[14]) > 7) {
				$rechazado = 1 ;
			}
			
			/**
				Campo 15 => Fecha Liquidación
				En caso de no tener fecha de liquidación, se inserta 1900-01-01
				Formato: AAAA-MM-DD
			*/
			$validacion_fecha = validar_fecha_v2 ($campos[15]);
			if ($validacion_fecha == 0) {
				$rechazado = 1;
				$motivo .= 'Fecha de liquidación inválida';
			} else if ($validacion_fecha == 2) {
				$campos[15] = '1900-01-01';
			} else {
				$campos[15] = $validacion_fecha;
			}
		}
		
		/**
			Si el registro no pasó la validación, se rechaza
		*/
		if ($rechazado) {
		
			/**
				Incremento la cantidad de rechazos
			*/
			$cantidad_rechazados += 1;
			
			/**
				Guardo el registro rechazado con su motivo para futuras consultas
			*/
			$sql = "
				insert into
					prestaciones.rechazados (id_provincia , motivos , registro_rechazado , lote)
				values
					('$provincia' , '" . htmlentities ($motivo) . "' , '" . implode (';' , $campos) . "' , $lote[0])";
			$res = pg_query ($sql);
			unset ($motivo);

		} else {
			if (($linea_actual % 10000) <> 0) {
				$sql_ext .= "('" . implode ("','" , $campos) . "'),";
			} else {
				$sql_ext = trim ($sql_ext , ',');
				echo $sql_ext; die();
				$res = pg_query ($sql_ext);
				if (!$res) {
					echo $sql_ext , '<br />';
					die();
				}
				$sql_ext = $sql;
			}
		}
	 }

	fclose ($padron);
	rename ($archivo , "../upload/prestaciones/back/" . date("YmdHis") . "-" . date("m") . "-P-" . $provincia . ".txt");
	 
	echo "
		Total de registros 		: $padron_lineas <br />
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