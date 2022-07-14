<?php
include(APPPATH.'Libraries/code128.php');
class PDF extends code128
{
    public bool $isLastPage = false;
    public string $headerType;
    public string $reportedPerson='';
    public object $firstRow;
    public object $firstLabData;
    public string $billDate;
    public string $rptDate;

    public function Header():void
    {
        if ($this->headerType==='WH') {
            $this->Image(APPPATH.'Images/VenkateshwaraHeader.jpg', 10, 5, 80, 25);

            $this->SetTextColor(60, 60, 60);
            $this->SetY(32);
        } else {
            $this->SetY(50);
        }
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('Archivo', 'B', 10);
        // $this->Cell(190, 7, 'PATIENT INFORMATION', 0, 1);

        $this->SetFont('Archivo', '', 10);
        $this->Cell(25, 12, "Patient Name", 0, 0);

        $this->SetFont('Archivo', 'B', 10);
        $this->Cell(98, 12, ': '.$this->firstRow->PName, 0, 0);
        $this->Code128(164, $this->GetY()+1, $this->firstRow->BillMonth.$this->firstRow->BillNo, 34, 7);

        $this->SetFont('Archivo', '', 10);

        $this->Cell(25, 12, 'SID No.', 0, 0);
        $this->Text(164, $this->GetY()+11, $this->firstRow->BillMonth.' - '.$this->firstRow->BillNo);
        $this->Cell(40, 12, ':', 0, 1);

        $this->Cell(25, 6, 'Age / Gender', 0, 0);
        $this->Cell(98, 6, ': '.$this->firstRow->Age . ' / ' . $this->firstRow->Gender, 0, 0);

        $this->Cell(25, 6, 'Page No', 0, 0);
        $this->Cell(40, 6, ': '.$this->PageNo().' of {nb}', 0, 1);

        $this->Cell(25, 6, 'UHID', 0, 0);
        $this->Cell(98, 6, ': '.$this->firstRow->PID, 0, 0);

        $this->Cell(25, 6, 'Collected On', 0, 0);
        $this->Cell(40, 6, ': '.$this->billDate, 0, 1);

        $this->Cell(25, 6, 'Referred By Dr', 0, 0);
        $this->Cell(98, 6, ': '.$this->firstRow->Consultant, 0, 0);

        $this->Cell(25, 6, 'Reported On', 0, 0);
        $this->Cell(40, 6, ': '.$this->rptDate, 0, 1);
        $this->SetLineWidth(.5);
        // $this->SetX(10);
        // if ($this->PageNo()===1) {
        //     $this->SetY(30);
        // } else {
        // }
        if ($this->PageNo()!==1) {
            // $this->Ln(5);
            $this->SetFont('ArchivoNarrow', 'B', 10);
            $this->Line(13, $this->GetY(), 198, $this->GetY());
            $this->Row(['TEST NAME', 'RESULT','UNIT','REFERENCE RANGE / METHOD'], false, 7);
            $this->Line(13, $this->GetY(), 198, $this->GetY());
            $this->ln(1);
            $this->SetDrawColor(122, 125, 123);
            $this->SetMargins(13, 10);
        }
    }

    public function Footer():void
    {
        $this->SetY(-40);
        if ($this->headerType==='WH') {
            $this->Image(APPPATH.'Images/VenkateshwaraFooter.jpg', 0, $this->GetY(), 210, 40);
        }
    }

    public function RowForResult(array $data, int $height = 5, bool $rect = true, string $norm='N')
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = $height * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            if ($rect) {
                $this->Rect($x, $y, $w, $h);
            }

            if ($i===1 && $norm!=='N') {
                $this->SetFont('Archivo', 'B');
            } else {
                $this->SetFont('Archivo', '');
            }
            //Print the text
            $this->MultiCell($w, $height, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }
}

$pdf = new PDF();
$pdf->SetAuthor('Crescent Technosoft');
$pdf->firstRow=$OPData;
$pdf->firstLabData=$LabData[0];
print_r($OPData->BillDate);
$pdf->headerType=$HeaderType;
$pdf->reportedPerson=$LabData[0]->ReportedBy;
$pdf->billDate=\DateTime::createFromFormat('Y-m-d H:i:s', $OPData->BillDate.' '.$OPData->BillTime)->format('d/m/Y h:i A');
$pdf->rptDate=\DateTime::createFromFormat('Y-m-d H:i:s', $LabData[0]->RptDate.' '.$LabData[0]->RptTime)->format('d/m/Y h:i A');


$pdf->AddFont('Archivo', '');
$pdf->AddFont('Archivo', 'B');
$pdf->AddFont('ArchivoNarrow', '');
$pdf->AddFont('ArchivoNarrow', 'B');
$pdf->SetMargins(13, 10);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 40);


