<?php
defined('BASEPATH') or die('Not Allowed');

$pdf=new FPDF();
$pdf->AddPage();
$pdf->AddFont("texgyrepagella", "B");
$pdf->AddFont("texgyrepagella", "");

$pdf->SetFont('texgyrepagella', 'B', 10);

$pdf->SetWidths([50, 20, 30, 50,30]);

$pdf->Row(['Item Name','Batch No','Expiry Date','Vendor','Stock Available'], 7, true);
$pdf->SetFont('texgyrepagella', '', 10);
foreach ($Stocks as $stock) {
    $pdf->Row([$stock->ItemName,$stock->BatchNo,$stock->ExpiryDate,$stock->Vendor,$stock->StockAvailable], 7, true);
}

$pdf->Output();