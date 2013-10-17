<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql = "
	select 
		e.id_entidad
		, coalesce (p.nombre , s.nombre) as nombre
		, o.registros_insertados
		, o.puco
	from sistema.entidades e
		left join sistema.provincias p on e.id_entidad = p.id_provincia
		left join sistema.entidades_sanitarias s on e.id_entidad = s.id_entidad_sanitaria
		left join (
			select *
			from sistema.procesos_obras_sociales
			where puco = 'N'
		) o on e.id_entidad = o.id_entidad
	where 
		e.id_tipo_entidad in (1,2)";

$res = pg_query ($sql);
?>

<script>
$(document).ready(function(){
	
	$("#gen_puco").button();
	
	$("#gen_puco").click(function(){
		
		$.fancybox.showActivity();
		
		$.ajax({
			url : 'modulos/padron/tab-puco/generar_puco.php' ,
			success : function (data) {
				
				$.fancybox.hideActivity();
				
				console.log (data);
			}
		});
		
		
	});
	
});
</script>

<table class="listado-procesos-osp">
	<thead>
		<tr>
			<th>Nombre</th>
			<th>Cantidad de registros</th>
			<th>Estado</th>
			<th>Eliminar</th>
		</tr>
	</thead>
	<tbody>
		<?php
		while ($reg = pg_fetch_assoc ($res)) {	
		echo '<tr>';
		echo '<td>' . $reg['nombre'] . '</td>';
		echo '<td>' . $reg['registros_insertados'] . '</td>';
		echo '<td>' . $reg['puco'] . '</td>';
		echo '<td>D</td>';
		echo '</tr>';
		}
		?>
		<tr><td colspan="4"><input type="button" value="Generar PUCO" id="gen_puco" /></td></tr>
	</tbody>
</table>