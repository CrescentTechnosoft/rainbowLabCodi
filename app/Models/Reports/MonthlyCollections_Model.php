<?php namespace App\Models\Reports;

class MonthlyCollections_Model
{
    private int $hosID;
    private object $db;

    function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    function GetData(string $startDate, string $endDate,string $cons=''):array
    {

        $escStart=$this->db->escapeString($startDate);
        $escEnd=$this->db->escapeString($endDate);

        $labBills= $this->db->table('tblLabBills')
        ->select('BillDate,InitialPaid,BillType')
        ->where('HosID', $this->hosID)
        ->where("BillDate BETWEEN '$escStart' AND '$escEnd'", null, false)
        ->getCompiledSelect();

        $dueBills=$this->db->table('tblDueCollected')
        ->select('CollectedDate,DuePaid,PayType')
        ->where('HosID', $this->hosID)
        ->where("CollectedDate BETWEEN '$escStart' AND '$escEnd'", null, false)
        ->getCompiledSelect();

        return $this->db->query($labBills.' UNION ALL '.$dueBills)->getResultObject();
    }
}
