<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "
	copy (
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ') from osp.osp_01 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ') from osp.osp_02 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_03 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_04 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_05 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_06 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_07 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_08 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_09 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_10 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_11 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_12 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_13 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_14 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_15 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_16 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_17 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_18 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_19 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_20 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_21 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_22 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')	from osp.osp_23 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || case when tipo_afiliado = 'T' then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')  from osp.osp_24 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || lpad (codigo_os :: text , 6 , '0') || case when codigo_parentesco :: int = 0 then 'S' else 'N' end || rpad (nombre_apellido , 30 , ' ')  from osp.osp_26 where  ultimo_aporte :: int >= to_char ( current_timestamp - interval '3 months' , 'YYYYMM') :: int or ultimo_aporte :: int = 0 union all
		select rpad (tipo_documento , 3 , ' ')	|| rpad (numero_documento :: text , 12 , ' ') || codigo_os || 'N' || rpad (nombre_apellido , 30 , ' ') from osp.osp_27
		)
	to
		'/var/www/sirge/export/PUCO_" . date("Y-m") . ".txt'";
$res = pg_query ($sql);

if ($puco = fopen ('../../../export/PUCO_' . date("Y-m") . '.txt' , 'rb')) {
	while (! feof ($puco)) {
		$linea = fgets ($puco);
		file_put_contents ('../../../export/TEST.txt' , str_replace ("\n" , "\r\n" , $linea) , FILE_APPEND);
	}
} else {
	echo "ERROR"; die();
}

echo "PUCO GENERADO";

?>
