<?php namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\Masters\TestMaster_Model;

class TestMaster extends BaseController
{
    private TestMaster_Model $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('TestMas');
        $this->m=new TestMaster_Model($this->session->get('hosID'));
    }

    public function index():void
    {
        $this->response->setContentType('application/json');
        //$this->response->setHeader('Content-Encoding','br');

        echo json_encode(['categories'=>$this->m->GetCategories(),'tests'=>$this->m->GetTestNames()]);
    }

    public function GetTestDetails():void
    {
        $test=$this->request->getGet('test');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetTestFields($test));
    }

    public function Save():void
    {
        $data=json_decode($this->request->getPost('data'));
        $fields=\json_decode($this->request->getPost('fields'));

        $this->response->setContentType('text/plain');
        echo $this->m->SaveTest($data,$fields);
    }

    public function Delete():void
    {
        $test=$this->request->getPost('test');

        $this->response->setContentType('text/plain');
        echo $this->m->DeleteTest($test);
    }
}
