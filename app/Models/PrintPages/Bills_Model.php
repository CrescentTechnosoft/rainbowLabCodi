<?php namespace App\Models\PrintPages;

class Bills_Model
{
    private int $hosID;
    private object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }
    
    public function GetBillDetails(int $month, int $billNo):array
    {
        return $this->db->table('tblLabBills b')
        ->select('b.*,d.TestName,d.Fees,d.Discount')
        ->join('tblLabBillDetails d', 'b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND b.HosID=d.HosID', 'INNER')
        ->where(['b.BillMonth'=> $month,'b.BillNo'=> $billNo,'b.HosID'=> $this->hosID])
        ->get()
        ->getResultObject();
    }
}
