<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\Reports\AppointmentReport_Model;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Appointments extends BaseController
{
    /**
     * @var AppointmentReport_Model
     */
    private AppointmentReport_Model $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('Rep');
        $this->m = new AppointmentReport_Model($this->session->get('hosID'));
    }

    public function index(): void
    {
        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetConsultants());
    }

    public function GetData(string $doc, $startDate, $endDate)
    {
        // return $decCons;
        // $decDate=base64_decode($date);
        // $cons=\base64_decode($decCons);
        // $data['Date']=(object)['start'=>\DateTime::createFromFormat('Y-m-d', $decDate)->format('d/m/Y')];
        $start = \DateTime::createFromFormat('Y-m-d', $startDate)->format('d/m/Y');
        $end = \DateTime::createFromFormat('Y-m-d', $endDate)->format('d/m/Y');
        $data = [
            'appointments' => $this->m->GetCollection($doc, $start, $end),
        ];

        $view = view('Reports/AppointmentsReport', $data);

        $this->response->setContentType('text/plain')->setBody(base64_encode($view));
        return $this->response;
    }

    // public function DownloadReport(string $decCons, string $decDate)
    // {
    //     $start = base64_decode($decDate);
    //     $cons = \base64_decode($decCons);

    //     $billData = $this->m->GetCollection($cons, $start);
    //     $dueData = $this->m->GetDueCollected($start);

    //     if (empty($billData) && empty($dueData)) {
    //         echo "<script>alert('No Datas found');window.close();</script>";
    //     } else {
    //         $spreadSheet = new Spreadsheet();
    //         $sheet = $spreadSheet->getSheet(0);
    //         $color = new \PhpOffice\PhpSpreadsheet\Style\Color();

    //         $i = 1;
    //         $sheet->getColumnDimension('B')->setWidth(10);
    //         $sheet->getColumnDimension('C')->setWidth(13);
    //         $sheet->getColumnDimension('D')->setWidth(13);
    //         $sheet->getColumnDimension('E')->setWidth(13);
    //         $sheet->getColumnDimension('F')->setWidth(12);
    //         $sheet->getColumnDimension('G')->setWidth(12);
    //         $sheet->getColumnDimension('H')->setWidth(12);
    //         $sheet->getColumnDimension('I')->setWidth(12);
    //         $sheet->getColumnDimension('J')->setWidth(12);
    //         $sheet->getColumnDimension('K')->setWidth(15);
    //         $sheet->getColumnDimension('L')->setWidth(30);

    //         $sheet->mergeCells("A$i:L" . ($i + 1));
    //         $sheet->getStyleByColumnAndRow(1, $i, 12, $i)->getAlignment()->setHorizontal('center');
    //         $sheet->getStyleByColumnAndRow(1, $i, 12, $i)->getFont()->setBold(true)->setSize(14)->getColor()->setARGB($color::COLOR_DARKGREEN);
    //         $sheet->setCellValue("A$i", 'Collection Report on ' . date('d/m/Y', strtotime($start)));
    //         $i += 3;

    //         $sheet->mergeCells("A$i:L$i");
    //         $sheet->getStyleByColumnAndRow(1, $i, 12, $i)->getAlignment()->setHorizontal('center');
    //         $sheet->getStyleByColumnAndRow(1, $i, 12, $i)->getFont()->setBold(true)->setSize(12);
    //         $sheet->setCellValue("A$i", 'Lab Collection');
    //         $i++;

    //         $sheet->getStyleByColumnAndRow(1, $i, 12, $i)->getFont()->setBold(true);
    //         $sheet->setCellValue("A$i", 'S No');
    //         $sheet->setCellValue("B$i", 'Bill No');
    //         $sheet->setCellValue("C$i", 'Bill Date');
    //         $sheet->setCellValue("D$i", 'Patient Name');
    //         $sheet->setCellValue("F$i", 'Total');
    //         $sheet->setCellValue("G$i", 'Discount');
    //         $sheet->setCellValue("H$i", 'Sub Total');
    //         $sheet->setCellValue("I$i", 'Initial Paid');
    //         $sheet->setCellValue("J$i", 'Due Amount');
    //         $sheet->setCellValue("K$i", 'Type');
    //         $sheet->setCellValue("L$i", 'Consultant');

    //         $sheet->mergeCells("D$i:E$i");
    //         $i++;

    //         $total = 0;
    //         $discount = 0;
    //         $subTotal = 0;
    //         $initialPaid = 0;
    //         $totalDuePaid = 0;
    //         $due = 0;

    //         $totalCash = 0;
    //         $totalCard = 0;
    //         $totalOthers = 0;
    //         foreach ($billData as $ind => $bill) {
    //             $feesType = floatval($bill->InitialPaid) == 0 ? '' : ($bill->BillType === 'Others' ? $bill->OtherPayments : $bill->BillType);

    //             $sheet->setCellValue("A$i", $ind + 1);
    //             $sheet->setCellValue("B$i", $bill->BillNo);
    //             $sheet->setCellValue("C$i", date('d-m-Y', strtotime($bill->BillDate)));
    //             $sheet->setCellValue("D$i", $bill->PName);
    //             $sheet->setCellValue("F$i", $bill->Total);
    //             $sheet->setCellValue("G$i", $bill->TotalDiscount);
    //             $sheet->setCellValue("H$i", $bill->SubTotal);
    //             $sheet->setCellValue("I$i", $bill->PaidAmount);
    //             $sheet->setCellValue("J$i", $bill->DueAmount);
    //             $sheet->setCellValue("K$i", $feesType);
    //             $sheet->setCellValue("L$i", $bill->Consultant);

    //             $sheet->mergeCells("D$i:E$i");

    //             $hasDue = floatval($bill->DueAmount) > 0;
    //             if ($hasDue) {
    //                 $sheet->getStyle("J$i")->getFont()->getColor()->setARGB($color::COLOR_RED);
    //             }

    //             $total += floatval($bill->Total);
    //             $discount += floatval($bill->TotalDiscount);
    //             $subTotal += floatval($bill->SubTotal);
    //             $initialPaid += floatval($bill->InitialPaid);
    //             $due += floatval($bill->DueAmount);

    //             if ($bill->BillType === 'Cash') {
    //                 $totalCash += floatval($bill->InitialPaid);
    //             } elseif ($bill->BillType === 'Card') {
    //                 $totalCard += floatval($bill->InitialPaid);
    //             } else {
    //                 $totalOthers += floatval($bill->InitialPaid);
    //             }

    //             $i++;
    //         }
    //         // unset($billData);

    //         $lastCell = $i - 1;
    //         $sheet->mergeCells("A$i:E$i");

    //         $sheet->setCellValue("F$i", "=SUM(F6:F$lastCell)");
    //         $sheet->setCellValue("G$i", "=SUM(G6:G$lastCell)");
    //         $sheet->setCellValue("H$i", "=SUM(H6:H$lastCell)");
    //         $sheet->setCellValue("I$i", "=SUM(I6:I$lastCell)");
    //         $sheet->setCellValue("J$i", "=SUM(J6:J$lastCell)");
    //         $sheet->getStyle("F$i:J$i")->getFont()->setBold(true);
    //         // $i+=2;


    //         // $sheet->getStyleByColumnAndRow(1, $i, 11, $i)->getFont()->setBold(true)->setSize(12);
    //         // $sheet->setCellValue("A$i", 'Total Amount Collected From Lab Bills is Rs.'.number_format($initialPaid, 2));
    //         // $sheet->mergeCells("A$i:L$i");
    //         $i += 3;

    //         $sheet->mergeCells("A$i:H$i");
    //         $sheet->getStyle("A$i")->getAlignment()->setHorizontal('center');
    //         $sheet->getStyle("A$i")->getFont()->setBold(true)->setSize(12);
    //         $sheet->setCellValue("A$i", 'Due Collection');
    //         $i++;

    //         $sheet->getStyleByColumnAndRow(1, $i, 11, $i)->getFont()->setBold(true);
    //         $sheet->setCellValue("A$i", 'S No');
    //         $sheet->setCellValue("B$i", 'Month');
    //         $sheet->setCellValue("C$i", 'Bill No');
    //         $sheet->setCellValue("D$i", 'Collected Date');
    //         $sheet->setCellValue("E$i", 'Patient Name');
    //         $sheet->setCellValue("G$i", 'Due Collected');
    //         $sheet->setCellValue("H$i", 'Type');
    //         $sheet->mergeCells("E$i:F$i");
    //         $i++;

    //         foreach ($dueData as $index => $due) {
    //             $feesType = $due->PayType === 'Others' ? $due->OtherType : $due->PayType;

    //             $sheet->setCellValue("A$i", $index + 1);
    //             $sheet->setCellValue("B$i", $due->BillMonth);
    //             $sheet->setCellValue("C$i", $due->BillNo);
    //             $sheet->setCellValue("D$i", date('d-m-Y', strtotime($due->CollectedDate)));
    //             $sheet->setCellValue("E$i", $due->PName);
    //             $sheet->setCellValue("G$i", $due->DuePaid);
    //             $sheet->setCellValue("H$i", $feesType);

    //             $sheet->mergeCells("E$i:F$i");
    //             $totalDuePaid += floatval($due->DuePaid);

    //             if ($due->PayType === 'Cash') {
    //                 $totalCash += floatval($due->DuePaid);
    //             } elseif ($due->PayType === 'Card') {
    //                 $totalCard += floatval($due->DuePaid);
    //             } else {
    //                 $totalOthers += floatval($due->DuePaid);
    //             }

    //             $isPrevious = empty(array_filter($billData, function ($val) use ($due) {
    //                 return $val->BillNo === $due->BillNo;
    //             }));

    //             if ($isPrevious) {
    //                 $sheet->getStyle("A$i:H$i")->getFont()->getColor()->setARGB($color::COLOR_BLUE);
    //             }
    //             $i++;
    //         }
    //         $i++;

    //         $sheet->getStyle("A$i:A" . ($i + 4))->getFont()->setBold(true);

    //         $sheet->setCellValue("A$i", 'Total Amount Collected is Rs.' . ($initialPaid + $totalDuePaid));
    //         $sheet->mergeCells("A$i:L$i");
    //         $i++;
    //         $sheet->setCellValue("A$i", 'Total Amount Collected as Cash is Rs.' . $totalCash);
    //         $sheet->mergeCells("A$i:L$i");
    //         $i++;
    //         $sheet->setCellValue("A$i", 'Total Amount Collected as Card is Rs.' . $totalCard);
    //         $sheet->mergeCells("A$i:L$i");
    //         $i++;
    //         $sheet->setCellValue("A$i", 'Total other Payments Collected is Rs.' . $totalOthers);
    //         $sheet->mergeCells("A$i:L$i");

    //         $this->response->setContentType(config('Mimes')::$mimes['xlsx'][0]);
    //         $this->response->setHeader('Content-Disposition', 'attachment; filename="Collection Report (' . date('d-m-Y', strtotime($start)) . ').xlsx"');

    //         $writer = new Xlsx($spreadSheet);
    //         $writer->save('php://output');
    //     }
    // }
}
