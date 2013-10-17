<?php
session_start();
require 'conectar_postgresql.php';

if (isset ($_POST['usuario'] , $_POST['password'])) {
	
	$usr = pg_escape_string (strtolower (trim ($_POST['usuario'])));
	$pwd = md5 ($_POST['password']);
	
	$sql = "
		select * 
		from sistema.usuarios 
		where 
			usuario = '$usr' 
			and password = '$pwd'";
	$res = pg_query ($sql);
	
	if (pg_num_rows ($res) == 1) {
		$reg = pg_fetch_assoc ($res);
		
		$_SESSION['grupo'] = $reg['id_entidad'];
		$_SESSION['menu'] = $reg['id_menu'];
		$_SESSION['descripcion'] = $reg['descripcion'];
		$_SESSION['id_usuario'] = $reg['id_usuario'];
		
		$sql = "
			insert into sistema.log_inicio_sesion
			values ( $_SESSION[id_usuario] , LOCALTIMESTAMP , '$_SERVER[REMOTE_ADDR]')";
		$res = pg_query ($sql);
		
		die ("ok");
		
	} else {
		die ("error");
	}
}