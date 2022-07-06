<?php namespace App\Models\PrintPages;

class LabReport_Model
{
    private int $hosID;

    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    private object $db;

    public function __construct(?int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetOPLabData(string $month, string $billNo, string $sel):array
    {
        $builder=$this->db->table('tblLabBills');
        $data['OPData'] = $builder
        ->select('BillMonth,BillNo,PID,PName,Age,Gender,Consultant,ContactNo,BillDate,BillTime')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        ->get()
        ->getRowObject();

        $builder->from('tblLabResult', true)
        ->select('Category,TestName')
        ->distinct()
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID]);

        if ($sel==='S') {
            $builder->where('IsSelected', true);
        }

        $data['CatData']=$builder->get()->getResultObject();

        $builder->from('tblLabResult r', true)
        ->select('r.Category,r.TestName,t.FieldCategory,r.FieldName,r.Result,r.RptDate,r.RptTime,r.ResultType,t.Units,t.NormalValue,t.Method,t.Comments,t.TotalComments,r.ReportedBy')
        ->join('tblTestMaster t', 'r.TestName=t.TestName AND r.FieldName=t.FieldName AND r.HosID=t.HosID')
        ->where(['r.BillMonth'=>$month,'r.BillNo'=>$billNo,'r.HosID'=>$this->hosID])
        ->orderBy('r.Alignment', 'ASC');

        if ($sel==='S') {
            $builder->where('r.IsSelected', true);
        }
        
        $data["LabData"] = $builder->get()->getResultObject();
        return $data;
    }

    public function GetCultureData(string $month, int $billNo, string $test)
    {
        $data['OPData'] = $this->db->query("select BillMonth,BillNo,PID,PName,Age,Gender,ContactNo,Consultant,Concat(BillDate,' ',BillTime) BillDate from tblLabBills 
        where BillMonth=? AND BillNo=? and HosID=?", [$month,$billNo, $this->hosID])->getRow();

        $data['LabData']=$this->db->table('tblCultureReport c')
        ->select('c.*,d.AntiBiotic')
        ->join('tblCultureReportDetails d', 'c.BillMonth=d.BillMonth AND c.BillNo=d.BillNo AND c.TestName=d.TestName AND c.HosID=d.HosID', 'LEFT OUTER')
        ->where(['c.BillMonth'=>$month,'c.BillNo'=>$billNo,'c.TestName'=>$test,'c.HosID'=> $this->hosID])
        ->get()
        ->GetResultObject();

        return $data;
    }

    public function GetOPData(int $month,int $billNo):object
    {
        return $this->db->table('tblLabBills')
        ->select('BillMonth,BillNo,PName,Age,Gender')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        ->get()
        ->getRow();
    }
    public function GetScanData($month,  $billNo, string $test)
    {
        // $data['OPData'] = $this->db->query("select BillMonth,BillNo,PID,PName,Age,Gender,ContactNo,Consultant,Concat(BillDate,' ',BillTime) BillDate from tblLabBills 
        // where BillMonth=? AND BillNo=? and HosID=?", [$month,$billNo, $this->hosID])->getRow();

        $data['OPData']=$this->db->table('tblLabBills')
        ->select('BillMonth,BillNo,PID,PName,Age,Gender,ContactNo,Consultant,BillDate,BillTime')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        ->get()
        ->getRow();
        $data['LabData']=$this->db->table('tblScanResult')
        ->select('*')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'TestName'=>$test,'HosID'=> $this->hosID])
        ->get()
        ->GetResultObject();

        return $data;
    }
    
}
