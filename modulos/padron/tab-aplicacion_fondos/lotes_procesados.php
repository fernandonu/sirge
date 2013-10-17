<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "
	select *
	from (
		select 
			l.*
			, e.descripcion
			, p.nombre
			, u1.usuario as usuario_proceso
			, u2.usuario as usuario_baja_lote
			, u3.usuario as usuario_cierre_lote
		from 
			sistema.lotes l
			left join sistema.tipo_lote e on l.id_estado = e.id_estado
			left join sistema.provincias p on l.id_provincia = p.id_provincia
			left join sistema.usuarios u1 on l.id_usuario_proceso = u1.id_usuario
			left join sistema.usuarios u2 on l.id_usuario_baja_lote = u2.id_usuario
			left join sistema.usuarios u3 on l.id_usuario_cierre_lote = u3.id_usuario";
$sql .= $_SESSION['grupo'] == 25 ?
	" where l.id_padron = 2
	order by l.lote desc ) r" : 
	" where 
		l.id_provincia = '$_SESSION[grupo]' 
		and l.id_padron = 2
	order by l.lote desc) r";

if (isset ($_GET['fecha'])) {
	$sql .= " where r.inicio :: date = '$_GET[fecha]'";
} else if (isset ($_GET['lote'])) {
	$sql .= " where r.lote = '$_GET[lote]'";
} else if (isset ($_GET['estado'])) {
	$sql .= " where r.id_estado = '$_GET[estado]'";
}

$res = pg_query ($sql);

$pagina_actual = isset ($_GET['pagina']) ? $_GET['pagina'] : 1 ;
$lotes_por_pagina = 25;
$total_lotes = pg_num_rows ($res);
$paginas_lotes = ceil ($total_lotes / $lotes_por_pagina);

$sql .= ' limit ' . $lotes_por_pagina . ' offset ' . ($pagina_actual - 1) * $lotes_por_pagina;
$res = pg_query ($sql);
	

