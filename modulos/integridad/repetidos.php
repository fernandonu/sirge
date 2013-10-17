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
				dialogo("repetidos");
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
			<th id="1"> Repetidos > 2011 </th>
			<th> % </th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
while ($row = pg_fetch_assoc ($res)) { ?>
	<tr>
		<td class="nombre"> <?php echo $row['nombre']; ?> </td>
		<td> 
			<a id="<?php echo $row['id_provincia']; ?>" title="REPETIDOS" href="#">
			<?php
				$sql_data = "
					select
						count (*) as cantidad
					from (
						select
							cuie ,tipo_comprobante ,numero_comprobante ,codigo_prestacion ,subcodigo_prestacion ,fecha_prestacion ,clave_beneficiario ,tipo_documento
						,clase_documento ,numero_documento ,precio_unitario	,difiere_precio	,pagado,peso ,tension_arterial ,fecha_liquidacion , count (*) as cantidad
						from prestaciones.p_" . $row['id_provincia']  . "
						where 
							fecha_prestacion >= '2011-01-01'
							and codigo_prestacion not like 'LMI%'
							and codigo_prestacion not in ('MPA17','MPA18')
						group by 
							cuie ,tipo_comprobante ,numero_comprobante ,codigo_prestacion ,subcodigo_prestacion ,fecha_prestacion ,clave_beneficiario ,tipo_documento
							,clase_documento ,numero_documento ,precio_unitario	,difiere_precio	,pagado,peso ,tension_arterial ,fecha_liquidacion
						) a
					where a.cantidad > 1";
				$res_data = pg_query ($sql_data);
				$row_data = pg_fetch_row ($res_data , 0);
				echo $row_data[0];
				$total += $row_data[0];
			?>
			</a> 
		</td>
		<td>
		<?php
			$sql_data = "
			select
				round ((select
					count (*) as cantidad_repetidos
				from (
					select  cuie ,tipo_comprobante ,numero_comprobante ,codigo_prestacion ,subcodigo_prestacion ,fecha_prestacion ,clave_beneficiario ,tipo_documento
							,clase_documento ,numero_documento ,precio_unitario	,difiere_precio	,pagado,peso ,tension_arterial ,fecha_liquidacion , count (*) as cantidad
					from prestaciones.p_" . $row['id_provincia']  . "
					where
						fecha_prestacion >= '2011-01-01'
						and codigo_prestacion not like 'LMI%'
						and codigo_prestacion not in ('MPA17','MPA18')
					group by 
						cuie ,tipo_comprobante	,numero_comprobante	,codigo_prestacion ,subcodigo_prestacion ,fecha_prestacion ,clave_beneficiario ,tipo_documento
						,clase_documento ,numero_documento ,precio_unitario	,difiere_precio	,pagado,peso ,tension_arterial ,fecha_liquidacion
					order by
						fecha_prestacion
						, clave_beneficiario
					) a
				where a.cantidad > 1) / (
				select count (*) 
				from prestaciones.p_" . $row['id_provincia']  . " 
				where fecha_prestacion >= '2011-01-01'
				) :: numeric * 100 , 2) as porcentajes";
			$res_data = pg_query ($sql_data);
			$row_data = pg_fetch_row ($res_data , 0);
			echo $row_data[0];
		?>
		</td>
	</tr>
<?php
} ?>
	<tr style="font-weight: bold; color: blue;">
		<td> Total </td>
		<td> <?php echo $total; ?> </td>
	</tr>
	</tbody>
</table>

<div class="dialogo-inconsistencias" style="display: none">
	<div id="repetidos"></div>
</div>