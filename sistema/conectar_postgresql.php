<?php
$servidor	= "192.6.0.90";
$servidor	= "200.69.210.3";
$servidor	= "localhost";

$base		= "sirge";

$user		= "postgres";
//$user		= "projekt";

$password	= "cache8080";
//$password	= "propcp";

if(! $conn =  pg_connect("host=$servidor dbname=$base user=$user password=$password")) {
	die ("Error en la conexi�n a la base de datos");
}
?>
