<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

if (isset ($_POST['acc'])) {
	switch ($_POST['acc']) {
		case 'consulta' :
			$sql = "
			select
				cuie
				, siisa
				, e.nombre
				, domicilio
				, codigo_postal
				, ciudad
				, g.latitud
				, g.longitud
				, p.nombre as provincia
				, nombre_localidad
				, nombre_departamento
				, te.descripcion as tipo_efector
				, e.id_tipo_efector
				, c.descripcion as categorizacion
				, e.id_categorizacion
				, rural
				, cics
				, da.descripcion as dependencia_administrativa
				, e.id_dependencia_administrativa
				, dependencia_sanitaria
				, habilitado as integrante
				, compromiso_gestion
				, alto_impacto
				, ppac
				, sumar
			from 
				efectores.efectores e left join 
				efectores.datos_geograficos g on e.id_efector = g.id_efector left join
				sistema.provincias p on g.id_provincia = p.id_provincia left join
				efectores.tipo_efector te on e.id_tipo_efector = te.id_tipo_efector left join
				efectores.tipo_categorizacion c on e.id_categorizacion = c.id_categorizacion left join
				efectores.tipo_dependencia_administrativa da on e.id_dependencia_administrativa = da.id_dependencia_administrativa left join
				efectores.departamentos d on d.id_provincia || d.id_departamento = p.id_provincia || g.id_departamento left join
				efectores.localidades l on l.id_provincia || l.id_departamento || l.id_localidad = p.id_provincia || g.id_departamento || g.id_localidad
			where cuie = '$_POST[cuie]'
			order by g.id_provincia asc";
			$res = pg_query ($sql);
			$reg = pg_fetch_assoc ($res , 0);
			
			die (json_encode ($reg));
		break;
		case 'telefono' :
			$sql = "
			select 
				t.*
				, tt.*
			from 
				efectores.efectores e left join
				efectores.telefonos t on e.id_efector = t.id_efector left join
				efectores.tipo_telefono tt on t.id_tipo_telefono = tt.id_tipo_telefono
			where
				cuie = '$_POST[cuie]'";
			$res = pg_query ($sql);
			$i = 0;
			while ($reg = pg_fetch_assoc ($res)) {
				$telefonos[$i]['numero'] = $reg['numero_telefono'];
				$telefonos[$i]['tipo'] = $reg['descripcion'];
				$telefonos[$i]['obs'] = $reg['observaciones'];
				$i ++;
			}
			die (json_encode ($telefonos));
		break;
		case 'email' :
			$sql = "
			select em.*
			from
				efectores.efectores e left join
				efectores.email em on e.id_efector = em.id_efector
			where cuie = '$_POST[cuie]'";
			$res = pg_query ($sql);
			$i = 0;
			while ($reg = pg_fetch_assoc ($res)) {
				$email[$i]['email'] = $reg['email'];
				$email[$i]['obs'] = $reg['observaciones'];
				$i ++;
			}
			die (json_encode ($email));
		break;
		case 'compromiso' :
			$sql = "
			select
				c.*
			from
				efectores.efectores e left join
				efectores.compromiso_gestion c on e.id_efector = c.id_efector
			where
				cuie = '$_POST[cuie]'";
			$res = pg_query ($sql);
			$i = 0;
			while ($reg = pg_fetch_assoc ($res)) {
				$compromiso[$i]['numero'] = $reg['numero_compromiso'];
				$compromiso[$i]['firmante'] = $reg['firmante'];
				$compromiso[$i]['fecha_suscripcion'] = $reg['fecha_suscripcion'];
				$compromiso[$i]['fecha_inicio'] = $reg['fecha_inicio'];
				$compromiso[$i]['fecha_fin'] = $reg['fecha_fin'];
				$compromiso[$i]['pago'] = $reg['pago_indirecto'];
				$i ++;
			}
			die (json_encode ($compromiso));
		break;
		case 'convenio' :
		$sql ="
		select
			c.*
		from
			efectores.efectores e left join
			efectores.convenio_administrativo c on e.id_efector = c.id_efector
		where
			cuie = '$_POST[cuie]'";
		$res = pg_query ($sql);
		$i = 0;
		while ($reg = pg_fetch_assoc ($res)) {
			$convenio[$i]['numero'] = $reg['numero_compromiso'];
			$convenio[$i]['firmante'] = $reg['firmante'];
			$convenio[$i]['nombre'] = $reg['nombre_tercer_administrador'];
			$convenio[$i]['codigo'] = $reg['codigo_tercer_administrador'];
			$convenio[$i]['sus'] = $reg['fecha_suscripcion'];
			$convenio[$i]['ini'] = $reg['fecha_inicio'];
			$convenio[$i]['fin'] = $reg['fecha_fin'];
			$i ++;
		}
		die (json_encode ($convenio));
		break;
	}
	
}
?>

