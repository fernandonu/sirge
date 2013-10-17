<?php
session_start();
$nivel = 1;
require '../seguridad.php';
require $ruta.'sistema/conectar_postgresql.php';
require $ruta.'sistema/conectar_postgresql.php';
require $ruta.'pdf/fpdf.php';

class PDF extends FPDF {
	function Header() {
	
		switch (date("m")) {
			case 1: $reg['mes'] = 'Enero';	break;
			case 2: $reg['mes'] = 'Febrero'; break;
			case 3: $reg['mes'] = 'Marzo'; break;
			case 4: $reg['mes'] = 'Abril'; break;
			case 5: $reg['mes'] = 'Mayo'; break;
			case 6: $reg['mes'] = 'Junio'; break;
			case 7: $reg['mes'] = 'Julio'; break;
			case 8: $reg['mes'] = 'Agosto'; break;
			case 9: $reg['mes'] = 'Septiembre'; break;
			case 10: $reg['mes'] = 'Octubre'; break;
			case 11: $reg['mes'] = 'Noviembre'; break;
			case 12: $reg['mes'] = 'Diciembre'; break;
			default: break;
		}
		
		$this->Image('../img/header_pdf.png',0,0,210);
		$this->Ln(15);
		$this->SetFont('helvetica','BUI',15);
		$this->Cell(20);
		$this->Cell(40,6,"INFORME DE CONSISTENCIA PRESTACIONAL DEL SIRGE");
		$this->SetFont('helvetica','B',10);
		$this->Ln();
		$this->Cell(70);
		$this->Cell(40,6,"Período: " . $reg['mes'] . " de " . date ("Y"));
		//$this->Cell(40,6,"Período: Septiembre 2012");
		$this->Line(0,40,400,40);
		$this->Ln(15);
		
	}
	
