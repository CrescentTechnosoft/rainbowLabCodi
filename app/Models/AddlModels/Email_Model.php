<?php namespace App\Models\AddlModels;

class Email_Model
{
    private int $hosID;
    private object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetEmailID($month, int $billNo):string
    {
        $row=$this->db->table('tblLabBills b')
        ->join('tblRegistration r', 'b.PID=r.PID AND b.HosID=r.HosID')
        ->select('r.EmailID')
        ->where(['b.BillMonth'=>$month,'b.BillNo'=>$billNo,'b.HosID'=>$this->hosID])
        ->get()
        ->getRow();

        return is_null($row)?'':$row->EmailID;
    }

    public function GetLabData(string $month, string $billNo):array
    {
        $builder=$this->db->table('tblLabBills');
        $data['OPData'] = $builder
        ->select('BillMonth,BillNo,PID,PName,Age,Gender,Consultant,ContactNo,BillDate,BillTime')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        ->get()
        ->getRowObject();

        $data['CatData']=$builder->from('tblLabResult', true)
        ->select('Category,TestName')
        ->distinct()
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'IsSelected'=>1,'HosID'=>$this->hosID])
        ->get()
        ->getResultObject();

        $data["LabData"] =$builder->from('tblLabResult r', true)
        ->select('r.Category,r.TestName,t.FieldCategory,r.FieldName,r.Result,r.RptDate,r.RptTime,r.ResultType,t.Units,t.NormalValue,t.Comments,t.TotalComments,r.ReportedBy')
        ->join('tblTestMaster t', 'r.TestName=t.TestName AND r.FieldName=t.FieldName AND r.HosID=t.HosID')
        ->where(['r.BillMonth'=>$month,'r.BillNo'=>$billNo,'r.IsSelected'=>1,'r.HosID'=>$this->hosID])
        ->orderBy('r.Alignment', 'ASC')
        ->get()
        ->getResultObject();
        $data['header'] ='WOH';
        return $data;
    }
    public function GetLabDataWhatsapp(string $month, string $billNo, string $header):array
    {
        $builder=$this->db->table('tblLabBills');
        $data['OPData'] = $builder
        ->select('BillMonth,BillNo,PID,PName,Age,Gender,Consultant,ContactNo,BillDate,BillTime')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        ->get()
        ->getRowObject();

        $data['CatData']=$builder->from('tblLabResult', true)
        ->select('Category,TestName')
        ->distinct()
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'IsSelected'=>1,'HosID'=>$this->hosID])
        ->get()
        ->getResultObject();

        $data["LabData"] =$builder->from('tblLabResult r', true)
        ->select('r.Category,r.TestName,t.FieldCategory,r.FieldName,r.Result,r.RptDate,r.RptTime,r.ResultType,t.Units,t.NormalValue,t.Comments,t.TotalComments,r.ReportedBy')
        ->join('tblTestMaster t', 'r.TestName=t.TestName AND r.FieldName=t.FieldName AND r.HosID=t.HosID')
        ->where(['r.BillMonth'=>$month,'r.BillNo'=>$billNo,'r.IsSelected'=>1,'r.HosID'=>$this->hosID])
        ->orderBy('r.Alignment', 'ASC')
        ->get()
        ->getResultObject();
        $data['HeaderType'] =$header;
        return $data;
    }
    public function GetScanData(string $month, string $billNo, string $test):array
    {
        // $builder=$this->db->table('tblLabBills');
        // $data['OPData'] = $builder
        // ->select('BillMonth,BillNo,PID,PName,Age,Gender,Consultant,ContactNo,BillDate,BillTime')
        // ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        // ->get()
        // ->getRowObject();

        // $data['CatData']=$builder->from('tblScanResult', true)
        // ->select('Category,TestName')
        // ->distinct()
        // ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        // ->get()
        // ->getResultObject();

        // $data["ScanData"] =$builder->from('tblScanResult r', true)
        // ->select('r.Category,r.TestName,r.FieldName,r.Result,r.RptDate,r.RptTime,r.Remarks,r.ReportedBy')
        // ->join('tblTestMaster t', 'r.TestName=t.TestName AND r.FieldName=t.FieldName AND r.HosID=t.HosID')
        // ->where(['r.BillMonth'=>$month,'r.BillNo'=>$billNo,'r.HosID'=>$this->hosID])
        // // ->orderBy('r.Alignment', 'ASC')
        // ->get()
        // ->getResultObject();
        // $data['header'] ='WOH';
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
    public function GetScanDataWhatsapp($month, $billNo, $test):array
    {
    //     $builder=$this->db->table('tblLabBills');
    //     $data['OPData'] = $builder
    //     ->select('BillMonth,BillNo,PID,PName,Age,Gender,Consultant,ContactNo,BillDate,BillTime')
    //     ->where(['BillMonth'=>$month,'BillNo'=>$billNo, 'HosID'=>$this->hosID])
    //     ->get()
    //     ->getRowObject();

    //     $data['CatData']=$builder->from('tblScanResult', true)
    //     //->select('Category,TestName')
    //     ->distinct()
    //     ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'TetName'=>$test,'HosID'=>$this->hosID])
    //     ->get()
    //     ->getResultObject();

    //     $data["LabData"] =$builder->from('tblScanResult r', true)
    //     ->select('r.Category,r.TestName,t.FieldCategory,r.FieldName,r.Result,r.RptDate,r.RptTime,r.ReportedBy')
    //     ->join('tblTestMaster t', 'r.TestName=t.TestName AND r.FieldName=t.FieldName AND r.HosID=t.HosID')
    //     ->where(['r.BillMonth'=>$month,'r.BillNo'=>$billNo,'r.HosID'=>$this->hosID])
    //     //->orderBy('r.Alignment', 'ASC')
    //     ->get()
    //     ->getResultObject();
    //    // $data['HeaderType'] =$header;
    //     return $data;
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
