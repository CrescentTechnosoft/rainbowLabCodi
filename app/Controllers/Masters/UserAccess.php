<?php namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\Masters\UserAccess_Model;

class UserAccess extends BaseController
{
    private UserAccess_Model $m;

    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('UserAcc');
        $this->m=new UserAccess_Model($this->session->get('hosID'));
    }

    public function index():void
    {
        $data=['Users'=>$this->m->GetUsers(),'Access'=>$this->m->GetAllAccessNames(),'UserAccess'=>[]];
        if (!empty($data['Users'])) {
            $data['UserAccess']=$this->m->GetUserAccess($data['Users'][0]);
        }

        $this->response->setContentType('application/json');
        //$this->response->setHeader('Content-Encoding','br'); -->
        // echo \brotli_compress(\json_encode($data)); -->
        echo \json_encode($data);
    }

    public function GetUserAccess():void
    {
        $user = $this->request->getGet('user');

        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetUserAccess($user));
    }

    public function Save():void
    {
        $user=$this->request->getPost('user');
        $data= json_decode($this->request->getPost('access'));

        $this->response->setContentType('text/plain');
        echo $this->m->SaveAccess($user, $data);
    }
}
