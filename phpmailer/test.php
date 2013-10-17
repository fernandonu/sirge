<?php
include "class.phpmailer.php";
include "class.smtp.php";
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Host = "smtp.gmail.com";
$mail->Port = 465;
$mail->Username = "gdhekel@gmail.com";
$mail->Password = "S7p3rn0v115!";

$mail->From = "gdhekel@gmail.com";
$mail->FromName = "Gustavo D. Hekel";
$mail->Subject = "Prueba";
//$mail->AltBody = "Este es un mensaje de prueba.";
//$mail->MsgHTML("<b>Este es un mensaje de prueba</b>.");

$mail->AddAddress("gustavo.hekel@gmail.com", "Gustavo Personal");

$body  = "Hola <strong>amigo</strong><br>";
$body .= "probando <i>PHPMailer<i>.<br><br>";
$body .= "<font color='red'>Saludos</font>";
$mail->Body = $body;

$mail->IsHTML(true);
if(!$mail->Send()) {
	echo "Error: " . $mail->ErrorInfo;
}else{
	echo "Mensaje enviado correctamente";
}
