<?php namespace App\Controllers\CashCounter;

use App\Controllers\BaseController;
use App\Models\CashCounter\DueBilling_Model;

class DueBilling extends BaseController
{
    /**
     * @var DueBilling_Model
     */
    private object $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('DueBill');
        $this->m=new DueBilling_Model($this->session->get('hosID'));
    }

    public function index():void
    {
        $data=['months'=>$this->m->GetBillMonths(),'billNos'=>[],'cardTypes'=>$this->m->GetCardTypes(),'payTypes'=>$this->m->GetPayTypes()];
        if (!empty($data['months'])) {
            $data['billNos']=$this->m->GetBillNos($data['months'][0]);
        }

        $this->response->setContentType('application/json');
        echo \json_encode($data);
    }

    public function GetBillNos():void
    {
        $month=$this->request->getGet('month');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetBillNos($month));
    }

    public function GetBillDetails():void
    {
        $data=json_decode($this->request->getGet('data'));

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetBillDetails($data->month, $data->billNo));
    }

    public function UpdateDue():void
    {
        $data=json_decode($this->request->getPost('data'));

        $this->response->setContentType('text/plain');
        echo $this->m->UpdateDueAmount($data);
    }

    public function DeleteBill()
    {
        if (!in_array('delete', \json_decode($this->session->get('Access')))) {
            echo json_encode(['status'=>false,'message'=>'Delete Permission is not Granted']);
        } else {
            $month=$this->request->getPost('month');
            $billNo=$this->request->getPost('billNo');

            $this->response->setContentType('application/json');
            echo json_encode($this->m->DeleteDue($month, $billNo));
        }
    }
}
