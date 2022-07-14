<?php
include(APPPATH.'Libraries/code128.php');

class PDF extends code128
{
    public $headerType;
    public $reportedPerson;
    public function Header()
    {
        if ($this->headerType==='wh') {
            $this->Image(APPPATH.'Images/rainbow_head.jpg', 0, 0, 210, 30);

            $this->SetXY(10, 32);
        } else {
            $this->SetXY(10, 50);
        }
    }

    public function Footer()
    {
        $this->SetY(-30);
        if ($this->headerType==='wh') {
            $this->Image(APPPATH.'Images/rainbow_foot.jpg', 0, $this->GetY(), 210, 30);
            $this->SetTextColor(255, 255, 255);
        }
    }
}

$firstLabData=$LabData[0];
$pdf = new PDF();
$pdf->headerType=$header;
$pdf->reportedPerson=$firstLabData->ReportedBy;
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->AddFont("Archivo", "B");
$pdf->AddFont("Archivo", "");
$pdf->AddFont('ArchivoNarrow', 'B');
$pdf->SetFont('Archivo', 'B', 10);
$pdf->SetLeftMargin(12);

$pdf->SetFont('Archivo', '', 10);
$pdf->Cell(25, 12, "Patient Name", 0, 0);

$pdf->SetFont('Archivo', 'B', 10);
$pdf->Cell(100, 12, ': '.$OPData->PName, 0, 0);
$pdf->Code128(165, $pdf->GetY()+1, $OPData->BillMonth.$OPData->BillNo, 35, 7);

$pdf->SetFont('Archivo', '', 10);

$pdf->Cell(25, 12, 'SID', 0, 0);
$pdf->Text(165, $pdf->GetY()+11, $OPData->BillMonth.' - '.$OPData->BillNo);
$pdf->Cell(40, 12, ': ', 0, 1);

$pdf->Cell(25, 6, 'Age / Gender', 0, 0);
$pdf->Cell(100, 6, ': '.$OPData->Age . ' / ' . $OPData->Gender, 0, 0);

$pdf->Cell(25, 6, 'Contact No', 0, 0);
$pdf->Cell(40, 6, ': '.$OPData->ContactNo, 0, 1);

$pdf->Cell(25, 6, 'UHID', 0, 0);
$pdf->Cell(100, 6, ': '.$OPData->PID, 0, 0);

$billDate=\DateTime::createFromFormat('Y-m-d H:i:s', $OPData->BillDate.$OPData->BillTime)->format('d/m/Y h:i A');
$pdf->Cell(25, 6, 'Collected On', 0, 0);
$pdf->Cell(40, 6, ': '.$billDate, 0, 1);

$pdf->Cell(25, 6, 'Referred By Dr', 0, 0);
$pdf->Cell(100, 6, ': '.$OPData->Consultant, 0, 0);

$rptDate=\DateTime::createFromFormat('Y-m-d H:i:s', $firstLabData->RptDate.' '.$firstLabData->RptTime)->format('d/m/Y h:i A');
$pdf->Cell(25, 6, 'Reported On', 0, 0);
$pdf->Cell(40, 6, ': '.$rptDate, 0, 1);

$pdf->SetFont('ArchivoNarrow', 'B', 10);
$pdf->Cell(190, 7, 'FINAL REPORT', 0, 1, 'C');

$pdf->SetLineWidth(.5);
$pdf->Line(13, $pdf->GetY(), 200, $pdf->GetY());
$pdf->SetLineWidth(.2);
// foreach
$pdf->SetFont('ArchivoNarrow', 'B', 11);
$pdf->Ln(5);
$pdf->Cell(50, 5, $firstLabData->TestName, 0, 1);

$pdf->Ln(5);
$pdf->SetFont('Archivo', 'B');
$pdf->Cell(50, 5, 'Result', 0, 1);

$pdf->SetFont('Archivo', '');
$pdf->MultiCell(180, 5, utf8_decode($firstLabData->Result), 0, 1);

// $pdf->SetFont('Archivo', 'B');
// $pdf->Cell(50, 5, 'Remarks', 0, 1);

// $pdf->SetFont('Archivo', '');
// $pdf->MultiCell(180, 5, utf8_decode($firstLabData->Remarks), 0, 1);

$pdf->Ln(10);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(.5);
$pdf->Line(13, $pdf->GetY()+2, 91, $pdf->GetY()+2);
$pdf->Line(120, $pdf->GetY()+2, 198, $pdf->GetY()+2);
$pdf->SetFont('Archivo', 'B');
$pdf->Cell(188, 5, 'End of the Report', 0, 1, 'C');
$pdf->Ln(15);

// $pdf->Image(APPPATH.'Images/ApexSign.jpg', 150, $pdf->GetY()-10, 40, 12);

//$pdf->Image(APPPATH.'Images/VenkateshwaraLabsMicrobiologistSign.jpg', 90, $pdf->GetY()-10, 30, 11);
//$pdf->Image(APPPATH.'Images/VenkateshwaraLabsPathologistSign.jpg', 153, $pdf->GetY()-11, 33, 12);

// $pdf->SetFont('Archivo', '', 10);
// $pdf->Cell(70, 5, 'Technologist', 0, 0);

// $pdf->SetFont('Archivo', 'B');
// $pdf->Cell(70, 5, 'DR.SELVAKUMARAN. PHD,', 0, 0);
// $pdf->Cell(70, 5, 'DR.VASANTHI.K,MD,PATH', 0, 1);
$pdf->SetX(15);
$pdf->Cell(70, 5, '', 0, 0);
$pdf->SetFont('Archivo', 'B','10');
$pdf->Cell(55, 5, '', 0, 0);
$pdf->Cell(60, 5, 'Dr.M.SOCKALINGAM, MBBS,PGD(USG),(FRCR)(UK)', 0, 1,'R');
$pdf->Cell(70, 5, '', 0, 0);
$pdf->Cell(110, 5, 'Consultant Sonologist & Radiologist', 0, 1,'R');
// $pdf->SetFont('Archivo');
// $pdf->SetX(89);
// $pdf->Cell(72, 5, 'MICROBIOLOGIST', 0, 0);
// $pdf->Cell(70, 5, 'PATHOLOGIST', 0, 1);
// $pdf->Output(isset($type)?$type:'I', isset($name)?$name:'Bill #' . $OPData->BillNo . '.pdf', 1);
$filename = $OPData->BillNo;
//$pdf->Output('F',WRITEPATH.('temp').'/'.$filename. '.pdf', true);
 $pdf->Output('', "Scan Report  #$OPData->BillMonth $OPData->BillNo.pdf", 1);
