<?php
defined('BASEPATH') or die('Not Allowed');

$pdf=new FPDF('P', 'mm', [220,297]);
$pdf->AddPage();
$pdf->AddFont("texgyrepagella", "B");
$pdf->AddFont("texgyrepagella", "");

$pdf->SetFont('texgyrepagella', 'B', 10);

$pdf->SetWidths([50, 20, 30, 30,30,40]);

$pdf->Row(['Item Name','Batch No','Expiry Date','Tests Used','Billed By','Entered Time'], 7, true);
$pdf->SetFont('texgyrepagella', '', 10);
foreach ($Consumed as $cons) {
    $pdf->Row([$cons->ItemName,$cons->BatchNo,$cons->ExpiryDate,$cons->Tests,$cons->BilledBy,$cons->ReducedDate.' '.$cons->ReducedTime], 7, true);
}

$pdf->Output();
