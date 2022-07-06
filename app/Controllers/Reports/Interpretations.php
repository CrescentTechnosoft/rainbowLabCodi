<?php namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\Reports\InterpretationsReport_Model;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Services\InterpretationsService;

class Interpretations extends BaseController
{
    /**
     * @var InterpretationsReport_Model
     */
    private InterpretationsReport_Model $m;

    /**
     * @var InterpretationsService
     */
    private InterpretationsService $service;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('Rep');
        $this->m= new InterpretationsReport_Model($this->session->get('hosID'));
        $this->service=new InterpretationsService;
    }

    public function index()
    {
        $this->response->setContentType('application/json');
        echo \json_encode($this->m->GetConsultantNames());
    }

    // public function Get(string $cons='', string $startDate='', string $endDate='')
    // {
    //     $decStart=base64_decode($startDate);
    //     $decEnd=base64_decode($endDate);
    //     $decCons=base64_decode($cons);

    //     $start=\DateTime::createFromFormat('Y-m-d', $decStart)->format('d/m/Y');
    //     $end=\DateTime::createFromFormat('Y-m-d', $decEnd)->format('d/m/Y');
    //     $data=[
    //         'Bills'=>$this->m->GetBills($decStart, $decEnd, $decCons),
    //         'Commission'=>$this->m->GetCommission($decCons),
    //         'Header'=>"Interpretations of $decCons From $start to $end"
    //     ];

    //     $this->response->setContentType('application/pdf');
    //     $this->response->setHeader('Content-Encoding', 'br');

    //     if (!is_null($this->request->getGet('base'))) {
    //         echo \brotli_compress(\base64_encode(view('Reports/InterpretationsReport', $data)));
    //     } else {
    //         echo \brotli_compress(view('Reports/InterpretationsReport', $data));
    //     }
    // }
    function date_sort($a, $b) {
        return strtotime($a) - strtotime($b);
    }

    public function download()
    {
        $cons=$this->request->getPost('consultant');
        $start=$this->request->getPost('start');
        $end=$this->request->getPost('end');

        $spreadSheet=new Spreadsheet();
        $sheet=$spreadSheet->getSheet(0);
        $color=new \PhpOffice\PhpSpreadsheet\Style\Color();

        $bills=$this->m->GetBills($start, $end, $cons);

        $this->service->commissions=$this->m->GetCommission($cons);

        if (empty($bills)) {
            echo "<script>alert('No Datas found');window.close();</script>";
        } else {
            $i=1;
            $sheet->getColumnDimension('B')->setWidth(50);
            $sheet->getColumnDimension('C')->setWidth(73);
            $sheet->getColumnDimension('D')->setWidth(10);
            $sheet->getColumnDimension('E')->setWidth(10);
            $sheet->getColumnDimension('F')->setWidth(10);

            $sheet->mergeCells("A$i:F".($i+1));
            $sheet->getStyleByColumnAndRow(1, $i, 10, $i)->getAlignment()->setHorizontal('center');
            $sheet->getStyleByColumnAndRow(1, $i, 10, $i)->getFont()->setBold(true)->setSize(14)->getColor()->setARGB($color::COLOR_DARKGREEN);
            $sheet->setCellValue("A$i", 'Interpretations Report of '.$cons.' From '.date('d/m/Y', strtotime($start)).' to '.date('d/m/Y', strtotime($end)));
            $i+=3;

            $sheet->getStyleByColumnAndRow(1, $i, 10, $i)->getFont()->setBold(true);
            $sheet->setCellValue("A$i", 'Bill No');
            $sheet->setCellValue("B$i", 'Patient Name');
            $sheet->setCellValue("C$i", 'Tests');
            $sheet->setCellValue("D$i", 'Cost');
            $sheet->setCellValue("E$i", 'Ref Amt');
            $sheet->setCellValue("F$i", 'Due Amt');
            $i++;

            $amount=0;
            $singleCommission=0;
            $totalCommission=0;
            $tests=[];
            
            
            $dates=array_unique(array_map(fn (object $val):string =>$val->BillDate, $bills));
            usort($dates,[$this,'date_sort']);
            $billNos=array_unique(array_map(fn (object $val):string =>$val->BillNo, $bills));
            
            foreach ($dates as $date) {
                $sheet->setCellValue("A$i", 'Bill Date : '.preg_replace('/(\d{4})-(\d{2})-(\d{2})/', '$3-$2-$1', $date));
                $sheet->getStyle("A$i")->getFont()->setBold(true);
                $sheet->mergeCells("A$i:F$i");
                $i++;
                foreach ($billNos as $billNo) {
                    $tests=[];
                    $singleCommission=0;
                    foreach ($bills as $bill) {
                        if ($bill->BillDate===$date && $bill->BillNo===$billNo) {
                            $singleCommission+=$this->service->GetCommissionAmount($bill->TestName, floatval($bill->Fees)-floatval($bill->Discount));
                            $tests[]=$bill->TestName;
                        }
                    }
                    if (!empty($tests)) {
                        $data=$this->service->GetName($billNo, $date, $bills);
                        $due=floatval($data->DueAmount)>0?$data->DueAmount:'';
                        $sheet->setCellValue("A$i", $billNo);
                        $sheet->setCellValue("B$i", $data->PName.' ('.$data->Age.')');
                        $sheet->setCellValue("C$i", implode(', ', $tests));
                        $sheet->setCellValue("D$i", $data->SubTotal);
                        $sheet->setCellValue("E$i", number_format($singleCommission, 2));
                        $sheet->setCellValue("F$i", $due);
                        if ($due!=='') {
                            $sheet->getStyle("A$i:F$i")->getFont()->getColor()->setARGB($color::COLOR_RED);
                        }
                        $i++;
                        $totalCommission+=$singleCommission;
                    }
                }
                $i++;
            }
            $i-=1;
            
            $lastCell=$i-1;
            // $sheet->mergeCells("A$i:F$i");
            $sheet->getStyleByColumnAndRow(1, $i, 10, $i)->getFont()->setBold(true)->setSize(13);
            $sheet->setCellValue("D$i", "=SUM(D6:D$lastCell)");
            $sheet->setCellValue("E$i", "=SUM(E6:E$lastCell)");
            $sheet->setCellValue("F$i", "=SUM(F6:F$lastCell)");

            $this->response->setContentType(config('Mimes')::$mimes['xlsx'][0]);
            $this->response->setHeader('Content-Disposition', 'attachment; filename="Interpretations Report of ('.$cons.').xlsx"');

            $writer=new Xlsx($spreadSheet);
            $writer->save('php://output');
        }
    }
}
