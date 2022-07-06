<?php namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\Masters\FeesMaster_Model;

class FeesMaster extends BaseController
{
    private FeesMaster_Model $m;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('FeesMas');
        $this->m=new FeesMaster_Model($this->session->get('hosID'));
    }

    public function index()
    {
        $this->response->setContentType('application/json');
        $this->response->setHeader('Content-Encoding', 'br');

        echo \brotli_compress(\json_encode($this->m->GetFees()));
    }

    public function Save()
    {
        $data=json_decode($this->request->getPost('data'));
        $this->response->setContentType('application/json');
        echo \json_encode($this->m->SaveFees($data));
    }

    public function Update()
    {
        $data=json_decode($this->request->getPost('data'));

        $this->response->setContentType('text/plain');
        echo $this->m->UpdateFees($data);
    }

    public function Delete()
    {
        $id=$this->request->getPost('id');
        
        $this->response->setContentType('text/plain');
        echo $this->m->DeleteFees($id);
    }
}