	function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
	}
	
	function provincias () {
		$sql = $_POST['provincia'] == '99' ? "select * from sistema.provincias" : "select * from sistema.provincias where id_provincia = '$_POST[provincia]'" ;
		//echo $sql; die();
		//$sql = "select * from sistema.provincias";
		$res = pg_query ($sql);
		while ($reg = pg_fetch_assoc ($res)) {
			$nombre[] = $reg['nombre'];
		}
		return $nombre;
	}
	
	function desc_controles () {
		$this->SetFont('helvetica','BUI',13);
		$this->Cell(70);
		$this->Cell(40,7,"Glosario de controles",0);
		$this->Ln();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"Fechas",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Indica la cantidad de registros que poseen una fecha de prestación fuera de rango lógico, siendo el mismo desde el 01/01/2004 al día de hoy. Este control ayuda a identificar aspectos a mejorar, en cuanto a validaciones, en los sistemas de facturación.");
		$this->Ln();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"CB-DNI",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Indica la cantidad de registros no poseen clave de beneficiario ni número de documento, excluyendo las prestaciones de comunidad. Este control ayuda a identificar aspectos a mejorar, en cuanto a validaciones, en los sistemas de facturación.");
		$this->Ln();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"CB <> 16",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Indica la cantidad de registros cuyo largo de la clave de beneficiario no sea de 16 dígitos, excluyendo las prestaciones de comunidad. Este control ayuda a identificar aspectos a mejorar, en cuanto a validaciones, en los sistemas de facturación.");
		$this->Ln();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"CUIE",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Indica la cantidad de registros cuyo largo del CUIE no sea de 6 caracteres. Este control ayuda a identificar aspectos a mejorar, en cuanto a validaciones, en los sistemas de facturación.");
		$this->Ln();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"Clase Doc",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Indica la cantidad de registros cuyo valor del campo 'clase_documento' no sea 'A' (Ajeno) o 'P' (propio).");
		$this->Ln();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"MPU23",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Muestra la cantidad de MPU23, posteriores al 01/01/2011, que no se diferencian de alguna forma si son 'A' o 'B'. Tener en cuenta que en este control verifica el campo 'subcodigo_prestacion' y cuenta como positivo el caso si se diferencian utilizando este campo. Dado que el código correcto de prestación es MPU23A o MPU23B, se deberá ajustar a este código en una etapa posterior.");
		$this->Ln();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"MEM07",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Muestra la cantidad de MEM07, posteriores al 01/01/2011, que no se diferencian de alguna forma si son 'A' o 'B'. Tener en cuenta que en este control verifica el campo 'subcodigo_prestacion' y cuenta como positivo el caso si se diferencian utilizando este campo. Dado que el código correcto de prestación es MPU23A o MPU23B, se deberá ajustar a este código en una etapa posterior.");
		$this->Ln();
		
		$this->AddPage();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"NPE42",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Muestra la cantidad de MEM07, posteriores al 01/01/2010, que no se diferencian de alguna forma si son 'A', 'B', 'C', 'D', 'E', 'F' o 'G'. Tener en cuenta que en este control verifica el campo 'subcodigo_prestacion' y cuenta como positivo el caso si se diferencian utilizando este campo. Dado que el código correcto de prestación es MPU23A o MPU23B, se deberá ajustar a este código en una etapa posterior.");
		$this->Ln();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"Códigos Ø",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Muestra la cantidad de códigos de prestaciones en blanco. Este control ayuda a identificar aspectos a mejorar, en cuanto a validaciones, en los sistemas de facturación.");
		$this->Ln();
		
		$this->SetFont('helvetica','BU',11);
		$this->Cell(40,7,"Sub códigos",0);
		$this->Ln();
		$this->SetFont('helvetica','',9);
		$this->Cell(20);
		$this->Write(
			8,
			"Muestra la cantidad de prestaciones que tienen un sub código cuando no corresponde (Excluyendo 'LMI46', 'LMI47', 'LMI48','LMI49', 'CMI65', 'CMI66', 'CMI67', 'NPE42', 'MEM07', 'MPU23'). Este control ayuda a identificar aspectos a mejorar, en cuanto a validaciones, en los sistemas de facturación.");
		$this->Ln();
		
	}
	
	function tabla_totales ($ids) {
		
		$this->SetFont('helvetica','BU',13);
		$this->Cell(70);
		$this->Cell(40,6,"TOTALES",0,0,"C");
		$this->Ln(10);
		
		$this->SetFont('helvetica','B',11);
		$this->SetTextColor(128);
		$this->SetDrawColor(128);
		$this->Cell(50);
		$this->Cell(40,10,"Provincia",0,0,"C");
		$this->Cell(40,10,"Cantidad",0,0,"C");
		$this->Ln();
		
		$this->SetTextColor(0);
		$this->SetFont('helvetica',"",9);
		$total = 0;
		for ($i = 1 ; $i <= count ($ids) ; $i ++) {
			$j = strlen($i) == 1 ? "0" . $i : $i ;
			$sql_2 = $_POST['provincia'] == 99 ? 
				"select count (*) 
				from prestaciones.p_$j" : 
				"select count (*) 
				from prestaciones.p_$_POST[provincia]";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$total += $reg_2[0];
			$this->Cell(50);
			$this->Cell(40,8,$ids[$i-1],"T");
			$this->Cell(40,8,number_format($reg_2[0]),"T",0,"R");
			$this->Ln();
		}
		$this->SetFont('helvetica','B',13);
		$this->Cell(50);
		$this->Cell(40,8,"Total","T");
		$this->Cell(40,8,number_format($total),"T",0,"R");
		$this->Ln();
	}
	
	function tabla_inmunizaciones ($ids) {
		$this->SetFont('helvetica','BU',13);
		$this->Cell(70);
		$this->Cell(40,6,"INMUNIZACIONES",0,0,"C");
		$this->Ln(10);
		
		$this->SetFont('helvetica','B',11);
		$this->SetTextColor(128);
		$this->SetDrawColor(128);
		$this->Cell(10);
		$this->Cell(40,10,"Provincia",0,0,"C");
		$this->Cell(40,10,"MPU23",0,0,"C");
		$this->Cell(40,10,"MEM07",0,0,"C");
		$this->Cell(40,10,"NPE42",0,0,"C");
		$this->Ln();
		
		$this->SetTextColor(0);
		$this->SetFont('helvetica',"",9);
		for ($i = 1 ; $i <= count ($ids) ; $i ++) {
			$j = strlen($i) == 1 ? "0" . $i : $i ;
			
			$sql_2 = $_POST['provincia'] == 99 ? 
				"select count (*)
				from prestaciones.p_$j
				where 
					codigo_prestacion = 'MPU23'
					and subcodigo_prestacion not in ('A','B')
					and fecha_prestacion >= '2011-01-01'":
				"select count (*)
				from prestaciones.p_$_POST[provincia]
				where 
					codigo_prestacion = 'MPU23'
					and subcodigo_prestacion not in ('A','B')
					and fecha_prestacion >= '2011-01-01'";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(10);
			$this->Cell(40,8,$ids[$i-1],"T");
			$this->Cell(40,8,number_format($reg_2[0]),"T",0,"R");
			
			$sql_2 = $_POST['provincia'] == 99 ? 
				"select count (*)
				from prestaciones.p_$j
				where 
					codigo_prestacion = 'MEM07'
					and subcodigo_prestacion not in ('A','B')
					and fecha_prestacion >= '2011-01-01'":
				"select count (*)
				from prestaciones.p_$_POST[provincia]
				where 
					codigo_prestacion = 'MEM07'
					and subcodigo_prestacion not in ('A','B')
					and fecha_prestacion >= '2011-01-01'";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(40,8,number_format($reg_2[0]),"T",0,"R");
			
			$sql_2 = $_POST['provincia'] == 99 ? 
				"select count (*)
				from prestaciones.p_$j
				where
					codigo_prestacion = 'NPE42'
					and subcodigo_prestacion not in ('A','B','C','D','E','F','G')
					and fecha_prestacion between '2010-01-01' and current_date":
				"select count (*)
				from prestaciones.p_$_POST[provincia]
				where
					codigo_prestacion = 'NPE42'
					and subcodigo_prestacion not in ('A','B','C','D','E','F','G')
					and fecha_prestacion between '2010-01-01' and current_date";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(40,8,number_format($reg_2[0]),"T",0,"R");

			$this->Ln();
		}
	}
	
	function tabla_varios ($ids) {
		$this->SetFont('helvetica','BU',13);
		$this->Cell(70);
		$this->Cell(40,6,"VARIOS",0,0,"C");
		$this->Ln(10);
		
		$this->SetFont('helvetica','B',11);
		$this->SetTextColor(128);
		$this->SetDrawColor(128);
		$this->Cell(5);
		$this->Cell(30,10,"Provincia",0,0,"C");
		$this->Cell(30,10,"Fechas",0,0,"C");
		$this->Cell(30,10,"CB-DNI",0,0,"C");
		$this->Cell(30,10,"CB <> 16",0,0,"C");
		$this->Cell(30,10,"CUIE",0,0,"C");
		$this->Cell(30,10,"Clase Doc",0,0,"C");
		$this->Ln();
		
		$this->SetTextColor(0);
		$this->SetFont('helvetica',"",9);
		for ($i = 1 ; $i <= count ($ids) ; $i ++) {
			$j = strlen($i) == 1 ? "0" . $i : $i ;
			
			$sql_2 = $_POST['provincia'] == 99 ? 
				"select case when sum (cantidad) is null then 0 else sum (cantidad) end as total
				from (
					select fecha_prestacion , count (*) as cantidad
					from prestaciones.p_$j
					where fecha_prestacion not between '2004-01-01' and current_date
					group by fecha_prestacion
					) a":
					"select case when sum (cantidad) is null then 0 else sum (cantidad) end as total
				from (
					select fecha_prestacion , count (*) as cantidad
					from prestaciones.p_$_POST[provincia]
					where fecha_prestacion not between '2004-01-01' and current_date
					group by fecha_prestacion
					) a";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(5);
			$this->Cell(30,8,$ids[$i-1],"T");
			$this->Cell(30,8,number_format($reg_2[0]),"T",0,"R");
			
			$sql_2 = $_POST['provincia'] == 99 ? 
				"select count (*) 
				from prestaciones.p_$j
				where 
					clave_beneficiario = '0' and numero_documento = 0
					and codigo_prestacion not like 'CMI%'":
				"select count (*) 
				from prestaciones.p_$_POST[provincia]
				where 
					clave_beneficiario = '0' and numero_documento = 0
					and codigo_prestacion not like 'CMI%'";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(30,8,number_format($reg_2[0]),"T",0,"R");
			
			$sql_2 = $_POST['provincia'] == 99 ? 
				"select count (*) 
				from prestaciones.p_$j
				where 
					length (clave_beneficiario) <> 16
					and codigo_prestacion not like 'CMI%'":
				"select count (*) 
				from prestaciones.p_$_POST[provincia]
				where 
					length (clave_beneficiario) <> 16
					and codigo_prestacion not like 'CMI%'";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(30,8,number_format($reg_2[0]),"T",0,"R");
			
			$sql_2 = $_POST['provincia'] == 99 ? 
				"select count(*) as cantidad
				from prestaciones.p_$j
				where length (cuie) <> 6":
				"select count(*) as cantidad
				from prestaciones.p_$_POST[provincia]
				where length (cuie) <> 6";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(30,8,number_format($reg_2[0]),"T",0,"R");
			
			$sql_2 = $_POST['provincia'] == 99 ?
				"select count (*) as cantidad
				from prestaciones.p_$j
				where clase_documento not in ('A','P')":
				"select count (*) as cantidad
				from prestaciones.p_$_POST[provincia]
				where clase_documento not in ('A','P')";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(30,8,number_format($reg_2[0]),"T",0,"R");
			
			$this->Ln();
		}
	}
	
	function tabla_prestaciones ($ids) {
		$this->SetFont('helvetica','BU',13);
		$this->Cell(80);
		$this->Cell(30,6,"INFORMACION PRESTACIONAL",0,0,"C");
		$this->Ln(10);
		
		$this->SetFont('helvetica','B',11);
		$this->SetTextColor(128);
		$this->SetDrawColor(128);
		$this->Cell(50);
		$this->Cell(30,10,"Provincia",0,0,"C");
		$this->Cell(30,10,"Códigos Ø",0,0,"C");
		$this->Cell(30,10,"Sub códigos",0,0,"C");
		$this->Ln();
		
		$this->SetTextColor(0);
		$this->SetFont('helvetica',"",9);
		for ($i = 1 ; $i <= count ($ids) ; $i ++) {
			$j = strlen($i) == 1 ? "0" . $i : $i ;
			
			$sql_2 = $_POST['provincia'] == 99 ?
				"select count(*) as cantidad
				from prestaciones.p_$j
				where length (codigo_prestacion) = 0":
				"select count(*) as cantidad
				from prestaciones.p_$_POST[provincia]
				where length (codigo_prestacion) = 0";;
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(50);
			$this->Cell(30,8,$ids[$i-1],"T");
			$this->Cell(30,8,number_format($reg_2[0]),"T",0,"R");
			
			$sql_2 = $_POST['provincia'] == 99 ?
				"select count (*)
				from prestaciones.p_$j
				where 
					length (cast (subcodigo_prestacion as character)) <> 0
					and codigo_prestacion not in ('LMI46','LMI47','LMI48','LMI49','CMI65','CMI66','CMI67','NPE42','MEM07','MPU23')":
				"select count (*)
				from prestaciones.p_$_POST[provincia]
				where 
					length (cast (subcodigo_prestacion as character)) <> 0
					and codigo_prestacion not in ('LMI46','LMI47','LMI48','LMI49','CMI65','CMI66','CMI67','NPE42','MEM07','MPU23')";
			$res_2 = pg_query ($sql_2);
			$reg_2 = pg_fetch_row ($res_2 , 0);
			$this->Cell(30,8,number_format($reg_2[0]),"T",0,"R");
			
			$this->Ln();
		}
	}
	
	function presentacion () {
		$this->SetFont('helvetica','',11);
		$this->Write(8, "Estimados responsables de operaciones:");
		$this->Ln(15);
		
		$this->Cell(10);
		$this->Write(7, "Les enviamos el informe de inconsistencias del SIRGE de este mes.");
		$this->Ln();
		
		$this->Cell(10);
		$this->Write(7, "Este documento se redacta con la finalidad de mejorar la calidad de información.");
		$this->Ln();
		
		$this->Cell(10);
		$this->Write(7, "Pedimos a quienes lo lean, le pongan mucha atención, ya que el SIRGE contiene información fundamental sobre la gestión de cada provincia.");
		$this->Ln();
		
		$this->Cell(10);
		$this->Write(7, "Tengan en cuenta que para poder realizar las correcciones necesarias, se deberá verificar la integridad de las bases de datos de los distintos sistemas de facturación y la base de datos que contiene el  SIRGE acumulado. (La misma se encuentra dentro de un archivo Access, el que se utiliza al momento de generar el archivo .uec que se remite a nosotros mensualmente)");
		$this->Ln();
		
		$this->Cell(10);
		$this->Write(7, "Otro aspecto importante a tener en cuenta, es que para corregir la mayor parte de estas inconsistencias, no es necesario modificar los sistemas de facturación, sino que con solo modificar el script .sql que se utiliza para exportar los datos es suficiente.");
		$this->Ln();
		
		$this->Cell(10);
		$this->Write(7, "Hemos notado una mejoría y una buena predisposición por parte de algunas jurisdicciones y agradecemos mucho su compromiso. De aquellas que aún no hemos recibido noticias, les pedimos encarecidamente que comiencen a trabajar con esto, ya que, como repetimos anteriormente, es información fundamental.");
		$this->Ln();
		
		$this->Cell(10);
		$this->Write(7, "De antemano ya saben que cuentan con todo el apoyo por parte de la UEC para cualquier duda o inconveniente que pueda llegar a surgir.");
		$this->Ln();
		
		$this->Cell(10);
		$this->Write(7, "A continuación les informamos los principales inconvenientes que surgen de los diferentes análisis. A medida que se vayan corrigiendo, se irán sumando otros.");
		$this->Ln();
		
		$this->Cell(10);
		$this->Write(7, "Desde ya agradecemos su colaboración.");
		$this->Ln(60);
		
		$this->Cell(140);
		$this->Write(7, "Área Operaciones - UEC");
		$this->Ln();
	}
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);
$data = $pdf->provincias();
$pdf->presentacion();
$pdf->AddPage();
$pdf->tabla_varios($data);
$pdf->AddPage();
$pdf->tabla_inmunizaciones($data);
$pdf->AddPage();
$pdf->tabla_prestaciones($data);
$pdf->AddPage();
$pdf->tabla_totales($data);
$pdf->AddPage();
$pdf->desc_controles();
$prov = $_POST['provincia'] == 99 ? "Completo" : $_POST['provincia'];
$pdf->Output("Informe SIRGE-$prov.pdf","D");
