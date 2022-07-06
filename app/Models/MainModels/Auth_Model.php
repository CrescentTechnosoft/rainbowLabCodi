<?php namespace App\Models\MainModels;

class Auth_Model
{
    private object $db;
    public function __construct()
    {
        $this->db=\Config\Database::connect();
    }

    public function ValidateUser(string $userName, string $password, string $ipAddress,int $hosID):object
    {
        $row=$this->db->table('tblUsers')
        ->select('UserID,UserName,Password,HosID')
        ->where(['UserName'=>$userName,'HosID'=>$hosID])
        ->get()
        ->getRow();

        if (!is_null($row) && password_verify($password, $row->Password)) {
            $this->AddSession($row);
            $this->LogUsersLogin($row, $ipAddress);
            return (object)['validated'=>true,'type'=>'Lab'];
        // } elseif ($this->ValidatePatient($userName, $password)) {
        //     session()->set('PID', $userName);
        //     return (object)['validated'=>true,'type'=>'Patient'];
        } elseif (is_null($row)) {
            return (object)['validated'=>false,'message'=>'User Name doesn\'t Exists'];
        } elseif (!password_verify($password, $row->Password)) {
            return (object)['validated'=>false,'message'=>'Incorrect Password'];
        }
        //  elseif ($this->isSubscriptionExpired($row)) {
        //     return (object)['validated'=>false,'message'=>'Your Subscription has been Expired'];
        // }
    }

    public function ValidatePatient(string $id, string $contact):bool
    {
        return $this->db->table('tblRegistration')
        ->where(['PID'=>$id,'Mobile'=>$contact])
        ->countAllResults()>0;
    }

    public function isSubscriptionExpired(object $row):bool
    {
        $expiry=$this->db->table('tblSubscriptions')
        ->select('ExpiryDate')
        ->where('HosID', $row->HosID)
        ->get()
        ->getRow()
        ->ExpiryDate;

        return \strtotime($expiry)<strtotime(date('Y-m-d'));
    }

    private function AddSession(object $row)
    {
        session()->set(
            [
                'hosID'=>(int)$row->HosID,
                'userID'=>(int)$row->UserID,
                'userName'=>$row->UserName,
                'Access'=>$this->GetAccess($row),
                'hasSubscription'=>false
            ]
        );
    }

    private function LogUsersLogin(object $row, string $ipAddress)
    {
        $this->db->table('tblLoginManager')
        ->set(['UserID'=>$row->UserID,'UserName'=>$row->UserName,'LoginDate'=>date('Y-m-d'),'LoginTime'=>date('H:i:s'),'IPAddress'=>$ipAddress,'HosID'=>$row->HosID])
        ->insert();
    }

    private function GetAccess(object $row):string
    {
        $row=$this->db->table('tblUserAccess')
        ->select('Access')
        ->where(['UserID'=>$row->UserID,'HosID'=>$row->HosID])
        ->get()
        ->getRow();
        return is_null($row)?'':$row->Access;
    }
}
