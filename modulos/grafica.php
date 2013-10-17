<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
require '../pchart/class/pData.class.php';
require '../pchart/class/pDraw.class.php';
require '../pchart/class/pImage.class.php';

$id_consulta = $_GET['id_consulta'];

$sql = "select consulta from sistema.queries where id = $id_consulta";
$res = pg_query ($sql);

if (!$res) {
	die ("Error");
} else {
	$reg = pg_fetch_assoc ($res);
	$sql = $reg['consulta'];
	$res = pg_query ($sql);
	$cant_col = pg_num_fields ($res);
	while ($reg = pg_fetch_row ($res)) {
		$i = 0;
		while ($i < $cant_col) {
			$campo[pg_field_name($res , $i)][] = $reg[$i];
			$i ++;
		}
	}
}

$MyData = new pData();
$MyData->loadPalette("../pchart/palettes/nacer.color", TRUE);
for ($i = 1 ; $i < $cant_col ; $i ++) {
	$MyData->addPoints($campo[pg_field_name($res , $i)] , pg_field_name($res , $i));
}
$MyData->addPoints($campo[pg_field_name($res , 0)],"absisa");
$MyData->setAbscissa("absisa");

$myPicture = new pImage(1200,700,$MyData);

$myPicture->drawGradientArea(0,0,1200,700,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100));
$myPicture->drawGradientArea(0,0,1200,700,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20));
$myPicture->setFontProperties(array("FontName"=>"../pchart/fonts/ERASLGHT.ttf","FontSize"=>11));
$myPicture->setGraphArea(50,30,1180,680);

$scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE,"DrawXLines"=>0); 
$myPicture->drawScale($scaleSettings); 
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>11));

$settings = array(
	"Gradient"=>TRUE
	,"DisplayPos"=>LABEL_POS_INSIDE
	,"DisplayValues"=>TRUE
	,"DisplayR"=>255
	,"DisplayG"=>255
	,"DisplayB"=>255
	,"DisplayShadow"=>TRUE
	,"Surrounding"=>10,
	);
	
$myPicture->drawBarChart($settings);
$myPicture->drawLegend(580,12,array("Style"=>LEGEND_ROUND,"Mode"=>LEGEND_VERTICAL));
$myPicture->autoOutput("pictures/example.drawBarChart.shaded.png");

?>