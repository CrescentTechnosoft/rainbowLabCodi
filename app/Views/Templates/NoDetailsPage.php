<?php
$pdf=new FPDF();

$pdf->AddPage();

$pdf->Image(APPPATH.'Images/NoDetailFrown.png', 80, 50, 50, 50);
$pdf->AddFont("texgyrepagella", "B");
$pdf->SetFont('texgyrepagella', 'B', 20);

$pdf->SetX(0);
$pdf->SetY(30);
$pdf->MultiCell(200, 20, "No Details Found", 0, 'C');

$pdf->Output('I', "No Details Found.pdf");
