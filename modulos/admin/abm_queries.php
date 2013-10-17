<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql_areas = "SELECT * FROM sistema.areas a WHERE a.id_area NOT IN (8)";
$res_areas = pg_query ($sql_areas);
?>
<script>
$(document).ready(function(){
	$("input:button").button();
	
	$("#listado-areas").change(function(){
		$(".data-queries").load("modulos/admin_abm_queries.php?area=" + $(this).val() + " .data-queries");
	});
	
	$("#nuevo-query").click(function(){
		$.fancybox({
			href : 'vistas/admin_alta_query.php'
		});
	});
	
	$("#listado-queries").on("click" , "a" , function() {
		switch ($(this).attr("id")) {
			case 'ver': 
				$.ajax({
					type : 'POST' ,
					url  : 'funciones/admin_abm_query.php' ,
					data : 'id=' + $(this).parents("tr").children(".titulo").attr("id") + '&accion=V' ,
					success : function(data){
						$.fancybox({
						content : data ,
						width : 700 ,
						height : 600 ,
						autoDimensions: false 
						});
					}
				});
				return false;
				break;
			case 'edi': 
				$.ajax({
					type : 'POST' ,
					url  : 'vistas/admin_edit_query.php' ,
					data : 'id=' + $(this).parents("tr").children(".titulo").attr("id") ,
					success : function(data){
						$.fancybox(data);
					}
				});
				break;
			case 'del':
				var id = $(this).parents("tr").children(".titulo").attr("id");
				$(function() {
					$("#dialog-confirm").dialog({
						resizable	: false ,
						draggable	: false ,
						modal		: true ,
						show		: "fold" ,
						buttons: {
							"Aceptar": function() {
								$(this).dialog("close");
								$.ajax({
									type : 'POST' ,
									url  : 'funciones/admin_abm_query.php' ,
									data : 'accion=D&id=' + id ,
									success : function (data) {
										$.fancybox (data);
									}
								});
							},
							"Cancelar": function() {
								$(this).dialog("close");
							}
						}
					});
				});
				break;
			default: break;
		}
	});
	
	$("#busqueda").keyup(function(){
		$("#listado-queries").load("modulos/admin/abm_queries.php?busqueda=" + $(this).val() + " #listado-queries table");
	});

});
</script>

<div class="div-input">
	<input id="nuevo-query" type="button" value="Nuevo query" />
</div>
<hr />
<table class="areas-queries">
	<tr>
		<td> Seleccione &aacute;rea </td>
		<td>
			<select id="listado-areas">
				<option value="0">Todas</option>
				<?php while ($reg_areas = pg_fetch_assoc ($res_areas)) {
					echo "<option value=\"$reg_areas[id]\"> $reg_areas[nombre] </option>";
				} ?>
			</select>
		</td>
		<td> B&uacute;squeda por nombre </td>
		<td><input id="busqueda" type="text" name="busqueda" /></td>
	</tr>
</table>
<?php
if (! (isset ($_GET['area'])) || $_GET['area'] == 0) { 
	$sql_queries = "
		SELECT q.id as qid, q.nombre, q.consulta, q.descripcion, q.grafico, a.nombre as area ,a.id_area
		FROM sistema.queries q LEFT JOIN sistema.areas a ON q.id_area = a.id_area
		ORDER BY area , q.nombre";
} else {
	$sql_queries = "
		SELECT q.id as qid, q.nombre, q.consulta, q.descripcion, q.grafico, a.nombre as area , a.id_area
		FROM sistema.queries q LEFT JOIN sistema.areas a ON q.id_area = a.id_area
		WHERE a.id_area = $_GET[area]
		ORDER BY area , q.nombre";
}

if (isset ($_GET['busqueda'])) {
	$sql_queries = "
		SELECT q.id as qid, q.nombre, q.consulta, q.descripcion, q.grafico, a.nombre as area , a.id_area
		FROM  sistema.queries q LEFT JOIN sistema.areas a ON q.id_area = a.id_area
		WHERE q.nombre ILIKE '%$_GET[busqueda]%' 
		ORDER BY area , q.nombre";
}
?>
<div id="listado-queries">
	<table class="data-queries">
		<thead>
			<tr>
				<th> Nombre </th>
				<th> Area </th>
				<th colspan="3"> Acciones </th>
			</tr>
		</thead>
		<tbody>
		<?php
		$res_queries = pg_query ($sql_queries);
		if (pg_num_rows ($res_queries) <> 0) {
			while ($reg_queries = pg_fetch_assoc ($res_queries)) { ?>
				<tr>
					<td class="titulo" title="<?php echo $reg_queries['nombre']; ?>" id="<?php echo $reg_queries['qid']; ?>"> <?php echo $reg_queries['nombre']; ?> </td>
					<td> <?php echo $reg_queries['area']; ?> </td>
					<td><a href="#" id="ver"> Ver SQL </a></td>
					<td><a href="#" id="edi"> Editar </a></td>
					<td><a href="#" id="del"> Eliminar </a></td>
				</tr> <?php
			} 
		} else { ?>
				<tr>
					<td colspan="5"> No se han encontrado consultas para el &aacute;rea seleccionada </td>
				</tr>
		<?php
		} ?>
		</tbody>
	</table>
</div>

<div style="display:none;">
	<div id="dialog-confirm" title="Eliminar query">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0px 7px 20px 0px;"></span>El query ser&aacute; eliminado. Confirma?</p>
	</div>
</div>