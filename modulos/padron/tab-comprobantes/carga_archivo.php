<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

if (!empty($_FILES['archivo'])) {
	foreach ($_FILES['archivo']['name'] as $orden => $nombre) {
		
		$grupo = strlen ($_SESSION['grupo']) == 2 ? $_SESSION['grupo'] : "0" . $_SESSION['grupo'];
		$uniqueid = date("YmdHis");
		
		if ($_FILES['archivo']['error'][$orden] == 0 && move_uploaded_file ($_FILES['archivo']['tmp_name'][$orden] , '../../../upload/comprobantes/' . date("m") . "-C-" . $grupo ."_" . $uniqueid .".txt")) {
			$subidas[] = $nombre;
			
			$sql = "
				insert into sistema.cargas_archivos (id_usuario_carga , id_padron , size , nombre_original , nombre_actual)
				values (
					'$_SESSION[id_usuario]' 
					, '3' 
					, " . round (($_FILES['archivo']['size'][$orden] / 1024),2) . " 
					, '" . $nombre . "' 
					, '" . date("m") . "-C-" . $grupo ."_" . $uniqueid . ".txt" . "' ) ";
			$res = pg_query ($sql);
			if (! $res) {
				unlink ("../upload/comprobantes/" . date("m") . "-C-" . $grupo . "_" . $uniqueid .".txt");
				echo pg_last_error();
			} else {
				die (json_encode ($subidas));
			}
		} else {
				echo getcwd() ; die ("ERRRORRR!!");
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
		
		var maxInputSize = 25;
		var fileInput = $("#archivo")[0];
		var data = new FormData();
	
		for ( var i = 0 ; i < fileInput.files.length ; i++ ) {
			data.append("archivo[]" , fileInput.files[i]);
			
			if (fileInput.files[i].type == "text/plain") {
				console.log ("Tipo de archivo : OK");
				if (fileInput.files[i].size / (1024 * 1024) < maxInputSize) {
					console.log ("Tama&ntilde;o m&aacute;ximo permitido : OK");
					var validacion_archivo = 1;
				} else {
					console.log ("Tama�o m�ximo permitido : No OK");
					var validacion_archivo = 2;
				}
			} else {
				console.log ("Tipo de archivo : No OK");
				var validacion_archivo = 0;
			}
		}
		
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
				
				request.open("post" , "modulos/padron/tab-comprobantes/carga_archivo.php");
				request.setRequestHeader("Cache-Control" , "no-cache");
				
				document.getElementById("progreso").style.display = "block";
				
				request.send(data);
			break;
			
			case 2 :
				$(".error").fadeIn().html("Tama&ntilde;o m&aacute;ximo exedido: " + maxInputSize + "MB");
			break;
			
			default: break;
		}
	});
});

</script>
<div class="formulario-subida-datos">
	<form action="" method="post" enctype="multipart/form-data">
		<table>
			<thead>
				<tr>
					<th style="background-color: rgb(153,204,95)" colspan="2">Ruta de archivo - Comprobantes</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><input type="file" id="archivo" name="archivo" size="50" /></td>
					<td><input type="submit" id="submit" value="Enviar" /></td>
				</tr>
				<tr>
					<td>
						<div id="subidas"></div>
						<div id="progreso" style="width: 500px; height: 20px; display: none; border: solid 1px #CCC;"></div>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<p class="error"></p>
<p class="ui-state-highlight" style="border-radius: 3px;">
	<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>Seleccione la ruta al archivo de <span class="nombre_padron">comprobantes</span> dentro de su ordenador. Recuerde respetar la estructura de datos. 
</p>
