<?php namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\Reports\MonthlyCollections_Model;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MonthlyCollection extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('Rep');
    }

    public function Get(string $encYear, string $encMonth):void
    {
        $year=\base64_decode($encYear);
        $month=\base64_decode($encMonth);

        $startDate=\DateTime::createFromFormat('d/n/Y', '01/'.$month.'/'.$year)->format('Y-m-d');
        $endDate=\DateTime::createFromFormat('d/n/Y', \cal_days_in_month(\CAL_GREGORIAN, $month, $year).'/'.$month.'/'.$year)->format('Y-m-d');
        
        $m=new MonthlyCollections_Model($this->session->get('hosID'));
        $data=$m->GetData($startDate, $endDate);

        $this->response->setContentType('application/pdf');
        $this->response->setHeader('Content-Encoding', 'br');

        if (!\is_null($this->request->getGet('base'))) {
            echo \brotli_compress(\base64_encode(view('Reports/MonthlyCollection', ['year'=>$year,'month'=>$month,'data'=>$data])));
        } else {
            echo \brotli_compress(view('Reports/MonthlyCollection', ['year'=>$year,'month'=>$month,'data'=>$data]));
        }
    }

    public function download(string $encYear, string $encMonth):void
    {
        $year=\base64_decode($encYear);
        $month=\base64_decode($encMonth);

        $startDate=\DateTime::createFromFormat('d/n/Y', '01/'.$month.'/'.$year)->format('Y-m-d');
        $endDate=\DateTime::createFromFormat('d/n/Y', \cal_days_in_month(\CAL_GREGORIAN, $month, $year).'/'.$month.'/'.$year)->format('Y-m-d');
        
        $m=new MonthlyCollections_Model($this->session->get('hosID'));
        $data=$m->GetData($startDate, $endDate);

        if (empty($data)) {
            echo '<script>alert("No data found!!!");window.close();</script>';
        } else {
            $spreadSheet=new Spreadsheet();
            $sheet=$spreadSheet->getSheet(0);
            $color=new \PhpOffice\PhpSpreadsheet\Style\Color();

            $i=1;

            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(10);

            $sheet->mergeCells("A$i:G".($i+1));
            $sheet->getStyleByColumnAndRow(1, $i, 7, $i)->getAlignment()->setHorizontal('center');
            $sheet->getStyleByColumnAndRow(1, $i, 7, $i)->getFont()->setBold(true)->setSize(14)->getColor()->setARGB($color::COLOR_DARKGREEN);
            $monthName=\DateTime::createFromFormat('n', $month)->format('M');
            $sheet->setCellValue("A$i", 'Monthly Collection Report of '.$monthName.' '.$year);
            $i+=3;

            $sheet->getStyleByColumnAndRow(1, $i, 12, $i)->getFont()->setBold(true);
            $sheet->setCellValue("A$i", 'S No');
            $sheet->setCellValue("B$i", 'Bill Date');
            $sheet->setCellValue("C$i", 'No of Bills');
            $sheet->setCellValue("D$i", 'Cash');
            $sheet->setCellValue("E$i", 'Card');
            $sheet->setCellValue("F$i", 'Others');
            $sheet->setCellValue("G$i", 'Total');
            $i++;

            $sno=1;
            $total=0;
            $cash=0;
            $card=0;
            $others=0;
            $bills=0;

            $dates=array_unique(array_map(fn (object $val):string =>$val->BillDate, $data));

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
                $t_date   = \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel($date);
                $sheet->setCellValue("A$i", $sno);
                $sheet->setCellValue("B$i", $t_date);
                $sheet->setCellValue("C$i", $bills);
                $sheet->setCellValue("D$i", $cash);
                $sheet->setCellValue("E$i", $card);
                $sheet->setCellValue("F$i", $others);
                $sheet->setCellValue("G$i", $total);
                $i++;
                $sno++;
            }
            $lastCell=$i-1;

            $sheet->getStyle("A$i:G$i")->getFont()->setBold(true);
            $sheet->mergeCells("A$i:B$i");
            $sheet->getStyle("A$i")->getAlignment()->setHorizontal('right');
            $sheet->setCellValue("A$i", 'Total ');
            $sheet->setCellValue("C$i", "=SUM(C5:C$lastCell)");
            $sheet->setCellValue("D$i", "=SUM(D5:D$lastCell)");
            $sheet->setCellValue("E$i", "=SUM(E5:E$lastCell)");
            $sheet->setCellValue("F$i", "=SUM(F5:F$lastCell)");
            $sheet->setCellValue("G$i", "=SUM(G5:G$lastCell)");

            $sheet->getStyle('B5:B'.$lastCell)
            ->getNumberFormat()
            ->setFormatCode('dd-mm-yyyy');

            $xlsx=new Xlsx($spreadSheet);

            $this->response->setHeader('Content-Disposition', 'attachment;filename="Monthly Collection of '.$monthName.' '.$year.'.xlsx"');
            $xlsx->save('php://output');
        }
    }
}
