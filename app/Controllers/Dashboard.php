<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MainModels\DashboardModel;

class Dashboard extends Controller
{
    private object $session;
    public function __construct()
    {
        $this->session=session();
    }

    public function index():void
    {
        if ($this->session->has('hosID')===false) {
            die('Login to Continue');
        } else {
            $m=new DashboardModel($this->session->get('hosID'));
            
            $this->response->setContentType('application/json');
            echo json_encode($m->GetCount());
        }
    }
}
