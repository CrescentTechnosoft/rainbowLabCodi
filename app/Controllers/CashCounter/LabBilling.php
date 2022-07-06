<?php namespace App\Controllers\CashCounter;

use App\Controllers\BaseController;
use App\Models\CashCounter\Billing_Model;

class LabBilling extends BaseController
{
    /**
     * @var Billing_Model
     */
    private object $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('LabBill');
        $this->m=new Billing_Model($this->session->get('hosID'));
    }
    
    public function index()
    {
        $data=[
            'ids'=>$this->m->GetPID(),
            'consultants'=>$this->m->GetConsultants(),
            'tests'=>$this->m->GetTests(),
            'profiles'=>$this->m->GetProfiles(),
            'feesList'=>$this->m->GetFeesList(),
            'cardTypes'=>$this->m->GetCardTypes(),
            'payTypes'=>$this->m->GetPayTypes()
            ];

        $this->response->setContentType('application/json');
        // $this->response->setHeader('Content-Encoding', 'br');

        echo \json_encode(($data));
    }

    public function GetConsultants():void
    {
        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetConsultants());
    }

    public function SearchPatients()
    {
        $search=$this->request->getGet('key');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetPatientsList($search));
    }

    public function GetPatientID()
    {
        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetPID());
    }

    public function GetPatientDetails()
    {
        $id=$this->request->getGet('id');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetPtDetails($id));
    }

    public function GetFees()
    {
        $data=\json_decode($this->request->getGet('data'));

        $value=[];

        switch ($data->type) {
            case 'Test':
                $value=$this->m->GetTestFees($data->feesType);
            break;
            case 'Profile':
                $value=$this->m->GetProfileFees($data->feesType);
            break;
            case 'Fees':
                $value=$this->m->GetFees($data->feesType);
            break;
            default:
        break;
        }

        $this->response->setContentType('application/json');
        echo \json_encode($value);
    }

    public function Save()
    {
        $data=json_decode($this->request->getPost('data'));
        $fees=json_decode($this->request->getPost('fees'));

        $this->response->setContentType('application/json');
        echo json_encode($this->m->SaveBillDetails($data, $fees, $this->session->get('userName')));
    }

    public function GetMonths()
    {
        $data=['months'=>$this->m->GetMonths(),'billNos'=>[]];
        if (!empty($data['months'])) {
            $data['billNos']=$this->m->GetBills($data['months'][0]);
        }

        $this->response->setContentType('application/json');
        echo \json_encode($data);
    }

    public function GetBillNos()
    {
        $month=$this->request->getGet('month');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetBills($month));
    }

    public function GetBillDetails()
    {
        $data=\json_decode($this->request->getGet('data'));

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetBillDetails($data->month, $data->billNo));
    }

    public function Update()
    {
        $this->ValidateUpdate();
        $data=json_decode($this->request->getPost('data'));
        $fees=json_decode($this->request->getPost('fees'));

        $this->response->setContentType('text/plain');
        echo $this->m->UpdateBillDetails($data, $fees, $this->session->get('userName'));
    }

    public function Delete()
    {
        $this->ValidateDelete();
        $data=\json_decode($this->request->getPost('data'));

        $this->response->setContentType('text/plain');
        echo $this->m->DeleteBillDetails((int)$data->month, (int)$data->billNo);
    }
    
    public function AddBillType()
    {
        $type=$this->request->getPost('type');

        $this->response->setContentType('text/plain');
        echo $this->m->InsertPayType($type);
    }

    public function RemoveBillType()
    {
        $type=$this->request->getPost('type');

        $this->response->setContentType('text/plain');
        echo $this->m->DeletePayType($type);
    }

    public function AddCardType()
    {
        $type=$this->request->getPost('type');

        $this->response->setContentType('text/plain');
        echo $this->m->InsertCardType($type);
    }

    public function RemoveCardType()
    {
        $type=$this->request->getPost('type');

        $this->response->setContentType('text/plain');
        echo $this->m->DeleteCardType($type);
    }
}
