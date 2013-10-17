<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';

switch ($_POST['acc']) {
	case 'edit-area':
		$area = htmlentities ($_POST['nom_area'], ENT_QUOTES , 'UTF-8');
		$obe = htmlentities ($_POST['obs_area'], ENT_QUOTES , 'UTF-8');
		
		$sql = "
			update sistema.areas
			set 
				nombre = '$area' ,
				observaciones = '$obe'
			where
				id = $_POST[id]";
		$res = pg_query ($sql);
		
		echo ($res) ? "Se ha realizado la modificaci&oacute;n con &eacute;xito" : "Se ha producido un error " . die (pg_last_error());
		
	break;
	
	case 'del-area':
		$sql = "
			delete from sistema.areas
			where id = $_POST[id]";
		$res = pg_query ($sql);
		if ($res) {
			echo "Area eliminada";
		} else {
			die (pg_last_error());
		}
	break;
	
	case 'ins-area':
		$area = htmlentities ($_POST['nom_area'], ENT_QUOTES , 'UTF-8');
		$obe = htmlentities ($_POST['obs_area'], ENT_QUOTES , 'UTF-8');
		
		$sql = "
			insert into sistema.areas (nombre , observaciones) 
			values ('$area' , '$obe')";
		$res = pg_query ($sql);
		
		echo ($res) ? "Area insertada" : "Se ha producido un error " . die (pg_last_error());
	break;
	
	case 'autocompletar-usr':
		$sql = "
			select *
			from sistema.usuarios
			where usuario = '$_POST[usuario]'";
		
		$res = pg_query ($sql);
		
		if (pg_num_rows ($res) == 0) {
			die('Error en selecci&oacute;n de usuario');
		} else {
			echo html_entity_decode (json_encode (pg_fetch_assoc ($res)) , ENT_QUOTES , 'UTF-8');
		}
	break;
	
	case 'edit-usr' :
		$nom = ucwords (strtolower (pg_escape_string (htmlentities (trim ($_POST['nombre']) , ENT_QUOTES , 'UTF-8'))));
		$mai = strtolower (pg_escape_string (htmlentities (trim ($_POST['email']))));
			
		$sql = "
			update sistema.usuarios
			set
				descripcion = '$nom'
				, email = '$mai'
				, jurisdiccion = '$_POST[juris]'
				, grupo = '$_POST[grupo]'
			where
				usuario = '$_POST[usuario]'";
		$res = pg_query($sql);
		
		echo $res ? "Usuario editado" : "Se ha producido un error " . die (pg_last_error());
	break;
	
	case 'del-usr':
		$nombre = trim ($_POST['nombre']);
		$sql = "
			delete from sistema.usuarios
			where usuario = '$nombre'";
		$res = pg_query ($sql);
		
		echo ($res) ? "Usuario eliminado" : "Se ha producido un error " . die (pg_last_error());
		
	break;
	
	case 'ins-usr':
		if (isset ($_POST['nombre'] , $_POST['usuario'] , $_POST['email'] , $_POST['juris'] , $_POST['grupo'] , $_POST['pass_1'])) {

			$nom = ucwords (strtolower (pg_escape_string (htmlentities (trim ($_POST['nombre']) , ENT_QUOTES , 'UTF-8'))));
			$usu = strtolower (pg_escape_string (htmlentities (trim ($_POST['usuario']))));
			$mai = strtolower (pg_escape_string (htmlentities (trim ($_POST['email']))));
			$jur = $_POST['juris'];
			$gru = $_POST['grupo'];
			$pwd = md5 (pg_escape_string (htmlentities (trim ($_POST['pass_1']))));
			
			$sql = "INSERT INTO sistema.usuarios (usuario , password , descripcion , email , activo , id_entidad , id_menu) VALUES ('$usu' , '$pwd' , '$nom' , '$mai' , 'S' , '$jur' , $gru)";
			$res = pg_query ($sql);
			
			echo ($res) ? "Usuario creado" : "Se ha producido un error: " . $sql . "<br />" . die (pg_last_error());
		} else {
			die ("Error en la generaci&oacute;n de usuario");
		}
	break;
	
	case 'edit-pass' :
		$pwd = md5 (pg_escape_string (htmlentities (trim ($_POST['password']))));
		
		$sql = "
			update sistema.usuarios
			set password = '$pwd'
			where usuario = '$_POST[usuario]'";
		
		$res = pg_query ($sql);
		
		echo $res ? "Password editada" : "Se ha producido un error " . die (pg_last_error());
	break;
	
	case 'del-archivo' :
		$archivo = trim ($_POST['file']);
		switch ($_POST['padron']) {
			
			
			case 'comprobantes' :
				$sql = "
					update sistema.cargas_archivos 
					set procesado = 'E' , fecha_baja = LOCALTIMESTAMP 
					where 
						nombre_actual = '$archivo'
						and id_padron = 3";
				$res = pg_query ($sql);
				
				if ($res) {
					if (unlink ("../upload/comprobantes/" . $archivo)) {
						echo "Archivo eliminado <br />";
					} else {
						echo "No se ha encontrado el archivo <br />";
					}
				} else {
					echo "No se ha podido actualizar la base de datos. Contacte al administrador del sistema.";
				}
			break;
			
			case 'fondos' : 
				$sql = "
					update sistema.cargas_archivos 
					set procesado = 'E' , fecha_baja = LOCALTIMESTAMP 
					where 
						nombre_actual = '$archivo'
						and id_padron = 2";
				$res = pg_query ($sql);
				
				if ($res) {
					if (unlink ("../upload/fondos/" . $archivo)) {
						echo "Archivo eliminado <br />";
					} else {
						echo "No se ha encontrado el archivo <br />";
					}
				} else {
					echo "No se ha podido actualizar la base de datos. Contacte al administrador del sistema.";
				}
			break;
			
			case 'prestaciones' :
				$sql = "
					update sistema.cargas_archivos 
					set procesado = 'E' , fecha_baja = LOCALTIMESTAMP 
					where 
						nombre_actual = '$archivo'
						and id_padron = 1";
				$res = pg_query ($sql);
				
				if ($res) {
					if (unlink ("../upload/prestaciones/" . $archivo)) {
						echo "Archivo eliminado <br />";
					} else {
						echo "No se ha encontrado el archivo <br />";
					}
				} else {
					echo "No se ha podido actualizar la base de datos. Contacte al administrador del sistema.";
				}
			break;
			
			case 'osp' : 
				$sql = "
					update sistema.cargas_archivos 
					set procesado = 'E' , fecha_baja = LOCALTIMESTAMP 
					where 
						nombre_actual = '$archivo'
						and id_padron = 6";
				$res = pg_query ($sql);
				
				if ($res) {
					if (unlink ("../upload/osp/" . $archivo)) {
						echo "Archivo eliminado <br />";
					} else {
						echo "No se ha encontrado el archivo <br />";
					}
				} else {
					echo "No se ha podido actualizar la base de datos. Contacte al administrador del sistema.";
				}
			break;
		}
	break;
	
	default: echo "Variable 'acc' no definida";
	break;
}
?>
