<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

if (isset($_POST['busqueda_carga'])){
	$sql = "
		select 
			*
		from 
			sistema.cargas_archivos c
			left join sistema.cargas_archivos_osp o on c.id_carga = o.id_carga
		where 
			c.id_padron = 6
			and procesado = 'N'
			and o.codigo_os = $_POST[entidad_puco]";
	$res = pg_query($sql);
	if (pg_num_rows($res) > 0) {
		die ('0');
	} else {
		die('1');
	}
}

$sql = "
	select 
		e.id_entidad
		, coalesce (p.nombre , s.nombre) as nombre_entidad
		, o.codigo_os
		, o.nombre as nombre_os
		, o.sigla
	from sistema.entidades e
		left join sistema.provincias p on e.id_entidad = p.id_provincia
		left join sistema.entidades_sanitarias s on e.id_entidad = s.id_entidad_sanitaria
		left join sistema.obras_sociales o on e.id_entidad = o.id_entidad
	where e.id_tipo_entidad in ('1','2')
	order by e.id_entidad , codigo_os";
$res = pg_query ($sql);

if (!empty ($_FILES['padron'])) {
	foreach ($_FILES['padron']['name'] as $orden => $nombre) {
		$grupo = strlen ($_SESSION['grupo']) == 2 ? $_SESSION['grupo'] : "0" . $_SESSION['grupo'];
		
		if ($_FILES['padron']['error'][$orden] == 0 && move_uploaded_file($_FILES['padron']['tmp_name'][$orden] , '../../../upload/osp/' . $_POST['entidad-puco'] . '.txt')) {
			$subidas[] = $nombre;
			
			$sql = "
				insert into sistema.cargas_archivos (
					id_usuario_carga
					, id_padron
					, nombre_original
					, nombre_actual
					, size) 
				values (
					$_SESSION[id_usuario]
					, 6
					, '" . $nombre . "'
					, '" . $_POST['entidad-puco'] . ".txt'
					, '" . round ($_FILES['padron']['size'][$orden] / 1024 , 2) . "');
				select currval ('sistema.cargas_sirge_id_carga_seq') limit 1";
			$res = pg_query ($sql);
			$id = pg_fetch_row($res);
			$sql = "
				insert into sistema.cargas_archivos_osp
				values ($id[0] , " . $_POST['entidad-puco'] . ")";
			$res = pg_query($sql);
			
			die (json_encode ($subidas));
		}
	}
}

?>
<script>
$(document).ready(function(){
	$("input:submit").button();
	$(".error").hide();
	
	$("#submit").click(function(event){
		$(".error").hide();
		event.preventDefault();
		event.stopPropagation();
		
		var maxInputSize = 800;
		var fileInput = $("#padron")[0];
		var data = new FormData();
		
		var entidad = $("#entidad-puco").val();
		console.log (entidad);
		
		for ( var i = 0 ; i < fileInput.files.length ; i++ ) {
			data.append("padron[]" , fileInput.files[i]);
			
			if (fileInput.files[i].type == 'text/plain') {
				console.log ("Type: OK");
				if (fileInput.files[i].size / (1024 * 1024) < maxInputSize) {
					console.log ("Size: OK");
					var validacion_archivo = 1;
				} else {
					console.log ("Size: NO");
					var validacion_archivo = 2;
				}
			} else {
				console.log ("Type: NO");
				var validacion_archivo = 0;
			}
		}
		
		data.append("entidad-puco" , entidad);
		
		switch (validacion_archivo) {
			case 0 :
				$(".error").fadeIn().html("Tipo de archivo no permitido, solo se admiten archivos .txt");
				break;
			case 1 :
				var request = new XMLHttpRequest();
				
				request.upload.addEventListener("progress" , function (event){
					if (event.lengthComputable) {
						var percent = event.loaded / event.total;
						
						$(function(){
							$("#progreso").progressbar({
								value : percent * 100
							});
						});
					}
				});
				
				request.upload.addEventListener("load" , function (event){
					var percent = 0;
					document.getElementById("progreso").style.display = "none";
				});
				
				request.upload.addEventListener("error" , function (event){
					alert ("Fallo la carga");
				});
				
				request.addEventListener("readystatechange" , function (event){
					if (this.readyState == 4) {
						if (this.status == 200) {
							var links = document.getElementById("subidas");
							console.log (this.response);
							var subidas = eval(this.response);
							var div, a;
							
							for (var i = 0 ; i < subidas.length ; i ++ ) {
								div = document.createElement("div");

								div.appendChild(document.createTextNode('Se ha cargado el arvhivo ' + subidas[i]));
								links.appendChild(div);
								
							}
						} else {
							console.log ("El servidor respondio con " + this.status);
						}
					}
				});
				
				request.open("post" , "modulos/padron/tab-puco/carga_archivo.php");
				request.setRequestHeader("Cache-Control" , "no-cache");
				document.getElementById("progreso").style.display = "block";
				request.send (data);
			break;
			
			case 2 :
				$(".error").fadeIn().html("Tama&ntilde;o m&aacute;ximo exedido: " + maxInputSize + "MB");
			break;
			
			default: break;
		}
	});
	
	$("select").change(function(){
		$(".error").hide();
		var entidad = $(this).val();
		
		$.ajax({
			type : 'post' ,
			url  : 'modulos/padron/tab-puco/carga_archivo.php' ,
			data : 'busqueda_carga=1&entidad_puco=' + entidad ,
			success : function (data){
				if (data == '0') {
					$(".error").fadeIn().html("Se tiene pendiente un padr&oacute;n de esta entidad.");
					$("input:submit").prop('disabled', true);
				} else {
					$("input:submit").prop('disabled', false);
				}
			}
		});
	});
});
</script>
<div class="formulario-subida-datos">
	<form action="" method="post" enctype="multipart/form-data">
		<table>
			<thead>
				<tr>
					<th>Entidad</th>
					<th colspan="2">Ruta</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<select id="entidad-puco" name="provincia">
							<option value=''></option>
							<?php
							while ($reg = pg_fetch_assoc ($res)) {
								echo '<option value="' . $reg['codigo_os'] .'">' . $reg['nombre_os'] . ' - ' . $reg['sigla'] . '</option>';
							}
							?>
						</select>
					</td>
					<td>
						<input type="file" name="padron" id="padron"/>
					</td>
					<td><input type="submit" value="Enviar" id="submit" /></td>
				</tr>
				<tr>
					<td colspan="3">
						<div id="subidas"></div>
						<div id="progreso" style="width: 300px; height: 20px; display: none; border: solid 1px #CCC;"></div>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<p class="error"></p>
<p class="ui-state-highlight" style="border-radius: 3px;">
	<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>Seleccione la ruta al archivo de <span class="nombre_padron">obra social</span> dentro de su ordenador. Recuerde respetar la estructura de datos.
</p>