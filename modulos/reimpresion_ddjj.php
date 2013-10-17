<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
require $ruta.'pdf/fpdf.php';

?>
<script>
	$(document).ready(function(){
		$("input:button").button();
		
		$("#buscar").click(function(){
			$("#contenedor-reimpresion").load("modulos/reimpresion_ddjj.php?" + $(this).parents("form").serialize() + " #tabla-reimpresion");
		});
	});
</script>
<style>
	table {text-align: center; position: relative; top: 20px; margin: 0px auto;}
	tbody tr { height: 50px; }
	td { width: 140px; border-top: 1px solid #ccc; }
</style>
<form>
	<table>
		<tr>
			<td> B&uacute;squeda por lote </td>
			<td> <input type="text" name="lote" /> </td>
			<td> <input type="button" id="buscar" value="Buscar" /> </td>
		</tr>
	</table>
</form>
<div id="contenedor-reimpresion"></div>

<?php
if (isset ($_GET['lote'])) { 

$sql = "
	select 
		  lote
		, registros_insertados
		, registros_rechazados
		, registros_insertados + registros_rechazados as total
		, inicio
		, padron
		, nombre
		, id_provincia
	from sistema.lotes l 
		left join sistema.provincias p on l.id_provincia :: numeric = p.idprovincia :: numeric
	where 
		lote = $_GET[lote]
		and estado <> 'E'";
$res = pg_query ($sql);

?>
<table id="tabla-reimpresion">
	<thead>
		<tr>
			<th> Lote </th>
			<th> Id provincia </th>
			<th> Fecha Carga </th>
			<th> Registros insertados </th>
			<th> Registros rechazados </th>
			<th> DDJJ </th>
		</tr>
	</thead>
	<tbody>
		<?php 
		while ($reg = pg_fetch_assoc ($res)) { ?>
		<tr>
			<td> <?php echo $reg['lote']; ?> </td>
			<td> <?php echo $reg['id_provincia']; ?> </td>
			<td> <?php echo $reg['inicio']; ?> </td>
			<td> <?php echo $reg['registros_insertados']; ?> </td>
			<td> <?php echo $reg['registros_rechazados']; ?> </td>
			<td> 
				<a href="funciones/genera_ddjj.php?provincia=<?php echo $reg['nombre']; ?>&lote=<?php echo $reg['lote']; ?>"> 
					<img src="img/print.png" title="Imprimir" />
				</a> 
			</td>
		</tr>
		<?php 
		} ?>
	</tbody>
</table>
<?php
} ?>