<?php
$ruta 	= '';
$i		= 0;
while ($i < $nivel) {
	$ruta = "../".$ruta;
	$i++;
}

if (! (isset ($_SESSION['grupo']))) { ?>
	<p class="error" style="text-align: center; position: absolute; top: 100px; left: 150px; width: 400px; ">Su sesi&oacute;n ha expirado</p>
<?php
die();
} 
?>
