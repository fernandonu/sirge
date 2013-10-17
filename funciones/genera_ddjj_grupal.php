<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
require $ruta.'pdf/fpdf.php';

$sql = "select * from sistema.lotes where lote not in (select lote from sistema.impresiones_ddjj) and id_provincia = '$_SESSION[grupo]' order by lote";
$res = pg_query ($sql);

while ($reg = pg_fetch_assoc ($res)) {
	$sql_in = 'insert into sistema.impresiones_ddjj values (' . $reg['lote'] . ')';
	$res_in = pg_query ($sql_in);
}
pg_result_seek ($res , 0);

class PDF extends FPDF {
	function header () {
		$sql_p = "select * from sistema.parametros where id = 1";
		$res_p = pg_query ($sql_p);
		$row_p = pg_fetch_assoc ($res_p);
		
		$sql_provincia = "select nombre from sistema.provincias where id_provincia = '$_SESSION[grupo]'";
		$res_provincia = pg_query ($sql_provincia);
		$row_provincia = pg_fetch_assoc ($res_provincia);
		
		$this->Image('../img/header_pdf.png',0,0,210);
		$this->SetFont('helvetica','B',13);
		$this->Ln(20);
		$this->Cell(155);
		$this->Cell(40,10,$row_p['valor'],0,0,'R');
		$this->Line(0,38,220,38);
		$this->Ln(10);
		$this->Cell(155);
		$this->Cell(40,10, utf8_decode (html_entity_decode ($row_provincia['nombre'])) . ", " . date("dMY"),0,0,'R');
		$this->Ln(7);
		$this->Cell(40,10, utf8_decode ("SEÑOR"),0,0,'D');
		$this->Ln(7);
		$this->Cell(40,10,"DR. MAXIMO DIOSQUE",0,0,'D');
		$this->Ln(7);
		$this->SetFont('helvetica','BU',13);
		$this->Cell(40,10,"S           /           D",0,0,'D');
		$this->Ln(20);
		$this->SetFont('helvetica','',13);
		$this->Cell(40,10, utf8_decode ("De mi mayor consideración:") ,0,0);
		$this->Ln(10);
	}
	
	function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->Cell(0,10,utf8_decode ('Página '.$this->PageNo().'/{nb}'),0,0,'C');
	}
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);

switch ($_GET['padron']) {
	case '1':
		$pdf->Write(7, utf8_decode ("Por medio de la presente elevo a Ud., en carácter de declaración jurada, los resultados del proceso y validación de la información PRESTACIONAL, de carga inicial del año 2013, correspondientes al SIRGE, que arrojó los siguientes resultados"));
		$pdf->Ln(10);
		$pdf->SetFont('helvetica','BU',13);
		$pdf->Cell(40,10,utf8_decode ("Información prestacional:"),0,0); 
		break;
	case '3':
		$pdf->Write(7,"Por medio de la presente elevo a Ud., en carácter de declaración jurada, los resultados del proceso y validación de la información de COMPROBANTES, de carga inicial del año 2013, correspondientes al SIRGE, que arrojó los siguientes resultados");
		$pdf->Ln(10);
		$pdf->SetFont('helvetica','BU',13);
		$pdf->Cell(40,10,"Información de comprobantes:",0,0);
		break;
}
$pdf->Ln(15);
$pdf->SetFont('Times','',12);


$cabecera = array ('Lote' , 'Registros presentados' , 'Registros acpetados' , 'Registros rechazados');

$pdf->Cell (15);
for ($i = 0 ; $i < count ($cabecera) ; $i++ )
	$pdf->Cell (40 , 8 , $cabecera[$i] , 1 , 0 , 'C');
$pdf->Ln();
while ($reg = pg_fetch_assoc ($res)) {
	$pdf->Cell(15);
	$pdf->Cell (40 , 7 , $reg['lote'] , 1 , 0 , 'R');
	$pdf->Cell (40 , 7 , $reg['registros_insertados'] + $reg['registros_rechazados'] , 1 , 0 , 'R');
	$pdf->Cell (40 , 7 , $reg['registros_insertados'] , 1 , 0 , 'R');
	$pdf->Cell (40 , 7 , $reg['registros_rechazados'] , 1 , 0 , 'R');
	$pdf->Ln();
}

$pdf->Ln(15);
$pdf->Cell(40,10,utf8_decode ("Sin otro particular saludo a Ud. con mi consideración más distinguida."),0,0);
$pdf->Ln(30);
$pdf->Cell(110);
$pdf->Cell(80,10,"Firma y sello del Coordinador Ejecutivo","T",0,"R");


$pdf->Output("ddjj-" . $_SESSION['grupo'] . ".pdf",'I');

?>
