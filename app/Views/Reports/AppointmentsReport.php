<?php
// use App\ThirdParty\Fpdf\Fpdf;
// use App\ThirdParty\Fpdf\Fpdf;
include(APPPATH.'Libraries/code128.php');

class PDF extends code128
{
    public $total = 0;
    public $startDate;
    public $endDate;

    public function Header()
    {
        $this->SetFont('times', 'B', 14);
        $this->Cell(200, 10, "Appointments Report", 0, 1, 'C');
        $this->Line(85, $this->GetY() - 2, 135, $this->GetY() - 2);
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

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
// $pdf->startDate = $Date->start;
$pdf->AddPage();

$pdf->SetAligns(['R',  'R', 'L', 'L', 'R']);
$pdf->SetWidths([20, 40, 40, 50, 40]);
$pdf->SetFont('times', 'B', 12);
$pdf->Cell(200, 10, 'Lab Collection', 0, 1, 'C');
$pdf->SetFont('times', 'B', 10);
$pdf->SetFillColor(200, 0, 0);
$pdf->SetFont('times', 'B', 11);
$pdf->Row(['S No', 'Patient Name', 'Contact No', 'AppointmentDate / Time', 'Consultant']);

$slNo = 1;
$pdf->SetFont('times', '', 9);

foreach ($appointments as $datas) {
    $pdf->Row([
        $slNo++,
        $datas->PName, 
        $datas->ContactNo,
        $datas->AppDate . ' ' . $datas->AppTime,
        $datas->DoctorName
    ]);
}

$pdf->Output('I', "OP Collection Report.pdf");
