<?php namespace App\Controllers\Lab;

use App\Controllers\BaseController;
use App\Models\Lab\LabResult_Model;

class LabResult extends BaseController
{
    /**
     * @var LabResult_Model
     */
    private LabResult_Model $m;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('LabRes');
        $this->m=new LabResult_Model($this->session->get('hosID'));
    }
    
    public function index()
    {
        // $data=['dates'=>$this->m->GetBillDates(),'bills'=>[]];

        // if (!empty($data['dates'])) {
            $data['bills']=$this->m->GetBillNos(date('Y-m-d'));
        // }
        
        $this->response->setContentType('application/json');
        echo \json_encode($data);
    }

    public function SearchPatients()
    {
        $search=$this->request->getGet('key');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetPatientsList($search));
    }

    public function GetBillNos()
    {
        $date=$this->request->getGet('date');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetBillNos($date));
    }

    public function GetBillDetails()
    {
        $data=\json_decode($this->request->getGet('data'));

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetLabDetails($data->month,  $data->billNo));
    }

    public function Save()
    {
        $data=\json_decode($this->request->getPost('data'));
        $fields=\json_decode($this->request->getPost('fields'));

        $this->response->setContentType('text/plain');
        echo $this->m->SaveLabResult($data, $fields, $this->session->get('userName'));
    }

    public function Delete()
    {
        $data=\json_decode($this->request->getPost('data'));

        $this->response->setContentType('text/plain');
        echo $this->m->DeleteLabResult($data->month, $data->billNo);
    }
}
