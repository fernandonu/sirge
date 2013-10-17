<?php
session_start();
$nivel = 2;
require '../../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
?>
<script>
$(document).ready(function(){
	$('input:button').button();
	
	$("#grupo-usu").change(function(){
		$("#load-menu").load("modulos/admin/abm_menu.php?usu_grup=" + $(this).val() + " #arbol-menu", function(){
			$('input:submit').button();
		});
	});
	
	$("form").submit(function(){
		$.ajax({
			type : 'POST' ,
			url  : 'funciones/abm_menu.php' ,
			data : $(this).serialize() ,
			success : function(data){
				$.fancybox(data);
			}
		});
		return false;
	});
	
	$("#ocultar-alta").toggle(function () {
		$(".div-input").slideToggle();
		$(this).html("Mostrar");
	} ,
	function () {
		$(".div-input").slideToggle();
		$(this).html("Ocultar");
	});
});
</script>
<div class="div-input">
	<input type="button" value="Nuevo men&uacute;" />
</div>
<span id="ocultar-alta">Ocultar</span>
<hr />
<?php
$sql = 'SELECT * FROM sistema.menues order by id_menu asc';
$res = pg_query ($sql);
if (!$res) {
	die ("Ha ocurrido un error :" . pg_last_error());
} else { ?>
	
	<div class="div-tree-menu">
		<form>
			<table class="tree-menu">
				<tr>
					<td> Seleccione grupo : </td>
					<td>
						<select id="grupo-usu" name="grupo">
							<option value=""></option>
<?php					while ($reg = pg_fetch_assoc ($res)) { ?>
						<option value="<?php echo $reg['codigo']; ?>"> <?php echo $reg['descripcion']; ?> </option>
<?php					} ?>
						</select>
					</td>
				</tr>
			</table>
			<div id="load-menu"></div>
		</form>
	</div>
<?php
}

if (isset ($_GET['usu_grup'])) {
	$sql = "
		SELECT m.id , m.nombre , CASE  WHEN e.id_grupo IS NOT NULL THEN 1 ELSE 0 END AS activo , u.descripcion
		FROM sistema.modulos m
			LEFT JOIN (
				SELECT * FROM sistema.grupos_menu ee WHERE ee.id_grupo = $_GET[usu_grup]
			) e
				ON m.id = e.id_modulo
			LEFT JOIN sistema.grupos_usuarios u 
				ON e.id_grupo = u.codigo
		ORDER BY
			m.nivel_1 ,m.nivel_2";
	$res = pg_query ($sql);
	if (pg_num_rows ($res) > 1) { ?>
		<div id="arbol-menu">
			<table class="sub-tree-menu">
<?php		$reg = pg_fetch_assoc ($res); ?>
				<tr>
					<td colspan="2"> <?php echo $reg['descripcion']; ?> </td>
				</tr>
<?php		pg_result_seek ($res , 0);
			while ($reg = pg_fetch_assoc ($res)) { ?>
				<tr>
					<td> <?php echo $reg['nombre']; ?> </td>
					<td>
						<input type="checkbox" name="modulo[]" value="<?php echo $reg['id']; ?>" <?php if ($reg['activo'] == 1) { echo "checked=\"checked\""; }?> />
					</td>
				</tr> <?php
			} ?>
				<tr>
					<td colspan="2">
						<input type="submit" value="Modificar" />
					</td>
				</tr>
			</table>
		</div> <?php
	}
} ?>