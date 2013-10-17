<?php
	session_start();
	$nivel = 2;
	require '../../seguridad.php';
	require $ruta.'sistema/conectar_postgresql.php';
?>
<script>
$(document).ready(function(){
	
	$("dd").hide();
	$("dt").addClass("no_sel");
	$("dt").click(function(event){
		var d = $(this).next();
		$("dt").removeClass("sel").addClass("no_sel");
		$(this).removeClass("no_sel").addClass("sel");
		$("dd").not(d).slideUp();
		d.slideToggle();
		event.preventDefault();
	});

	$(".consulta").click(function(event){
		event.preventDefault();
		var id = $(this).parents("dd").attr("id");
		$.fancybox({
			href : 'modulos/recibe_queries.php?id=' + id
		});
	});
	
	$(".descarga").click(function(event){
		event.preventDefault();
		var id = $(this).parents("dd").attr("id");
		$.ajax({
			type : 'get' ,
			data : 'id=' + id ,
			url  : 'modulos/descarga_info.php' ,
			success : function () {
				window.location.href = 'modulos/descarga_info.php?id=' + id ;
			}
		});
	});
	
	$(".grafico").click(function(){
		var id = $(this).parents("dd").attr("id");
		$.fancybox({
			href  : 'modulos/grafica.php?q=' + id ,
			type : 'image'
		});
	});
	
	$("span").click(function(){
		area = $(this).attr("class");
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

$registros_por_pagina = 3;

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
			<?php echo $reg['descripcion']; ?>
			<hr />
			<div>
				<img class="consulta" title="Consultar" src="img/search.png" />
				<img class="descarga" title="Descargar" src="img/save.png" />
				<?php
				if ($reg['grafico'] == 'S') { ?>
				<img class="grafico" title="Graficar" src="img/bar-chart.png" /> 
				<?php
				}
				?>
			</div>
		</dd>
		<?php
		}
		?>
	</dl>
	<div class="consulta-areas-paginado">
		<?php
		for ($i = 1 ; $i <= $paginas_a_mostrar  ; $i ++) {
			echo "<span class='$_GET[area]'>$i</span>";
		}
		?>
	</div>
</div>