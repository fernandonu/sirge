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
				dialogo("mpu23");
			break;
			case '2':
				dialogo("mem07");
			break;
			case '3':
				dialogo("npe42");
			break;
			default:break;
		}
	});
});

</script>
<table>
	<thead>
		<tr>
			<th>Provincia</th>
			<th id="1"> MPU23 </th>
			<th id="2"> MEM07 </th>
			<th id="3"> NPE42 </th>
		</tr>
	</thead>
	<tbody>
	<?php
	while ($row = pg_fetch_assoc ($res)) { ?>
		<tr>
			<td class="nombre"> <?php echo $row['nombre']; ?> </td>
			<td> 
				<a id="<?php echo $row['id_provincia']; ?>" title="MPU23" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=MPU23">
				<?php
				$sql_data = "
					select count (*)
					from prestaciones.p_" . $row['id_provincia'] ."
					where 
						codigo_prestacion = 'MPU23'
						and subcodigo_prestacion not in ('A','B')
						and fecha_prestacion >= '2011-01-01'";
				$res_data = pg_query ($sql_data);
				$row_data = pg_fetch_row ($res_data , 0);
				echo $row_data[0];
				?>
				</a>
			</td>
			<td> 
				<a id="<?php echo $row['id_provincia']; ?>" title="MEM07" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=MEM07">
				<?php
				$sql_data = "
					select count (*)
					from prestaciones.p_" . $row['id_provincia'] ."
					where 
						codigo_prestacion = 'MEM07'
						and subcodigo_prestacion not in ('A','B')
						and fecha_prestacion >= '2011-01-01'";
				$res_data = pg_query ($sql_data);
				$row_data = pg_fetch_row ($res_data , 0);
				echo $row_data[0];
				?>
				</a> 
			</td>
			<td> 
				<a id="<?php echo $row['id_provincia']; ?>" title="NPE42" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=NPE42"> 
				<?php
				$sql_data = "
					select count (*)
					from prestaciones.p_" . $row['id_provincia'] ."
					where
						codigo_prestacion = 'NPE42'
						and subcodigo_prestacion not in ('A','B','C','D','E','F','G')
						and fecha_prestacion between '2010-01-01' and current_date";
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
	<div id="mpu23">Muestra la cantidad de MPU23, posteriores al 01/01/2011, que no se diferencian por A o B</div>
	<div id="mem07">Muestra la cantidad de MEM07, posteriores al 01/01/2011, que no se diferencian por A o B</div>
	<div id="npe42">Muestra la cantidad de NPE42, posteriores al 01/01/2010, que no se diferencian por A, B, C, D, E, F o G</div>
</div>