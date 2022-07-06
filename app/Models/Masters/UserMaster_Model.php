<?php namespace App\Models\Masters;

class UserMaster_Model
{
    private int $hosID;
    protected object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetUsers():array
    {
        $builder=$this->db->table('tblUsers');

        $builder->select('UserName')
        ->where('HosID', $this->hosID);

        return array_map(fn (object $val):string =>$val->UserName, $builder->get()->getResultObject());
    }

    private function CheckUserExistence(string $userName):int
    {
        return $this->db->table('tblUsers')
        ->select('UserID')
        ->where(['UserName'=>$userName,'HosID'=>$this->hosID])
        ->countAllResults();
    }

    public function AddUser(object $data):array
    {
        $result = ['exists'=>false];
        if ($this->CheckUserExistence($data->user) < 1) {
            $hash = \password_hash($data->pass, PASSWORD_BCRYPT);
            $data = ["UserName" => $data->user, "Password" => $hash, "HosID" => $this->hosID, "IsActive" => true];

            $this->db->table('tblUsers')->insert($data);
        } else {
            $result['exists'] = true;
        }
        return $result;
    }

    public function UpdatePassword(object $data):string
    {
        $this->db->table('tblUsers')
        ->set('Password', \password_hash($data->pass, PASSWORD_BCRYPT))
        ->where(['UserName'=>$data->user,'HosID'=>$this->hosID])
        ->update();

        return 'Password Updated...';
    }

    private function GetUserID(string $user):int
    {
        return $this->db->table('tblUsers')
        ->select('UserID')
        ->where(['UserName'=>$user,'HosID'=>$this->hosID])
        ->get()
        ->getRow()
        ->UserID;
    }

    public function DeleteUser(string $user):string
    {
        $userID=$this->GetUserID($user);

        $builder=$this->db->table('tblUsers');

        $builder->where(['UserName'=>$user,'HosID'=>$this->hosID])
        ->delete();

        $builder->from('tblUserAccess', true)
        ->where(['UserID'=>$userID,'HosID'=>$this->hosID])
        ->delete();

        return 'User Deleted';
    }
}
