<?php
include(APPPATH.'Libraries/code128.php');
class PDF extends code128
{
    public $headerType;
    public $reportedPerson;
    public function Header()
    {
        if ($this->headerType==='WH') {
            $this->Image(APPPATH.'Images/rainbow_head.jpg', 0, 0, 210, 30);

            $this->SetXY(10, 32);
        } else {
            $this->SetXY(10, 50);
        }
    }

    public function Footer()
    {
        $this->SetY(-40);
        if ($this->headerType==='WH') {
            $this->Image(APPPATH.'Images/rainbow_foot.jpg', 0, $this->GetY(), 210, 40);
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

$billDate=\DateTime::createFromFormat('Y-m-d H:i:s', $OPData->BillDate)->format('d/m/Y h:i A');
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

$pdf->SetWidths([40, 170]);
$pdf->SetAligns(['L', 'L']);

$pdf->SetFont('ArchivoNarrow', 'B', 10);
$pdf->Cell(100, 6, 'MICROBIOLOGY', 0, 1);
$pdf->SetFont('ArchivoNarrow', 'B', 9);
$pdf->Cell(100, 6, $firstLabData->TestName, 0, 1);
$pdf->SetFont('Archivo', '', 9);
$pdf->Row(['SPECIMEN', ': '. $firstLabData->Specimen], false, 7);

$isoHead=$firstLabData->Culture==='Growth'?'ISOLATE':'CULTURE';
$isoVal=$firstLabData->Culture==='Growth'?$firstLabData->Isolate:'No Growth '.$firstLabData->Isolate;
$pdf->Row([$isoHead, ': '. $isoVal], false, 7);
if ($firstLabData->Culture==='Growth') {
    $pdf->Row(['COLONY COUNT', ': '. $firstLabData->Colony], false, 7);
}

if ($firstLabData->Culture==='Growth') {
    $pdf->ln();
    $pdf->SetWidths([62,62,62]);
    $pdf->SetAligns(['L', 'L', 'L']);

    $high=[];
    $moderate=[];
    $resistant=[];

    foreach ($LabData as $lab) {
        $data=explode('|', $lab->AntiBiotic);
        switch ($data[1]) {
          case 'Highly Sensitive':
          $high[]=$data[0];
          break;
          case 'Moderately Sensitive':
          $moderate[]=$data[0];
          break;
          case 'Resistant':
          $resistant[]=$data[0];
          break;
        }
    }

    $max=max(count($high), count($moderate), count($resistant));

    $pdf->SetFont("Archivo", "B", 9);
    $pdf->Row(['Highly Sensitive','Moderately Sensitive','Resistant'],true,6);
    $pdf->SetFont('Archivo', '', 9);
    for ($i=0;$i<$max;$i++) {
        $pdf->Row([
        isset($high[$i])?$high[$i]:'',
        isset($moderate[$i])?$moderate[$i]:'',
        isset($resistant[$i])?$resistant[$i]:'',
      ],true,6);
    }
}

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

// $pdf->SetFont('Archivo');
// $pdf->SetX(89);
// $pdf->Cell(72, 5, 'MICROBIOLOGIST', 0, 0);
// $pdf->Cell(70, 5, 'PATHOLOGIST', 0, 1);

$pdf->Output('', "Report Bill #$OPData->BillMonth$OPData->BillNo.pdf", 1);
