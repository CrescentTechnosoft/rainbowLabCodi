<?php namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\Masters\UserMaster_Model;

class UserMaster extends BaseController
{
    private UserMaster_Model $m;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('UserMas');
        $this->m=new UserMaster_Model($this->session->get('hosID'));
    }

    public function index():void
    {
        $this->response->setContentType('application/json');
        echo \json_encode($this->m->GetUsers());
    }

    public function Add():void
    {
        $data=\json_decode($this->request->getPost('data'));

        $this->response->setContentType('application/json');
        echo json_encode($this->m->AddUser($data));
    }

    public function Update():void
    {
        $data=\json_decode($this->request->getPost('data'));

        $this->response->setContentType('text/plain');
        echo $this->m->UpdatePassword($data);
    }

    public function Delete():void
    {
        $user=$this->request->getPost('user');

        $this->response->setContentType('text/plain');
        echo $this->m->DeleteUser($user);
    }
}
