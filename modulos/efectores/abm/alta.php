<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

if (isset ($_POST['acc'])) {
	switch ($_POST['acc']) {
		case 'provincia':
			$sql = "select * from efectores.departamentos where id_provincia = '$_POST[provincia]'";
			$res = pg_query ($sql);
			
			$i = 0 ;
			while ($reg = pg_fetch_assoc ($res)) {
				$depto[$i]['id_departamento'] = $reg['id_departamento'];
				$depto[$i]['nombre'] = html_entity_decode ($reg['nombre_departamento']);
				$i ++;
			}
			die (json_encode ($depto));
		break;
		case 'departamento':
			$sql = "select * from efectores.localidades where id_provincia = '$_POST[provincia]' and id_departamento = '$_POST[departamento]'";
			$res = pg_query ($sql);
			
			$i = 0 ;
			while ($reg = pg_fetch_assoc ($res)) {
				$loca[$i]['id_localidad'] = $reg['id_localidad'];
				$loca[$i]['nombre'] = html_entity_decode ($reg['nombre_localidad']);
				$i ++;
			}
			die (json_encode ($loca));
		break;
		case 'newcuie' :
			$sql = "select 
						substring (max (cuie) from 1 for 1) || (substring (max (cuie) from 2 for 5) :: int + 1) :: varchar as cuie
					from (
						select
							*
						from 
							efectores.efectores e left join 
							efectores.datos_geograficos d on e.id_efector = d.id_efector
						where substring (cuie from 2 for 5) :: int <> 99999 ) e
					where id_provincia = '$_POST[provincia]'";
			$res = pg_query ($sql);
			$row = pg_fetch_assoc ($res);
			die (json_encode ($row));
		break;
		case 'alta' :
			$efector = array (
				$_POST['cuie']
				, $_POST['siisa']
				, $_POST['nombre']
				, $_POST['domicilio']
				, $_POST['codigo_postal']
				, $_POST['tipo_efector']
				, $_POST['rural']
				, $_POST['cics']
				, $_POST['categorizacion']
				, $_POST['dependencia_adm']
				, $_POST['dependencia_san']
				, $_POST['integrante']
				, $_POST['compromiso']
				, $_POST['alto_impacto']
				, $_POST['sumar']
				, '2'
			);
			
			$sql = "
				insert into efectores.efectores (cuie , siisa , nombre , domicilio , codigo_postal , id_tipo_efector , rural , cics , id_categorizacion , id_dependencia_administrativa , dependencia_sanitaria , habilitado , compromiso_gestion , alto_impacto , sumar , id_estado)
				values ('" . implode ("','" , $efector) . "');
				select currval ('efectores.efectores_id_efector_seq') limit 1";
			$res = pg_query ($sql);
			
			if ($res) {
				
				$id_efector = pg_fetch_row ($res , 0);
				
				// Datos geográficos
				$geograficos = array (
					$id_efector[0]
					, $_POST['provincia']
					, $_POST['departamento']
					, $_POST['localidad']
					, $_POST['ciudad']
				);
				$sql = "
					insert into efectores.datos_geograficos (id_efector , id_provincia , id_departamento , id_localidad , ciudad)
					values ('" . implode ("','" , $geograficos) . "');";
				
				// Referente
				$referente = array (
					$id_efector[0]
					, $_POST['referente']
				);
				$sql .= "
					insert into efectores.referentes (id_efector , nombre) 
					values ('" . implode ("','" , $referente) . "');";
				
				// Teléfono
				$telefono = array (
					$id_efector[0]
					, $_POST['telefono']
					, $_POST['tipo_telefono']
					, $_POST['telefono_observaciones']
				);
				$sql .= "
					insert into efectores.telefonos (id_efector , numero_telefono , id_tipo_telefono , observaciones)
					values ('" . implode ("','" , $telefono) . "');";
				
				// Email
				$email = array (
					$id_efector[0]
					, $_POST['email']
					, $_POST['email_observaciones']
				);
				$sql .= "
					insert into efectores.email (id_efector , email , observaciones)
					values ('" . implode ("','" , $email) . "');";
				
				if ($_POST['integrante'] == 'S' && $_POST['compromiso'] == 'S') {
					
					// Compromiso gestión
					$compromiso = array(
						$id_efector[0]
						, $_POST['numero_compromiso']
						, $_POST['firmante_compromiso']
						, $_POST['fecha_sus_cg']
						, $_POST['fecha_ini_cg']
						, $_POST['fecha_fin_cg']
						, $_POST['pago_indirecto']
					);
					$sql .= "
						insert into efectores.compromiso_gestion
						values ('" . implode ("','" , $compromiso) . "');";
					
					
					if ($_POST['pago_indirecto'] == 'S') {
						
						// Convenio tercer administrador
						$convenio = array (
							$id_efector[0]
							, $_POST['numero_convenio']
							, $_POST['firmante_convenio']
							, $_POST['nombre_adm']
							, $_POST['codigo_adm']
							, $_POST['fecha_sus_ca']
							, $_POST['fecha_ini_ca']
							, $_POST['fecha_fin_ca']
						);
						$sql .= "
							insert into efectores.convenio_administrativo
							values ('" . implode ("','" , $convenio) . "')";
					}
				}
				//echo $sql; die();
				$res = pg_query ($sql);
				if ($res) {
					
					$sql = "
						insert into efectores.solicitudes_efectores (id_efector , id_usuario_solicitud , fecha_solicitud)
						values ('$id_efector[0]' , $_SESSION[id_usuario] , localtimestamp)";
					$res = pg_query ($sql);
					if ($res) {
						die ("Alta de efector solicitada");
					} else {
						pg_query ("delete from efectores.efectores where id_efector = $id_efector[0]");
						die ("Error al solicitar el alta del efector");
					}
				}
			} else {
				die ("Error al solicitar el alta del efector");
			}
		break;
	}
}

