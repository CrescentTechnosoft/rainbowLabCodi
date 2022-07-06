<?php namespace App\Models\Reports;

use CodeIgniter\Model;

class RegistrationReport_Model extends Model
{
    private $hosID;
    protected $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetRegistrationsList(string $start, string $end)
    {
        $escStart=$this->db->escapeString($start);
        $escEnd=$this->db->escapeString($end);
        
        return $this->db->table('tblRegistration')
        ->select('PID,PName,Age,Gender,Mobile,EmailID,RegDate')
        ->where('HosID', $this->hosID)
        ->where("RegDate BETWEEN '$escStart' AND '$escEnd'")
        ->get()
        ->getResultObject();
    }
}
