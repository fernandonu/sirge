<?php
require $ruta.'sistema/conectar_postgresql.php';

$sql = "
	select
		m2.*
	from 
		sistema.modulos_menu m1
		left join sistema.modulos m2 on m1.id_modulo = m2.id_modulo
	where 
		id_menu = $_SESSION[menu]
		and nivel_2 = 0
	order by 
		nivel_1
		, nivel_2";

function arma_sub_menu ($nivel_padre) {
	$sql="
		select
			m2.* 
		from 
			sistema.modulos_menu m1
			left join sistema.modulos m2 on m1.id_modulo = m2.id_modulo
		where 	
			m2.nivel_1 = $nivel_padre 
			and m2.nivel_2 <> 0 
			and m1.id_menu = $_SESSION[menu]
		order by m2.nivel_2 asc";
	return $sql;
}?>
<script>
$(document).ready(function(){
	$('#show').load('modulos/resumen_datos.php');
	$('.subnavegador').hide();
	
	$("#modulos-menu li div").click(function(){
		
		$("#modulos-menu li div").removeClass("activo");
		$(this).addClass("activo");
		
		var href = $(this).attr("href");
		
		switch (href) {
			case 'index.php': window.location = href;
				break;
			case '#' : 
				$(this).next("ul").slideToggle();
				break;
			default:
				$("#show").empty().html('<img style="margin-left: 350px; margin-top: 200px;" src="img/fancybox_loading.gif" />');
				$("#show").hide().load(href).fadeIn();
				break;
		}
	});
});
</script>
<div class="menu">
	<ul id="modulos-menu">
	<?php
	$res = pg_query ($sql);
	while ($reg = pg_fetch_assoc ($res)){
	
		echo '<li>';
		if (! $reg['modulo']) {
			echo '<div class="desplegable menu-padre" href="#">' , $reg['nombre'] , '</div>';
			
		} else if ($reg['modulo'] == 'index.php') {
			echo '<div class="menu-padre" href="' . $reg['modulo'] . '">' . $reg['nombre'] . '</div>';
			
		} else {
			echo '<div class="menu-padre" href="modulos/' . $reg['modulo'] . '">' .$reg['nombre'] . '</div>';
		}
		
		$res_sub = pg_query (arma_sub_menu ($reg['nivel_1']));
		if (pg_num_rows ($res_sub) > 0) {
			echo '<ul class="subnavegador">';
			
			while ($reg_sub = pg_fetch_assoc ($res_sub)) {
				echo '
				<li>
					<div href="modulos/' . $reg_sub['modulo'] . '">' . $reg_sub['nombre'] . '</div>
				</li>';
			}
			echo '</ul>';
		}
		echo '</li>';
	}
	?>
	</ul>
</div>