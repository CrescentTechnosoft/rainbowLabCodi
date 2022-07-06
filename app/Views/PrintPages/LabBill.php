<?php
include(APPPATH.'Libraries/code128.php');
class PDF extends code128
{
    public function Header()
    {
        // $this->Ln(5);
        // $this->SetFont('times', 'B', 12);
        // $this->Cell(138, 6, 'VENKATESWARA DIAGNOSTICS CENTRE', 0, 1, 'C');
        // $this->SetFont('times', '', 9);
        // $address="NO 7B,HIMACHAL NAGAR ANNEXE,CHENNAI - 600125\nPhone : 9551727329, 8754919966\nWorking Hours : 07:00AM to 09:00 PM  Sunday : 07:00AM to 01:00PM\nLunch Hours : 01:00PM to 04:00PM\nEmail : vdck14@yahoo.in";
        // $this->MultiCell(138, 5, $address, 0, 'C');
        // $this->SetFontSize(12);
        // $this->Cell(138, 6, 'BILL / RECEIPT', 0, 1, 'C');

        // $this->SetXY(10, 50);
    }

    public function Footer()
    {
        // $this->SetY(-40);
        // $this->Image(APPPATH.'Images/VenkateshwaraFooter.jpg', 0, $this->GetY(), 210, 40);
    }
}

$firstRow = $BillData[0];

$pdf = new PDF('P', 'mm', [148,210]);
$pdf->AliasNbPages();
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();

$pdf->Ln(5);
if($firstRow->HosID ==1){
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell(138, 6, 'RAINBOW SCANS', 0, 1, 'C');
        $pdf->SetFont('times', '', 9);
        $address="NO 32,Abdulla St, Off Choolaimedu High Road,\nChoolaimedu,Chennai - 94\nPhone : 7305217778, 7305217779\nEmail : rainbowscans.c@gmail.com";
        $pdf->MultiCell(138, 5, $address, 0, 'C');
        $pdf->SetFontSize(12);
        $pdf->Cell(138, 6, 'BILL / RECEIPT' , 0, 1, 'C');

        $pdf->SetXY(10, 50);
        
}else if($firstRow->HosID ==2){
   
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell(138, 6, 'RAINBOW SCANS', 0, 1, 'C');
        $pdf->SetFont('times', '', 9);
        $address="NO 102,Tana Street,Purasaiwakkam\nChennai - 07\nPhone : 7305137778, 7305317778\nEmail : rainbowscans.p@gmail.com";
        $pdf->MultiCell(138, 5, $address, 0, 'C');
        $pdf->SetFontSize(12);
        $pdf->Cell(138, 6, 'BILL / RECEIPT', 0, 1, 'C');
        $pdf->SetXY(10, 50);
}else{
    $pdf->SetFont('times', 'B', 12);
        $pdf->Cell(138, 6, 'RAINBOW SCANS', 0, 1, 'C');
        $pdf->SetFont('times', '', 9);
        $address="NO 303,Triplicane High Road,\nTriplicane,Chennai - 05\nPhone : 7305317778, 7305327778\nEmail : rainbowscans.t@gmail.com";
        $pdf->MultiCell(138, 5, $address, 0, 'C');
        $pdf->SetFontSize(12);
        $pdf->Cell(138, 6, 'BILL / RECEIPT' , 0, 1, 'C');

        $pdf->SetXY(10, 50);
}

$pdf->Code128(6, $pdf->GetY()-3, $firstRow->BillMonth.'-'.$firstRow->BillNo, 35, 7);
$pdf->Ln(5);
$pdf->SetFont('times', 'B', 10);
$pdf->Cell(25, 5, 'SID No', 0, 0);
$pdf->Cell(25, 5, ': '.$firstRow->BillNo, 0, 1);

$pdf->SetFont('times', '');
$pdf->Cell(25, 5, 'Patient', 0, 0);
$pdf->Cell(110, 5, ': '.$firstRow->PName.'('.$firstRow->Age.'/'.$firstRow->Gender[0].')', 0, 1);

$pdf->Cell(25, 5, 'Date / Time', 0, 0);

$billDate=\DateTime::createFromFormat('Y-m-d H:i:s', $firstRow->BillDate.' '.$firstRow->BillTime)->format('d/m/Y h:i A');
$pdf->SetFont('times', '', 10);
$pdf->Cell(50, 5, ': ' .$billDate, 0, 1);

$pdf->Cell(25, 5, 'Referred By Dr', 0, 0);
$pdf->Cell(85, 5, ': '.$firstRow->Consultant, 0, 1);



$pdf->ln(4);


$pdf->SetFont("times", '', 10);

$pdf->SetAligns(['R', 'L', 'R']);
$pdf->SetWidths([10, 80, 40]);

$pdf->Line(5, $pdf->GetY(), 134, $pdf->GetY());
$pdf->Row(['S.No','Test Name','Rate(Rs.)'], false);
$pdf->Line(5, $pdf->GetY(), 134, $pdf->GetY());
$pdf->ln(2);

// $pdf->SetFont('times', '', 10);

$sNo = 1;
foreach ($BillData as $index=>$result) {
    $pdf->Row([($index+1).' .', $result->TestName, $result->Fees], false, 6);
}
$pdf->Line(5, $pdf->GetY(), 134, $pdf->GetY());

$pdf->SetWidths([110,20]);
$pdf->SetAligns(['R','R']);
$pdf->Row(['Bill Amount',$firstRow->Total], false);

if (floatval($firstRow->TotalDiscount)>0) {
    $pdf->Row(['Discount',$firstRow->TotalDiscount]);
    $pdf->Row(['Sub Total',$firstRow->SubTotal]);
}
$pdf->Row(['Amount Received',$firstRow->PaidAmount], false);

if (floatval($firstRow->DueAmount)>0) {
    $pdf->Row(['Balance',$firstRow->DueAmount], false);
}

$pdf->Ln(10);
if($firstRow->HosID ==1){
$pdf->Cell(118, 5, 'FOR VENKATESWARA DIAGNOSTICS CENTRE', 0, 1, 'R');
}else{
    $pdf->Cell(130, 5, 'Authorised signature', 0, 1, 'R');
}
// $pdf->SetTextColor(12, 117, 15);
// $pdf->Cell(190, 7, 'In Words : ' . getIndianCurrency(floatval($firstRow->Total)), 0, 1, 'L');

$pdf->Output('I', 'Bill #' . $firstRow->BillNo . '.pdf', 1);
