<?php
session_start();
$nivel = 3;
require '../../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

function mes_a_texto ($mes_numerico) {

	switch ($mes_numerico) {

		case 1	: return 'enero'; break;
		case 2	: return 'febrero'; break;
		case 3	: return 'marzo'; break;
		case 4	: return 'abril'; break;
		case 5	: return 'mayo'; break;
		case 6	: return 'junio'; break;
		case 7	: return 'julio'; break;
		case 8	: return 'agosto'; break;
		case 9	: return 'septiembre'; break;
		case 10	: return 'octubre'; break;
		case 11	: return 'noviembre'; break;
		case 12	: return 'diciembre'; break;
		default: die("Error!!"); 
	}
}

if (isset ($_POST['documento'])) {
	$mes = mes_a_texto ($_POST['mes']);
	
	$sql = "
	select *
	from (
		select * from osp.osp_02 union all
		select * from osp.osp_04 union all
		select * from osp.osp_06 union all
		select * from osp.osp_07 union all
		select * from osp.osp_09 union all
		select * from osp.osp_11 union all
		select * from osp.osp_12 union all
		select * from osp.osp_13 union all
		select * from osp.osp_14 union all
		select * from osp.osp_16 union all
		select * from osp.osp_19 union all
		select * from osp.osp_21 union all
		select * from osp.osp_23 union all
		select * from osp.osp_24) p left join osp.nombres_os n on p.codigo_os = n.codigo_os
	where 
		numero_documento = $_POST[documento]
		and tipo_documento = '$_POST[tipo_documento]'
		and " . $mes . "_" . $_POST['anio'] . " = 'S'";
	$res = pg_query ($sql);
	if (pg_num_rows ($res) > 0) {
		$reg = pg_fetch_assoc ($res);
		die (json_encode ($reg));
	} else {
		die ("0");
	}
}

?>

<script>
	$(document).ready(function(){
		
		$("#data_puco").hide();
		$("input:submit").button();

		$("form").submit(function(event){	
			event.preventDefault();
			
			$.ajax({
				type : 'post' ,
				url  : 'modulos/padron/tab-puco/consultas.php' ,
				data : $(this).serialize() ,
				success : function (data) {
					if (data == 0) {
						$("#dialog-confirm").html("Persona no encontrada");
						$("#dialog-confirm").dialog({
							title		: "<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0px 7px 0px 0px;\"></span> B&uacute;squeda PUCO" ,
							resizable	: false ,
							show		: "fold" ,
							modal		: true ,
							width		: 350 ,
							buttons		: {
								"Aceptar" : function () {
									$(this).dialog("close");
								}
							}
						}).prev().addClass("ui-state-error");
						
					} else {
						data = JSON.parse (data);
						
						console.log (data);
						
						$("#nombre").html(data["nombre_apellido"]);
						$("#documento").html(data["numero_documento"]);
						$("#tipo_documento").html(data["tipo_documento"]);
						$("#sexo").html(data["sexo"]);
						$("#os").html(data["nombre_os"]);
						
						switch (data["tipo_afiliado"]) {
							case 'T' : $("#transmite").html("Si"); break;
							case "A" : $("#transmite").html("No"); break;
							default  : $("#transmite").html("No especificado");
						}
						
						$("#data_puco").dialog({
							title		: "<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0px 7px 0px 0px;\"></span> B&uacute;squeda PUCO" ,
							resizable	: false ,
							show		: "fold" ,
							modal		: true ,
							width		: 500 ,
							buttons		: {
								"Aceptar" : function () {
									$(this).dialog("close");
								}
							}
						});
					}
				}
			});
		});
	});
</script>

<?php
$sql = "select * from sistema.tipo_documento";
$res = pg_query ($sql);
?>

<div class="consulta-puco">
	<form autocomplete="on">
		<table>
			<thead>
				<tr>
					<th>N&uacute;mero de documento</th>
					<th>Tipo</th>
					<th>Per&iacute;odo de b&uacute;squeda</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><input name="documento" type="text" /></td>
					<td>
						<select name="tipo_documento">
						<?php
							while ($reg = pg_fetch_assoc ($res)) {
								echo '<option value="' . $reg['tipo_documento'] . '">' . $reg['descripcion'] . '</option>';
							}
						?>
						</select>
					</td>
					<td>
						Mes <select name="mes">
							<?php
							for ($i = 1 ; $i <= 12 ; $i ++) {
								echo '<option ' ; 
								
								if ($i == date('m') - 1) {
									echo 'selected = "selected">' . $i . '</option>';
								} else {
									echo '>' . $i . '</option>';
								}
							}
							?>
						</select>
						 - A&ntilde;o 
						<select name="anio">
							<?php
							for ($i = date('Y') ; $i >= 2005 ; $i --) {
								echo '<option>' . $i . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="3"><input type="submit" value="Buscar" /></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<div style="display:none;">
	<div id="data_puco">
		<table>
			<tr>
				<td>Nombre y apellido</td>
				<td id="nombre"></td>
			</tr>
			<tr>
				<td>N&uacute;mero de documento</td>
				<td id="documento"></td>
			</tr>
			<tr>
				<td>Tipo de documento</td>
				<td id="tipo_documento"></td>
			</tr>
			<tr>
				<td>Sexo</td>
				<td id="sexo"></td>
			</tr>
			<tr>
				<td>C&oacute;digo Obra Social</td>
				<td id="os"></td>
			</tr>
			<tr>
				<td>Transmisor</td>
				<td id="transmite"></td>
			</tr>
		</table>
	</div>
	<div id="dialog-confirm"></div>
	<div id="dialog-respuesta"></div>
</div>