<script>

var map;

$(document).ready(function(){
	
	$("input:submit").button();
	
	$("dd").hide();
	$("dt").click(function(){
		$(this).next("dd").slideToggle();
		$("dd").not($(this).next()).slideUp();
	});
	
	$("#dialog").dialog({
		autoOpen: false ,
		modal: true ,
		title: 'Prueba' ,
		width: 1000 , 
		height: 350 ,
		resizeStop: function(event, ui) {google.maps.event.trigger(map, 'resize') },
		open: function(event, ui) {google.maps.event.trigger(map, 'resize'); }
	});
				
	$("#listado-efectores").on("click" , ".mas-data" , function(event){
		event.preventDefault();

		var efector = $(this).parents("tr").find("td").html();
		$("dd").hide();
		
		$.ajax({
			type : 'post' , 
			url  : 'modulos/efectores/listado.php' ,
			data : 'acc=consulta&cuie=' + efector ,
			success : function (data) {
				data = JSON.parse (data);
				console.log (data);
				
				$("#dialog").dialog({title: data["cuie"]});
				
				$("#efector-nombre").html(data["nombre"]);
				$("#efector-cuie").html(data["cuie"]);
				$("#efector-siisa").html(data["siisa"]);
				$("#efector-provincia").html(data["provincia"]);
				$("#efector-ciudad").html(data["ciudad"]);
				$("#efector-domicilio").html(data["domicilio"]);
				$("#efector-departamento").html(data["nombre_departamento"]);
				$("#efector-localidad").html(data["nombre_localidad"]);
				$("#efector-cp").html(data["codigo_postal"]);
				$("#efector-tipo").html(data["tipo_efector"]);
				$("#efector-categoria").html(data["categorizacion"]);
				$("#efector-dependencia").html(data["dependencia_administrativa"]);
				$("#efector-cics").html(data["cics"]);
				$("#efector-rural").html(data["rural"]);
				$("#efector-sanitaria").html(data["dependencia_sanitaria"]);
				$("#efector-integrante").html(data["integrante"]);
				$("#efector-compromiso").html(data["compromiso_gestion"]);
				$("#efector-ai").html(data["alto_impacto"]);
				$("#efector-ppac").html(data["ppac"]);
				$("#efector-sumar").html(data["sumar"]);
				
				$("#dialog").dialog("open");
				
				var latlng = new google.maps.LatLng(data["latitud"], data["longitud"]);
				
				map.setCenter(latlng);

				new google.maps.Marker({
					map: map ,
					position: latlng ,
					title : data["nombre"]
				});
			}
		});
	});
	initialize();
	
	function initialize() {
		var latlng = new google.maps.LatLng(0,0);
        var myOptions = {
          zoom: 16,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
       map = new google.maps.Map(document.getElementById("map_canvas"),  myOptions);
	}
	
	$("#texto_busqueda").keyup(function(){
		var valores = $(this).parents("form").serialize();
		
		$("#listado-efectores").load("modulos/efectores/listado.php?" + valores + " #listado-efectores table");
	});
	
	$("#contacto-telefono").click(function(){
		var efector = $("#efector-cuie").html();
		$("#tabla-telefonos").html('');
		
		$.ajax({
			type : 'post' ,
			url  : 'modulos/efectores/listado.php' ,
			data : 'acc=telefono&cuie=' + efector ,
			success : function (data){
				var data = JSON.parse (data);
				var encabezado = "<tr><th>N&uacute;mero</th><th>Tipo</th><th>Observaciones</th></tr>";
				$(encabezado).appendTo("#tabla-telefonos");
				for (i = 0 ; i < data.length ; i ++) {
					var dd = "<tr><td>" + data[i]['numero'] + "</td><td>" + data[i]['tipo'] + "</td><td>" + data[i]['obs'] + "</td></tr>";
					$(dd).appendTo("#tabla-telefonos");
				}
			}
		});
	});
	
	$("#contacto-email").click(function(){
		var efector = $("#efector-cuie").html();
		$("#tabla-correos").html('');
			
		$.ajax({
			type : 'post',
			url  : 'modulos/efectores/listado.php',
			data : 'acc=email&cuie=' + efector,
			success : function (data) {
				var data = JSON.parse (data);
				var encabezado = "<tr><th>Email</th><th>Observaciones</th></tr>";
				$(encabezado).appendTo("#tabla-correos");
				for (i = 0 ; i < data.length ; i ++) {
					var dd = "<tr><td>" + data[i]['email'] + "</td><td>" + data[i]['obs'] + "</td>";
					$(dd).appendTo("#tabla-correos");
				}
			}
		});
	});
	
	$("#compromiso-gestion").click(function(){
		var efector = $("#efector-cuie").html();
		$("#tabla-compromiso").html('');
		
		$.ajax({
			type : 'post',
			url  : 'modulos/efectores/listado.php',
			data : 'acc=compromiso&cuie=' + efector ,
			success : function (data) {
				var data = JSON.parse (data);
				var encabezado = "<tr><th>Numero</th><th>Firmante</th><th>Suscripcion</th><th>Inicio</th><th>Fin</th><th>Pago indirecto</th></tr>";
				$(encabezado).appendTo("#tabla-compromiso");
				for (i = 0 ; i < data.length ; i ++) {
					var dd = "<tr><td>" + data[i]['numero'] + "</td><td>" + data[i]['firmante'] + "</td><td>" + data[i]['fecha_suscripcion'] + "</td><td>" + data[i]['fecha_inicio'] + "</td><td>" + data[i]['fecha_fin'] + "</td><td>" + data[i]['pago'] + "</td>";
					$(dd).appendTo("#tabla-compromiso");
				}
			}
		});
	});
	
	$("#convenio-admin").click(function(){
		var efector = $("#efector-cuie").html();
		$("#tabla-convenio").html('');
		
		$.ajax({
			type : 'post',
			url  : 'modulos/efectores/listado.php',
			data : 'acc=convenio&cuie=' + efector ,
			success : function (data) {
				var data = JSON.parse (data);
				var encabezado = "<tr><th>Numero</th><th>Firmante</th><th>Nombre</th><th>CUIE</th><th>Suscripcion</th><th>Inicio</th><th>Fin</th></tr>";
				$(encabezado).appendTo("#tabla-convenio");
				for (i = 0 ; i < data.length ; i ++) {
					var dd = "<tr><td>" + data[i]['numero'] + "</td><td>" + data[i]['firmante'] + "</td><td>" + data[i]['nombre'] + "</td><td>" + data[i]['codigo'] + "</td><td>" + data[i]['sus'] + "</td><td>" + data[i]['ini'] + "</td><td>" + data[i]['fin'] + "</td>";
					$(dd).appendTo("#tabla-convenio");
				}
			}
		});
	});
});
</script>


<div class="filtros_efectores">
	<form>
		<table>
			<tr>
				<td>B&uacute;squeda por </td>
				<td>
					<select name="campo_busqueda" id="select_busqueda">
						<option value="cuie">CUIE</option>
						<option value="siisa">SIISA</option>
						<option value="nombre">Nombre</option>
					</select>
				</td>
				<td id="busqueda-dependiente"><input type="text" name="texto_busqueda" id="texto_busqueda" /></td>
			</tr>
		</table>
	</form>
</div>
<?php
if (isset ($_GET['campo_busqueda'])) {
	$sql = "
		select *
		from efectores.efectores
		where 
			" . $_GET['campo_busqueda'] . " ilike '%" . $_GET['texto_busqueda'] . "%'
		order by cuie
		limit 35";
} else {
	$sql = '
		select * 
		from efectores.efectores 
		order by cuie
		limit 35';
}
$res = pg_query ($sql);
?>
<div id="listado-efectores">
	<table>
		<thead>
			<tr>
				<th>CUIE</th>
				<th>Nombre</th>
				<th>M&aacute;s datos</th>
			</tr>
		</thead>
		<tbody>
	<?php
	while ($reg = pg_fetch_assoc ($res)) { ?>
		<tr>
			<td><?php echo $reg['cuie']; ?></td>
			<td><?php echo $reg['nombre']; ?></td>
			<td><a href="#" class="mas-data"><img src="img/ID.png" title="Ver"/></a></td>
		</tr>
	<?php
	}
	?>
		</tbody>
	</table>
</div>
<div id="dialog">
	<div id="data_map">
		<dt>Informaci&oacute;n general</dt>
		<dd>
			<table>
				<tr>
					<td>Nombre</td>
					<td id="efector-nombre"></td>
				</tr>
				<tr>
					<td>CUIE</td>
					<td id="efector-cuie"></td>
				</tr>
				<tr>
					<td>SIISA</td>
					<td id="efector-siisa"></td>
				</tr>
				<tr>
					<td>Tipo efector</td>
					<td id="efector-tipo"></td>
				</tr>
				<tr>
					<td>Categorizaci&oacute;n</td>
					<td id="efector-categoria"></td>
				</tr>
				<tr>
					<td>Dependencia administrativa</td>
					<td id="efector-dependencia"></td>
				</tr>
				<tr>
					<td>Rural</td>
					<td id="efector-rural"></td>
				</tr>
				<tr>
					<td>CICS</td>
					<td id="efector-cics"></td>
				</tr>
				<tr>
					<td>Dependencia sanitaria</td>
					<td id="efector-sanitaria"></td>
				</tr>
				<tr>
					<td>Integrante</td>
					<td id="efector-integrante"></td>
				</tr>
				<tr>
					<td>Compromiso gesti&oacute;n</td>
					<td id="efector-compromiso"></td>
				</tr>
				<tr>
					<td>Alto impacto</td>
					<td id="efector-ai"></td>
				</tr>
				<tr>
					<td>PPAC</td>
					<td id="efector-ppac"></td>
				</tr>
				<tr>
					<td>SUMAR</td>
					<td id="efector-sumar"></td>
				</tr>
			</table>
		</dd>
		<dt>Informaci&oacute;n geogr&aacute;fica</dt>
		<dd>
			<table>
				<tr>
					<td>Domicilio</td>
					<td id="efector-domicilio"></td>
				</tr>
				<tr>
					<td>C&oacute;digo postal</td>
					<td id="efector-cp"></td>
				</tr>
				<tr>
					<td>Provincia</td>
					<td id="efector-provincia"></td>
				</tr>
				<tr>
					<td>Departamento</td>
					<td id="efector-departamento"></td>
				</tr>
				<tr>
					<td>Localidad</td>
					<td id="efector-localidad"></td>
				</tr>
			</table>
		</dd>
		<dt id="compromiso-gestion">Informaci&oacute;n compromiso de gesti&oacute;n</dt><dd><table id="tabla-compromiso"></table></dd>
		<dt id="convenio-admin">Informaci&oacute;n tercer administrador</dt><dd><table id="tabla-convenio"></table></dd>
		<dt id="contacto-telefono">Tel&eacute;fonos</dt><dd><table id="tabla-telefonos"></table></dd>
		<dt id="contacto-email">Direcciones de correo electr&oacute;nico</dt><dd><table id="tabla-correos"></table></dd>
	</div>
	<div id="map_canvas"></div>
</div>

<style>
	#map_canvas {
		height: 300px;
		width: 300px;
		position: absolute;
		top: 5px;
		left: 690px;
		border: solid 1px #ccc ;
	}
	#data_map {
		height: 200px;
		width: 250px;
		position: absolute;
		top: 5px;
		left: 5px;
	}
	
	#data_map dt {
		width: 670px;
		border-radius: 3px;
		background-color: #DBEBF6;
		padding: 3px;
		margin-bottom: 3px;
	}
	
	#data_map dt:hover {
		background-color: rgb(250,169,81);
	}
	
	#data_map dd table {
		width: 600px;
	}
	
	#data_map dd table td:first-child {
		width: 150px; 
	}
</style>
