<?php namespace App\Controllers\PrintPages;

use App\Controllers\BaseController;
use App\Models\PrintPages\Bills_Model;

class Bills extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser();
    }
    
    public function LabBill(string $month='', string $billNo='')
    {
        if ($this->session->has('hosID')) {
            $decMonth=base64_decode($month);
            $decBillNo=base64_decode($billNo);
            $data=['BillData'=>(new Bills_Model($this->session->get('hosID')))->GetBillDetails($decMonth, $decBillNo)];
            $this->response->setHeader('Content-Type', 'application/pdf');
            echo view('PrintPages/LabBill', $data);
        } else {
            echo view('MainPages/Login');
        }
    }
}
