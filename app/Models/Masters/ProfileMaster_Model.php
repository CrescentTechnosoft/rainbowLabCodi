<?php namespace App\Models\Masters;

class ProfileMaster_Model
{
    private int $hosID;
    protected object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetProfileNames():array
    {
        $builder=$this->db->table('tblProfileMaster');
        $builder->select('ProfileName')
        ->distinct()
        ->where('HosID', $this->hosID);

        return array_map(fn (object $val):string => $val->ProfileName, $builder->get()->getResultObject());
    }

    public function GetTestNames():array
    {
        $builder=$this->db->table('tblTestMaster');
        $builder->select('ShortName')
        ->distinct()
        ->where('HosID', $this->hosID);

        return array_map(fn (object $val):string => $val->ShortName, $builder->get()->getResultObject());
    }

    public function SaveProfile(object $data, array $tests):string
    {
        $this->db->table('tblProfileMaster')
        ->where(['ProfileName'=> $data->profile,'HosID'=>$this->hosID])
        ->delete();

        $insertVal = [];
        foreach ($tests as $test) {
            $insertVal[] = ['ProfileName' => $data->profile, 'TestName' => $test, 'Fees' => $data->fees, 'HosID' => $this->hosID];
        }
        
        $this->db->table('tblProfileMaster')
        ->insertBatch($insertVal);

        return 'Profile Added';
    }

    public function GetProfileDetails(string $profileName):array
    {
        $dbResult=$this->db->table('tblProfileMaster')
        ->select('Fees,TestName')
        ->where(['ProfileName'=>$profileName,'HosID'=>$this->hosID])
        ->get()
        ->getResultObject();

        $result=[];
        $result['fees']=$dbResult[0]->Fees;
        $result['tests']=array_map(fn(object $val):string=>$val->TestName,$dbResult);

        return $result;
    }

    public function DeleteProfile(string $profileName):string
    {
        $this->db->table('tblProfileMaster')
        ->where(['ProfileName'=>$profileName,'HosID'=>$this->hosID])
        ->delete();
        return 'Profile Deleted';
    }
}
