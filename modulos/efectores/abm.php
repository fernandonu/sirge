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
		<li><a href="modulos/efectores/abm/alta.php">Alta efector</a></li>
		<li><a href="modulos/efectores/abm/modificacion.php">Modificaci&oacute;n efector</a></li>
		<li><a href="modulos/efectores/abm/baja.php">Baja efector</a></li>
	</ul>
</div>
