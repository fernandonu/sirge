<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

if (isset ($_POST['acc'])) {
	$sql = "select * from sistema.areas where id_area = $_POST[area]";
	$res = pg_query ($sql);
	die (json_encode (pg_fetch_assoc($res)));
}


$sql = "select * from sistema.areas order by id_area";
$res = pg_query ($sql);
?>
<script>
$(document).ready(function(){
	$("input:button").button();
	
	$("#areas").on("click" , ".edit" , function (event) {
		event.preventDefault();
		
		var id = $(this).parents("tr").find("td").attr("id");
		
		$.ajax({
			type : 'post' ,
			url  : 'modulos/admin/abm_areas.php' , 
			data : 'acc=editar&area=' + id ,
			success : function (data) {
				var area = JSON.parse (data);
				
				$("#nom-area").val(area["nombre"]);
				$("#obs-area").val(area["observaciones"]);
			}
		});
		
		$("#editar-areas").dialog({
			title 		: '<span class="ui-icon ui-icon-info" style="float:left; margin:0px 7px 0px 0px;"></span> Editar area' ,
			modal		: true ,
			resizable	: false ,
			show		: "fold" ,
			buttons		: {
				"Aceptar" : function () {
					$(this).dialog("close");
					$.ajax({
						type : 'post' ,
						data : $(this).find("form").serialize() + '&acc=edit-area&id=' + id ,
						url  : 'funciones/funciones_adm.php' ,
						success : function (data) {
							$("#respuesta-ajax").html(data);
							$("#respuesta-ajax").dialog({
								title 		: '<span class="ui-icon ui-icon-circle-check" style="float:left; margin:0px 7px 0px 0px;"></span> Editar area' ,
								modal		: true ,
								resizable	: false ,
								show		: "fold" ,
								buttons		: {
									"Aceptar" : function () {
										$(this).dialog("close");
										$("#areas").load("modulos/admin/abm_areas.php #areas");
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
	
	$("#areas").on("click" , ".del" , function (event) {
		event.preventDefault();
		var nombre = $(this).parents("tr").find("td").html();
		var id = $(this).parents("tr").find("td").attr("id");
		
		$("#mensaje-alerta").html('Est&aacute; por eliminar el &aacute;rea <span style="font-weight: bold">' + nombre + '</span>, est&aacute; seguro?');
		$("#mensaje-alerta").dialog({
			title 		: '<span class="ui-icon ui-icon-alert" style="float:left; margin:0px 7px 0px 0px;"></span> Eliminar &aacute;rea' ,
			modal		: true ,
			resizable	: false ,
			show		: "fold" ,
			buttons		: {
				"Aceptar" : function () {
					$(this).dialog("close");
					$.ajax({
						type : 'post' ,
						data : 'acc=del-area&id=' + id ,
						url  : 'funciones/funciones_adm.php' ,
						success : function (data) {
							$("#respuesta-ajax").html(data);
							$("#respuesta-ajax").dialog({
								title 		: '<span class="ui-icon ui-icon-alert" style="float:left; margin:0px 7px 0px 0px;"></span> Eliminar &aacute;rea' ,
								modal		: true ,
								resizable	: false ,
								show		: "fold" ,
								buttons		: {
									"Aceptar" : function () {
										$(this).dialog("close");
										$("#areas").load("modulos/admin/abm_areas.php #areas");
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
	
	$("#new").click(function () {
		$("#editar-areas").dialog({
			title 		: '<span class="ui-icon ui-icon-disk" style="float:left; margin:0px 7px 0px 0px;"></span> Nueva area' ,
			modal		: true ,
			resizable	: false ,
			show		: "fold" ,
			buttons		: {
				"Guardar" : function () {
					$(this).dialog("close");
					$.ajax({
						type : 'post' ,
						data : $(this).find("form").serialize() + '&acc=ins-area' ,
						url  : 'funciones/funciones_adm.php' ,
						success : function (data) {
							$("#respuesta-ajax").html(data);
							$("#respuesta-ajax").dialog({
								title 		: '<span class="ui-icon ui-icon-disk" style="float:left; margin:0px 7px 0px 0px;"></span> Nueva area' ,
								modal		: true ,
								resizable	: false ,
								show		: "fold" ,
								buttons		: {
									"Aceptar" : function () {
										$(this).dialog("close");
										$("#areas").load("modulos/admin/abm_areas.php #areas");
									}
								}
							});
						}
					});
				}
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
	<input type="button" value="Nueva &aacute;rea" id="new" /> <br />
</div>
<span id="ocultar-alta">Ocultar</span>
<hr />
<div id="areas">
	<table class="lista-areas">
		<thead>
			<tr>
				<th>Nombre</th>
				<th>Observaciones</th>
				<th colspan="2">Acciones</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		if ($res) {
			while ($reg = pg_fetch_assoc ($res)) { ?>
			<tr>
				<td id="<?php echo $reg['id']; ?>"><?php echo $reg['nombre']; ?></td>
				<td><?php echo $reg['observaciones']; ?></td>
				<td>
					<a href="#" class="edit">Editar<img src="img/edit.png" title="Editar" /></a>
				</td>
				<td>
					<a href="#" class="del">Eliminar<img src="img/delete-item.png" title="Eliminar" /></a>
				</td>
			</tr>
		<?php	
		} 
		?>
		</tbody>
	</table>
</div>
<?php
} else {
	die ("Ha ocurrido un error " . pg_last_error());
}
?>

<div style="display:none;">
	<div id="mensaje-alerta"></div>
	<div id="respuesta-ajax"></div>
	<div id="editar-areas">
		<form>
			<table class="edit-areas">
				<tr>
					<td> Nombre </td>
					<td><input type="text" name="nom_area" id="nom-area" /> </td>
				</tr>
				<tr>
					<td> Observaciones </td>
					<td><input type="text" name="obs_area" id="obs-area" /> </td>
				</tr>
			</table>
		</form>
	</div>
</div>