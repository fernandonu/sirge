<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "select * from sistema.provincias order by id_provincia";
$res = pg_query ($sql);
?>
<style>
	table { text-align: center; position: relative; top: 20px; margin: 0px auto; font-size: 12px; }
	table tbody tr { border-bottom: solid 1px #CCCCCC; height: 30px; } 
	.nombre { width: 150px; }
	td:not(.nombre) { width: 70px; }
	table tbody td a:hover { color: red; }
	th { cursor: help; }
	th:hover { color: black; }
</style>
<script>

function dialogo (id) {
	$("#" + id).dialog({
		height : 120 ,
		modal : true ,
		draggable : false ,
		hide : "slide" ,
		show : "blind" ,
		title : "C&oacute;digos" ,
		zIndex : 3999
	});
}

$(document).ready(function(){
	$("th").click(function(){
		var id = $(this).attr("id");
		switch (id) {
			case '1':
				dialogo("codigos");
			break;
			case '2':
				dialogo("subcodigos");
			break;
			case '3':
				dialogo("nomenclador");
			break;
			default:break;
		}
	});
});

</script>
<table>
	<thead>
		<tr>
			<th> Nombre </th>
			<th id="1"> C&oacute;digos &Oslash; </th>
			<th id="2"> SubCodigos </th>
			<th id="3"> Nomenclador </th>
		</tr>
	</thead>
	<tbody>
<?php
while ($row = pg_fetch_assoc ($res)) { ?>
	<tr>
		<td class="nombre"> <?php echo $row['nombre']; ?> </td>
		<td> 
			<a id="<?php echo $row['id_provincia']; ?>" title="CODIGOS" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=CODIGOS">
			<?php
			$sql_data = "
				select count(*) as cantidad
				from prestaciones.p_" . $row['id_provincia'] ." 
				where length (codigo_prestacion) = 0";
			$res_data = pg_query ($sql_data);
			$row_data = pg_fetch_row ($res_data , 0);
			echo $row_data[0];
			?>
			</a> 
		</td>
		<td> 
			<a id="<?php echo $row['id_provincia']; ?>" title="SUBCODIGOS" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=SUBCODIGO">
			<?php
			$sql_data = "
				select count (*)
				from prestaciones.p_" . $row['id_provincia'] ." 
				where 
					length (cast (subcodigo_prestacion as character)) <> 0
					and codigo_prestacion not in ('LMI46','LMI47','LMI48','LMI49','CMI65','CMI66','CMI67','NPE42','MEM07','MPU23')";
			$res_data = pg_query ($sql_data);
			$row_data = pg_fetch_row ($res_data , 0);
			echo $row_data[0];
			?>
			</a> 
		</td>
		<td> 
			<a id="<?php echo $row['id_provincia']; ?>" title="NOMENCLADOR" href="#" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=NOMENCLADOR">
			<?php
			$sql_data = "
				select count (*) as cantidad
				from prestaciones.p_" . $row['id_provincia'] ." 
				where codigo_prestacion not in (
					select *
					from sistema.nomenclador)
					and length (codigo_prestacion) <> 0
					and codigo_prestacion not in ('NPE42','MEM07','MPU23')";
			$res_data = pg_query ($sql_data);
			$row_data = pg_fetch_row ($res_data , 0);
			echo $row_data[0];
			?>
			</a>
		</td>
	</tr>
<?php
} ?>
	</tbody>
</table>

<div class="dialogo-inconsistencias" style="display: none">
	<div id="codigos">Muestra la cantidad de c&oacute;digos de prestaciones en blanco</div>
	<div id="subcodigos">Muestra la cantidad de prestaciones que tienen un subc&oacute;digo cuando no corresponde (Excluyendo NPE42, MEM07, MPU23)</div>
	<div id="nomenclador">Muestra la cantidad de prestaciones que no coinciden con el nomenclador (Excluyendo NPE42, MEM07, MPU23)</div>
</div>
