<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "
	select * 
	from sistema.usuarios 
	where id_usuario not in (2)
	order by id_usuario asc";
$res = pg_query ($sql);
?>

<script>
$(document).ready(function(){
	$("#nuevo-usuario").button();
	$(".error").hide();
	
	$("#nuevo-usuario").click(function(){
		$("#alta-usuario")[0].reset();
		$("#div-alta-usuario").dialog({
			title 		: '<span class="ui-icon ui-icon-disk" style="float:left; margin:0px 7px 0px 0px;"></span> Nuevo usuario' ,
			modal		: true ,
			resizable	: false ,
			show		: "fold" ,
			width		: 500 ,
			buttons		: {
				"Aceptar" : function () {
					$(this).dialog("close");
					$.ajax({
						type : "post" ,
						data : $(this).find("form").serialize() + "&acc=ins-usr" ,
						url  : "funciones/funciones_adm.php" ,
						success : function (data) {
							$("#respuesta-ajax").html(data);
							$("#respuesta-ajax").dialog({
								title 		: '<span class="ui-icon ui-icon-disk" style="float:left; margin:0px 7px 0px 0px;"></span> Nuevo usuario' ,
								modal		: true ,
								resizable	: false ,
								show		: "fold" ,
								buttons		: {
									"Aceptar" : function () {
										$(this).dialog("close");
										$("#usuarios").load("modulos/admin/abm_usuarios.php #usuarios");
									}
								}
							});
						}
					});
				} ,
				"Cancelar" : function () {
					$(this).dialog("close");
				}
			}
		});
	});
	
	
	$("#usuarios").on("click" , ".del", function (event) {
		event.preventDefault();
		nombre = $(this).parents("tr").find("td").html();
		
		$("#mensaje-alerta").html('Est&aacute; eliminar el usuario <span style="font-weight: bold">' + nombre + '</span>, est&aacute; seguro?');
		$("#mensaje-alerta").dialog({
			title 		: '<span class="ui-icon ui-icon-alert" style="float:left; margin:0px 7px 0px 0px;"></span> Eliminar usuario' ,
			modal		: true ,
			resizable	: false ,
			show		: "fold" ,
			buttons		: {
				"Aceptar" : function () {
					$(this).dialog("close");
					$.ajax({
						type : "post" ,
						data : "acc=del-usr&nombre=" + nombre ,
						url  : "funciones/funciones_adm.php" ,
						success : function (data) {
							$("#respuesta-ajax").html(data);
							$("#respuesta-ajax").dialog({
								title 		: '<span class="ui-icon ui-icon-alert" style="float:left; margin:0px 7px 0px 0px;"></span> Eliminar usuario' ,
								modal		: true ,
								resizable	: false ,
								show		: "fold" ,
								buttons		: {
									"Aceptar" : function () {
										$(this).dialog("close");
										$("#usuarios").load("modulos/admin/abm_usuarios.php #usuarios");
									}
								}
							});
						}
					});
				} ,
				"Cancelar" : function () {
					$(this).dialog("close");
				}
			}
		}).prev().addClass("ui-state-error");
	});
	
	$("#usuarios").on("click" , ".edit", function (event) {
		$(".error").hide();
		event.preventDefault();
		nombre = $(this).parents("tr").find("td").html();
		
		$.ajax({
			type: 'post' ,
			url : 'funciones/funciones_adm.php' , 
			data: 'acc=autocompletar-usr&usuario=' + nombre ,
			success : function (data) {
				var usuario = JSON.parse(data);

				console.log (usuario);
				
				$("#div-alta-usuario").find(".pass").hide();
				
				$("#nombre-usuario").val(usuario["descripcion"]);
				$("#nombre-ingreso-usuario").val(usuario["usuario"]);
				$("#nombre-ingreso-usuario").attr("readonly","readonly");
				$("#usuario-email").val(usuario["email"]);
				$("#usuario-jurisdiccion").val(usuario["id_entidad"]);
				$("#usuario-grupo").val(usuario["id_menu"]);
				
				$("#div-alta-usuario").dialog({
					title 		: '<span class="ui-icon ui-icon-info" style="float:left; margin:0px 7px 0px 0px;"></span> Editar usuario' ,
					modal		: true ,
					resizable	: false ,
					show		: "fold" ,
					width		: 500 ,
					buttons		: {
						"Aceptar" : function () {
							$(this).dialog("close");
							$.ajax({
								type : 'post' ,
								url  : 'funciones/funciones_adm.php' ,
								data : $(this).find("form").serialize() + "&acc=edit-usr" ,
								success : function (data) {
									$("#respuesta-ajax").html(data);
									$("#respuesta-ajax").dialog({
										title 		: '<span class="ui-icon ui-icon-info" style="float:left; margin:0px 7px 0px 0px;"></span> Nuevo usuario' ,
										modal		: true ,
										resizable	: false ,
										show		: "fold" ,
										width		: 350 ,
										buttons		: {
											"Aceptar" : function() {
												$(this).dialog("close");
												$("#usuarios").load("modulos/admin/abm_usuarios.php #usuarios");
											}
										}
									});
								}
							})
						} ,
						"Editar clave" : function () {
							$(this).dialog("close");
							$("#div-edit-password").dialog({
								title 		: '<span class="ui-icon ui-icon-info" style="float:left; margin:0px 7px 0px 0px;"></span> Editar password' ,
								modal		: true ,
								resizable	: false ,
								show		: "fold" ,
								width		: 350 ,
								buttons		: {
									"Aceptar" : function () {
									
									/**
										Validación de password
									*/
										var pass = $(this).find("form").serializeArray();
										if (pass[0].value.length < 5) {
											$(".error").fadeIn().html("La contraseña es demasiado corta");
										} else {
											$(this).dialog("close");
											$.ajax({
												type : 'post' , 
												url  : 'funciones/funciones_adm.php' ,
												data : $(this).find("form").serialize() + '&acc=edit-pass&usuario=' + nombre , 
												success : function (data) {
													$("#respuesta-ajax").html(data);
													$("#respuesta-ajax").dialog({
														title 		: '<span class="ui-icon ui-icon-info" style="float:left; margin:0px 7px 0px 0px;"></span> Editar password' ,
														modal		: true ,
														resizable	: false ,
														show		: "fold" ,
														width		: 350 ,
														buttons		: {
															"Aceptar" : function () {
																$(this).dialog("close");
															}
														}
													});
												}
											});	
										}
									}
								}
							});
						} , 
						"Cancelar" : function () {
							$(this).dialog("close");
						}
					}
					
				});
			}
		});
	});
	
	$("#ocultar-alta").toggle(function () {
		$(".div-input").slideToggle();
		$(this).html("Mostrar");
	} ,
	function () {
		$(".div-input").slideToggle();
		$(this).html("Ocultar");
	});
});
</script>

