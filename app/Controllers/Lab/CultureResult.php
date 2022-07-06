<?php namespace App\Controllers\Lab;

use App\Controllers\BaseController;
use App\Models\Lab\CultureResult_Model;

class CultureResult extends BaseController
{
    private object $m;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('CultRes');
        $this->m=new CultureResult_Model($this->session->get('hosID'));
    }

    public function index():void
    {
        $data=['months'=>$this->m->GetBillMonths(),'billNos'=>[],'medicines'=>$this->m->GetMedicines()];
        if (!empty($data['months'])) {
            $data['billNos']=$this->m->GetBillNos($data['months'][0]);
        }
        $this->response->setContentType('application/json');
        echo json_encode($data);
    }

    public function GetBillNos():void
    {
        $month=$this->request->getGet('month');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetBillNos($month));
    }

    public function GetBillDetails():void
    {
        $data=\json_decode($this->request->getGet('data'));

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetPtDetails($data->month, $data->billNo));
    }

    public function GetTestDetails():void
    {
        $data=\json_decode($this->request->getGet('data'));

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetPtTestDetails($data->month, $data->billNo, $data->test));
    }

    public function Save():void
    {
        $data=\json_decode($this->request->getPost('data'));
        $antiBiotics=json_decode($this->request->getPost('antiBiotics'));

        $this->response->setContentType('text/plain');
        echo $this->m->SaveCultureResult($data, $antiBiotics, $this->session->get('userName'));
    }

    public function Delete():void
    {
        $data=\json_decode($this->request->getPost('data'));

        $this->response->setContentType('text/plain');
        echo $this->m->DeleteCultureReport($data->month, $data->billNo, $data->test);
    }

    public function AddMedicine():void
    {
        $medicine=$this->request->getPost('medicine');
        $this->response->setContentType('text/plain');

        echo $this->m->AddMedicine($medicine);
    }

    public function RemoveMedicine():void
    {
        $medicine=$this->request->getPost('medicine');
        $this->response->setContentType('text/plain');

        echo $this->m->RemoveMedicine($medicine);
    }
}
