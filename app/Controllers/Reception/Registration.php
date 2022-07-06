<?php
namespace App\Controllers\Reception;

use App\Controllers\BaseController;
use App\Models\Reception\Registration_Model;

class Registration extends BaseController
{
    private $m;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('Reg');
        $this->m=new Registration_Model($this->session->get('hosID'));
    }

    public function index():void
    {
        $this->response->setContentType('application/json');
        echo \json_encode($this->m->GetConsultantNames());
    }

    public function Save():void
    {
        $data=json_decode($this->request->getPost('data'));

        $this->response->setContentType('text/plain');
        echo $this->m->SaveRegistration($data,$this->session->get('userName'));
    }
}
