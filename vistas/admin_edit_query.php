<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql_queries = "
	SELECT q.id as qid, q.nombre, q.consulta, q.descripcion, q.grafico, a.nombre as area ,a.id 
	FROM sistema.queries q LEFT JOIN sistema.areas a ON q.area = a.id 
	WHERE q.id = $_POST[id]";
$reg_queries = pg_fetch_assoc (pg_query ($sql_queries));

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
			data : $(this).serialize() + '&accion=E' ,
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
			<td><input type="text" name="nombre_query" value="<?php echo $reg_queries['nombre']; ?>" /></td>
		</tr>
		<tr>
			<td>Descripci&oacute;n</td>
			<td><input type="text" name="descr_query" value="<?php echo $reg_queries['descripcion']; ?>" /></td>
		</tr>
		<tr>
			<td>Area</td>
			<td>
				<select name="area_query">
				<?php while ($reg_areas = pg_fetch_assoc ($res_areas)) {
					echo "<option value=\"$reg_areas[id]\"";
					if ($reg_queries['id'] == $reg_areas['id']) {
						echo "selected=\"selected\"";
					}
					echo "> $reg_areas[nombre] </option>";
				} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Grafico</td>
			<td>
				<select name="grafico_query">
					<option value="S" <?php if ($reg_queries['grafico'] == 'S') {echo "selected=\"selected\""; } ?> >Si</option>
					<option value="N" <?php if ($reg_queries['grafico'] == 'N') {echo "selected=\"selected\""; } ?> >No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2">Sentencia</td>
		</tr>
		<tr>
			<td colspan="2"><textarea name="sql_query"><?php echo $reg_queries['consulta']; ?></textarea></td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" value="Modificar" />
				<input type="hidden" name="id" value="<?php echo $_POST['id']; ?>" />
			</td>
		</tr>
	</table>
</form>