<?php namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\Masters\CommissionMaster_Model;

class CommissionMaster extends BaseController
{
    private $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('CommMas');
        $this->m=new CommissionMaster_Model($this->session->get('hosID'));
    }

    public function index():void
    {
        $this->response->setContentType('application/json');
        echo \json_encode($this->m->GetConsultants());
    }

    public function Get():void
    {
        $consultant=$this->request->getGet('doctor');

        $this->response->setContentType('application/json');
        $this->response->setHeader('Content-Encoding','br');

        echo \brotli_compress(\json_encode($this->m->GetTests($consultant)));
    }

    public function Save():void
    {
        $doctor=$this->request->getPost('doctor');
        $data=\json_decode($this->request->getPost('data'));

        $this->response->setContentType('text/plain');
        echo $this->m->SaveCommission($doctor,$data);
    }
}
