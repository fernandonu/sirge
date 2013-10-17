<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
$sql = "SELECT * FROM sistema.usuarios u WHERE u.id_usuario = $_SESSION[id_usuario]";
$res = pg_query ($sql);
if (pg_num_rows ($res) != 1) {
	die("Error en id de usuario");
} else {
	$reg = pg_fetch_assoc ($res);
}
?>
<style>

	
</style>
<script>
$(document).ready(function(){
	
	$("#perfil-usuario").on("click" , "#edit-email" , function(event) {
		event.preventDefault();
		$(".error").hide();
		
		$("#mail_2").bind("paste",function(){
			alert ("No est\u00e1 permitido pegar datos");
			return false;
		});
		
		$("#cambia-email").dialog({
			title 		: "Editar e-mail" ,
			modal 		: true ,
			resizable	: false ,
			show		: "fold" ,
			buttons 	: {
				"Aceptar" : function () {
					var mail_1 = $("#mail_1").val();
					var mail_2 = $("#mail_2").val();
					if (mail_1 == mail_2 && mail_1.length > 1 && mail_2.length > 1) {
						$(this).dialog("close");
						$.fancybox.showActivity();
						$.ajax({
							type 	: 'post' ,
							data 	: $(this).find("form").serialize() + '&acc=email' ,
							url  	: 'funciones/funciones_usr.php' ,
							success : function (data){
								$.fancybox.hideActivity();
								$("#respuesta-ajax").html(data);
								$("#respuesta-ajax").dialog({
									title 		: "Editar e-mail" ,
									modal		: true ,
									resizeable	: false ,
									show		: "fold" ,
									buttons		: {
										"Aceptar" : function () {
											$("#perfil-usuario").load("modulos/datos_usuario.php #perfil-usuario");
											$(this).dialog("close");
										}
									}
								});
							}
						});
					} else {
						$("p").html("Los datos no son v&aacute;lidos").fadeIn();
					}
				},
				"Cancelar" : function (){
					$(this).dialog("close");
				}
			}
		});
	});
	
	$("#perfil-usuario").on("click" , "#edit-pass" , function (event) {
		event.preventDefault();
		$(".error").hide();
		
		$("#cambia-pass").dialog({
			title 		: "Cambiar contrase&ntilde;a" ,
			modal 		: true ,
			resizable	: false ,
			show		: "fold" ,
			buttons		: {
				"Aceptar" : function () {
					var pass_1 = $("#pass_1").val();
					var pass_2 = $("#pass_2").val();
					var pass_3 = $("#pass_3").val();
					
					if (pass_2 == pass_3 && pass_1.length > 5) {
						if (pass_2.length > 5) {
						$(this).dialog("close");
							$.ajax({
								type : 'post' ,
								data : $(this).find("form").serialize() + '&acc=password',
								url  : 'funciones/funciones_usr.php' ,
								success : function (data) {
									$("#respuesta-ajax").html(data);
									$("#respuesta-ajax").dialog({
										title 		: "Cambiar contrase&ntilde;a" ,
										modal		: true ,
										resizeable	: false ,
										show		: "fold" ,
										buttons		: {
											"Aceptar" : function () {
												$(this).dialog("close");
											}
										}
									})
								}
							});
						} else {
							$("p").html("La contrase&ntilde;a debe ser de 6 caracteres como m&iacute;nimo").fadeIn();
						}
					} else {
						$("p").html("Los datos ingresados son incorrectos").fadeIn();
					}
				} ,
				"Cancelar": function () {
					$(this).dialog("close");
				}
			}
		});
	});
	
	
	
});
</script>
<div id="perfil-usuario">
	<table class="datos-usuario">
		<tr>
			<td> Nombre </td>
			<td colspan="2"><?php echo $reg['descripcion']; ?></td>
		</tr>
		<tr>
			<td> Usuario </td>
			<td colspan="2"><?php echo $reg['usuario']; ?></td>
		</tr>
		<tr>
			<td> Email </td>
			<td> <?php echo $reg['email']; ?> </td>
			<td> <a id="edit-email" href="#"> Editar <img src="img/edit.png" /> </a> </td>
		</tr>
		<tr>
			<td> Contrase&ntilde;a </td>
			<td> ****** </td>
			<td> <a id="edit-pass" href="#"> Editar <img src="img/edit.png" /> </a> </td>
		</tr>
		<!--<tr>
			<td>Foto de perfil</td>
			<td><img style="height: 100px"src=""/></td>
		</tr>
		-->
	</table>
</div>

<div style="display: none;">
	<div id="cambia-email">
		<p class="error"></p>
		<form autocomplete="off">
			<table class="cambia-email">
				<tr>
					<td> Ingrese su nuevo Email </td>
					<td> <input type="text" id="mail_1" name="mail_1" /> </td>
				</tr>
				<tr>
					<td> Repita su nuevo Email </td>
					<td> <input type="text" id="mail_2" name="mail_2" /> </td>
				</tr>
			</table>
		</form>
	</div>
	<div id="cambia-pass">
		<p class="error"></p>
		<form id="cambia-pass">
			<table class="cambia-pass">
				<tr>
					<td> Contrase&ntilde;a actual </td>
					<td> <input type="password" id="pass_1" name="pass_1" /> </td>
				</tr>
				<tr>
					<td> Contrase&ntilde;a nueva </td>
					<td> <input type="password" id="pass_2" name="pass_2" /> </td>
				</tr>
				<tr>
					<td> Repita contrase&ntilde;a </td>
					<td> <input type="password" id="pass_3" name="pass_3" /> </td>
				</tr>
			</table>
		</form>
	</div>
	<div id="respuesta-ajax"></div>
</div>
