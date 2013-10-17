<?php
session_start();
//die ("Estamos realizando mantenimiento. Disculpe las molestias");
$nivel = 0;
if (! (isset ($_SESSION['grupo']))) {
	
	$navegador = get_browser(null,true);
	
	if ($navegador['browser'] == 'IE') {
		die ("Por el momento, use Firefox, Chrome, Opera, Safari, Dolphin, cualquiera menos IE :)");
	} else {
		require 'vistas/formulario_login.php';
	}
} else {
	require 'seguridad.php';
	require 'vistas/general.php';
	require $ruta.'disenio/footer.php';
}
?>