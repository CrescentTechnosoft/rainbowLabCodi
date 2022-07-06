<?php namespace App\Controllers;

use App\Controllers\BaseController;

class Downloads extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser();
    }

    public function LabResult(string $month, string $billNo, string $header)
    {
        $decMonth=base64_decode($month);
        $decBillNo=base64_decode($billNo);

        $expHeader=explode('-', $header);
        $m=new \App\Models\PrintPages\LabReport_Model($this->session->get('hosID'));
        $data=$m->GetOPLabData($decMonth, $decBillNo, 'A');
        
        if (empty($data['LabData'])) {
            echo "<script>alert('No Details Found!!!');window.close();</script>";
        } else {
            $data['HeaderType']=$expHeader[0];
            $this->response->setContentType('application/pdf');
            $this->response->setHeader('Content-Encoding', 'br');
            $this->response->setHeader('Content-Disposition', 'attachment; filename="Bill No '.$decBillNo.'.pdf"');
            
            echo \json_encode(\view('PrintPages/LabReport/LabReport', $data));
        }
    }
}
