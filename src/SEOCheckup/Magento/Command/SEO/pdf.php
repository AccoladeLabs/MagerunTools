<?php
require('fpdf.php');

class PDF extends FPDF
{
// Page header
function Header()
{
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(10,10,'SEO Checkup Report',0,0,'C');
    // Line break
    $this->Ln(20);
}
// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}
$array = explode("\n", file_get_contents('pdflines.txt'));
// Instanciation of inherited class
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);
if (file_exists('screenshot.png'))
{
	$pdf->Image('screenshot.png');
}
foreach($array as $element)
    $pdf->Cell(0,10,$element,0,1);
if (file_exists('SEO_Report_'.date("m.d.Y").'.pdf')){
	unlink('SEO_Report_'.date("m.d.Y").'.pdf');
}
$pdf->Output('F', 'SEO_Report_'.date("m.d.Y").'.pdf',"a+", 1);
//$pdf->Output('F', 'SEO_Report.pdf', 1);
unlink('pdflines.txt');
?>