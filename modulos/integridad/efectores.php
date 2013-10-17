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
$(document).ready(function(){

});
</script>
<table>
	<thead>
		<tr>
			<th>Nombre</th>
			<th>Efectores</th>
		</tr>
	</thead>
	<tbody>
<?php
while ($row = pg_fetch_assoc ($res)) { ?>
	<tr>
		<td class="nombre"> <?php echo $row['nombre']; ?> </td>
		<td> 
			<a id="<?php echo $row['id_provincia']; ?>" title="EFECTORES" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=EFECTORES">
			<?php
			$sql_data = "
				select count(*) 
				from prestaciones.p_" . $row['id_provincia'] . "
				where cuie not in (select cuie from sistema.efectores where id_provincia = '" . $row['id_provincia'] . "')";
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