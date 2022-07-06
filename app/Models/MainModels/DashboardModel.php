<?php
namespace App\Models\MainModels;

class DashboardModel
{
    private int $hosID;
    protected object $db;

    public function __construct(int $hosID)
    {
        $this->hosID= $hosID;
        $this->db=\Config\Database::connect();
    }
    public function GetCount():array
    {
        $date=date('Y-m-d');
        $data = [];
        // $query = $this->db->query('select distinct(BillNo),PaidAmount from tblLabBills where BillDate=? and HosID=?', [$date, $this->hosID])->getResultArray();
        // $data['LabColl'] = array_sum(array_map(function ($val) {
        //     return $val['PaidAmount'];
        // }, $query));

        $regQuery = $this->db->query('select COUNT(PID) reg from tblRegistration where RegDate=? and HosID=?', [$date, $this->hosID])->getRow();
        $data['Regs'] = (int)$regQuery->reg;

        $billQuery = $this->db->query('select COUNT(BillNo) bill from tblLabBills where BillDate=? and HosID=?', [$date, $this->hosID])->getRow();
        $data['LabBills'] = (int)$billQuery->bill;

        $resultQuery = $this->db->query('select COUNT(DISTINCT(BillNo)) bill from tblLabResult where RptDate=? and HosID=?', [$date, $this->hosID])->getRow();
        $data['LabResults'] = (int)$resultQuery->bill;
        
        $cultureQuery=$this->db->query('select COUNT(BillNo) bill from tblCultureReport where RptDate=? and HosID=?', [$date, $this->hosID])->getRow();
        $data['CultureResults'] = (int)$cultureQuery->bill;

        return $data;
    }
}