?>
<script>
$(document).ready(function(){
	$("input:button").button();
	$("#compromiso_gestion , #convenio_administrativo").hide();
	
	$("#compromiso").change(function(){
		var estado = $(this).val();
		var integrante = $("#integrante").val();
		
		if (integrante == 'S') {
		
			switch (estado) {
				case 'S':
					$("#compromiso_gestion").fadeIn(); 
				break;
				case 'N': 
					$("#compromiso_gestion , #convenio_administrativo").fadeOut();
				break;
			}
		}
	});
	
	$("#integrante").change(function(){
		var estado = $(this).val();
		var compromiso = $("#compromiso").val();
		
		if (compromiso == 'S') {
		
			switch (estado) {
				case 'S':
					$("#compromiso_gestion").fadeIn(); 
				break;
				case 'N': 
					$("#compromiso_gestion , #convenio_administrativo").fadeOut();
				break;
			}
		}
	});
	
	$("#pago_indirecto").change(function(){
	var estado = $(this).val();
		
		switch (estado) {
			case 'S': $("#convenio_administrativo").fadeIn(); break;
			case 'N': $("#convenio_administrativo").fadeOut();; break;
		}
	
	});
	
	$("#fecha_sus_cg , #fecha_ini_cg , #fecha_fin_cg , #fecha_sus_ca , #fecha_ini_ca , #fecha_fin_ca").datepicker({
		dateFormat: "yy-mm-dd" ,
		maxDate : '2015-12-31' ,
		monthNames  : ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"]
	});
	
	$("#provincia").change(function(){
		var provincia = $(this).val();
		$("#departamento").html('');
		$("#localidad").html('');
		
		$.ajax({
			type : 'post' ,
			url  : 'modulos/efectores/abm/alta.php' ,
			data : 'acc=provincia&provincia=' + provincia ,
			success : function (data) {
				data = JSON.parse (data);
				console.log (data);
				$("#departamento").append($("<option>" , {
					value : '0' ,
					text  : 'Seleccione departamento'
				}));
				
				for ( i = 0 ; i < (data.length) ; i ++ ) {
					$("#departamento").append($("<option>",{
						value : data[i]['id_departamento'] ,
						text  : data[i]['nombre']
					}));
				}
			}
		});
		
		$.ajax({
			type : 'post' ,
			url  : 'modulos/efectores/abm/alta.php' ,
			data : 'acc=newcuie&provincia=' + provincia ,
			success : function (data) {
				data = JSON.parse (data);
				$("#cuie").val(data['cuie']);
			}
		});
	});
	
	$("#departamento").change(function(){
		var provincia = $("#provincia").val();
		var departamento = $(this).val();
		$("#localidad").html('');
		
		$.ajax({
			type : 'post' ,
			url  : 'modulos/efectores/abm/alta.php' ,
			data : 'acc=departamento&provincia=' + provincia + '&departamento=' + departamento ,
			success : function (data) {
				data = JSON.parse (data);
				
				$("#localidad").append($("<option>" , {
					value : '0' ,
					text  : 'Seleccione localidad'
				}));
				
				for ( i = 0 ; i < (data.length) ; i ++ ) {
					$("#localidad").append($("<option>",{
						value : data[i]['id_localidad'] ,
						text  : data[i]['nombre']
					}));
				}
			}
		});
	});
	
	$("#cuie").tooltip({content: "Valor autogenerado con el próximo CUIE disponible"});
	$("#siisa").tooltip({content: "Complete con el código SIISA correspondiente. Solo se admiten números"});
	$("#nombre").tooltip({content: "Ingrese el nombre del efector. Campo obligatorio"});
	$("#tipo_efector").tooltip({content: "Indique de que tipo de efector se trata. Campo obligatorio"});
	$("#rural").tooltip({content: "Indique si el efector es rural o no"});
	$("#cics").tooltip({content: "Indique si el efector es CICS o no"});
	$("#categorizacion").tooltip({content: "Indique la categorización del efector"});
	$("#dependencia_adm").tooltip({content: "Seleccione la dependencia administrativa del efector. Campo obligatorio"});
	$("#dependencia_san").tooltip({content: "Seleccione la dependencia sanitaria del efector"});
	$("#integrante").tooltip({content: "Indique si el efector es integrante o no. Campo obligatorio"});
	$("#compromiso").tooltip({content: "Indique si el efector posee compromiso de gestión o no. Campo obligatorio"});
	$("#alto_impacto").tooltip({content: "Indique si el efector es de alto impacto o no"});
	$("#referente").tooltip({content: "Ingrese el referente del efector"});
	
	$("#provincia").tooltip({content: "Provincia para la cual se solicita el alta del efector. Campo obligatorio"});
	$("#departamento").tooltip({content: "Departamento de la provincia para la cual se solicita el alta del efector. Campo obligatorio"});
	$("#localidad").tooltip({content: "Localidad del departamento previamente informado. Campo obligatorio"});
	$("#ciudad").tooltip({content: "Ciudad en la cual se encuentra el efector"});
	$("#domicilio").tooltip({content: "Domicilio del efector. Campo obligatorio"});
	$("#codigo_postal").tooltip({content: "Código postal del efector"});
	
	/*
	$("#email").tooltip({content: "Email de contacto con el efector"});
	$("#email_observaciones").tooltip({content: "Observación del email ingresado"});
	*/
	
	$("#tipo_telefono").tooltip({content: "Identifique de que tipo de teléfono se trata"});
	/*
	$("#telefono").tooltip({content: "Teléfono de contacto con el efector"});
	$("#telefono_observaciones").tooltip({content: "Observaciones del teléfono ingresado"});
	*/
	
	/*
	$("#numero_compromiso").tooltip({content: "Ingrese el número del compromiso de gestión. Campo obligatorio"});
	$("#firmante_compromiso").tooltip({content: "Ingrese el firmante del compromiso de gestión. Campo obligatorio"});
	*/
	$("#pago_indirecto").tooltip({content: "Identifique si el efector tiene modelo de pago indirecto"});
	$("#sumar").tooltip({content: "Identifique si el efector convenio con el programa SUMAR"});
	
	/*
	$("#numero_convenio").tooltip({content: "Ingrese en número de convenio con el tercer administrador"});
	$("#firmante_convenio").tooltip({content: "Ingrese el firmante de dicho convenio"});
	$("#nombre_adm").tooltip({content: "Ingrese el nombre del tercer administrador"});
	$("#codigo_adm").tooltip({content: "Ingrese el código (CUIE si es posible)"});
	*/
	
	$("#email , #email_observaciones , #telefono , #telefono_observaciones , #numero_compromiso , #firmante_compromiso , #numero_convenio , #firmante_convenio , #nombre_adm , #codigo_adm" ).focus(function(){
		if (this.value == this.title) {
			$(this).val("");
		}
	}).blur(function(){
		if (this.value == "") {
			$(this).val(this.title);
		}
	});
	
	$("#siisa").keydown(function(event){
        // Allow: backspace, delete, tab, escape, and enter
        if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 || event.ctrlKey == true ||
             // Allow: Ctrl+A
            (event.keyCode == 86 && event.ctrlKey === true) || 
             // Allow: home, end, left, right
            (event.keyCode >= 35 && event.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        } else {
            // Ensure that it is a number and stop the keypress
            if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
                event.preventDefault();
            } 
        }
    });
	
	$("#solicitar_alta").click(function(){
		/** Validacion de campos **/
		var rechazo = 0;
		
		// SIISA
		if ($("#siisa").val().length != 14) {
			var rechazo = 1;
			var motivo = 'Codigo SIISA inválido';
		}
		
		// Nombre
		if ($("#nombre").val().length == 0) {
			var rechazo = 1;
			var motivo = 'No se especificó el nombre del efector';
		}
		
		// Departamento
		if ($("#departamento").val() == null || $("#departamento").val() == "0") {
			var rechazo = 1;
			var motivo = 'Seleccione un departamento';
		}
		
		// Localidad
		if ($("#localidad").val() == null || $("#localidad").val() == "0") {
			var rechazo = 1;
			var motivo = 'Seleccione una localidad';
		}
		
		// Referente
		if ($("#referente").val().length == 0) {
			var rechazo = 1;
			var motivo = 'Ingrese un referente';
		}
		
		// Tipo efector
		if ($("#tipo_efector").val() == 0) {
			var rechazo = 1;
			var motivo = 'Seleccione un tipo de efector';
		}
		
		// Dependencia administrativa
		if ($("#dependencia_adm").val() == null) {
			var rechazo = 1;
			var motivo = 'Seleccione una dependencia administrativa';
		}
		
		// Compromiso gestión e integrante
		if ($("#compromiso").val() == 'S' && $("#integrante").val() == 'S') {
			// Valido campos complementarios del compromiso de gestión
			
			// Numero compromiso
			if ($("#numero_compromiso").val() == $("#numero_compromiso").attr("title")) {
				var rechazo = 1;
				var motivo = 'Ingrese el número de compromiso de gestión';
			}
			
			// Firmante compromiso
			if ($("#firmante_compromiso").val() == $("#firmante_compromiso").attr("title")) {
				var rechazo = 1;
				var motivo = 'Ingrese el firmante del compromiso de gestión';
			}
			
			// Fecha suscripcion cg
			if ($("#fecha_sus_cg").val() == $("#fecha_sus_cg").attr("title")) {
				var rechazo = 1;
				var motivo = 'Ingrese la fecha de suscripción al compromiso de gestión';
			}
			
			// Fecha inicio cg
			if ($("#fecha_ini_cg").val() == $("#fecha_ini_cg").attr("title")) {
				var rechazo = 1;
				var motivo = 'Ingrese la fecha de inicio del compromiso de gestión';
			}
			
			// Fecha fin cg
			if ($("#fecha_fin_cg").val() == $("#fecha_fin_cg").attr("title")) {
				var rechazo = 1;
				var motivo = 'Ingrese la fecha de finalización del compromiso de gestión';
			}
			
			if ($("#pago_indirecto").val() == 'S') {
				
				// Numero convenio tercer administrador
				if ($("#numero_convenio").val() == $("#numero_convenio").attr("title")) {
					var rechazo = 1;
					var motivo = 'Ingrese número de convenio con el tercer administrador';
				}
				
				// Firmante convenio tercer administrador
				if ($("#firmante_convenio").val() == $("#firmante_convenio").attr("title")) {
					var rechazo = 1;
					var motivo = 'Ingrese el firmante del convenio';
				}
				
				// Nombre convenio tercer administrador
				if ($("#nombre_adm").val() == $("#nombre_adm").attr("title")) {
					var rechazo = 1;
					var motivo = 'Ingrese nombre del tercer administrador';
				}
				
				// Fecha suscripción convenio tercer administrador
				if ($("#fecha_sus_ca").val() == $("#fecha_sus_ca").attr("title")) {
					var rechazo = 1;
					var motivo = 'Ingrese la fecha de suscripción';
				}
				
				// Fecha inicio convenio tercer administrador
				if ($("#fecha_ini_ca").val() == $("#fecha_ini_ca").attr("title")) {
					var rechazo = 1;
					var motivo = 'Ingrese la fecha de inicio';
				}
				
				// Fecha fin convenio tercer administador
				if ($("#fecha_fin_ca").val() == $("#fecha_fin_ca").attr("title")) {
					var rechazo = 1;
					var motivo = 'Ingrese la fecha de finalización';
				}
			}
		}
		
		if (rechazo == 1) {
			$("#dialog_respuesta").html("El formulario posee errores, por favor corregirlos.")
			$("#dialog_respuesta").dialog({
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
			
			$.fancybox.showActivity();
			$.ajax({
				type : 'post' ,
				url  : 'modulos/efectores/abm/alta.php' ,
				data : 'acc=alta&' + $("#formu_alta").serialize() ,
				success : function (data) {
					$.fancybox.hideActivity();
					$("#dialog_respuesta").html(data);
					$("#dialog_respuesta").dialog({
						modal		: true ,
						title		: "Confirmación" ,
						width		: 350 ,
						resizable	: false ,
						show		: "fold" ,
						buttons		: {
							"Aceptar" : function (){
								$(this).dialog("close");
							}
						}
					});
				}
			});
		}
	});
	
	$("#siisa").blur(function(){
		if ($(this).val().length != 14) {
			$(this).parent().append("<span>Verifique que el código SIISA sea de 14 dígitos</span>");
		}
	}).focus(function(){
		$(this).parent().find("span").html("");
	});
	
	$("#nombre").blur(function(){
		if ($(this).val().length < 5) {
			$(this).parent().append("<span>Ingrese un nombre válido</span>");
		}
	}).focus(function(){
		$(this).parent().find("span").html("");
	});
	
	$("#tipo_efector , #categorizacion , #dependencia_adm , #provincia , #departamento , #localidad").blur(function(){
		if ($(this).val() == 0) {
			$(this).parent().append("<span>Seleccione un valor de la lista</span>");
		}
	}).focus(function(){$(this).parent().find("span").html("");});
	
	
	
	$("#tipo_efector").blur(function(){}).focus(function(){$(this).parent().find("span").html("");});
	$("#tipo_efector").blur(function(){}).focus(function(){$(this).parent().find("span").html("");});
	$("#tipo_efector").blur(function(){}).focus(function(){$(this).parent().find("span").html("");});
	$("#tipo_efector").blur(function(){}).focus(function(){$(this).parent().find("span").html("");});
	$("#tipo_efector").blur(function(){}).focus(function(){$(this).parent().find("span").html("");});
	$("#tipo_efector").blur(function(){}).focus(function(){$(this).parent().find("span").html("");});
	$("#tipo_efector").blur(function(){}).focus(function(){$(this).parent().find("span").html("");});
	$("#tipo_efector").blur(function(){}).focus(function(){$(this).parent().find("span").html("");});
	
});
</script>
<div style="display:none">
	<div id="dialog_respuesta"></div>
</div>
<div id="alta">
	<form id="formu_alta">
		<div id="generales">
			<div class="titulo">Generales</div>
			<table>
				<tr>
					<td>CUIE</td>
					<td><input id="cuie" name="cuie" type="text" readonly="readonly" value="<?php
					$sql = "
					select 
						substring (max (cuie) from 1 for 1) || (substring (max (cuie) from 2 for 5) :: int + 1) :: varchar as cuie
					from (
						select
							*
						from 
							efectores.efectores e left join 
							efectores.datos_geograficos d on e.id_efector = d.id_efector
						where substring (cuie from 2 for 5) :: int <> 99999 ) e
					where id_provincia = '$_SESSION[grupo]'";
					$res = pg_query ($sql);
					$reg = pg_fetch_row ($res);
					echo trim ($reg[0]);
					?>" title="cuie" /></td>
				</tr>
				<tr>
					<td>SIISA</td>
					<td><input id="siisa" name="siisa" type="text" title="SIISA"/></td>
				</tr>
				<tr>
					<td>Nombre</td>
					<td><input id="nombre" name="nombre" type="text" title="Nombre"/></td>
				</tr>
				<tr>
					<td>Tipo efector</td>
					<td>
						<select id="tipo_efector" name="tipo_efector" title="Tipo efector">
							<option value="0">Seleccione un tipo</option>
							<?php
							$sql = "select * from efectores.tipo_efector where id_tipo_efector <> 8";
							$res = pg_query ($sql);
							while ($reg = pg_fetch_assoc ($res)) {
								echo '<option value="' . $reg['id_tipo_efector'] . '">' . $reg['descripcion'] . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Rural</td>
					<td>
						<select id="rural" name="rural" title="rural">
							<option value="N">No</option>
							<option value="S">Si</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>CICS</td>
					<td>
						<select id="cics" name="cics" title="cics">
							<option value="N">No</option>
							<option value="S">Si</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Categorizaci&oacute;n</td>
					<td>
						<select id="categorizacion" name="categorizacion" title="categorizacion">
							<option value="0">Seleccione categor&iacute;a</option>
							<?php
							$sql = "select * from efectores.tipo_categorizacion where id_categorizacion <> 10";
							$res = pg_query ($sql);
							while ($reg = pg_fetch_assoc ($res)) {
								echo '<option value="' . $reg['id_categorizacion'] . '">' . $reg['descripcion'] . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Dependencia administrativa</td>
					<td>
						<select id="dependencia_adm" name="dependencia_adm" title="dependencia_adm">
							<option value="0">Seleccione dependencia</option>
							<?php
							$sql = "select * from efectores.tipo_dependencia_administrativa where id_dependencia_administrativa <> 5";
							$res = pg_query ($sql);
							while ($reg = pg_fetch_assoc ($res)) {
								echo '<option value="' . $reg['id_dependencia_administrativa'] . '">' . $reg['descripcion'] . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Dependencia sanitaria</td>
					<td><input id="dependencia_san" name="dependencia_san" type="text" title="dependencia_san" /></td>
				</tr>
				<tr>
					<td>Integrante</td>
					<td>
						<select id="integrante" name="integrante" title="integrante">
							<option value="N">No</option>
							<option value="S">Si</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Compromiso gesti&oacute;n</td>
					<td>
						<select id="compromiso" name="compromiso" title="compromiso">
							<option value="N">No</option>
							<option value="S">Si</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Alto impacto</td>
					<td>
						<select id="alto_impacto" name="alto_impacto" title="alto_impacto">
							<option value="N">No</option>
							<option value="S">Si</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Referente</td>
					<td><input id="referente" name="referente" type="text" title="referente" /></td>
				</tr>
			</table>
		</div>
		<div id="compromiso_gestion">
			<div class="titulo">Datos complementarios compromiso de gesti&oacute;n</div>
			<table>
				<tr>
					<td><input id="numero_compromiso" name="numero_compromiso" type="text" value="N&uacute;mero compromiso" title="N&uacute;mero compromiso" /></td>
					<td><input id="firmante_compromiso" name="firmante_compromiso" type="text" value="Firmante compromiso" title="Firmante compromiso" /></td>
					<td><input id="fecha_sus_cg" name="fecha_sus_cg" type="text" value="Fecha suscripci&oacute;n" title="Fecha suscripci&oacute;n" /></td>
					<td><input id="fecha_ini_cg" name="fecha_ini_cg" type="text" value="Fecha inicio" title="Fecha inicio" /></td>
					<td><input id="fecha_fin_cg" name="fecha_fin_cg" type="text" value="Fecha fin" title="Fecha fin" /></td>
				</tr>
				<tr>
					<td>Pago indirecto</td>
					<td>
						<select id="pago_indirecto" name="pago_indirecto" title="pago_indirecto">
							<option value="N">No</option>
							<option value="S">Si</option>
						</select>
					</td>
					<td>SUMAR</td>
					<td>
						<select id="sumar" name="sumar" title="sumar">
							<option value="N">No</option>
							<option value="S">Si</option>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<div id="convenio_administrativo">
			<div class="titulo">Convenio tercer administrador</div>
			<table>
				<tr>
					<td><input id="numero_convenio" name="numero_convenio" type="text" value="N&uacute;mero convenio" title="N&uacute;mero convenio" /></td>
					<td><input id="firmante_convenio" name="firmante_convenio" type="text" value="Firmante convenio" title="Firmante convenio" /></td>
					<td><input id="fecha_sus_ca" name="fecha_sus_ca" type="text" value="Fecha suscripci&oacute;n" title="Fecha suscripci&oacute;n" /></td>
					<td><input id="fecha_ini_ca" name="fecha_ini_ca" type="text" value="Fecha inicio" title="Fecha inicio" /></td>
					<td><input id="fecha_fin_ca" name="fecha_fin_ca" type="text" value="Fecha fin" title="Fecha fin" /></td>
				</tr>
				<tr>
					<td><input id="nombre_adm" name="nombre_adm" type="text" value="Nombre administrador" title="Nombre administrador" /></td>
					<td><input id="codigo_adm" name="codigo_adm" type="text" value="C&oacute;digo administrador" title="C&oacute;digo administrador" /></td>
				</tr>
			</table>
		</div>
		<div id="geograficos">
			<div class="titulo">Datos geogr&aacute;ficos</div>
			<table>
				<tr>
					<td>Provincia</td>
					<td>
						<select id="provincia" name="provincia" title="provincia">
							<?php
							if ($_SESSION['grupo'] == 25) {
								echo '<option value="0">Seleccione provincia</option>';
							}
							
							$sql = "select * from sistema.provincias order by id_provincia";
							$res = pg_query ($sql);
							while ($reg = pg_fetch_assoc ($res)) {
								$op = '<option value="' . $reg['id_provincia'] . '" ';
								
								if ($_SESSION['grupo'] < 25) {
									if ($_SESSION['grupo'] == $reg['id_provincia']) {
										$op .= 'selected="selected"';
									} else {
										$op .= 'disabled="disabled"';
									}
								}
								$op .= '>' . $reg['nombre'] . '</option>';
								echo $op;
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Departamento</td>
					<td>
						<select id="departamento" name="departamento" title="departamento" >
						<?php
						if ($_SESSION['grupo'] < 25) {
							echo '<option value="0">Seleccione un departamento</option>';
							$sql = "select * from efectores.departamentos where id_provincia = '$_SESSION[grupo]'";
							$res = pg_query ($sql);
							while ($reg = pg_fetch_assoc ($res)) {
								echo '<option value="' , $reg['id_departamento'] , '">' , $reg['nombre_departamento'] , '</option>';
							}
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Localidad</td>
					<td><select id="localidad" name="localidad" title="localidad"></select></td>
				</tr>
				<tr>
					<td>Ciudad</td>
					<td><input id="ciudad" name="ciudad" type="text" title="ciudad" /></td>
				</tr>
				<tr>
					<td>Domicilio</td>
					<td><input id="domicilio" name="domicilio" type="text" title="domicilio"/></td>
				</tr>
				<tr>
					<td>C&oacute;digo postal</td>
					<td><input id="codigo_postal" name="codigo_postal" type="text" title="codigo_postal" /></td>
				</tr>
			</table>
		</div>
		<div id="contacto">
			<div class="titulo">Direcci&oacute;n de correo electr&oacute;nico</div>
			<table>
				<tr>
					<td><input id="email" name="email" type="text" value="Email" title="Email" /></td>
					<td><input id="email_observaciones" name="email_observaciones" type="text" value="Observaciones" title="Observaciones" /></td>
				</tr>
			</table>
			<div class="titulo">N&uacute;mero de tel&eacute;fono</div>
			<table>
				<tr>
					<td><input id="telefono" name="telefono" type="text" value="N&uacute;mero" title="N&uacute;mero"/></td>
					<td>
						<select id="tipo_telefono" name="tipo_telefono" title="tipo_telefono">
							<?php
							$sql = "select * from efectores.tipo_telefono order by id_tipo_telefono";
							$res = pg_query ($sql);
							while ($reg = pg_fetch_assoc ($res)) {
								echo '<option value="' . $reg['id_tipo_telefono'] . '">' . $reg['descripcion'] . '</option>';
							}
							?>
						</select>
					</td>
					<td><input id="telefono_observaciones" name="telefono_observaciones" type="text" value="Observaciones" title="Observaciones" /></td>
				</tr>
			</table>
		</div>
		<input id="solicitar_alta" type="button" value="Solicitar alta" />
	</form>
</div>
<style>
	#alta table td {
		font-size: 11px !important;
	}
	
	.ui-tooltip {
		border-color: rgb(250,169,81);
	}
	
	.titulo {
		border: solid 0px;
		border-radius: 3px;
		background-color: rgb(153,204,95);
		text-align: center;
	}
	
	form span {
		color: red;
	}
</style>
