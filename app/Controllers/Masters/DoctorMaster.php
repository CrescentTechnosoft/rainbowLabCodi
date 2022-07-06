<?php namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\Masters\DoctorMaster_Model;

class DoctorMaster extends BaseController
{
    private DoctorMaster_Model $m;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('DocMas');
        $this->m=new DoctorMaster_Model($this->session->get('hosID'));
    }

    public function index():void
    {
        $this->response->setContentType('application/json');
        $this->response->setHeader('Content-Encoding','br');
        echo \brotli_compress(\json_encode($this->m->GetDoctorsDetails()));
    }

    public function Save():void
    {
        $data = json_decode($this->request->getPost("data"));

        $this->response->setContentType('text/plain');
        echo $this->m->SaveDoctorDetails($data);
    }

    public function Delete():void
    {
        $id = $this->request->getPost('id');

        $this->response->setContentType('text/plain');
        echo $this->m->DeleteDoctorDetails($id);
    }
}
