<?php namespace App\Models\Reception;

use CodeIgniter\Model;

class PatientHistory_Model extends Model
{
    private $hosID;
    protected $db;
    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }
    
    public function GetPatientsList(string $key):array
    {
        $res=$this->db->table('tblRegistration')
        ->select('PID,PName,Mobile')
        ->where('HosID', $this->hosID)
        ->like('PID', $key, 'after')
        ->orLike('PName', $key, 'after')
        ->orLike('Mobile', $key, 'after')
        ->get()
        ->getResultArray();

        return array_map('array_values', $res);
    }

    public function GetPID():array
    {
        $res=$this->db->table('tblRegistration')
        ->select('PID')
        ->where('HosID', $this->hosID)
        ->orderBy('PID', 'DESC')
        ->limit(250)
        ->get()
        ->getResultObject();

        return array_map(function ($val) {
            return $val->PID;
        }, $res);
    }

    public function GetPtDetails(int $id):array
    {
        $row=$this->db->table('tblRegistration')
        ->select('PName,Age,Gender,Mobile,PtType')
        ->where(['PID'=>$id,'HosID'=>$this->hosID])
        ->get()
        ->getRow();

        return ['name'=>$row->PName,'age'=>$row->Age,'gender'=>$row->Gender,'contact'=>$row->Mobile,
        'type'=>$row->PtType,'bill'=>$this->GetBillDetails($id),
        'lab'=>$this->GetLabDetails($id),'culture'=>$this->GetCultureDetails($id),
        'due'=>$this->GetDueAmount($id)
        ];
    }

    private function GetBillDetails(int $id):array
    {
        $res=$this->db->table('tblLabBills')
        ->select('BillMonth,BillNo,BillDate')
        ->where(['PID'=>$id,'HosID'=>$this->hosID])
        ->get()
        ->getResultArray();

        return array_map('array_values', $res);
    }

    private function GetLabDetails(int $id):array
    {
        $res=$this->db->table('tblLabBills b')
        ->select('b.BillMonth,b.BillNo,b.BillDate')
        ->distinct()
        ->join('tblLabResult r', 'b.BillMonth=r.BillMonth AND b.BillNo=r.BillNo AND b.HosID=r.HosID', 'inner')
        ->where(['b.PID'=>$id,'b.HosID'=>$this->hosID])
        ->get()
        ->getResultArray();

        return array_map('array_values', $res);
    }

    private function GetCultureDetails(int $id):array
    {
        $res=$this->db->table('tblLabBills b')
        ->select('b.BillMonth,b.BillNo,b.BillDate,r.TestName')
        ->join('tblCultureReport r', 'b.BillMonth=r.BillMonth AND b.BillNo=r.BillNo AND b.HosID=r.HosID', 'inner')
        ->where(['b.PID'=>$id,'b.HosID'=>$this->hosID])
        ->get()
        ->getResultArray();

        return array_map('array_values', $res);
    }

    public function GetDueAmount(int $id):float
    {
        $due=$this->db->table('tblLabBills')
        ->selectSum('DueAmount', 'due')
        ->where(['PID'=>$id,'HosID'=>$this->hosID])
        ->get()
        ->getRow()
        ->due;

        return is_null($due)?0:$due;
    }
}
