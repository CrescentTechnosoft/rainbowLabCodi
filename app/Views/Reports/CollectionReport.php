<?php

class PDF extends FPDF
{
    public $total = 0;
    public $startDate;
    public $endDate;

    public function Header()
    {
        $this->SetFont('times', 'B', 14);
        $this->Cell(295, 10, "Daily Collection Report of $this->startDate", 0, 1, 'C');
        $this->Line(118, $this->GetY() - 2, 197, $this->GetY() - 2);
    }

    public function Footer()
    {
        // Position at 1.5 cm from bottomz
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', [295, 297]);
$pdf->AliasNbPages();
$pdf->startDate = $Date->start;
$pdf->AddPage();

$pdf->SetAligns(['R',  'R', 'L', 'L', 'R', 'R', 'R', 'R', 'R', 'L']);
$pdf->SetWidths([10, 15, 20, 50, 20, 20, 20, 20, 20, 20,60]);
$pdf->SetFont('times', 'B', 12);
$pdf->Cell(250, 10, 'Lab Collection', 0, 1, 'C');
$pdf->SetFont('times', 'B', 10);
$pdf->Row(['S No', 'Bill No', 'Bill Date', 'Patient Name', 'Total', 'Discount', 'Sub Total', 'Initial Paid', 'Due', 'Type','Consultant']);
$pdf->SetFont('times', '', 8);
$pdf->SetFillColor(200, 0, 0);

$sNo = 1;
$total = 0;
$discount = 0;
$subTotal = 0;
$initialPaid = 0;
$totalDuePaid = 0;
$due = 0;

$totalCash = 0;
$totalCard = 0;
$totalOthers = 0;
foreach ($LabData as $detail) {
    if ($detail->BillType === 'Cash') {
        $totalCash += floatval($detail->InitialPaid);
    } elseif ($detail->BillType === 'Card') {
        $totalCard += floatval($detail->InitialPaid);
    } else {
        $totalOthers += floatval($detail->InitialPaid);
    }
    
//    $pdf->SetWidths([10, 15, 15, 20, 40, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20]);
    $hasDue = floatval($detail->DueAmount) > 0;
    $feesType = floatval($detail->InitialPaid) == 0 ? '' : ($detail->BillType === 'Others' ? $detail->OtherPayments : $detail->BillType);
    $pdf->Cell(10, 6, $sNo, 1, 0, 'R');
    $pdf->Cell(15, 6, $detail->BillNo, 1, 0, 'R');
    $pdf->Cell(20, 6, date('d/m/Y', strtotime($detail->BillDate)), 1, 0);
    $pdf->Cell(50, 6, $detail->PName.' ('.$detail->Age.')', 1, 0);
    $pdf->Cell(20, 6, $detail->Total, 1, 0, 'R');
    $pdf->Cell(20, 6, $detail->TotalDiscount, 1, 0, 'R');
    $pdf->Cell(20, 6, $detail->SubTotal, 1, 0, 'R');
    $pdf->Cell(20, 6, $detail->InitialPaid, 1, 0, 'R');
    if ($hasDue) {
        $pdf->SetTextColor(255, 0, 0);
    }
    $pdf->Cell(20, 6, $detail->DueAmount, 1, 0, 'R');
    if ($hasDue) {
        $pdf->SetTextColor(0, 0, 0);
    }
    $pdf->Cell(20, 6, $feesType, 1, 0);
    $pdf->Cell(60, 6, $detail->Consultant, 1, 1);

    $total += floatval($detail->Total);
    $discount += floatval($detail->TotalDiscount);
    $subTotal += floatval($detail->SubTotal);
    $initialPaid += floatval($detail->InitialPaid);
    $due += floatval($detail->DueAmount);
    $sNo++;
}

$pdf->SetWidths([95, 20, 20, 20, 20, 20,20, 60]);
$pdf->SetAligns(['L', 'R', 'R', 'R', 'R', 'R', 'R', 'R']);
$pdf->SetFont('times', 'B', 9);
$pdf->Row([
    '',
    sprintf('%0.02f', $total),
    sprintf('%0.02f', $discount),
    sprintf('%0.02f', $subTotal),
    sprintf('%0.02f', $initialPaid),
    sprintf('%0.02f', $due),
    '',''
        ], 7);
$pdf->Ln(10);

$pdf->SetFont('times', 'B', 12);
$pdf->Cell(150, 10, 'Due Collection', 0, 1, 'C');
$pdf->SetFont('times', 'B', 10);
$pdf->SetWidths([12, 15, 15, 30, 60, 30, 30]);
$pdf->SetAligns(['R', 'L', 'R', 'L','L', 'R', 'L']);
$pdf->Row(['S No', 'Month', 'Bill No', 'Collected Date', 'Patient Name', 'Due Collected', 'Type']);

$pdf->SetFont('times', '', 8);
$sNo = 1;
$total = 0;

$isExists = false;
foreach ($DueData as $dueDat) {
    if ($dueDat->PayType === 'Cash') {
        $totalCash += floatval($dueDat->DuePaid);
    } elseif ($dueDat->PayType === 'Card') {
        $totalCard += floatval($dueDat->DuePaid);
    } else {
        $totalOthers += floatval($dueDat->DuePaid);
    }
    $isPrevious=empty(array_filter($LabData, function ($val) use ($dueDat) {
        return $val->BillNo===$dueDat->BillNo;
    }));
    
    if ($isPrevious) {
        $pdf->SetTextColor(15, 79, 184);
    }
    $pdf->Row([
        $sNo,
        $dueDat->BillMonth,
        $dueDat->BillNo, date('d/m/Y', strtotime($dueDat->CollectedDate)),
        $dueDat->PName.' ('.$dueDat->Age.')',
        $dueDat->DuePaid,
        $dueDat->PayType === 'Others' ? $dueDat->OtherType : $dueDat->PayType
            ], 6);
    if ($isPrevious) {
        $pdf->SetTextColor(0, 0, 0);
    }
    $totalDuePaid += floatval($dueDat->DuePaid);
    $sNo++;
}

$pdf->Ln(5);


$pdf->SetFont('times', 'B', 10);
$pdf->Cell(50, 7, 'Total Amount Collected ', 0, 0);
$pdf->Cell(30, 7, ': Rs.' . number_format($initialPaid + $totalDuePaid, 2), 0, 1);

$pdf->Cell(50, 7, 'Total Cash Collected ', 0, 0);
$pdf->Cell(30, 7, ': Rs.' . number_format($totalCash, 2), 0, 1);

$pdf->Cell(50, 7, 'Total Card Collected ', 0, 0);
$pdf->Cell(30, 7, ': Rs.' . number_format($totalCard, 2), 0, 1);

$pdf->Cell(50, 7, 'Total Other Payments Collected ', 0, 0);
$pdf->Cell(30, 7, ': Rs.' . number_format($totalOthers, 2), 0, 1);

$pdf->Output('I', "OP Collection Report.pdf");
