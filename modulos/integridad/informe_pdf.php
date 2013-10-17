<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "select * from sistema.provincias order by id_provincia";
$res = pg_query ($sql);
?>
<style>
	table {text-align: center; position: relative; top: 20px; margin: 0px auto;}
	tbody tr { height: 50px; }
	td { width: 140px; border-top: 1px solid #ccc; }
</style>
<script>
	$(document).ready(function(){
		$("input:submit").button();
	});
</script>
<form method="post" action="funciones/informe_completo_pdf.php">
	<table>
		<tr>
			<td>
				<select name="provincia">
					<option value="99"> Todas </option>
					<?php
					while ($reg = pg_fetch_assoc ($res)) { ?>
					<option value="<?php echo $reg['id_provincia']?>"> <?php echo $reg['nombre']?> </option>
					<?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" value="Generar PDF" />
			</td>
		</tr>
	</table>
</form>