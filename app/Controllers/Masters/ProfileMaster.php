<?php namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\Masters\ProfileMaster_Model;

class ProfileMaster extends BaseController
{
    private ProfileMaster_Model $m;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('ProfMas');
        $this->m=new ProfileMaster_Model($this->session->get('hosID'));
    }

    public function index():void
    {
        $this->response->setContentType('application/json');
        //$this->response->setHeader('Content-Encoding','br');

        echo \json_encode(['profiles'=>$this->m->GetProfileNames(),'tests'=>$this->m->GetTestNames()]);
    }

    public function Save():void
    {
        $data=\json_decode($this->request->getPost('data'));
        $tests=\json_decode($this->request->getPost('tests'));

        $this->response->setContentType('text/plain');
        echo $this->m->SaveProfile($data, $tests);
    }

    public function Get():void
    {
        $profile=$this->request->getGet('profile');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetProfileDetails($profile));
    }

    public function Delete():void
    {
        $profile=$this->request->getPost('profile');

        $this->response->setContentType('text/plain');
        echo $this->m->DeleteProfile($profile);
    }
}
