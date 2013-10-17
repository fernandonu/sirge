<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
require $ruta.'pdf/fpdf.php';

function verifica_fecha_impresion ($lote) {
	$sql = "select fecha_impresion_ddjj :: date from sistema.impresiones_ddjj where lote = $lote";
	$res = pg_query ($sql);
	
	if (pg_num_rows ($res)) {
		$fecha = pg_fetch_row ($res , 0);
		$fecha = explode ("-" , $fecha[0]);
		
		$retorno =  $fecha[2] . '/' . $fecha[1] . '/' . $fecha[0];
	} else {
		pg_query ("insert into sistema.impresiones_ddjj values (" . $_GET['lote'] . ")");
		$retorno = date("d/m/Y");
	}
	
	$res = pg_query ("select nombre from sistema.lotes l left join sistema.provincias p on l.id_provincia = p.id_provincia where l.lote = $lote");
	$id_provincia = pg_fetch_row ($res , 0);
	
	return utf8_decode (html_entity_decode ($id_provincia[0])) . ', ' . $retorno;
}

class PDF extends FPDF {
	
	function header () {
		$this->SetFont('Arial','B',11);
		$this->Image('../img/sumar-1.png',25,10,20);
		$this->Image('../img/min_logo.jpg',130,15,0,15);
		$this->Line(10,35,200,35);
		$this->Ln(25);
		$this->Cell(130);
		$this->Cell(25,8,verifica_fecha_impresion($_GET['lote']),0,0,'R');
		$this->Ln();
		$this->Cell(0,5,utf8_decode ("SEÑOR"));
		$this->Ln();
		$this->Cell(0,5,"DIRECTOR NACIONAL DEL PROGRAMA SUMAR");
		$this->Ln();
		$this->Cell(0,5,utf8_decode ("DR. MÁXIMO DIOSQUE"));
		$this->Ln();
		$this->SetFont('Arial','BU',11);
		$this->Cell(0,5,"S           /           D",0,0,'D');
		$this->SetFont('Arial','',11);
		$this->Ln(12);
		$this->Cell(0,8,utf8_decode("De mi mayor consideración:"));
		$this->Ln();
		$this->Cell(50);
	}
	
	function cuerpo ($id_padron) {

		$texto = "Por medio de la presente elevo a Ud. en carácter de Declaración Jurada, los resultados del proceso de validación de la información en el ";
		$nombre_sistema = "SIRGe Web ";
		$prestaciones = "correspondiente a las PRESTACIONES aprobadas desde la última presentación hasta el día de la fecha, detalladas en el siguiente cuadro:";
		$comprobantes = "correspondiente a los COMPROBANTES recibidos desde la última presentación hasta el día de la fecha, detallados en el siguiente cuadro:";
		$fondos		  = "correspondiente a las APLICACIONES DE FONDOS reportadas por los efectores desde la última presentación hasta el día de la fecha, detalladas en el siguiente cuadro:";
		
		$this->SetFont('Arial','',11);
		$this->Write(8,utf8_decode($texto));
		$this->SetFont('Arial','B',11);
		$this->Write(8,$nombre_sistema);
		$this->SetFont('Arial','',11);
		
		switch ($id_padron) {
			case 1:
				$this->Write(8,utf8_decode($prestaciones));
				$this->SetFont('Arial','BU',11);
				$this->Ln(10);
				$this->Cell(0,8,utf8_decode("INFORMACIÓN DE PRESTACIONES:"));
				break;
			case 2:
				$this->Write(8,utf8_decode($fondos));
				$this->SetFont('Arial','BU',11);
				$this->Ln(10);
				$this->Cell(0,8,utf8_decode("INFORMACIÓN DE APLICACION DE FONDOS:"));
				break;
			case 3:
				$this->Write(8,utf8_decode($comprobantes));
				$this->SetFont('Arial','BU',11);
				$this->Ln(10);
				$this->Cell(0,8,utf8_decode("INFORMACIÓN DE COMPROBANTES:"));
				break;
			default: die("ERROR");
		}
		$this->Ln(10);
	}
	
	function genera_tabla ($lote) {
		
		$encabezado = array ('LOTE','REGISTROS PRESENTADOS','REGISTROS ACEPTADOS','REGISTROS RECHAZADOS');
		
		$this->SetFont('Arial','B',8);
		$this->SetFillColor(220,220,220);
		
		foreach ($encabezado as $columna) {
			$this->Cell(40,8,$columna,1,0,'C',1);
		}
		$this->Ln();
		$this->SetFont('Arial','',9);
		
		$res = pg_query ("select * from sistema.lotes where lote = $lote");
		
		$reg_totales = 0;
		$reg_in 	 = 0;
		$reg_out	 = 0;
		
		while ($reg = pg_fetch_assoc($res)){
			
			$reg_totales += ($reg['registros_insertados'] + $reg['registros_rechazados']);
			$reg_in += $reg['registros_insertados'];
			$reg_out += $reg['registros_rechazados'];
			
			$this->Cell(40,8,$reg['lote'],1,0,'R');
			$this->Cell(40,8,number_format($reg['registros_insertados'] + $reg['registros_rechazados'] , 0 , ',' , '.'),1,0,'R');
			$this->Cell(40,8,number_format($reg['registros_insertados'] , 0 , ',' , '.'),1,0,'R');
			$this->Cell(40,8,number_format($reg['registros_rechazados'] , 0 , ',' , '.'),1,0,'R');
			$this->Ln();
		}
		$this->SetFont('Arial','B',8);
		$this->Cell(40,8,"TOTALES",1,0,'L',1);
		$this->SetFont('Arial','B',9);
		$this->Cell(40,8,number_format($reg_totales , 0 , ',' , '.'),1,0,'R',1);
		$this->Cell(40,8,number_format($reg_in , 0 , ',' , '.'),1,0,'R',1);
		$this->Cell(40,8,number_format($reg_out , 0 , ',' , '.'),1,0,'R',1);
		$this->Ln(15);
	}
	
	function saludo () {
		$this->SetFont('Arial','',11);
		$this->Cell(0,6,utf8_decode("Sin otro particular saludo a Ud. con mi consideración más distinguida"));
		$this->SetY(-30);
		$this->Cell(80);
		$this->Cell(80,6,utf8_decode("Firma y sello del Coordinador Ejecutivo"),'T',0,'C');
	}
	
	function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial','',8);
		$this->Line(10,280,200,280);
		$this->Cell(0,10,utf8_decode('Página '.$this->PageNo().'/{nb}'),0,0,'C');
	}
}

$pdf = new PDF();
$pdf->SetLeftMargin(25);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->cuerpo($_GET['padron']);
$pdf->genera_tabla($_GET['lote']);
$pdf->saludo();
$pdf->Output("DDJJ_$_GET[provincia]_Lote $_GET[lote].pdf",'D');
