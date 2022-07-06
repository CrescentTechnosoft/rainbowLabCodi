<?php namespace App\Models\Reception;

class PatientsList_Model
{
    private int $hosID;
    private object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetPatientsData(string $key):array
    {
        $query=<<<QUERY
        SELECT DISTINCT `PID` `id`,CONCAT(`Saluation`,'.',`PName`) `name`,`age`,`gender`,`Mobile` `contact` from 
        `tblRegistration` WHERE `HosID`=? AND (`PID` LIKE ? OR `PName` LIKE ? OR `Mobile` LIKE ?)
        QUERY;

        return $this->db->query($query,[$this->hosID,$key.'%',$key.'%',$key.'%'])->getResultObject();
    }

    public function DeletePatient(int $id):string
    {
        $this->db->table('tblRegistration')
        ->where(['PID' => $id, 'HosID' => $this->hosID])
        ->delete();

        return 'Data Deleted';
    }
}
