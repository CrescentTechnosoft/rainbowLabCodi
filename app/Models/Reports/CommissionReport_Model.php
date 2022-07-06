<?php namespace App\Models\Reports;

class CommissionReport_Model
{
    private $hosID;
    protected $db;
    
    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetConsultantNames():array
    {
        $builder=$this->db->table('tblDoctorMaster');
        $builder->select('DoctorName')
        ->distinct()
        ->where('HosID', $this->hosID);

        return array_map(function ($val) {
            return $val->DoctorName;
        }, $builder->get()->getResultObject());
    }
    
    public function GetCommission(string $doctor):array
    {
        return $this->db->table('tblCommissionMaster')
        ->select('TestName,CommissionAmount,CommissionType')
        ->distinct()
        ->where(['DoctorName'=>$doctor,'HosID'=>$this->hosID])
        ->get()
        ->getResultObject();
    }

    public function GetBills(string $start, string $end, string $cons):array
    {
        $escStart=$this->db->escapeString($start);
        $escEnd=$this->db->escapeString($end);
        return $this->db->table('tblLabBillDetails d')
        ->select('b.PID,b.PName,b.SubTotal,d.TestName,d.Fees,d.Discount')
        ->join('tblLabBills b', 'b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND b.HosID=d.HosID', 'inner')
        ->where(['b.Consultant'=>$cons,'d.MainCategory<'=>2,'b.HosID'=>$this->hosID])
        ->where("b.BillDate BETWEEN '$escStart' AND '$escEnd'")
        ->get()
        ->getResultObject();
    }
}
