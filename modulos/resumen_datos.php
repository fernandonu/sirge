<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

if (isset ($_POST['id_provincia'])) {
	
$sql = "
select
	a.mes
	, b.\"2009\"
	, c.\"2010\"
	, d.\"2011\"
	, e.\"2012\"
	, f.\"2013\"
from (
	select extract (month from fecha_prestacion) as mes
	from prestaciones.p_$_POST[id_provincia]
	group by extract (month from fecha_prestacion)
	order by extract (month from fecha_prestacion)
	) a left join (
		select
			extract (month from fecha_prestacion) as mes
			, count (*) as \"2009\"
		from prestaciones.p_$_POST[id_provincia]
		where fecha_prestacion between '2009-01-01' and '2009-12-31'
		group by extract (month from fecha_prestacion)
	) b on a.mes = b.mes left join (
		select
			extract (month from fecha_prestacion) as mes
			, count (*) as \"2010\"
		from prestaciones.p_$_POST[id_provincia]
		where fecha_prestacion between '2010-01-01' and '2010-12-31'
		group by extract (month from fecha_prestacion)

	) c on a.mes = c.mes left join (
		select
			extract (month from fecha_prestacion) as mes
			, count (*) as \"2011\"
		from prestaciones.p_$_POST[id_provincia]
		where fecha_prestacion between '2011-01-01' and '2011-12-31'
		group by extract (month from fecha_prestacion)
	) d on a.mes = d.mes left join (
		select
			extract (month from fecha_prestacion) as mes
			, count (*) as \"2012\"
		from prestaciones.p_$_POST[id_provincia]
		where fecha_prestacion between '2012-01-01' and '2012-12-31'
		group by extract (month from fecha_prestacion)
	) e on a.mes = e.mes left join (
		select
			extract (month from fecha_prestacion) as mes
			, count (*) as \"2013\"
		from prestaciones.p_$_POST[id_provincia]
		where fecha_prestacion between '2013-01-01' and '2013-12-31'
		group by extract (month from fecha_prestacion)
	) f on a.mes = f.mes";
		
	$res = pg_query ($sql);
	
	echo "
	<table>
		<tr>
				<th>Mes</th>
				<th>2009</th>
				<th>2010</th>
				<th>2011</th>
				<th>2012</th>
				<th>2013</th>
			</tr>";
	while ($reg = pg_fetch_assoc ($res)) {
		echo 
			"<tr>
				<td>$reg[mes]</td>
				<td>$reg[2009]</td>
				<td>$reg[2010]</td>
				<td>$reg[2011]</td>
				<td>$reg[2012]</td>
				<td>$reg[2013]</td>
			</tr>";
	}
	echo "</table>";
	die();
}

?>
<script>
$(document).ready(function(){
	$("dd").hide();
	$("dt").click(function(){
		
		$("dd").not($(this).next()).slideUp();
		$(this).next("dd").slideToggle();
		
		var dt = $(this);
		$.fancybox.showActivity();
		
		$.ajax({
			type : 'post' ,
			data : 'id_provincia=' + $(this).attr('id_provincia') ,
			url  : 'modulos/resumen_datos.php' ,
			success : function (data) {
				
				console.log (data);
				
				$(".pepe").html(data);
				
				$.fancybox.hideActivity();
			}
		});
	});

});
</script>
<div class="texto_resumen">Resumen de datos - Haga click sobre la provincia para conocer las cantidades de prestaciones informadas, agrupadas por fecha de prestaci&oacute;n</div><div class="tabla_resumen">

<?php

$sql = "select * from sistema.provincias order by id_provincia";
$res = pg_query ($sql);

while ($reg = pg_fetch_assoc ($res)) {
	echo '<dt id_provincia=' , $reg['id_provincia'] , '>' , $reg['nombre'] , '</dt>';
	echo '
	<dd class="pepe">
	
	</dd>';

	
}

?>
</div>
