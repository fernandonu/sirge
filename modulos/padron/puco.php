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
			error: function( xhr, status, index, anchor ) {
				$( anchor.hash ).html("Se ha producido un error, por favor p&oacute;ngase en contacto con el administrador del sistema.");
			}
		}
	});
});
</script>

<div class="sub-modulos">
	<ul>
		<li><a href="modulos/padron/tab-puco/resumen_puco.php">Resumen</a></li>
		<li><a href="modulos/padron/tab-puco/carga_archivo.php">Carga archivos</a></li>
		<li><a href="modulos/padron/tab-puco/subidas.php">Archivos subidos</a></li>
		<li><a href="modulos/padron/tab-puco/consultas.php">Consultas</a></li>
	</ul>
</div>