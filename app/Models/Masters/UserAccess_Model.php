<?php namespace App\Models\Masters;

class UserAccess_Model
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
        ->where('HosID', $this->hosID)
        ->orderBy('UserName', 'Asc');

        return array_map(fn (object $val):string =>$val->UserName, $builder->get()->getResultObject());
    }

    private function GetUserID(string $user):int
    {
        $row = $this->db->query('select UserID from tblUsers where UserName=? and HosID=?', [$user, $this->hosID])->getRow();
        return is_null($row) ?0:$row->UserID;
    }

    public function GetAllAccessNames():array
    {
        return array_map(fn (string $value):array =>['access'=>$value,'allowed'=>false], \array_keys(config('Access')->userAccessNames));
    }

    public function GetUserAccess(string $user): array
    {
        $userID = $this->GetUserID($user);
        $row = $this->db->query('select Access from tblUserAccess where UserID=? and HosID=?', [$userID, $this->hosID])->getRow();

        if (is_null($row)) {
            return [];
        } else {
            $allAccess=config('Access')->userAccessNames;
            $userAccess=\json_decode($row->Access);

            //Returning Keys from All Access by Values
            return array_map(fn (string $val):string => \array_search($val, $allAccess), $userAccess);
        }
    }

    public function SaveAccess(string $user, array $data):string
    {
        $userID = $this->GetUserID($user);
        //Delete Old Access (If Exists)
        $this->db->table('tblUserAccess')
        ->where(['UserID' => $userID, 'HosID' => $this->hosID])
        ->delete();

        //Get Access Codes
        $allAccess=config('Access')->userAccessNames;
        $access=array_map(fn (string $val):string=> $allAccess[$val], $data);

        //Insert Access
        $this->db->table('tblUserAccess')
        ->set(['UserID' => $userID, 'Access' => \json_encode($access), 'HosID' => $this->hosID])
        ->insert();

        return 'User Access Saved';
    }
}