$pdf->SetWidths([60, 45, 20, 60]);
$pdf->SetAligns(['L', 'L', 'L']);
$pdf->SetFont('ArchivoNarrow', 'B', 10);
$pdf->Cell(190, 7, 'FINAL REPORT', 0, 1, 'C');
// $pdf->Line(95, $pdf->GetY()-2, 115, $pdf->GetY()-2);
// $pdf->SetFont('Archivo', 'B', 9);
$pdf->SetLineWidth(.5);
$pdf->Line(13, $pdf->GetY(), 198, $pdf->GetY());
$pdf->Row(['TEST NAME', 'RESULT','UNIT','REFERENCE RANGE / METHOD'], false, 7);
$pdf->Line(13, $pdf->GetY(), 198, $pdf->GetY());
$pdf->ln(1);
$pdf->SetDrawColor(171, 176, 172);
$pdf->SetLineWidth(.1);

$categories=array_values(array_unique(array_map(fn (object $val):string =>$val->Category, $CatData)));

$totalCat=count($categories)-1;
$totalTests=count($CatData)-1;
$totalFields=count($LabData)-1;

$addedTests=[];

foreach ($categories as $catIndex=>$category) {
    $pdf->CheckPageBreak(18);
    $pdf->SetFont('ArchivoNarrow', 'B', 10);
    $pdf->SetY($pdf->GetY());
    // $pdf->SetX(10);
    $pdf->MultiCell(185, 7, $category, 1);

    foreach ($CatData as $testIndex=>$coll) {
        if ($coll->Category === $category) {
            foreach ($LabData as $fieldIndex=>$lab) {
                if ($coll->Category === $lab->Category && $coll->TestName === $lab->TestName) {
                    if (!in_array($lab->TestName, $addedTests)&& $lab->TestName!==$lab->FieldName) {
                        $pdf->CheckPageBreak(12);
                        $pdf->SetFont('archivonarrow', 'B', 10);
                        $pdf->MultiCell(185, 6, $lab->TestName, 1, 'L');
                        $addedTests[]=$lab->TestName;
                    }
                    if ($lab->FieldCategory!=='') {
                        $pdf->SetFont('ArchivoNarrow', 'B');
                        $pdf->Cell(185, 6, $lab->FieldCategory, 1, 1);
                    }
                    $pdf->SetFont('Archivo', '', 9);
                    $isLast=$totalCat===$catIndex && $totalTests===$testIndex && $totalFields===$fieldIndex;
                    if ($isLast) {
                        $lines=max($pdf->NbLines(60, $lab->FieldName), $pdf->NbLines(60, $lab->NormalValue));
                        $pdf->CheckPageBreak((6*$lines)+35);
                    }
                    $pdf->RowForResult([$lab->FieldName, $lab->Result, $lab->Units,  $lab->NormalValue], 5, true, $lab->ResultType);
                    if (strlen(trim($lab->Comments))>0) {
                        $pdf->SetFontSize(9);
                        $nb=$pdf->NbLines(185, $lab->Comments);
                        $pdf->CheckPageBreak($nb*5);
                        $pdf->MultiCell(185, 5, $lab->Comments, 1, 'L');
                    }
                }
            }
        }
    }
}
$pdf->Ln(4);
$pdf->SetFont('Archivo', 'B', 10);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(.5);
$pdf->Line(13, $pdf->GetY()+2, 91, $pdf->GetY()+2);
$pdf->Line(123, $pdf->GetY()+2, 198, $pdf->GetY()+2);
$pdf->Cell(188, 5, 'End of the Report', 0, 1, 'C');
$pdf->Ln(15);

// $pdf->Image(APPPATH.'Images/ApexSign.jpg', 150, $pdf->GetY()-10, 40, 12);
$pdf->CheckPageBreak(25);
if ($pdf->GetY()<100) {
    $pdf->Ln(20);
}
// $pdf->Image(APPPATH.'Images/VenkateshwaraLabsMicrobiologistSign.jpg', 90, $pdf->GetY()-10, 30, 11);
// $pdf->Image(APPPATH.'Images/VenkateshwaraLabsPathologistSign.jpg', 153, $pdf->GetY()-11, 33, 12);

// $pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Archivo', '', 10);
$pdf->Cell(70, 5, 'Technologist', 0, 0);

$pdf->SetFont('Archivo', 'B');
// $pdf->Cell(70, 5, 'DR.SELVAKUMARAN. PHD,', 0, 0);
// $pdf->Cell(70, 5, 'DR.VASANTHI.K,MD,PATH', 0, 1);

$pdf->SetFont('Archivo');
$pdf->SetX(89);
$pdf->Cell(72, 5, 'MICROBIOLOGIST', 0, 0);
$pdf->Cell(70, 5, 'PATHOLOGIST', 0, 1);

// $pdf->Output(isset($type)?$type:'I', isset($name)?$name:'Bill #' . $OPData->BillNo . '.pdf', 1);
$filename = $pdf->firstRow->PID.$pdf->firstRow->PName;
$pdf->Output('F',APPPATH.('whatsapp').'/'.$filename. '.pdf', true);
