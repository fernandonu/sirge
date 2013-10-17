<?php
session_start();
require '../../sistema/conectar_postgresql.php'; ?>

<style>
/*
#gilla-datos { font-size : 11px; font-family : "Trebuchet MS"; }
#gilla-datos td , #gilla-datos th { border : solid 1px black; }
#gilla-datos th	{ text-transform : capitalize; background-color: #A0A0A0; color : white; }
#gilla-datos tr	{ height : 10px; }
#gilla-datos td	{ padding : 3px; }
#gilla-datos tbody tr:nth-child(even) { background-color: #EAEAEA; }
#gilla-datos tbody tr:hover { background-color: #ABCDEF; }
.paginado a	{ text-decoration : none; color : black; }
.anterior, .pagina, .siguiente { border : solid 1px #236BA3; margin : 3px; padding : 1px; float : left; text-align : center; cursor : pointer; font-size : 11px; }
.anterior, .siguiente { width : 60px; }
.pag_activa { color : white; background-color: #236BA3; }
.paginado { display : table; margin : 0px auto; }
.descarga {	text-align : center; }
*/


.tabla_datos { text-align: center; }
.tabla_datos tbody tr { border-bottom: solid 1px #ccc; }
.tabla_datos tbody tr:hover { background-color: rgb(172,218,243); }


</style>
<script>
	function carga (pag , id) {
		$.fancybox.showActivity();
		$.fancybox.resize();
		$.ajax({
			type : 'GET' ,
			url  : 'modulos/recibe_queries.php' ,
			data : 'pagina=' + pag + '&id=' + id ,
			success : function (data){
				$.fancybox(data);
			}
		});
	}
	
	$(document).ready(function(){
		$("#descarga").click(function(event){
			$.ajax({
				type : 'GET' ,
				url  : 'modulos/descarga_info.php'
			});
		});
	});
</script>
<?php
if (isset ($_POST['provincia'] , $_POST['padron'])) {
	
	$provincia 	= $_POST['provincia'];
	$padron 	= $_POST['padron'];
	$indice = sizeof ($_POST['campo']);
	
	for ($i = 0 ; $i < $indice ; $i++) {
		
		$campo[$i] 		= $_POST['campo'][$i];
		$condicion[$i] 	= $_POST['condicion'][$i];
		
		if ($condicion[$i] == 'BETWEEN') {
			$valores_between[] = explode ("," , pg_escape_string (trim ($_POST['valor_buscado'][$i])));
		} else {
			$valor[$i] = strtoupper (pg_escape_string (trim ($_POST['valor_buscado'][$i])));
		}

		if (isset ($_POST['logica'][$i])){
			$cond_log[$i] = $_POST['logica'][$i];
		}
	}
	
	$counter = 0;
	$sql = "SELECT * FROM ";
	
	switch ($padron) {
			case 1:
				$sql .= "prestaciones.p_" . $provincia . " j ";
				break;
			case 2:
				$sql .= "aplicacion_fondos.f_" . $provincia . " j ";
				break;
			case 3:
				$sql .= "comprobantes.c_" . $provincia . " j ";
				break;
			case 4:
				$sql .= "nomencladores.n_" . $provincia . " j ";
				break;
			default:
				die("Error!");
	}
	
	if (isset ($campo) && strlen($campo[0])) {
		$sql .= 'WHERE';
	}
	
	for ($i = 0 ; $i < $indice ; $i ++) {
		if (strlen ($campo[$i])) {
			$sql .= ' j.';
			if ($condicion[$i] == 'IN' || $condicion[$i] == 'NOT IN') {
				
				$pos = strpos ($valor[$i],",");
				
				if (!$pos){
					die("Use el caracter coma (,) como separador");
				}else{
					$valores_in = explode (",",$valor[$i]);
				}
				
				for ($j = 0 ; $j < count ($valores_in) ; $j ++){
					$valores_in[$j] = "'" . $valores_in[$j] . "'";
				}
				
				$valor[$i] = implode (",",$valores_in);
				
				$sql .= $campo[$i] . " " . $condicion[$i] . " (" . $valor[$i] . ")";
				
			} else if ($condicion[$i] == 'BETWEEN') {
				
				$sql .= $campo[$i] . " " . $condicion[$i] . " ('" . $valores_between[$counter][0] . "')" . " AND ('" . $valores_between[$counter][1] . "')";
				$counter ++;

			} else if ($condicion[$i] == 'LIKE') {
				$sql .= $campo[$i] . " " . $condicion[$i] . " '%" . $valor[$i] . "%' ";
				
			} else {
				$sql .= $campo[$i] . " " . $condicion[$i] . " '" . $valor[$i] . "'";
				
			}
		}
	
		if (isset ($cond_log[$i+1])) {
			$sql .= " " . $cond_log[$i] . " ";
		}
	}
	
	$_SESSION['sql_din'] = $sql;
	$id = 0;
	
	$rs = pg_query ($sql);
	
	if (!$rs) {
		die ($sql . ": Consulta mal generada, revise su sint&aacute;xis");
	} else {
		$sql_to_db = str_replace ("'","''",$sql);
		$sql_to_db = "
			insert into sistema.log_queries_din (id_usuario , consulta , timestamp) 
			values (" . $_SESSION['id_usuario'] . " , '". htmlentities ($sql_to_db) . "' , LOCALTIMESTAMP)";
		pg_query ($sql_to_db);
	}
	
} else if (isset ($_GET['id']) && $_GET['id'] <> 0){
		
		$id = $_GET['id'];
		
		$sql = "SELECT q.consulta FROM sistema.queries q WHERE q.id = ". $id;
		$rs = pg_query ($sql);
		if ($rs) {
			$rg = pg_fetch_assoc ($rs);
			$sql = $rg['consulta'];
			$rs = pg_query ($sql);
			if (!$rs){
				return "Error en sentencia SQL: " . $sql . "<br />";
			}
		}else{
			return "No se ha encontrado el Query";
		}
} else {
	$sql = $_SESSION['sql_din'];
	
	$rs = pg_query ($sql);
	
	if (!$rs) {
		echo $sql;
		die ("Consulta mal generada, por favor, revise su sint&aacute;xis");
	}
	
	$id = 0;
}

if (isset ($_GET['pagina'])){
	$pagina = $_GET['pagina'];
} else {
	$pagina = 1;
}

/**
	Cantidad de registros a mostrar por página
*/
$fx = 25;

/**
	Guardo el número de filas en la variable "$fi"
*/
$fi = pg_num_rows ($rs);

/**
	Guardo el número de columnas en la variable "$co"
*/
$co = pg_num_fields ($rs);

/**
	Agrego limite de filas al query
*/
$sql .= " LIMIT " . $fx . " OFFSET " . (($pagina - 1) * $fx);

$rs = pg_query ($sql);

if (!$rs) {
	die ("Error en sentencia SQL: " . $sql);
}

/**
	Cantidad de páginas a mostrar
*/
$pa = ceil ($fi / $fx); ?>

<table class="tabla_datos">
	<thead>
		<tr>
		<?php 
		if (pg_num_rows ($rs) == 0) {
			echo "No se han encontrado datos";
		} else {
			for ($i = 0 ; $i < $co ; $i++){
			echo '<th>' , pg_field_name ($rs , $i) , '</th>';
			}
		}
		?>
		</tr>
	</thead>
	<tbody>
		<?php 
		while ($rg = pg_fetch_row ($rs)) {
			echo '<tr>';
			for ($i = 0 ; $i < $co ; $i ++) {
				echo '<td>' , $rg[$i] , '</td>';
			}
			echo '</tr>';
		} 
		?>
	</tbody>
</table>

<div class="paginado">
<?php
	if ( $pagina != 1 ){
	?>
		<a class="anterior full" href="#" onClick="carga(<?php echo 1 . "," . $id; ?>);"> << </a>
		<a class="anterior" href="#" onClick="carga(<?php echo ($pagina - 1) . "," . $id; ?>);"> Anterior </a>
	<?php
	}
	if (($pagina + 2) > $pa){
		$pivotizq = $pa - $pagina;
	}else{
		$pivotizq = 2;
	}
	
	if (($pagina - 2) < 1){
		$pivotder = ($pagina - 1);
	}else{
		$pivotder = 2;
	}
	
	for ( $i = ($pagina - $pivotder) ; $i <= ($pagina + $pivotizq) ; $i ++){ ?>
		<a class="pagina <?php if ($pagina == $i) { echo "pag_activa"; } ?>" href="#" onClick="carga(<?php echo ($i) . "," . $id; ?>);"> <?php echo $i; ?> </a>
	<?php
	}
	if ( $pa != $pagina) { ?>
		<a class="siguiente" href="#" onClick="carga(<?php echo ($pagina + 1) . "," . $id; ?>);"> Siguiente </a>
		<a class="siguiente full" href="#" onClick="carga(<?php echo $pa . "," . $id; ?>);"> >> </a>
	<?php
	} ?>
</div>
<div class="descarga">
	<a id="descarga" href="modulos/descarga_info.php?id=<?php echo $id ?>"><img src="img/save.png" title="Descargar"></a>
</div>