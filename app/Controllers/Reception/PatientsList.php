<?php namespace App\Controllers\Reception;

use App\Controllers\BaseController;
use App\Models\Reception\PatientsList_Model;

class PatientsList extends BaseController
{
    private PatientsList_Model $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('List');
        $this->m=new PatientsList_Model($this->session->get('hosID'));
    }

    public function GetPatientsList():void
    {
        $key=$this->request->getGet('key');
        
        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetPatientsData($key));
    }

    public function Delete():void
    {
        $id=$this->request->getPost('id');

        $this->response->setContentType('text/plain');
        echo $this->m->DeletePatient($id);
    }
}
