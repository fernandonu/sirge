<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
?>
<script>
$(document).ready(function(){
	$("input:submit").button();
	$("#sugerencia").submit(function(){
		$.fancybox.showActivity();
		$.ajax({
			type : 'post' ,
			url  : 'funciones/funciones_usr.php' ,
			data : $(this).serialize() + '&acc=sugerencia' ,
			success : function(data){
				$.fancybox.hideActivity();
				$("#respuesta-ajax").html(data);
				$("#respuesta-ajax").dialog({
					title 		: "Sugerencia enviada" ,
					modal		: true ,
					resizeable	: false ,
					show		: "fold" ,
					buttons		: {
						"Aceptar" : function () {
							$(this).dialog("close");
						}
					}
				});
				$("#sugerencia")[0].reset();
			}
		});
		return false;
	});
});
</script>

<form id="sugerencia">
	<div>Remitente</div>
	<input name="remitente" type="text" value="<?php echo $_SESSION['descripcion']; ?>" readonly="readonly" />
	<div> Mensaje </div>
	<textarea name="texto" cols="45" rows="20"></textarea>
	<br />
	<input type="submit" value="Enviar" />
</form>
<div style="display:none;">
	<div id="respuesta-ajax"></div>
</div>
