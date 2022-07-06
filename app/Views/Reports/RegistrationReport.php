<?php

class PDF extends FPDF
{
    public function Header()
    {
        $this->SetFont("times", "B", 10);
        $this->Row(['S.No','Reg Date','PID','Name','Age','Gender','Contact No','Email ID'],true,7);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->AddFont("times", '');
        $this->SetFont("times", "", 10);
        $this->Cell(200, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 1, 'C');
    }
}

$pdf = new PDF('P', 'mm', [220,297]);
$pdf->AliasNbPages();
$pdf->SetWidths([10,20,15,50,10,20,25,50]);
$pdf->SetAligns(['R','L','R','L','L','L','L','L']);

$pdf->AddPage();
$pdf->SetFont("times", '', 10);

$sNo=0;
foreach ($Data as $details) {
   $pdf->Row([++$sNo,date('d/m/Y',strtotime($details->RegDate)),$details->PID,$details->PName,$details->Age,$details->Gender,$details->Mobile,$details->EmailID],true,6);
}
$pdf->Output("I", 'Registration List.pdf');
