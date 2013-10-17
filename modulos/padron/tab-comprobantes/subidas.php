<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "
	select 
		c.*
	from sistema.cargas_archivos c
		left join sistema.usuarios u on c.id_usuario_carga = u.id_usuario
	where 
		id_padron = 3
		and procesado = 'N'";

$sql .= $_SESSION['grupo'] == 25 ? '' : " and id_entidad = '$_SESSION[grupo]'";
$res = pg_query ($sql);
?>
<script>
$(document).ready(function(){
	$(".listado-archivos-subidos").on("click" , ".del" , function(event){
		event.preventDefault();
		var file_ori = $(this).parents("tr").children().next().html();
		var file = $(this).parents("tr").children().html();
		
		$("#dialog-confirm").html("Est&aacute; por eliminar el archivo <span style='font-weight: bold; color: red;'>" + file_ori+ "</span>, est&aacute; seguro ?");
		$("#dialog-confirm").dialog({
			title		: "Eliminar archivo" ,
			resizable	: false ,
			show		: "fold" ,
			modal		: true ,
			width		: 350 ,
			buttons: {
				"Aceptar" : function () {
					$(this).dialog("close");
					$.ajax({
						type : 'post' ,
						data : 'acc=del-archivo&padron=comprobantes&file=' + file ,
						url  : 'funciones/funciones_adm.php' ,
						success : function(data) {
							$("#dialog-respuesta").html(data);
							$("#dialog-respuesta").dialog({
								modal		: true ,
								title		: "Confirmación" ,
								width		: 350 ,
								resizable	: false ,
								show		: "fold" ,
								buttons: {
									Ok: function() {
										$(".listado-archivos-subidos").load("modulos/padron/tab-comprobantes/subidas.php .listado-archivos-subidos table");
										$(this).dialog("close");
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
	
	$(".listado-archivos-subidos").on("click" , ".procesar" , function(event){
		event.preventDefault();
		
		var file_ori = $(this).parents("tr").children().next().html();
		var file = $(this).parents("tr").children().html();
		
		$("#dialog-confirm").html("Est&aacute; por procesar el archivo <span style='font-weight: bold;'>" + file_ori + "</span>, confirma la operaci&oacute;n ?");
		$("#dialog-confirm").dialog({
			title		: "Procesar archivo" ,
			resizable	: false ,
			show		: "fold" ,
			modal		: true ,
			width		: 350 ,
			buttons: {
				"Aceptar" : function () {
					$(this).dialog("close");
					$.fancybox.showActivity();
					$.ajax({
						type : 'post' ,
						data : 'file=' + file ,
						url  : 'funciones/valida_comprobantes_nuevo_formato.php' ,
						success : function(data) {
							$.fancybox.hideActivity();
							$("#dialog-respuesta").html(data);
							$("#dialog-respuesta").dialog({
								modal		: true ,
								title		: "Confirmación" ,
								width		: 350 ,
								resizable	: false ,
								show		: "fold" ,
								buttons: {
									"Aceptar" : function() {
										$(".listado-archivos-subidos").load("modulos/padron/tab-comprobantes/subidas.php .listado-archivos-subidos table");
										$(this).dialog("close");
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
		}).prev().removeClass("ui-state-error");
	});
});
</script>

<div class="listado-archivos-subidos">
	<table>
		<thead>
			<tr>
				<th style="background-color: rgb(153,204,95)">Nombre</th>
				<th style="background-color: rgb(153,204,95)">Fecha de carga</th>
				<th style="background-color: rgb(153,204,95)">Tama&ntilde;o</th>
				<th style="background-color: rgb(153,204,95)">Procesar</th>
				<th style="background-color: rgb(153,204,95)">Eliminar</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (pg_num_rows ($res) == 0) { ?>
			<tr>
				<td colspan="5">No se han encontrado archivos pendientes de procesar</td>
			</tr>
			<?php
			} else {
				while ($reg = pg_fetch_assoc ($res)) { ?>
			<tr>
				<td style="display: none;"><?php echo $reg['nombre_actual']; ?></td>
				<td> <?php echo $reg['nombre_original']; ?> </td>
				<td> <?php echo substr ($reg['fecha_carga'] , 0 , 16); ?> </td>
				<td> <?php echo $reg['size']; ?> kb </td>
				<td><a class="procesar" href="#"><img src="img/processing.png" title="Procesar" /></a></td>
				<td><a class="del" href="#"><img src="img/delete-item.png" title="Eliminar" /></a></td>
			</tr>
			<?php
				}
			}
			?>
		</tbody>
	</table>
</div>

<div style="display:none;">
	<div id="dialog-confirm"></div>
	<div id="dialog-respuesta"></div>
</div>
