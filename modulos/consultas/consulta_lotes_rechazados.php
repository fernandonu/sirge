<?php
session_start();
require '../../sistema/conectar_postgresql.php';
?>
<style>
.contenedor_data { text-align: center; }
.contenedor_data tbody tr { border-bottom: solid 1px #ccc; }
.contenedor_data tbody tr:hover { background-color: rgb(172,218,243); }
img { height: 25px; }
</style>

<script>
$(document).ready(function(){

	$('form').submit(function(event){
		
		//console.log ($(this).serialize());
		
		event.preventDefault();
		$.fancybox.showActivity();
		$.fancybox.resize();
		$.ajax({
			type : 'get' ,
			url  : 'modulos/consultas/consulta_lotes_rechazados.php' ,
			data : $(this).serialize() ,
			success : function (data) {
				$.fancybox(data);
			}
		});
	});
	
	$('.descargar').click(function(event){
		var lote = $(this).attr('lote');
		var padron = $(this).attr('padron');
		
		console.log (lote + padron);
		
		location.href = 'modulos/consultas/descarga_registros_rechazados.php?lote=' + lote + '&padron=' + padron;
	});
});
</script>


<?php
if (isset ($_GET['lote'])) {
	
	if (isset ($_GET['pagina_actual'])) {
		$pagina_actual = $_GET['pagina_actual'];
	} else {
		$pagina_actual = 1;
	}
	
	switch ($_GET['padron']) {
		case 1 : $sql = "
			select
				case
					when motivos like '%pkey%' then 'Registro ya informado o campo ORDEN mal generado'
					when motivos like '%fkey_codigo_prestacion%' then 'Codigo de prestacion se encuentra fuera de los existentes'
					when motivos like '%NPE42%' or motivos like '%MEM07%' or motivos like '%MPU23%' then motivos || ', debe especificar A, B, etc, segun corresponda, dentro del campo CODIGO'
					else motivos
				end as motivo
			, registro_rechazado
			, lote
			from prestaciones.rechazados
			where 
				lote = $_GET[lote]";
		break;
		case 2 : $sql = "
			select
				case
					when motivos like '%fkey_subcodigo_gasto%' then 'Sub codigo de gasto invalido'
					when motivos like '%fkey_codigo_gasto%' then 'Codigo de gasto invalido'
					else motivos
				end as motivos
				, registro_rechazado
				, lote
			from aplicacion_fondos.rechazados where lote = $_GET[lote]";
		break;
		case 3 : $sql = "
			select
				case
					when motivos like '%pkey%' then 'Tipo y numero de comprobantes ya existentes'
					else motivos
				end as motivo
				, registro_rechazado
				, lote
			from comprobantes.rechazados
			where
				lote = $_GET[lote]"; 
		break;
		default : die("ERROR");
	}
	$res = pg_query ($sql);
	
	$registros = pg_num_rows ($res);
	$paginas_totales = ceil ($registros / 25);
	
	$sql .= " limit 25 offset " . ($pagina_actual - 1) * 25;
	
	$res = pg_query ($sql);
	
	$columnas = pg_num_fields ($res);
?>
<table  class="contenedor_data">
	<thead>
		<tr>
			<?php
			for ($i = 0 ; $i < $columnas ; $i ++) {
				echo "<th>" , pg_field_name ($res , $i) , "</th>";
			}
			?>
		</tr>
	</thead>
	<tbody>
	<?php
		while ($reg = pg_fetch_row ($res)) {
			echo "<tr>";
				for ($i = 0 ; $i < $columnas ; $i ++) {
				echo "<td>" , $reg[$i] , "</td>";
			}
			echo "</tr>";
		}
		?>
	</tbody>
</table>
<form>
	<table>
		<tr>
			<td>	
				Ir a p&aacute;gina <input type="text" name="pagina_actual" size="1" value="<?php echo $pagina_actual; ?>" /> de <?php echo $paginas_totales; ?>
				<input type="hidden" name="lote" value="<?php echo $_GET['lote']; ?>" />
				<input type="hidden" name="padron" value="<?php echo $_GET['padron']; ?>" />
				<input type="submit" value="Ir" />
			</td>
			<td>
				<a href="#" class="descargar" lote="<?php echo $_GET['lote'];?>" padron="<?php echo $_GET['padron'];?>" ><img src="img/save.png" title="Descargar consulta" /></a>
			</td>
		</tr>
	</table>
</form>

<?php
}
?>
