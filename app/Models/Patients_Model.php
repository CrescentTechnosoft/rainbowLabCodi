<?php namespace App\Models;

class Patients_Model
{
    private object $db;

    public function __construct()
    {
        $this->db=\Config\Database::connect();
    }

    public function GetResults(int $id)
    {
        $builder = $this->db->table('tblLabBills b');
        $builder->join('tblLabResult r', 'b.BillMonth=r.BillMonth AND b.BillNo=r.BillNo AND b.HosID=r.HosID')
        ->select('b.BillMonth,b.BillNo,b.BillDate,b.DueAmount')
        ->distinct()
        ->where(['b.PID'=>$id,'DueAmount'=>0]);

        $data=[];

        foreach ($builder->get()->getResultObject() as $val) {
            $due=$val->DueAmount>0;
            $data[]=['month'=>$val->BillMonth,'billNo'=>(int)$val->BillNo,
            'billDate'=>\DateTime::createFromFormat('Y-m-d', $val->BillDate)->format('d/m/Y')];
        }
        return $data;
    }

    public function GetLabData(string $month, int $billNo,int $id)
    {
        $builder=$this->db->table('tblLabBills');
        $data['OPData'] = $builder
        ->select('BillMonth,BillNo,PID,PName,Age,Gender,Consultant,ContactNo,BillDate,BillTime')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'PID'=>$id])
        ->get()
        ->getRowObject();

        $builder->from('tblLabResult', true)
        ->select('Category,TestName')
        ->distinct()
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo]);

        $data['CatData']=$builder->get()->getResultObject();

        $builder->from('tblLabResult r', true)
        ->select('r.Category,r.TestName,t.FieldCategory,r.FieldName,r.Result,r.RptDate,r.RptTime,r.ResultType,t.Units,t.NormalValue,t.Comments,t.TotalComments,r.ReportedBy')
        ->join('tblTestMaster t', 'r.TestName=t.TestName AND r.FieldName=t.FieldName AND r.HosID=t.HosID')
        ->where(['r.BillMonth'=>$month,'r.BillNo'=>$billNo])
        ->orderBy('r.Alignment', 'ASC');
        
        $data["LabData"] = $builder->get()->getResultObject();
        return $data;
    }
}
