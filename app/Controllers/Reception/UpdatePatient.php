<?php namespace App\Controllers\Reception;

use App\Controllers\BaseController;
use App\Models\Reception\Update_Model;

class UpdatePatient extends BaseController
{
    private Update_Model $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('PtUpd');
        $this->m=new Update_Model($this->session->get('hosID'));
    }

    public function index(string $id='0'):void
    {
        if (is_numeric($id)) {
            $data=['details'=>$this->m->GetPatientDetails($id),'cons'=>$this->m->GetConsultants()];

            $this->response->setContentType('application/json');
            echo json_encode($data);
        }
    }

    public function Update():void
    {
        $data=json_decode($this->request->getPost('data'));

        $this->response->setContentType('text/plain');
        echo $this->m->UpdatePatient($data);
    }
}
