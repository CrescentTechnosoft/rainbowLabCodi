<?php namespace App\Controllers\PrintPages;

use App\Controllers\BaseController;
use App\Models\PrintPages\LabReport_Model;

class LabReport extends BaseController
{
    /** // RainbowscansCrescent0
     * @var LabReport_Model
     */
    private LabReport_Model $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser();
        $this->m=new LabReport_Model($this->session->get('hosID'));
    }

    public function LabResult(string $month, string $billNo, string $header)
    {
        $decMonth=base64_decode($month);
        $decBillNo=base64_decode($billNo);

        $expHeader=explode('-', $header);
        $data=$this->m->GetOPLabData($decMonth, $decBillNo, $expHeader[1]);
        $this->response->setContentType('application/pdf');
        //$this->response->setHeader('Content-Encoding', 'br');
        if (empty($data['LabData'])) {
            echo (view('Templates/NoDetailsPage'));
        } else {
            $data['HeaderType']=$expHeader[0];
            echo (view('PrintPages/LabReport/LabReport', $data));
        }
        
    }

    public function CultureResult(string $month='', string $billNo='', string $test='', string $header='WOH')
    {
        $decMonth=base64_decode($month);
        $decBillNo=base64_decode($billNo);
        $decTest=base64_decode($test);

        $data=$this->m->GetCultureData($decMonth, $decBillNo, $decTest);
        $this->response->setContentType('application/pdf');
        //$this->response->setHeader('Content-Encoding', 'br');
        if (empty($data['LabData'])) {
            echo (view('Templates/NoDetailsPage'));
        } else {
            $data['header']=$header;
            echo (view('PrintPages/LabReport/CultureResult', $data));
        }
    }

    public function BarCode(string $month, string $billNo)
    {
        $data=$this->m->GetOPData(base64_decode($month), base64_decode($billNo));

        $this->response->setContentType('application/pdf');
        echo view('PrintPages/LabReport/Barcode', ['data'=>$data]);
    }
    public function ScanResult($month,$billNo,$test ,$header)
    {
        $decMonth=base64_decode($month);
        $decBillNo=base64_decode($billNo);
        $decTest=base64_decode($test);
        // print_r($billNo);
        // print_r($header);
        $data=$this->m->GetScanData($decMonth, $decBillNo, $decTest);
        // print_r($data[LabData]);

        $this->response->setContentType('application/pdf');
        // $this->response->setHeader('Content-Encoding', 'br');
        if (empty($data['LabData'])) {
            echo (view('Templates/NoDetailsPage'));
        } else {
            $data['header']=$header;
            echo (view('PrintPages/LabReport/ScanResult', $data));
        }
    }
    
}
