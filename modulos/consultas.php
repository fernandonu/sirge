<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "
	select * 
	from sistema.areas 
	order by nombre asc";
$res = pg_query ($sql);
if (!$res){
	die ("Error en sql");
}
?>
<script>
$(document).ready(function(){
	$(".sub-modulos").tabs({
		ajaxOptions: {
			error: function( xhr, status, index, anchor ) {
				$(anchor.hash).html("Se ha producido un error, por favor p&oacute;ngase en contacto con el administrador del sistema.");
			}
		}
	});
});
</script>
<div class="sub-modulos">
	<ul>
	<?php
	while ($reg = pg_fetch_assoc ($res)){
		if ($_SESSION['grupo'] <= 24 ) {
			echo ($reg['id_area'] == 8) ? "<li><a href=\"modulos/consultas/consulta_dinamica.php\"> $reg[nombre] </a></li>" : '' ;
			echo ($reg['id_area'] == 9) ? "<li><a href=\"modulos/consultas/consulta_area.php?area=$reg[id_area]\"> $reg[nombre] </a></li>" : '' ;
		} else {
			echo ($reg['id_area'] == 8) ? 
				"<li><a href=\"modulos/consultas/consulta_dinamica.php\"> $reg[nombre] </a></li>" : 
				"<li><a href=\"modulos/consultas/consulta_area.php?area=$reg[id_area]\"> $reg[nombre] </a></li>";
		}
	}
	?>
	</ul>
</div>