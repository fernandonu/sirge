<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
require $ruta.'pdf/fpdf.php';

function verifica_impresion ($lote) {
	$sql = "select fecha_impresion_ddjj :: date from sistema.impresiones_ddjj where lote = $lote";
	$res = pg_query ($sql);
	
	return $res;
}

class PDF extends FPDF {
	function header() {
		
		$res = verifica_impresion ($_GET['lote']);

		if (pg_num_rows ($res))
			$fecha = pg_fetch_row($res , 0);
		else {
			$sql = "insert into sistema.impresiones_ddjj values (" . $_GET['lote'] . ")";
			$res = pg_query ($sql);
			$fecha = pg_fetch_row(verifica_impresion ($_GET['lote'] , 0));
		}
		
		$lote = $_GET['lote'];
		$sql = "
			select 
				* 
			from 
				sistema.lotes l left join sistema.provincias p on l.id_provincia = p.id_provincia
			where lote = $lote";
		$res = pg_query ($sql);
		$reg = pg_fetch_assoc ($res);
		
		$a_fecha = explode ('-' , $fecha[0]);
		
		switch ($a_fecha[1]) {
			case 1: $a_fecha[1] = 'Enero';	break;
			case 2: $a_fecha[1] = 'Febrero'; break;
			case 3: $a_fecha[1] = 'Marzo'; break;
			case 4: $a_fecha[1] = 'Abril'; break;
			case 5: $a_fecha[1] = 'Mayo'; break;
			case 6: $a_fecha[1] = 'Junio'; break;
			case 7: $a_fecha[1] = 'Julio'; break;
			case 8: $a_fecha[1] = 'Agosto'; break;
			case 9: $a_fecha[1] = 'Septiembre'; break;
			case 10: $a_fecha[1] = 'Octubre'; break;
			case 11: $a_fecha[1] = 'Noviembre'; break;
			case 12: $a_fecha[1] = 'Diciembre'; break;
			default: break;
		}
		
		$sql_p = "select * from sistema.parametros where id = 1";
		$res_p = pg_query ($sql_p);
		$row_p = pg_fetch_assoc ($res_p);
		
		
		$fecha = $a_fecha[2] . ' de ' .  $a_fecha[1] . ' de ' . $a_fecha[0];
		
		$this->Image('../img/header_pdf.png',0,0,210);
		$this->SetFont('helvetica','B',13);
		$this->Ln(20);
		$this->Cell(155);
		$this->Cell(40,10,$row_p['valor'],0,0,'R');
		$this->Line(0,38,220,38);
		$this->Ln(10);
		$this->Cell(155);
		$this->Cell(40,10, utf8_decode (html_entity_decode ($reg['nombre'])) . ", " . $fecha,0,0,'R');
		$this->Ln(7);
		$this->Cell(40,10,"SEÑOR",0,0,'D');
		$this->Ln(7);
		$this->Cell(40,10,"DR. MAXIMO DIOSQUE",0,0,'D');
		$this->Ln(7);
		$this->SetFont('helvetica','BU',13);
		$this->Cell(40,10,"S           /           D",0,0,'D');
		$this->Ln(20);
		$this->SetFont('helvetica','',13);
		$this->Cell(40,10,"De mi mayor consideración:",0,0);
		$this->Ln(10);
		$this->Cell(40);
		
		switch ($reg['id_padron']) {
			case '1':
				$this->Write(7,"Por medio de la presente elevo a Ud., en carácter de declaración jurada, los resultados del proceso y validación de la información PRESTACIONAL, de carga inicial del año 2012, correspondientes al SIRGE, que arrojó los siguientes resultados");
				$this->Ln(10);
				$this->SetFont('helvetica','BU',13);
				$this->Cell(40,10,"Información prestacional:",0,0); 
				break;
			case '3':
				$this->Write(7,"Por medio de la presente elevo a Ud., en carácter de declaración jurada, los resultados del proceso y validación de la información de COMPROBANTES, de carga inicial del año 2012, correspondientes al SIRGE, que arrojó los siguientes resultados");
				$this->Ln(10);
				$this->SetFont('helvetica','BU',13);
				$this->Cell(40,10,"Información de comprobantes:",0,0);
				break;
		}
		$this->Ln(10);
		$this->Cell(40);
		$this->SetFont('helvetica','',13);
		$this->Cell(40,10,"Registros informados: " . ($reg['registros_insertados'] + $reg['registros_rechazados']) ,0,0);
		$this->Ln(7);
		$this->Cell(40);
		$this->Cell(40,10,"Registros aceptados: " . $reg['registros_insertados'] ,0,0);
		$this->Ln(7);
		$this->Cell(40);
		$this->Cell(40,10,"Registros rechazados: " . $reg['registros_rechazados'] ,0,0);
		$this->Ln(7);
		$this->Cell(40);
		$this->Cell(40,10,"Número de Lote procesado: " . $reg['lote'] ,0,0);
		
		$this->Ln(80);
		$this->Cell(40);
		$this->Cell(40,10,"Sin otro particular saludo a Ud. con mi consideración más distinguida.",0,0);
		
		$this->Ln(40);
		$this->Cell(110);
		$this->Cell(80,10,"Firma y sello del Coordinador Ejecutivo","T",0,"R");

	}

	function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
	}
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);
$pdf->Output("ddjj-" . $_GET['provincia'] . "-Lote" . $_GET['lote'] . ".pdf",'D');

?>
