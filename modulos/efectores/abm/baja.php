<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

if (isset ($_POST['acc'])) {
	switch ($_POST['acc']) {
	
		case 'consulta':
			if (strlen ($_POST['efector']) == 6) {
				$busqueda = 'cuie';
			} else if (strlen ($_POST['efector']) == 14) {
				$busqueda = 'siisa';
			}
			
			$sql = "
				select 
					e.cuie
					, e.siisa
					, e.nombre
					, e.domicilio
					, e.rural
					, e.cics
					, e.dependencia_sanitaria
					, e.habilitado
					, e.compromiso_gestion
					, e.alto_impacto
					, e.sumar
					, e.id_estado
					, c.descripcion as categorizacion
					, d.descripcion as dependencia_administrativa
					, t.descripcion as tipo_efector 
				from 
					efectores.efectores e left join 
					efectores.tipo_categorizacion c on e.id_categorizacion = c.id_categorizacion left join
					efectores.tipo_dependencia_administrativa d on e.id_dependencia_administrativa = d.id_dependencia_administrativa left join
					efectores.tipo_efector t on e.id_tipo_efector = t.id_tipo_efector
				where " . $busqueda . " = '" . $_POST['efector'] . "'";
			$res = pg_query ($sql);
			$row = pg_fetch_assoc ($res);
			if ($row['id_estado'] == 3) {
				die ("0");
			} else {
				die (json_encode ($row));
			}
		break;
		case 'baja':
			$sql = "update efectores.efectores set id_estado = 3 where cuie = '$_POST[cuie]'";
			$res = pg_query ($sql);
			die ("Se solicit&oacute; la baja del efector");
		break;
	}
	
}

?>
<script>
$(document).ready(function(){
	$("input:submit , input:button").button();
	$("#baja_detalle , #mensaje").hide();
	
	$("#formu_baja").submit(function(event){
		event.preventDefault();
		var efector = $("#efector").val();
		
		if (efector.length != 6 && efector.length != 14) {
			$("#mensaje").html("CUIE o código SIISA inválidos");
			$("#mensaje").dialog({
				modal		: true ,
				title		: "Atención" ,
				width		: 350 ,
				resizable	: false ,
				show		: "fold" ,
				buttons 	: {
					"Aceptar" : function(){
						$(this).dialog("close");
					}
				}
			});
		} else {
			$.ajax({
				type : 'post' ,
				data : 'acc=consulta&' + $(this).serialize() ,
				url  : 'modulos/efectores/abm/baja.php' ,
				success : function (data) {
					if (data == 0) {
						$("#mensaje").html("La baja de este efector ya fue solicitada. En caso de ser necesario comunicarse con la UEC.");
						$("#mensaje").dialog({
							modal		: true ,
							title		: "Atención" ,
							width		: 350 ,
							resizable	: false ,
							show		: "fold" ,
							buttons 	: {
								"Aceptar" : function(){
									$(this).dialog("close");
								}
							}
						});
					} else {
						$("#baja_detalle").fadeIn();
						data = JSON.parse(data);
						//console.log (data);
						$("#cuie_b").html(data['cuie']);
						$("#siisa_b").html(data['siisa']);
						$("#nombre_b").html(data['nombre']);
						$("#tipo_efector_b").html(data['tipo_efector']);
						$("#rural_b").html(data['rural']);
						$("#cics_b").html(data['cics']);
						$("#categorizacion_b").html(data['categorizacion']);
						$("#dependencia_adm_b").html(data['dependencia_administrativa']);
						$("#dependencia_san_b").html(data['dependencia_sanitaria']);
						$("#integrante_b").html(data['habilitado']);
						$("#compromiso_b").html(data['compromiso_gestion']);
						$("#alto_impacto_b").html(data['alto_impacto']);
					}
				}
			});
		}
	});
	
	$("#solicita_baja").click(function(){
		var cuie = $("#cuie_b").html();
		
		$.ajax ({
			type : 'post' ,
			data : 'acc=baja&cuie=' + cuie ,
			url  : 'modulos/efectores/abm/baja.php' ,
			success : function (data) {
				$("#mensaje").html(data);
				$("#mensaje").dialog({
					modal		: true ,
					title		: "Confirmación" ,
					width		: 350 ,
					resizable	: false ,
					show		: "fold" ,
					buttons 	: {
						"Aceptar" : function(){
							$(this).dialog("close");
						}
					}
				});
			}
		});
	});
	
});
</script>
<div id="baja">
	<form id="formu_baja">
		<div>
			Ingrese el CUIE o c&oacute;digo SIISA del efector a dar de baja
			<input id="efector" name="efector" type="text" />
			<input type="submit" value="Buscar" />
		</div>
	</form>
</div>

<div id="mensaje"></div>
<div id="baja_detalle">
	<table>
		<tr>
			<td>CUIE</td>
			<td id="cuie_b"></td>
		</tr>
		<tr>
			<td>SIISA</td>
			<td id="siisa_b"></td>
		</tr>
		<tr>
			<td>Nombre</td>
			<td id="nombre_b"></td>
		</tr>
		<tr>
			<td>Tipo efector</td>
			<td id="tipo_efector_b"></td>
		</tr>
		<tr>
			<td>Rural</td>
			<td id="rural_b"></td>
		</tr>
		<tr>
			<td>CICS</td>
			<td id="cics_b"></td>
		</tr>
		<tr>
			<td>Categorizaci&oacute;n</td>
			<td id="categorizacion_b"></td>
		</tr>
		<tr>
			<td>Dependencia administrativa</td>
			<td id="dependencia_adm_b"></td>
		</tr>
		<tr>
			<td>Dependencia sanitaria</td>
			<td id="dependencia_san_b"></td>
		</tr>
		<tr>
			<td>Integrante</td>
			<td id="integrante_b"></td>
		</tr>
		<tr>
			<td>Compromiso gesti&oacute;n</td>
			<td id="compromiso_b"></td>
		</tr>
		<tr>
			<td>Alto impacto</td>
			<td id="alto_impacto_b"></td>
		</tr>
	</table>
	<input id="solicita_baja" type="button" value="Solicitar baja" />
</div>

<style>
	#baja {
		border-radius: 3px;
		background-color: rgb(250,150,81);
		padding: 5px;
		text-align: center;
	}
	
	#baja_detalle {
		text-align: center;
	}
	
	#baja_detalle table {
		width: 600px;
		position: relative;
		margin: 5px auto;
	}
	
	#baja_detalle table td {
		padding: 8px;
		width: 300px;
		border-bottom: solid 1px #ccc;
	}
	
</style>
