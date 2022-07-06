<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MainModels\Auth_Model;

class Auth extends BaseController
{
    public function index()
    {
        $this->session->remove(['hosID','userID','userName','Access']);
        $this->response->setContentType('text/plain');
        echo '';
    }

    public function ValidateLogin():void
    {
        $branch=$this->request->getPost('branch');
        $user=$this->request->getPost('user');
        $pass=$this->request->getPost('pass');
        $ipAddress=$this->request->getIPAddress();
        $hosID=config('CustomValues')->branches[$branch];

        $result=(new Auth_Model)->ValidateUser($user, $pass, $ipAddress, $hosID);
        
        $this->response->setContentType('application/json');
        if ($result->validated===true) {
            echo \json_encode(['status'=>true,'type'=>$result->type,'access'=>$this->GetAccess()]);
        } else {
            echo \json_encode(['status'=>false,'message'=>$result->message]);
        }
    }

    public function AuthenticateUser():void
    {
        $page=$this->request->getGet('page');
        $this->response->setContentType('application/json');
        if (is_null($this->session->get('hosID'))) {
            echo \json_encode(['status'=>'Login']);
        } elseif ($this->ValidateUserAccess($page)===false || !$this->session->has('hasSubscription')) {
            echo \json_encode(['status'=>'Restricted']);
        } else {
            echo \json_encode(['status'=>'Granted','access'=>$this->GetAccess()]);
        }
    }

    private function ValidateUserAccess(string $page):bool
    {
        return in_array($page, $this->GetAccess());
    }

    private function GetAccess():array
    {
        $access=$this->session->get('Access');
        if (is_null($access)) {
            return [];
        } else {
            $allAccess=config('Access')->userAccessNames;
            $allAccess['Dashboard']='Dashboard';

            $userAccess=\json_decode($access);
            $userAccess[]='Dashboard';

            return array_map(fn (string $val):string =>array_search($val, $allAccess), $userAccess);
        }
    }

    public function AuthenticatePatient():void
    {
        $this->response->setContentType('text/plain');
        echo is_null($this->session->get('PID'))?'Failed':'Granted';
    }
}
