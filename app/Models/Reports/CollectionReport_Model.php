<?php namespace App\Models\Reports;

use CodeIgniter\Model;

class CollectionReport_Model extends Model
{
    private $hosID;
    protected $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetConsultants():array
    {
        $builder=$this->db->table('tblDoctorMaster')
        ->select('DoctorName')
        ->where('HosID', $this->hosID)
        ->orderBy('DoctorName');

        return array_map(fn (object $val):string =>$val->DoctorName, $builder->get()->getResultObject());
    }

    public function GetCollection(string $cons, string $date):array
    {
        $builder=$this->db->table('tblLabBills');

        $builder->select('BillMonth,BillNo,PName,Age,Total,TotalDiscount,SubTotal,InitialPaid,PaidAmount,DueAmount,BillDate,BillType,OtherPayments,Consultant')
        ->where('HosID', $this->hosID)
        ->where('BillDate', $date);

        if ($cons!=='All') {
            $builder->where('Consultant', $cons);
        }

        return $builder->get()->getResultObject();
    }

    public function GetDueCollected(string $date):array
    {
        $query=<<<QUERY
        SELECT b.BillMonth,b.BillNo,b.PName,b.Age,d.CollectedDate,d.DuePaid,PayType,OtherType from tblLabBills b
        INNER JOIN tblDueCollected d ON b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND
        b.HosID=d.HosID WHERE d.HosID=? AND d.CollectedDate =?
        QUERY;

        return $this->db->query($query, [$this->hosID,$date])->getResultObject();
    }
}
