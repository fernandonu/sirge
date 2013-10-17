<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

$sql_prov = "SELECT * FROM sistema.provincias";
$sql_padr = "SELECT * FROM sistema.tipo_padron WHERE idpadron <> 5";

if (isset ($_GET['pad'])) {
	switch ($_GET['pad']) {
		case 1:
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name ='p_24'";
			break;
		case 2:
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name ='f_24'";
			break;
		case 3:
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name ='c_24'";
			break;
		case 4:
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name ='n_24'";
			break;
		default:
			echo "Error!";
			break;
	}
} else {
	$sql = "SELECT column_name FROM information_schema.columns WHERE table_name ='p_24'";
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
	$('form input[type="submit"]').button();
	$('form input[type="button"]').button();
	
	$('#help').click(function(){
		$.fancybox({
			href : 'modulos/consultas/help_queries_dinamicos.html' ,
			centerOnScroll : true 
		});
	});
	
	$("#padron").change(function(){
		$("#campo").parent().load("modulos/consultas/consulta_dinamica.php?pad=" + $(this).val() +" #campo");
	});
	
	$("#filtros").on("change" , "#filtros tr:last-child td:last-child" , function() {
		$(this).parents("tr").clone().appendTo($(this).parents("table"));
	});
	
	$("#filtros").on("change" , "#filtros tr:last-child td:nth-child(2) select" , function() {
		$(this).parent().next().children().css("display","inline");
	});
	
	$("#filtros").on("keyup" , "#filtros tr td:nth-child(3) input" , function() {
		var campo = $(this).parents("tr").find("select").val();
		var valor = $(this).val();
		var provincia = $("#provincia").val();
		var padron = $("#padron").val();
		var condicion = $(this).parents("tr").find("#condicion").val();
		
		switch (condicion) {
			case 'BETWEEN':
				var valor_arr = $(this).val().split(",");
				var valor = $.trim(valor_arr[valor_arr.length-1]);
				$.ajax ({
					type : 'post' , 
					data : 'campo=' + campo + '&valor=' + valor + '&provincia=' + provincia + '&padron=' + padron ,
					url  : 'modulos/consultas/autocompletar.php' ,
					datatype : 'json' ,
					success : function (data) {
						availableTags = eval(data);
						function split( val ) {
							return val.split( /,\s*/ );
						}
						function extractLast( term ) {
							return split( term ).pop();
						}

						$( "#filtros tr:last-child td:nth-child(3) input" )
							// don't navigate away from the field on tab when selecting an item
							.bind( "keydown", function( event ) {
								if ( event.keyCode === $.ui.keyCode.TAB &&
										$( this ).data( "autocomplete" ).menu.active ) {
									event.preventDefault();
								}
							})
							.autocomplete({
								minLength: 0,
								source: function( request, response ) {
									// delegate back to autocomplete, but extract the last term
									response( $.ui.autocomplete.filter(
										availableTags, extractLast( request.term ) ) );
								},
								focus: function() {
									// prevent value inserted on focus
									return false;
								},
								select: function( event, ui ) {
									var terms = split( this.value );
									// remove the current input
									terms.pop();
									// add the selected item
									terms.push( ui.item.value );
									// add placeholder to get the comma-and-space at the end
									terms.push( "" );
									this.value = terms.join( ", " );
									return false;
								}
							});
						}
					});
				break;
			default:
				$.ajax({
					type : 'post' , 
					data : 'campo=' + campo + '&valor=' + valor + '&provincia=' + provincia + '&padron=' + padron ,
					url  : 'modulos/consultas/autocompletar.php' ,
					datatype : 'json' ,
					success : function (data) {
						availableTags = eval(data);
						$( "#filtros tr:last-child td:nth-child(3) input" ).autocomplete({
							source: availableTags
						});
					}
				});
		}
	});
	
	$('form').submit(function(){
		$.fancybox.showActivity();
		$.ajax({
			type:	'post',
			url: 	'modulos/recibe_queries.php' ,
			data: 	$(this).serialize() ,
			success:function(data) {
				$.fancybox(data);
			}
		});
		return false;
	});
});
</script>
<style>
table { border-collapse: collapse; width: 100%; text-align: center; }
tr { border-top: solid 1px #CCC; height: 40px; }

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
	<table>
		<tr>
			<td> Seleccione provincia a consultar </td>
			<td>
				<select name="provincia" id="provincia">
					<?php 
					if ($_SESSION['grupo'] <= 24) {
						$idprovincia =  (strlen ($_SESSION['grupo']) == 1) ? '0' . $_SESSION['grupo'] : $_SESSION['grupo'] ;
						echo "<option value=\"$idprovincia\" > $_SESSION[descripcion] </option>";
					} else {
						$res_prov = pg_query ($sql_prov);
						while ($reg_prov = pg_fetch_assoc ($res_prov)){
							echo "<option value=\"$reg_prov[idprovincia]\"> $reg_prov[nombre] </option>";
						}
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td> Seleccione el padr&oacute;n a consultar </td>
			<td>
				<select name="padron" id="padron">
					<?php
					$res_padr = pg_query ($sql_padr);
					while ($reg_padr = pg_fetch_assoc ($res_padr)){
						echo "<option value=\"$reg_padr[idpadron]\"> $reg_padr[nombre] </option>";
					}
					?>
				</select>
			</td>
		</tr>
	</table>
	<table id="filtros">
		<tr>
			<th> Campo </th>
			<th> Condici&oacute;n </th>
			<th> Valor buscado </th>
			<th> Nexo </th>
		</tr>
		<tr>
			<td>
				<select name="campo[]" id="campo">
				<option value=""> </option>
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
					<option value="BETWEEN" on> Entre </option>
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
	</table>
	<table>
		<tr>
			<td colspan="2"> <input type="submit" value="Consultar" /> </td>
		</tr>
		<tr>
			<td colspan="2"> <input type="button" id="help" value="Ayuda" /> </td>
		</tr>
	</table>
</form>