<div class="div-input">
	<input id="nuevo-usuario" type="button" value="Nuevo usuario" />
</div>
<span id="ocultar-alta">Ocultar</span>
<hr />
<div id="usuarios">
	<table class="adm-usr">
		<thead>
			<tr>
				<th>Usuario</th>
				<th>Nombre</th>
				<th>Email</th>
				<th>Activo</th>
				<th colspan="2">Acciones</th>
			</tr>
		</thead>
		<tbody>
		<?php
		while ($reg = pg_fetch_assoc ($res)) {
			echo '
				<tr>
					<td>' , $reg['usuario'] , '</td>
					<td>' , $reg['descripcion'] , '</td>
					<td>' , $reg['email'] , '</td>
					<td>' , $reg['activo'] , '</td>
					<td><a href="#" class="edit">Editar<img src="img/edit.png" title="Editar usuario" /></a></td>
					<td><a href="#" class="del">Eliminar<img src="img/delete-item.png" title="Eliminar usuario" /> </a></td>
				</tr>';
		}
		?>
		</tbody>
	</table>
</div>

<div style="display: none;">
	<div id="mensaje-alerta"></div>
	<div id="respuesta-ajax"></div>
	<div id="div-alta-usuario">
		<p class="error"></p>
		<form id="alta-usuario" autocomplete="off">
			<table class="alta-usu">
				<tr>
					<td>Nombre y apellido</td>
					<td><input type="text" name="nombre" id="nombre-usuario" size="50"/></td>
				</tr>
				<tr>
					<td>Usuario</td>
					<td><input type="text" name="usuario" id="nombre-ingreso-usuario" size="50"/></td>
				</tr>
				<tr>
					<td>Email</td>
					<td><input type="text" name="email" id="usuario-email" size="50"/></td>
				</tr>
				<tr>
					<td>Jurisdicci&oacute;n</td>
					<td>
						<select name="juris" id="usuario-jurisdiccion">
							<?php
							$sjuris = '
								select
									e.id_entidad
									, coalesce (p.nombre, a.nombre, s.nombre) as nombre
								from sistema.entidades e
									left join sistema.entidades_administrativas a on e.id_entidad = a.id_entidad_administrativa
									left join sistema.entidades_sanitarias s on e.id_entidad = s.id_entidad_sanitaria
									left join sistema.provincias p on e.id_entidad = p.id_provincia';
							$rjuris = pg_query ($sjuris);
							while ($fjuris = pg_fetch_assoc ($rjuris)) {
								echo '<option value="' . $fjuris['id_entidad'] . '">' . $fjuris['nombre'] . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Grupo</td>
					<td>
						<select name="grupo" id="usuario-grupo">
							<?php
							$gsql = 'select * from sistema.menues';
							$rsql = pg_query ($gsql);
							while ($fsql = pg_fetch_assoc ($rsql)) {
								echo '<option value="' . $fsql['id_menu'] .'">' . $fsql['descripcion'] . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr class="pass">
					<td>Contrase&ntilde;a</td>
					<td><input type="password" name="pass_1" size="50"/></td>
				</tr>
				<tr class="pass">
					<td>Repita Contrase&ntilde;a</td>
					<td><input type="password" name="pass_2" size="50"/></td>
				</tr>
			</table>
		</form>
	</div>
	<div id="div-edit-password">
		<form>
			<table>
				<tr>
					<td>Ingrese nueva contraseña</td>
					<td><input type="password" name="password" /></td>
				</tr>
			</table>
			<p class="error"></p>
		</form>
	</div>
</div>