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

<input type="button" value="Imprimir" />

