<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql_provincias = "select * from sistema.provincias order by id_provincia";
$sql_padron = "select * from sistema.tipo_padron where id_padron in (1,2,3)";

if (isset ($_GET['pad'])) {
	switch ($_GET['pad']) {
		case 1:
			$sql = "
				select * , column_name 
				from information_schema.columns 
				where 
					table_name ='p_24' 
					and ordinal_position not in (4)
				order by ordinal_position";
			break;
		case 2:
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name ='f_24'";
			break;
		case 3:
			$sql = "
				select column_name 
				from information_schema.columns 
				where 
					table_name ='c_24'
					and ordinal_position not in (12)
				order by ordinal_position";
			break;
		case 4:
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name ='n_24'";
			break;
		default:
			echo "Error!";
			break;
	}
} else {
	$sql = "
		select * , column_name 
		from information_schema.columns 
		where 
			table_name ='p_24' 
			and ordinal_position not in (4)
		order by ordinal_position";
}

$res = pg_query ($sql);
if (!$res){
	die ("Error general!!");
} else {
	$co  = pg_num_fields ($res);
}

?>
<script>
$(document).ready(function(){
	$('input:submit').button();
	$('input:button').button();
	
	$('#padron').change(function(){
		var padron = $(this).val();
		$('#campo').parent().load('modulos/consultas/consulta_dinamica.php?pad=' + padron + ' #campo');
	});
	
	$("#filtros").on("change" , "#filtros tr:last-child td:last-child" , function(){
		var linea_clon = $(this).parents("tr").clone();
		var contenedor_clones = $(this).parents("table");
		
		linea_clon.find("#valor_buscado").val("");
		linea_clon.appendTo(contenedor_clones);
		
	});
	
	$("#filtros").on("change" , "#filtros tr:last-child td:nth-child(2) select" , function (){
		$(this).parent().next().children().css("display","inline");
	});

	$("#filtros").on("focus" , "#filtros tr td:nth-child(3) input" , function (){
		var campo = $(this).parents("tr").find("select").val();
		
		
		
		if (campo.substring (0 , 5) == 'fecha') {
			$(this).datepicker({
				dateFormat	: "yy-mm-dd" ,
				changeMonth	: true ,
				changeYear	: true , 
				showOtherMonths: true ,
				selectOtherMonths: true ,
				showAnim : 'fold'
			});
		} else if (campo == 'cuie') {
			$(this).datepicker('destroy');
			
			var condicion = $(this).parent().prev().find("select").val();
			
			$(this).keyup(function(){
				var busqueda = $(this).val();
				console.log (busqueda);
				
				switch (condicion) {
					case 'BETWEEN' :
					break;
					
					default :
						$.ajax ({
							type : 'post' , 
							data : 'campo=' + campo + '&valor=' + busqueda ,
							url  : 'modulos/consultas/autocompletar.php' ,
							success : function (data) {
								availableTags = JSON.parse (data);
								$("#filtros tr:last-child td:nth-child(3) input").autocomplete({
									source: availableTags
								});
							}
						});
				}
			});
		} else {
			$(this).datepicker('destroy');
		}
	});
	
	$('form').submit(function(event){
		event.preventDefault();
		$.fancybox.showActivity();
		$.ajax({
			type:	'post',
			url: 	'modulos/consultas/recibe_queries_dinamicos.php' ,
			data: 	$(this).serialize() + '&fuente=dinamico' ,
			success:function(data) {
				$.fancybox(data);
			}
		});
	});
});
</script>
<style>

.ui-autocomplete {
	max-height: 100px;
	overflow-y: auto;
	/* prevent horizontal scrollbar */
	overflow-x: hidden;
	/* add padding to account for vertical scrollbar */
	padding-right: 20px;
}
/* IE 6 doesn't support max-height
 * we use height instead, but this forces the menu to always be this tall
 */
* html .ui-autocomplete {
	height: 100px;
}
</style>

<form>
	<table class="filtro_superior">
		<thead>
			<tr>
				<th>Provincia</th>
				<th>Base de datos</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<select name="provincia" id="provincia">
						<?php
						$res_provincias = pg_query ($sql_provincias);
						while ($reg_provincias = pg_fetch_assoc ($res_provincias)) {
							
							$op = '<option value="' . $reg_provincias['id_provincia'] . '" ';
							
							if ($_SESSION['grupo'] < 25) {
								if ($_SESSION['grupo'] == $reg_provincias['id_provincia']) {
									$op .= 'selected="selected"';
								} else {
									$op .= 'disabled="disabled"';
								}
							}
							
							$op .= '>' . $reg_provincias['nombre'] . '</option>';
							echo $op;
						}
						?>
					</select>
				</td>
				<td>
					<select name="padron" id="padron">
						<?php
						$res_padron = pg_query ($sql_padron);
						while ($reg_padron = pg_fetch_assoc ($res_padron)) {
							echo '<option value="' . $reg_padron['id_padron'] . '">' . $reg_padron['nombre'] . '</option>';
						}
						?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<table id="filtros" class="filtro_inferior">
		<thead>
			<tr>
				<th>Campo</th>
				<th>Condici&oacute;n</th>
				<th>Valor buscado</th>
				<th>Nexo</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<select name="campo[]" id="campo">
					<option value=""></option>
					<?php
					while ($reg = pg_fetch_assoc ($res)) {
						echo "<option value=\"$reg[column_name]\"> $reg[column_name] </option>";
					}
					?>
					</select>
				</td>
				<td>
					<select name="condicion[]" id="condicion">
						<option value=""></option>
						<option value="="> Igual </option>
						<option value="<>"> Distinto </option>
						<option value=">"> Mayor que </option>
						<option value=">="> Mayor o igual que </option>
						<option value="<"> Menor que </option>
						<option value="<="> Menor o igual que </option>
						<!--<option value="BETWEEN" on> Entre </option>-->
						<option value="LIKE"> Contiene a </option>
						<option value="IN"> En </option>
						<option value="NOT IN"> No en </option>
					</select>
				</td>
				<td>
					<input style="display: none;" id="valor_buscado" name="valor_buscado[]" type="text"/> <br />
					<div id="autocompletar-data"></div>
				</td>
				<td>
					<select name="logica[]" id="logica">
						<option value=""> </option>
						<option value="AND"> Y </option>
						<option value="OR"> O </option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<br />
	<table style="text-align: center; margin: 0px auto">
		<tr>
			<td colspan="2"><input type="submit" value="Consultar" /></td>
			<td colspan="2"><input type="button" id="help" value="Ayuda" /></td>
		</tr>
	</table>
</form>
