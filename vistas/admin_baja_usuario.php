<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
$usu_b = $_POST['usuario'];

echo "
<p class=\"baja-usr-adm\">
	Ud. est&aacute; por dar de baja al usuario <span>$usu_b</span>
	<br />
	Confirma esta operaci&oacute;n? 
	<br />
	<br />
	<input type=\"button\" value=\"Si\" id=\"yes\" />
	<input type=\"button\" value=\"No\" id=\"nou\" />
	<input type=\"hidden\" value=\"$usu_b\" />
</p>";
?>
<script>
$(document).ready(function(){
	$("input:button").button();
	
	$("#yes").click(function(){
		$.ajax({
			type : 'POST',
			url  : 'funciones/baja_usuario_admin.php',
			data : 'usuario=' + $('input[type="hidden"]').val(),
			success : function (data) {
				$.fancybox(data);
			}
		});
		return false;
	});
	
	$("#nou").click(function(){
		$.fancybox.close();
	});
});
</script>
