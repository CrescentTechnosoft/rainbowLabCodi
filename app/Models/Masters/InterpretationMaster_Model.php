<?php namespace App\Models\Masters;

class InterpretationMaster_Model
{
    private int $hosID;
    private object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetConsultants():array
    {
        $builder=$this->db->table('tblDoctorMaster');
        $builder->select('DoctorName')
        ->distinct()
        ->where('HosID', $this->hosID);

        return array_map(fn (object $val):string => $val->DoctorName, $builder->get()->getResultObject());
    }

    public function GetTests(string $cons):array
    {
        $query=<<<QUERY
        select t.ShortName test,IFNULL(c.CommissionAmount,0) commAmount,IFNULL(c.CommissionType,'Percentage') commType from 
        (select distinct(ShortName),HosID from tblTestMaster 
        UNION 
        Select distinct(ProfileName),HosID from tblProfileMaster) t 
        Left OUTER JOIN tblCommissionMaster c on t.ShortName=c.TestName and t.HosID=c.HosID and c.DoctorName=? and c.HosID=? 
        order by t.ShortName
        QUERY;
        return $this->db->query($query, [$cons, $this->hosID])->getResultObject();
    }

    public function SaveCommission(string $doctor, array $data):string
    {
        $builder=$this->db->table('tblCommissionMaster');

        $builder->where(['DoctorName' => $doctor, 'HosID' => $this->hosID])
        ->delete();

        $insertVal = [];
        foreach ($data as $val) {
            $insertVal[] = ['DoctorName' => $doctor, 'TestName' => $val->test, 'CommissionAmount' => $val->commAmount,
                'CommissionType' => $val->commType, 'HosID' => $this->hosID];
        }
        $builder->from('tblCommissionMaster', true)
        ->insertBatch($insertVal);

        return 'Data Saved';
    }
}
