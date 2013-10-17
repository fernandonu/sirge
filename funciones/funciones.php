<?php
	/**
		Retorna 0 - Fecha inválida
		Retorna Fecha - Fecha válida
		Retorna 2 - Fecha vacía 
	*/
	function validar_fecha_v2 ($string) {
		if (strlen ($string) == 0 || $string == 0) {
			return 2;
		} else {
			$existe_barra = strpos ($string , "/");
			if ($existe_barra) {
				$fecha = str_replace ("/" , "-" , $string);
				$array_fecha = explode ("-" , $fecha);
			} else {
				$array_fecha = explode ("-" , $string);
			}
			
			$campos_fecha = count ($array_fecha);
			
			if ($campos_fecha <> 3) {
				return 0;
			} else {
				for ($i = 0 ; $i < $campos_fecha ; $i ++) {
					if ($i == 2) {
						$crlf = array("\r\n", "\n", "\r");
						$array_fecha[2] = str_replace ($crlf , "", $array_fecha[2]);
					}
					
					if (! is_numeric ($array_fecha[$i])) {
						return 0;
					}
				}
				
				$formato_fecha = strlen ($array_fecha[0]) == 4 ? 'AMD' : 'DMA';
				
				switch ($formato_fecha) {
					case 'AMD':
						if (checkdate ($array_fecha[1] , $array_fecha[2] , $array_fecha[0])) {
							return implode ("-" , $array_fecha);
						} else {
							return 0;
						}
						break;
					case 'DMA':
						if (checkdate ($array_fecha[1] , $array_fecha[0] , $array_fecha[2])) {
							return $array_fecha[2] . '-' . $array_fecha[1] . '-' . $array_fecha[0];
						} else {
							echo "4";
							return 0;
						}
						break;
					default : return 0;
				}
				
			}
		}
	}

	function validar_fecha_iso8601 ($fecha) {
		/**
			Retornos:
				0 = Fecha inválida
				1 = Fecha válida
		*/
		
		if (strlen ($fecha) == 0) {
			return 0;
		} else {
			$arr = explode ('-' , $fecha);
			if (count ($arr) <> 3) {
				return 0;
			} else {
				if (checkdate ($arr[1] , $arr[2] , $arr[0]) && $arr[0] >= 2004) {
					if (strtotime("now") < strtotime ($fecha)) {
						return 0;
					} else {
						return 1;
					}
				} else {
					return 0;
				}
			}
		}
	}
	
	function truncar_tabla ($prov,$padron){
	/**
		Caso 1 => PRESTACIONES
		Caso 2 => COMPROBANTES
		Caso 3 => APLICACION FONDOS
		Caso 4 => NOMENCLADORES
	*/
		switch ($padron){
			case 1:
				$sql = 'TRUNCATE TABLE prestaciones.p_' . $prov;
				$reg = pg_query ($sql);
				if (!$reg){
					die ("Error al truncar tabla PRESTACIONES");
				}
				break;
			case 2:
				$sql = 'TRUNCATE TABLE comprobantes.c_' . $prov;
				$reg = pg_query ($sql);
				if (!$reg){
					die ("Error al truncar tabla COMPROBANTES");
				}
				break;
			case 3:
				$sql = 'TRUNCATE TABLE aplicacion_fondos.f_' . $prov;
				$reg = pg_query ($sql);
				if (!$reg){
					die ("Error al truncar tabla APLICACION FONDOS");
				}
				break;
			case 4:
				$sql = 'TRUNCATE TABLE nomencladores.n_' . $prov;
				$reg = pg_query ($sql);
				if (!$reg){
					die ("Error al truncar tabla NOMENCLADORES");
				}
				break;
			default:
				die ("Error");
		}
	}
?>
