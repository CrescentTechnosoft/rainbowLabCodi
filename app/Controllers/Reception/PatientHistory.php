<?php namespace App\Controllers\Reception;

use App\Controllers\BaseController;
use App\Models\Reception\PatientHistory_Model;

class PatientHistory extends BaseController
{
    private $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('PtHist');
        $this->m=new PatientHistory_Model($this->session->get('hosID'));
    }

    public function index()
    {
        $data=['Header'=>'Patient History','firstLink'=>'Reception','secondLink'=>'Patient History',
        'content'=>get_view_path('Reception/PatientHistory'),'ID'=>$this->m->GetPID()];

        echo view('Templates/Header', $data);
    }

    public function SearchPatient()
    {
        $search=$this->request->getGet('search');
        echo json_encode($this->m->GetPatientsList($search));
    }

    public function GetPatientDetails()
    {
        $id=$this->request->getPost('id');
        echo json_encode($this->m->GetPtDetails($id));
    }
}
