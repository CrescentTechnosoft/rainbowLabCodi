<?php

class PDF extends FPDF
{
    public $header;
    public $commissions;
    public function Header()
    {
        $this->SetFont('times', 'B', 12);
        $this->Cell(190, 10, $this->header, 0, 1, 'C');
        $this->Ln(5);
    }

    // Page footer
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // public function GetUniqueID(array $bills):array
    // {
    //     return array_values(array_unique(array_map(fn (object $val):string =>$val->PID, $bills)));
    // }

    public function GetCommissionAmount(string $testName, float $fees):float
    {
        $commission=0;
        foreach ($this->commissions as $comm) {
            if ($comm->TestName===$testName) {
                $amt=floatval($comm->CommissionAmount);
                $commission=$comm->CommissionType==='Percentage'?(($fees/100)*$amt):$amt;
                break;
            }
        }
        return $commission;
    }

    public function GetCommission(string $testName):array
    {
        $commission=[0,'Amount'];
        foreach ($this->commissions as $comm) {
            if ($comm->TestName===$testName) {
                $commission=[$comm->CommissionAmount,$comm->CommissionType];
                break;
            }
        }
        return $commission;
    }

    public function GetName(int $billNo, string $billDate, array $bills):object
    {
        $all= array_values(array_filter($bills, fn (object $val):bool =>(int)$val->BillNo===$billNo && $val->BillDate===$billDate));
        return $all[0];
    }
}

$pdf = new PDF('P', 'mm', [235,297]);
$pdf->commissions=$Commission;
$pdf->header=$Header;
unset($Commission);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('times', 'B', 10);
$pdf->SetAligns(['R','L','L','R','R','R']);
$pdf->SetWidths([13,45,110,16,16,16]);
// $pdf->SetX(20);
$pdf->Row(['Bill No','Patient Name','Tests','Cost','Ref Amt','Due Amt']);

// $uniqueIDs=$pdf->GetUniqueID($Bills);
$amount=0;
$singleCommission=0;
$totalCommission=0;
$tests=[];

$dates=array_unique(array_map(fn (object $val):string =>$val->BillDate, $Bills));
$billNos=array_unique(array_map(fn (object $val):string =>$val->BillNo, $Bills));

foreach ($dates as $date) {
    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(216, 6, 'Bill Date : '.preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '$3/$2/$1', $date), 1, 1);
    $pdf->SetFont('times', '', 9);
    foreach ($billNos as $billNo) {
        $tests=[];
        $singleCommission=0;
        foreach ($Bills as $bill) {
            if ($bill->BillDate===$date && $bill->BillNo===$billNo) {
                $singleCommission+=$pdf->GetCommissionAmount($bill->TestName, floatval($bill->Fees)-floatval($bill->Discount));
                $tests[]=$bill->TestName;
            }
        }
        if (!empty($tests)) {
            $data=$pdf->GetName($billNo, $date, $Bills);
            $due=floatval($data->DueAmount)>0?$data->DueAmount:'';
            if ($due!=='') {
                $pdf->SetTextColor(255, 0, 0);
            }
            $pdf->Row([$billNo,$data->PName.' ('.$data->Age.')',implode(', ', $tests),$data->SubTotal,number_format($singleCommission, 2),$due], true, 6);
            if ($due!=='') {
                $pdf->SetTextColor(0, 0, 0);
            }
            $totalCommission+=$singleCommission;
        }
    }
}

// foreach ($uniqueIDs as $id) {
//     $singleCommission=0;
//     $tests=[];
//     $amount=0;
//     foreach ($Bills as $bill) {
//         if ($bill->PID===$id) {
//             $fees=floatval($bill->Fees)-floatval($bill->Discount);
//             $amount+=$fees;
//             $singleCommission+=$pdf->GetCommissionAmount($bill->TestName, $fees);
//             $tests[]=$bill->TestName;
//         }
//     }
//     $data=$pdf->GetName($id, $Bills);
//     $due=floatval($data->due)>0?$data->due:'';
//     $pdf->Row([$id,$data->PName.' ('.$data->Age.')',implode(', ', $tests),$amount,number_format($singleCommission, 2),$due], true, 6);
//     $totalCommission+=$singleCommission;
// }

$pdf->SetFont('times', 'B', 10);
$pdf->Cell(190, 10, "Total Referral Amount is Rs." . sprintf('%0.02f', $totalCommission), 0, 1);
$pdf->Output('I', "Referral Report.pdf");
