<?php

defined('BASEPATH') or exit('No direct script access allowed');

class PDF extends FPDF
{
    public $firstRow;
    public function Header()
    {
        // $this->Image(realpath('Images/Assets/logo.png'), 10, 6, 40, 25);
        $this->AddFont("texgyrepagella", "");
        $this->AddFont("texgyrepagella", "B");
        $this->AddFont("opensans", "B");

        $this->SetTextColor(120, 120, 120);
        $this->SetFont('opensans', 'B', 14);
        $this->Text(11, 14, 'From : VRWWCC');
        $this->SetFont('opensans', 'B', 10);
        $this->SetXY(10, 15);
        $this->MultiCell(70, 5, 'No.5, 1st Main Road,Mogappair West garden,Nolambur Phase II,' . PHP_EOL . 'Chennai-600037,Tamilnadu,India.');
        $this->SetXY(0, 32);
        $this->MultiCell(210, 7, "Phone : +91 63813 87482 | Email : info@vrwwcc.in | Website : www.vrwwcc.in", 1, 'C');
        $this->SetFont('opensans', 'B', 18);
        $this->Text(150, 15, 'Purchase Order');
        $this->SetFont('opensans', 'B', 9);
        $this->Text(150, 20, 'Date : '.$this->firstRow->EnteredDate);
    }

    public function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('times', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(100, 5, '');
        $this->Cell(80, 5, 'Authorized By', 0, 1, 'R');
    }
}

$pdf = new PDF();
$pdf->firstRow = $PurData[0];
$pdf->AddPage();
$pdf->AddFont("texgyrepagella", "B");
$pdf->AddFont("texgyrepagella", "");
$pdf->AddFont("opensans", "B");
$pdf->AddFont("opensans", "");

$pdf->SetFont('texgyrepagella', 'B', 15);
$pdf->SetTextColor(110, 110, 110);

$pdf->Cell(110, 7, "To", 0, 0);

$pdf->Cell(32, 7, "Ship To : ", 0, 0);
$pdf->Cell(25, 7, " ", 0, 1);

$pdf->SetFont('texgyrepagella', 'B', 10);
$pdf->Cell(110, 5, $pdf->firstRow->VendorName, 0, 0);

$pdf->Cell(32, 5, "VRWWCC", 0, 1);
$y=$pdf->GetY();
$pdf->MultiCell(110, 5, str_replace('<br />', '', $Vendor->Address).PHP_EOL.$Vendor->ContactNo, 0, 'L');

$pdf->SetXY(120, $y);
$pdf->MultiCell(90, 5, 'No.5, 1st Main Road,Mogappair West garden, Nolambur Phase II, Chennai-600037'.PHP_EOL.'63813 87482', 0, 'L');

$pdf->ln(3);

$pdf->SetFont("times", 'B', 10);
$pdf->SetFillColor(120, 120, 120);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(255, 255, 255);

$pdf->Cell(15, 7, "  S NO", 1, 0, 'L', true);
$pdf->Cell(60, 7, "PRODUCT NAME", 1, 0, 'L', true);
$pdf->Cell(45, 7, "BRAND NAME", 1, 0, 'L', true);
$pdf->Cell(30, 7, 'Pack', 1, 0, 'L', true);
$pdf->Cell(20, 7, "QTY", 1, 0, 'L', true);
$pdf->Cell(20, 7, 'Amount', 1, 1, 'R', true);

$pdf->ln(2);
//Bill Details Starts here
$pdf->SetTextColor(0, 0, 0);
$pdf->SetWidths([15, 60, 45,30,20, 20]);

$pdf->SetAligns(['R', 'L', 'L', 'L','L','R']);
$pdf->SetFont('texgyrepagella', 'B', 9);

$pdf->SetTextColor(128, 128, 128);
$sNo = 1;
$total=0;
foreach ($PurData as $result) {
    $pdf->Row([$sNo." .", $result->ItemName, $result->BrandName,$result->Pack, $result->Qty,$result->Amount], 6, false);
    $sNo += 1;
    $total+=floatval($result->Amount);
}
$pdf->Ln(5);
$pdf->Cell(160, 6, "Total", 0, 0, 'R');
$pdf->AddFont('rupee', '');
$pdf->SetFont('rupee', '', 10);
$pdf->Cell(30, 6, "`." . sprintf('%0.2f',$total), 0, 1, 'R');
//New Line
$pdf->Ln(10);

$pdf->Output('', 'Bill #' . $pdf->firstRow->PurchaseNo . ".pdf", 1);
