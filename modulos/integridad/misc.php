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
				dialogo("fechas");
			break;
			case '2':
				dialogo("cbdni");
			break;
			case '3':
				dialogo("cuie");
			break;
			case '4':
				dialogo("clasedoc");
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
			<th id="1"> Fechas </th>
			<th id="2"> CB - DNI </th>
			<th id="5"> CB <> 16 </th>
			<th id="3"> CUIE </th>
			<th id="4"> Clase DOC </th>
		</tr>
	</thead>
	<tbody>
<?php
while ($row = pg_fetch_assoc ($res)) { ?>
	<tr>
		<td class="nombre"> <?php echo $row['nombre']; ?> </td>
		<td> 
			<a id="<?php echo $row['id_provincia']; ?>" title="FECHAS" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=FECHAS">
			<?php
			$sql_data = "
				select case when sum (cantidad) is null then 0 else sum (cantidad) end as total
				from (
					select fecha_prestacion , count (*) as cantidad
					from prestaciones.p_" . $row['id_provincia'] . "
					where fecha_prestacion not between '2004-01-01' and current_date
					group by fecha_prestacion
					) a";
			$res_data = pg_query ($sql_data);
			$row_data = pg_fetch_row ($res_data , 0);
			echo $row_data[0];
			?>
			</a> 
		</td>
		<td> 
			<a id="<?php echo $row['id_provincia']; ?>" title="CB-DNI" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=CB-DNI"> 
			<?php
			$sql_data = "
				select count (*) 
				from prestaciones.p_" . $row['id_provincia'] ." 
				where 
					clave_beneficiario = '0' and numero_documento = 0
					and codigo_prestacion not like 'CMI%'";
			$res_data = pg_query ($sql_data);
			$row_data = pg_fetch_row ($res_data , 0);
			echo $row_data[0];
			?>
			</a> 
		</td>
		<td>
			<a id="<?php echo $row['id_provincia']; ?>" title="CB-NOT16" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=CB-NOT16"> 
			<?php
			$sql_data = "
				select count (*) 
				from prestaciones.p_" . $row['id_provincia'] ." 
				where 
					length (clave_beneficiario) <> 16
					and codigo_prestacion not like 'CMI%'";
			$res_data = pg_query ($sql_data);
			$row_data = pg_fetch_row ($res_data , 0);
			echo $row_data[0];
			?>
			</a>
		</td>
		<td> 
			<a id="<?php echo $row['id_provincia']; ?>" title="CUIE" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=CUIE">
			<?php
			$sql_data = "
				select count(*) as cantidad
				from prestaciones.p_" . $row['id_provincia']  . "
				where length (cuie) <> 6";
			$res_data = pg_query ($sql_data);
			$row_data = pg_fetch_row ($res_data , 0);
			echo $row_data[0];
			?>
			</a> 
		</td>
		<td> 
			<a id="<?php echo $row['id_provincia']; ?>" title="CLASEDOC" href="modulos/descarga_inconsistencias.php?p=<?php echo $row['id_provincia']; ?>&a=CLASEDOC"> 
			<?php
			$sql_data = "
				select count (*) as cantidad
				from prestaciones.p_" . $row['id_provincia']  . "
				where clase_documento not in ('A','P')";
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
	<div id="fechas">Muestra la cantidad de fechas fuera de rango l&oacute;gico (01/01/2004 a hoy)</div>
	<div id="cbdni">Muestra la cantidad de prestaciones con c&oacute;digo de beneficiario vac&iacute;o y n&uacute;mero de documento vac&iacute;o. No tiene en cuenta los códigos de CMI</div>
	<div id="cuie">Muestra la cantidad de registros con el cuie vac&iacute;o</div>
	<div id="clasedoc">Muestra la cantidad de prestaciones en donde la clase de documento sea diferente de P (propio) o A (ajeno)</div>
	<div id="cbnot16">Muestra la cantidad de prestaciones en donde clave de beneficiario no sea de 16 dígitos. No tiene en cuenta los códigos CMI</div>
</div>