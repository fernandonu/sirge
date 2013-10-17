<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
?>
<script>
$(document).ready(function(){
	/**
		Escondo todos los detalles de las consultas
	*/
	$("dd").hide();
	
	/**
		Agrego la clase 'no_sel' a todos los 'dt'"
	*/
	$("dt").addClass("no_sel");
	
	$("dt").click(function(event){
		event.preventDefault();
		
		/**
			Para el 'dt' seleccionado, quito la clase 'no_sel' y agrego la clase 'sel'
		*/
		$(this).removeClass("no_sel").addClass("sel");
		
		/**
			Para los 'dt' no seleccionados, quito la clase 'sel' en caso de que exista y agrego la clase 'no_sel'
		*/
		$("dt").not($(this)).removeClass("sel").addClass("no_sel");
		
		/**
			Muestro el detalle de la selección y oculto el resto
		*/
		$("dd").not($(this).next()).slideUp();
		$(this).next().slideToggle();
	});

	$(".consulta").click(function(event){
		event.preventDefault();
		var id = $(this).parents("dd").attr("id");
		$.fancybox({
			href : 'modulos/consultas/recibe_queries.php?id=' + id
		});
	});
	
	$(".descarga").click(function(event){
		event.preventDefault();
		var id = $(this).parents("dd").attr("id");
		$.ajax({
			type : 'get' ,
			data : 'id=' + id ,
			url  : 'modulos/consultas/descarga_consulta_estatica.php' ,
			success : function () {
				window.location.href = 'modulos/consultas/descarga_consulta_estatica.php?id=' + id ;
			}
		});
	});
	
	$(".grafico").click(function(event){
		var id = $(this).parents("dd").attr("id");
		$.fancybox({
			href  : 'modulos/grafica.php?id_consulta=' + id
		});
	});
	
	$("span").click(function(event){
		event.preventDefault();
		area = $(this).attr("area");
		page = $(this).html();
		$(".consultas-areas").load("modulos/consultas/consulta_area.php?area=" + area + "&pagina=" + page).hide().fadeIn("slow");
	});
});
	
</script>

<?php


$sql = "
	select *
	from sistema.queries
	where id_area = " . $_GET['area'] ." 
	order by id";
$res = pg_query ($sql);

$pagina_actual = isset ($_GET['pagina']) ? $_GET['pagina'] : 1;

$registros_totales = pg_num_rows ($res);

$registros_por_pagina = 5;

$paginas_a_mostrar = $registros_totales == 0 ? 0 : ceil ($registros_totales / $registros_por_pagina);

$sql .= " limit " . $registros_por_pagina . " offset " . ($pagina_actual - 1) * $registros_por_pagina;

$res = pg_query ($sql);

?>

<div class="consultas-areas">
	<dl>
		<?php
		while ($reg = pg_fetch_assoc ($res)) { ?>
		<dt><?php echo $reg['nombre']; ?></dt>
		<dd id="<?php echo $reg['id']; ?>">
			<table>
				<tr>
					<td><?php echo $reg['descripcion']; ?></td>
					<td><img class="consulta" title="Consultar" src="img/search.png" /></td>
					<td><img class="descarga" title="Descargar" src="img/save.png" /></td>
					<?php
					if ($reg['grafico'] == 'S') { ?>
					<td><img class="grafico" title="Graficar" src="img/bar-chart.png" /></td>
					<?php
					}
					?>
				</tr>
			</table>
		</dd>
		<?php
		}
		?>
	</dl>
	<div class="consulta-areas-paginado">
		<?php
		for ($i = 1 ; $i <= $paginas_a_mostrar  ; $i ++) {
			echo $pagina_actual == $i ? 
				'<span area="' . $_GET['area'] . '" class="sel">' . $i . '</span>' 
				: '<span area="' . $_GET['area'] . '">' . $i . '</span>';
		}
		?>
	</div>
</div>