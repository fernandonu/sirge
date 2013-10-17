<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "
	select 
		coalesce (e1.nombre , e2.nombre) as nombre
		, e0.id_entidad
		, o1.codigo_os
		, o1.nombre as nombre_os
		, o1.sigla
		, substring (c3.fecha_carga :: text from 1 for 10) as fecha_carga
		, c3.size
		, c3.nombre_original
		, c3.nombre_actual
	from 
		sistema.entidades e0
		left join sistema.provincias e1 on e0.id_entidad = e1.id_provincia
		left join sistema.entidades_sanitarias e2 on e0.id_entidad = e2.id_entidad_sanitaria
		left join sistema.obras_sociales o1 on e0.id_entidad = o1.id_entidad
		left join (
			select *
			from 
				sistema.cargas_archivos c1
				left join sistema.cargas_archivos_osp c2 on c1.id_carga = c2.id_carga
			where
				c1.procesado = 'N'
				and c1.id_padron = 6
		) c3 on o1.codigo_os = c3.codigo_os
	where id_tipo_entidad in (1,2)";

$res = pg_query($sql);

?>
<script>
$(document).ready(function(){
	
	$(".listado-archivos-subidos").on("click" , ".procesar" , function (event){
		event.preventDefault();
		
		var archivo = $(this).parents("tr").children("td").html();
		console.log (archivo);
		
		$("#dialog-confirm").html("Est&aacute; por procesar el archivo <span style='font-weight: bold;'>" + archivo + "</span>, est&aacute; seguro?");
		$("#dialog-confirm").dialog({
			title		: "<span class=\"ui-icon ui-icon-info\" style=\"float:left; margin:0px 7px 0px 0px;\"></span> Procesar archivo" ,
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
						url  : 'funciones/valida_padron_osp_v2.php' ,
						data : 'archivo=' + archivo ,
						success : function(data) {
							
							$.fancybox.hideActivity();
							
							$("#dialog-respuesta").html(data);
							$("#dialog-respuesta").dialog({
								modal		: true ,
								title		: "Confirmaci&oacute;n" ,
								width		: 350 ,
								resizable	: false ,
								show		: "fold" ,
								buttons: {
									Ok: function() {
										$(".listado-archivos-subidos").load("modulos/padron/tab-puco/subidas.php .listado-archivos-subidos table");
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
		});
	});
	
	$(".listado-archivos-subidos").on("click" , ".del" , function(event){
		event.preventDefault();
		var archivo = $(this).parents("tr").children("td").html();
		console.log (archivo);
		
		$("#dialog-confirm").html("Est&aacute; por eliminar el archivo <span style='font-weight: bold; color: red;'>" + archivo + "</span>, est&aacute; seguro?");
		$("#dialog-confirm").dialog({
			title		: "<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0px 7px 0px 0px;\"></span> Eliminar archivo" ,
			resizable	: false ,
			show		: "fold" ,
			modal		: true ,
			width		: 350 ,
			buttons: {
				"Aceptar" : function () {
					$(this).dialog("close");
					$.ajax({
						type : 'post' ,
						data : 'acc=del-archivo&padron=osp&file=' + archivo ,
						url  : 'funciones/funciones_adm.php' ,
						success : function(data) {
							$("#dialog-respuesta").html(data);
							$("#dialog-respuesta").dialog({
								modal		: true ,
								title		: "Confirmaci&oacute;n" ,
								width		: 350 ,
								resizable	: false ,
								show		: "fold" ,
								buttons: {
									Ok: function() {
										$(".listado-archivos-subidos").load("modulos/padron/tab-puco/subidas.php .listado-archivos-subidos table");
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
});
</script>
<div class="listado-archivos-subidos">
	<table>
		<thead>
			<tr>
				<th>Entidad</th>
				<th>Sigla</th>
				<th>Nombre</th>
				<th>Fecha</th>
				<th>Tama&ntilde;o</th>
				<th>Procesar</th>
				<th>Eliminar</th>
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
				<td style="display: none"><?php echo $reg['nombre_actual']; ?></td>
				<td><?php echo $reg['nombre']; ?></td>
				<td><?php echo $reg['sigla']; ?></td>
				<td><?php echo $reg['nombre_original']; ?></td>
				<td><?php echo $reg['fecha_carga']; ?></td>
				<td><?php echo $reg['size']; ?></td>
				<?php
				if (strlen ($reg['size']) > 0) { ?>
				<td> 
					<a class="procesar" href="#"><img src="img/processing.png" title="Procesar" /></a>
				</td>
				<td>
					<a class="del" href="#"><img src="img/delete-item.png" title="Eliminar" /></a>
				</td>
				<?php
				} else { ?>
					<td colspan="2"></td>
				<?php
				}
				?>
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