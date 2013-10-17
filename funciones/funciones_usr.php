<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require '../phpmailer/class.phpmailer.php';
require '../phpmailer/class.smtp.php';
require $ruta.'sistema/conectar_postgresql.php';

switch ($_POST['acc']) {
	case 'password':
		$pass_old = md5 ($_POST['pass_1']);
		$pass_new = md5 ($_POST['pass_2']);
		$sql = "SELECT * FROM sistema.usuarios WHERE id_usuario = $_SESSION[id_usuario] AND password = '$pass_old'";
		
		$res = pg_query ($sql);
		if (pg_num_rows ($res) == 1) {
			$sql = "UPDATE sistema.usuarios SET password = '$pass_new' WHERE id_usuario = $_SESSION[id_usuario]";
			$res = pg_query ($sql);
			if ($res) {
				echo "Contrase&ntilde;a cambiada con &eacute;xito";
			} else {
				die ("Ha ocurrido un error");
			}
		} else {
			die ("Contrase&ntilde;a incorrecta");
		}
	break;
	case 'email':
		$mail = htmlentities ($_POST['mail_1'] , ENT_QUOTES , 'UTF-8');
		$sql = "UPDATE sistema.usuarios SET email = '$mail' WHERE id_usuario = $_SESSION[id_usuario]";
		$res = pg_query ($sql);
		if ($res) {
			echo "Se ha cambiado su direcci&oacute;n de correo electr&oacute;nico";
		} else {
			die ("Se ha producido un error");
		}
	break;
	case 'sugerencia':
		$remi = $_POST['remitente'];
		$text = nl2br ($_POST['texto']);

		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = "ssl";
		$mail->Host = "smtp.gmail.com";
		$mail->Port = 465;
		$mail->Username = "sistemasuec@gmail.com";
		$mail->Password = "riv@davia875";

		$mail->FromName = "SIRGE 2.0";
		$mail->Subject = "Sugerencia";

		$mail->AddAddress("sistemasuec@gmail.com", "Sistemas UEC");

		$body  = $text . "<br /><hr />" . $remi;
		$mail->Body = $body;

		$mail->IsHTML(true);
		if(!$mail->Send()) {
			echo "Error: " . $mail->ErrorInfo;
		}else{
			echo "Sugerencia enviada con &eacute;xito, gracias.";
		}

		/**
			Guardo el mensaje en la BDD
		*/
		$sql = "INSERT INTO sistema.sugerencias (id_usuario , sugerencia) VALUES ($_SESSION[id_usuario] , '" . htmlentities ($text, ENT_QUOTES , 'UTF-8') ."')" ;
		$res = pg_query ($sql);
	break;
	default: echo "Variable 'acc' no definida";
	break;
}

?>
