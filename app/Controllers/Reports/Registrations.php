<?php namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\Reports\RegistrationReport_Model;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Registrations extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('Rep');
    }

    public function Get(string $startDate='', string $endDate='')
    {
        $m=new RegistrationReport_Model($this->session->get('hosID'));
        $decStart=base64_decode($startDate);
        $decEnd=base64_decode($endDate);

        $data=['Data'=>$m->GetRegistrationsList($decStart, $decEnd)];
       
        $this->response->setContentType('application/pdf');
        $this->response->setHeader('Content-Encoding', 'br');

        if (!is_null($this->request->getGet('base'))) {
            echo \brotli_compress(\base64_encode(view('Reports/RegistrationReport', $data)));
        } else {
            echo \brotli_compress(view('Reports/RegistrationReport', $data));
        }
    }

    public function download(string $encStart, string $encEnd)
    {
        $m=new RegistrationReport_Model($this->session->get('hosID'));
        $fromDate=base64_decode($encStart);
        $toDate=base64_decode($encEnd);

        $data=$m->GetRegistrationsList($fromDate, $toDate);

        if (empty($data)) {
            die('<script>alert("No data found!!!");window.close();</script>');
        }

        $spreadSheet=new Spreadsheet();
        $sheet=$spreadSheet->getSheet(0);
        $color=new \PhpOffice\PhpSpreadsheet\Style\Color();

        $i=1;

        $sheet->getColumnDimension('B')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(30);

        $sheet->mergeCells("A$i:H".($i+1));
        $sheet->getStyleByColumnAndRow(1, $i, 8, $i)->getAlignment()->setHorizontal('center');
        $sheet->getStyleByColumnAndRow(1, $i, 8, $i)->getFont()->setBold(true)->setSize(14)->getColor()->setARGB($color::COLOR_DARKGREEN);

        $sheet->setCellValue("A$i", 'Registration Report from '.$fromDate.' to '.$toDate);
        $i+=3;

        $sheet->getStyleByColumnAndRow(1, $i, 12, $i)->getFont()->setBold(true);
        $sheet->setCellValue("A$i", 'S No');
        $sheet->setCellValue("B$i", 'Reg Date');
        $sheet->setCellValue("C$i", 'Pt ID');
        $sheet->setCellValue("D$i", 'Name');
        $sheet->setCellValue("E$i", 'Age');
        $sheet->setCellValue("F$i", 'Gender');
        $sheet->setCellValue("G$i", 'Contact No');
        $sheet->setCellValue("H$i", 'Email ID');
        $i++;

        foreach ($data as $index=>$registration) {
            $sheet->setCellValue("A$i", $index+1);

            $t_date   = \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel($registration->RegDate);
            $sheet->setCellValue("B$i", $t_date);
            $sheet->setCellValue("C$i", $registration->PID);
            $sheet->setCellValue("D$i", trim($registration->PName));
            $sheet->setCellValue("E$i", $registration->Age);
            $sheet->setCellValue("F$i", $registration->Gender);
            $sheet->setCellValue("G$i", (int)$registration->Mobile);
            $sheet->setCellValue("H$i", $registration->EmailID);
            $i++;
        }
        $sheet->getStyle('B5:B'.($i-1))
        ->getNumberFormat()
        ->setFormatCode('dd-mm-yyyy');

        $writer=new Xlsx($spreadSheet);
        $this->response->setHeader('Content-Disposition', 'attachment;filename="Registration Report from '.$fromDate.' to '.$toDate.'.xlsx"');
        $writer->save('php://output');
    }
}
