<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql_areas = "SELECT * FROM sistema.areas a WHERE a.id NOT IN (8)";
$res_areas = pg_query ($sql_areas);
?>
<script>
$(document).ready(function(){
	$("input:submit").button();
	
	$("form").submit(function(){
		$.ajax({
			type : 'POST' ,
			url  : 'funciones/admin_abm_query.php' ,
			data : $(this).serialize() + '&accion=A' ,
			success : function(data){
				$.fancybox(data);
			}
		});
	return false;
	});
	
});
</script>
<style>
textarea { width: 1000px; height: 200px; }
input[type=text] { width: 630px; }
select { width: 200px; }
tr { height: 50px; border-top: solid 1px #CCC; }
</style>
<form>
	<table>
		<tr>
			<td>Nombre</td>
			<td><input type="text" name="nombre_query" /></td>
		</tr>
		<tr>
			<td>Descripci&oacute;n</td>
			<td><input type="text" name="descr_query" /></td>
		</tr>
		<tr>
			<td>Area</td>
			<td>
				<select name="area_query">
				<?php while ($reg_areas = pg_fetch_assoc ($res_areas)) {
					echo "<option value=\"$reg_areas[id]\"> $reg_areas[nombre] </option>";
				} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Grafico</td>
			<td>
				<select name="grafico_query">
					<option value="S">Si</option>
					<option value="N" selected="selected">No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2">Sentencia</td>
		</tr>
		<tr>
			<td colspan="2"><textarea name="sql_query">-- Pegue aqu&iacute; la sentencia SQL</textarea></td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" value="Enviar" />
			</td>
		</tr>
	</table>
</form>