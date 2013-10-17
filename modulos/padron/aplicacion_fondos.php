<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
?>
<script>
$(document).ready(function(){
	$(".sub-modulos").tabs({
		ajaxOptions: {
			spinner: "Buscando datos..." ,
			error: function( xhr, status, index, anchor ) {
				$(anchor.hash).html("Se ha producido un error, por favor p&oacute;ngase en contacto con el administrador del sistema.");
			}
		}
	});
});
</script>

<div class="sub-modulos">
	<ul>
		<li><a href="modulos/padron/tab-aplicacion_fondos/carga_archivo.php">Carga archivos</a></li>
		<li><a href="modulos/padron/tab-aplicacion_fondos/subidas.php">Archivos subidos</a></li>
		<li><a href="modulos/padron/tab-aplicacion_fondos/lotes_procesados.php">Lotes procesados</a></li>
		<li><a href="modulos/padron/tab-aplicacion_fondos/impresion_grupal_ddjj.php">Impresi&oacute;n grupal DDJJ</a></li>
	</ul>
</div>
