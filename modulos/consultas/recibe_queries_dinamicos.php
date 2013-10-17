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
		event.preventDefault();
		$.fancybox.showActivity();
		$.fancybox.resize();
		$.ajax({
			type : 'get' ,
			url  : 'modulos/consultas/recibe_queries_dinamicos.php' ,
			data : $(this).serialize() ,
			success : function (data) {
				$.fancybox(data);
			}
		});
	});
	
	$('.descargar').click(function(event){
		location.href = 'modulos/consultas/descarga_consulta_dinamica.php' ;
	});
	
});
</script>
<?php
if (isset ($_GET['pagina_actual'])) {
	$pagina_actual = $_GET['pagina_actual'];
} else {
	if (isset ($_POST['padron'])) {
		$pagina_actual = 1 ;
		$sql = 'select * from ';
		
		switch ($_POST['padron']) {
			case 1:	$sql .= 'prestaciones.p_' . $_POST['provincia'] ; break;
			case 2:	$sql .= 'aplicacion_fondos.f_' . $_POST['provincia'] ; break;
			case 3:	$sql .= 'comprobantes.c_' . $_POST['provincia'] ; break;
			default: break;
		}

		if (strlen ($_POST['campo'][0]) > 0) {
			$sql .= ' where ';
			
			for ($i = 0 ; $i < sizeof ($_POST['campo']) ; $i ++) {
				$sql .= $_POST['campo'][$i] . ' ' . $_POST['condicion'][$i];
				
				if (strlen ($_POST['condicion'][$i]) > 0) {
					switch ($_POST['condicion'][$i]) {
						case 'BETWEEN':
							$valores = explode (',' , pg_escape_string (trim ($_POST['valor_buscado'][$i])));
							$sql .= " '" . $valores[0] . "' and '" . $valores[1] . "'";
							break;
						case 'IN' : 
							$valores = explode (',' , pg_escape_string (trim ($_POST['valor_buscado'][$i])));
							$sql .= " ('" . implode ("','" , $valores) . "')" ;
							break;
						case 'NOT IN' : 
							$valores = explode (',' , pg_escape_string (trim ($_POST['valor_buscado'][$i])));
							$sql .= " ('" . implode ("','" , $valores) . "')" ;
							break;
						case 'LIKE' : 
							$sql .= " '%" . pg_escape_string (trim ($_POST['valor_buscado'][$i])) . "%' ";
							break;
						default :
							$sql .= " '" . pg_escape_string (trim ($_POST['valor_buscado'][$i])) . "'";
					}
				}
			$sql .= ' ' . $_POST['logica'][$i] . ' ' ;
			}
		}
		$_SESSION['consulta'] = $sql;
	}
}
	
/** 
* Inserto el Query en BDD
**/

$sql_in_d = "insert into sistema.log_queries_din (id_usuario , consulta , timestamp) values ($_SESSION[id_usuario] , '" . pg_escape_string ($_SESSION['consulta']) . "' , localtimestamp)";
$res_in_d = pg_query ($sql_in_d);

if ($res = pg_query ($_SESSION['consulta'])) {
	
	
	$columnas = pg_num_fields ($res);
	$filas = pg_num_rows ($res);
	
	$lineas_por_pagina = 25 ;
	$paginas_totales = ceil ($filas / $lineas_por_pagina);
	
	$sql_part = $_SESSION['consulta'] . ' limit ' . $lineas_por_pagina . ' offset ' . ($pagina_actual - 1) * $lineas_por_pagina ;
	
	$res = pg_query ($sql_part);
	?>
	<table class="contenedor_data">
		<thead>
			<tr>
				<?php 
				if (pg_num_rows ($res) == 0) {
					echo "No se han encontrado datos";
				} else {
					for ($i = 0 ; $i < $columnas ; $i++){
						echo '<th>' , pg_field_name ($res , $i) , '</th>';
					}
				} 
				?>
			</tr>
		</thead>
		<tbody>
			<?php 
			while ($reg = pg_fetch_row ($res)) {
				echo '<tr>';
				for ($i = 0 ; $i < $columnas ; $i ++) {
					echo '<td>' , $reg[$i] , '</td>';
				}
				echo '</tr>';
			} 
			?>
		</tbody>
	</table>
	
	<form>
		<table>
			<tr>
				<td>	
					Ir a p&aacute;gina <input type="text" name="pagina_actual" size="1" value="<?php echo $pagina_actual; ?>" /> de <?php echo $paginas_totales; ?>
					<input type="submit" value="Ir" />
				</td>
				<td>
					<a href="#" class="descargar"><img src="img/save.png" title="Descargar consulta"/></a>
				</td>
			</tr>
		</table>
	</form>
<?php
} else {
	die ("Consulta mal generada, por favor, revise su sint&aacute;xis");
}
?>
