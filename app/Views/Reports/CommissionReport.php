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

    public function GetUniqueID(array $bills):array
    {
        return array_values(array_unique(array_map(fn (object $val):string =>$val->PID, $bills)));
    }

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

    public function GetName(string $id, array $bills):object
    {
        $all= array_values(array_filter($bills, fn (object $val):bool =>$val->PID===$id));
        $fees=array_sum(array_map(fn (object $val):float =>(floatval($val->Fees)-floatval($val->Discount)), $all));

        $result=$all[0];
        $result->fees=$fees;
        return $result;
    }
}

$pdf = new PDF();
$pdf->commissions=$Commission;
$pdf->header=$Header;
unset($Commission);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('times', 'B', 10);
$pdf->SetAligns(['R','L','L','R','R','R']);
$pdf->SetWidths([12,35,110,16,16,20]);
// $pdf->SetX(20);
$pdf->Row(['Pt ID','Patient Name','Tests','Cost','Ref Amt']);

$uniqueIDs=$pdf->GetUniqueID($Bills);
$singleCommission=0;
$totalCommission=0;
$tests=[];

$pdf->SetFont('times');
foreach ($uniqueIDs as $id) {
    $singleCommission=0;
    $tests=[];
    foreach ($Bills as $bill) {
        if ($bill->PID===$id) {
            $singleCommission+=$pdf->GetCommissionAmount($bill->TestName, floatval($bill->Fees)-floatval($bill->Discount));
            $tests[]=$bill->TestName;
        }
    }
    $data=$pdf->GetName($id, $Bills);
    $pdf->Row([$id,$data->PName,implode(', ', $tests),$data->SubTotal,number_format($singleCommission, 2)], true, 6);
    $totalCommission+=$singleCommission;
}

$pdf->SetFont('times', 'B', 10);
$pdf->Cell(190, 10, "Total Referral Amount is Rs." . sprintf('%0.02f', $totalCommission), 0, 1);
$pdf->Output('I', "Referral Report.pdf");
