<?php namespace App\Controllers\Lab;

use App\Controllers\BaseController;
use App\Models\Lab\PendingReports_Model;

class PendingReports extends BaseController
{
    private PendingReports_Model $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('PendRes');
        $this->m=new PendingReports_Model($this->session->get('hosID'));
    }
    
    public function index()
    {
        $data=['months'=>$this->m->GetBillMonths(),'bills'=>['tests'=>[],'cultures'=>[]]];
        if (!empty($data['months'])) {
            $data['bills']=$this->m->GetPendingBills($data['months'][0]);
        }
        $this->response->setContentType('application/json');
        echo \json_encode($data);
    }

    public function GetBills()
    {
        $month=$this->request->getGet('month');

        $this->response->setContentType('application/json');
        echo \json_encode($this->m->GetPendingBills($month));
    }
}