?>
<script>
$(document).ready(function(){
	
	$("input:submit").button();
	
	$("#listado-lotes").on("click", ".baja-lote", function(event){
		event.preventDefault();
		
		var lote = $(this).parents("dd").attr("lote");
		var provincia =  $(this).parents("dd").attr("provincia");
		
		$("#dialog-confirm").html("Est&aacute; por eliminar el lote <span style='font-weight: bold;'>" + lote + "</span>, est&aacute; seguro?");
		$("#dialog-confirm").dialog({
			title		: "Eliminar lote" ,
			resizable	: false ,
			show		: "fold" ,
			modal		: true ,
			width		: 350 ,
			buttons: {
				"Aceptar" : function () {
					$(this).dialog("close");
					$.ajax({
						type : 'post' ,
						data : 'acc=B&lote=' + lote + '&provincia=' + provincia + '&padron=aplicacion_fondos',
						url  : 'funciones/abm_lotes.php' ,
						success : function (data) {
							$("#dialog-respuesta").html(data);
							$("#dialog-respuesta").dialog({
								modal		: true ,
								title		: "Confirmación" ,
								width		: 350 ,
								resizable	: false ,
								show		: "fold" ,
								buttons: {
									Ok: function() {
										$("#listado-lotes").load("modulos/padron/tab-aplicacion_fondos/lotes_procesados.php #listado-lotes" , function () {
											$("dd").hide();
										});
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
	
	$("#listado-lotes").on("click", ".cerrar-lote", function(event){
		event.preventDefault();
		var lote = $(this).parents("dd").attr("lote");
		var provincia =  $(this).parents("dd").attr("provincia");
		
		$("#dialog-confirm").html("Est&aacute; por cerrar el lote <span style='font-weight: bold;'>" + lote + "</span>, est&aacute; seguro?");
		$("#dialog-confirm").dialog({
			title		: "Cerrar lote" ,
			resizable	: false ,
			show		: "fold" ,
			modal		: true ,
			width		: 350 ,
			buttons: {
				"Aceptar" : function () {
					$(this).dialog("close");
					$.ajax({
						type : 'post' ,
						data : 'acc=C&lote=' + lote + '&provincia=' + provincia + '&padron=aplicacion_fondos',
						url  : 'funciones/abm_lotes.php' ,
						success : function (data) {
							$("#dialog-respuesta").html(data);
							$("#dialog-respuesta").dialog({
								modal		: true ,
								title		: "Confirmación" ,
								width		: 350 ,
								resizable	: false ,
								show		: "fold" ,
								buttons: {
									Ok: function() {
										$("#listado-lotes").load("modulos/padron/tab-aplicacion_fondos/lotes_procesados.php #listado-lotes" , function () {
											$("dd").hide();
										});
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
	
	$("#listado-lotes").on("click" , ".listado-lotes-paginado span" , function(event){
		event.preventDefault();
		var page = $(this).html();
		
		$("#listado-lotes").load("modulos/padron/tab-aplicacion_fondos/lotes_procesados.php?pagina=" + page + " #listado-lotes" , function(){
			$("dd").hide();
		});
	});
	
	$("#listado-lotes").on("click" , ".imprimir-ddjj" , function (event){
		event.preventDefault();
		var lote = $(this).parents("dd").attr("lote");
		var provincia =  $(this).parents("dd").attr("provincia");
		
		$.ajax({
			type : 'get' ,
			data : 'lote=' + lote + 'provincia=' + provincia,
			url  : 'funciones/genera_declaracion_jurada.php' ,
			success : function () {
				window.location.href = 'funciones/genera_declaracion_jurada.php?padron=2&lote=' + lote + '&provincia=' + provincia ;
			}
		});
	});
	
	
	$("dd").hide();
	$("#listado-lotes").on("click" , "dt" , function(){
		$(this).next("dd").slideToggle();
		$("dd").not($(this).next("dd")).slideUp();
	});
	
	
	
	$("#select-busqueda").change(function(){
		var parametro = $(this).val();
		
		switch (parametro) {
			case 'estado':
				var select = "<select name='estado'><option value='2'>Procesado</option><option value='3'>Eliminado</option><option value='1'>Cerrado</option></select>";
				$("#busqueda-dependiente").html(select);
			break;
			case 'lote':
				$("#busqueda-dependiente").html("<input type='text' name='lote' id='input-lote' />");
			break;
			case 'fecha':
				$("#busqueda-dependiente").html("<input type='text' name='fecha' />");
				$("#busqueda-dependiente input").datepicker({
					dateFormat	: "yy-mm-dd" ,
					changeMonth	: true ,
					changeYear	: true , 
					showOtherMonths: true ,
					selectOtherMonths: true ,
					showAnim : 'fold'
				});
			break;
			default: break;
		}
	});
	
	$("#tabla-busqueda-lotes").on("keydown" , "#input-lote" , function(event){
		 if(event.keyCode < 48 || event.keyCode > 57 && event.keyCode < 96 || event.keyCode > 105){
			if (event.keyCode != 8 && event.keyCode != 46){
				return false;
			}
        }
	});
	
	$("form").submit(function(event){
		event.preventDefault();
		$("#listado-lotes").load('modulos/padron/tab-aplicacion_fondos/lotes_procesados.php?' + $(this).serialize() + ' #listado-lotes' , function(){
			$("dd").hide();
		});
	});
	
	$("#listado-lotes").on("click" , ".registros-rechazados" , function(){
		$.fancybox.showActivity
		var lote = $(this).parents("dd").attr("lote");
		
		$.ajax ({
			type : 'get' ,
			url  : 'modulos/consultas/consulta_lotes_rechazados.php' ,
			data : 'padron=2&lote=' + lote ,
			success : function (data) {
				$.fancybox(data);
			}
		});
	});
	
});
</script>

<div id="tabla-busqueda-lotes">
	<form>
		<table>
			<tr>
				<td>B&uacute;squeda por </td>
				<td>
					<select name="" id="select-busqueda">
						<option value="lote">Lote</option>
						<option value="fecha">Fecha</option>
						<option value="estado">Estado</option>
					</select>
				</td>
				<td id="busqueda-dependiente"><input type="text" name="lote" id="input-lote" /></td>
				<td><input type="submit" value="Buscar" /></td>
			</tr>
		</table>
	</form>
</div>
<div id="listado-lotes">
	<dl class="data-lotes">
	<?php
	if (pg_num_rows ($res) == 0) {
		echo "No se han encontrado lotes procesados.";
	} else {
		while ($reg = pg_fetch_assoc ($res)) { ?>
			<dt class="<?php echo $reg['descripcion']?>">
			<?php 
			echo "Lote: " , $reg['lote'] , " - Fecha: " , substr ($reg['inicio'],0,10) , " - <span style=\"font-weight: bold;\">Estado: " , $reg['descripcion'] , "</span>";
			?>
			</dt>
			<dd lote="<?php echo $reg['lote']; ?>" provincia="<?php echo $reg['id_provincia']; ?>">
				<table class="data-tabla-lotes">
					<tr>
						<td>Provincia</td>
						<td><?php echo $reg['nombre']; ?></td>
					</tr>
					<tr>
						<td>Registros insertados</td>
						<td><?php echo $reg['registros_insertados']; ?></td>
					</tr>
					<tr>
						<td>Registros rechazados</td>
						<td class="registros-rechazados"><?php echo$reg['registros_rechazados']; ?></td>
					</tr>
					<tr>
						<td>Registros presentados</td>
						<td><?php echo$reg['registros_insertados'] + $reg['registros_rechazados']; ?></td>
					</tr>
					<tr>
						<td>Hs. Inicio</td>
						<td><?php echo $reg['inicio']; ?></td>
					</tr>
					<tr>
						<td>Hs. Fin</td>
						<td><?php echo $reg['fin']; ?></td>
					</tr>
					<tr>
						<td>Usuario carga</td>
						<td><?php echo $reg['usuario_proceso']; ?></td>
					</tr>
					<?php
					switch ($reg['id_estado']) {
						case 2 : ?>
							<tr>
								<td>Cerrar lote</td>
								<td><a href="#"><img class="cerrar-lote" src="img/database.png" title="Cerrar lote" /></a></td>
							</tr>
							<tr>
								<td>Eliminar lote</td>
								<td><a href="#"><img class="baja-lote" src="img/delete-item.png" title="Eliminar lote" /></a></td>
							</tr>
							<?php
						break;
						case 1 : ?>
							<tr>
								<td>Usuario cierre</td>
								<td><?php echo  $reg['usuario_cierre_lote']; ?></td>
							</tr>
							<tr>
								<td>Imprimir DDJJ</td>
								<td><a href="#"><img class="imprimir-ddjj" title="Imprimir" src="img/print.png" /></a></td>
							</tr>
						<?php
						break;
						case 3 : ?>
							<tr>
								<td>Usuario baja</td>
								<td><?php echo  $reg['usuario_baja_lote']; ?></td>
							</tr>
						<?php
						break;
						default: echo "Error";
					}
					?>
				</table>
			</dd>
		<?php
		}
	}
	?>
	</dl>
	<div class="listado-lotes-paginado">
		<?php
		for ($i = 1 ; $i <= $paginas_lotes ; $i ++) {
			echo "<span>$i</span>";
		}
		?>
	</div>
</div>
<div style="display:none;">
	<div id="dialog-confirm"></div>
	<div id="dialog-respuesta"></div>
</div>
