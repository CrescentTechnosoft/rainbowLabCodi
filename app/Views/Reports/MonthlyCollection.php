<?php

$pdf = new FPDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('times', 'B', 12);
$monthName=DateTime::createFromFormat('n', $month)->format('M');
$pdf->Cell(110, 10, 'Monthly Collection of '.$monthName.' '.$year, 0, 1, 'C');

$pdf->Ln(3);
$pdf->SetDrawColor(169, 169, 169);
$pdf->SetFont('times', 'B', 10);

$pdf->SetAligns(['R','L','R','R','R','R','R']);
$pdf->SetWidths([10,25,25,25,25,25,25]);

$pdf->Row(['S No','Bill Date','No of Bills','Cash','Card','Others','Total'], true, 7);
$pdf->SetFont('times', '', 9);
$dates=array_unique(array_map(fn (object $val):string =>$val->BillDate, $data));

$grandTotal=0;
$grandCash=0;
$grandCard=0;
$grandOthers=0;
$total=0;
$cash=0;
$card=0;
$others=0;
$bills=0;
$totalBills=0;

$i=0;
foreach ($dates as $date) {
    $total=0;
    $cash=0;
    $card=0;
    $others=0;
    $bills=0;
    foreach ($data as $dat) {
        if ($date===$dat->BillDate) {
            $total+=$dat->InitialPaid;
            if ($dat->BillType==='Cash') {
                $cash+=floatval($dat->InitialPaid);
            } elseif ($dat->BillType==='Card') {
                $card+=floatval($dat->InitialPaid);
            } else {
                $others+=floatval($dat->InitialPaid);
            }
            $bills++;
        }
    }
    $pdf->Row([++$i,date('d/m/Y', strtotime($date)),$bills,$cash,$card,$others,$total], true, 6);
    $grandTotal+=$total;
    $grandCash+=$cash;
    $grandCard+=$card;
    $grandOthers+=$others;
    $totalBills+=$bills;
}

$pdf->SetFont('times', 'B', 10);

$pdf->Cell(160, 7, 'Total No of Bills Done '.$totalBills, 'LTR', 1);
$pdf->Cell(160, 7, 'Total Amount Collected is Rs.'.number_format($grandTotal, 2), 'LR', 1);
$pdf->Cell(160, 7, 'Total Amount Collected as Cash is Rs.'.number_format($grandCash, 2), 'LR', 1);
$pdf->Cell(160, 7, 'Total Amount Collected as Card is Rs.'.number_format($grandCard, 2), 'LR', 1);
$pdf->Cell(160, 7, 'Total Amount Collected as Other Payments is Rs.'.number_format($grandOthers, 2), 'LBR', 1);


$pdf->Output('I', 'Monthly Collection.pdf');
