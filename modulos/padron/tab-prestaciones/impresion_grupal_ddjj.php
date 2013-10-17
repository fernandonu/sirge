<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

?>

<script>
$(document).ready(function(){
	$("input:button").button();
	
	$("input:button").click(function(){
		
		window.location.href = 'funciones/genera_declaracion_jurada_grupal.php?padron=1';
		
	});
});
</script>
<div>
	Esta opción permite imprimir una &uacute;nica declaraci&oacute;n jurada con un resumen de todos los lotes que no se hayan declarado. <br/>
	Los siguientes lotes se encuentran pendientes de informar mediante una DDJJ. <br/>
	Recuerde que esta operaci&oacute;n solo se puede realizar una vez por lote
</div>
<?php

$sql = 
	"select * 
	from sistema.lotes 
	where 
		id_padron = 1
		and lote not in (select lote from sistema.impresiones_ddjj) 
		and id_provincia = '$_SESSION[grupo]'
		and id_estado = 1
	order by lote";
$res = pg_query ($sql);

if (pg_num_rows ($res) == 0) {
	echo "No hay lotes pendientes por declarar <br/>";
} else {
	while ($reg = pg_fetch_assoc ($res)) {
		echo $reg['lote'] , "<br/>";
	}
}

?>

<input type="button" value="Imprimir" />

