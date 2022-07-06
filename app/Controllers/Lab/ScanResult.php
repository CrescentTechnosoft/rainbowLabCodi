<?php namespace App\Controllers\Lab;

use App\Controllers\BaseController;
use App\Models\Lab\ScanResult_Model;

class ScanResult extends BaseController
{
    /**
     * @var LabResult_Model
     */
    private ScanResult_Model $m;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('LabRes');
        $this->m=new ScanResult_Model($this->session->get('hosID'));
    }
    
    public function index($billDate)
    {
        // $data=['dates'=>$this->m->GetBillDates(),'bills'=>[]];

        // if (!empty($data['dates'])) {
            $data['bills']=$this->m->GetBillNos($billDate);
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

    public function GetBillDetails($month,$billNo)
    {
        // $data=\json_decode($this->request->getGet('data'));

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetLabDetails($month,  $billNo));
    }

    public function SaveResult()
    {
        $month=json_decode($this->request->getPost('billMonth'));
        $data=json_decode($this->request->getPost('datas'));
        // print_r($data);
        // return '';
        $this->response->setContentType('text/plain');
        echo $this->m->SaveResult($month, $data, $this->session->get('userName'));
    }
    public function getTestDetails(){
        $data=\json_decode($this->request->getGet('data'));
        // print_r($data);
        // $month=json_decode($this->request->getPost($data->month));
        // $billNo=json_decode($this->request->getPost('billNo'));
        // $testName=json_decode($this->request->getPost('testName'));
        // print_r(base64_decode($testName));
        $this->response->setContentType('application/json');
        echo json_encode($this->m->getTestDetails($data->month,  $data->billNo,$data->testName));
    }
    public function DeleteResult($month,$billNo,)
    {

        $this->response->setContentType('text/plain');
        echo $this->m->DeleteLabResult($month, $billNo);
    }
}
