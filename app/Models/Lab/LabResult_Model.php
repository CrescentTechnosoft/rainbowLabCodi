<?php namespace App\Models\Lab;

class LabResult_Model
{
    private int $hosID;

    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected object $db;
    
    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetPatientsList(string $key):array
    {
        $query=<<<QUERY
        SELECT DISTINCT `BillDate` `date`,`BillMonth` `month`,`BillNo` `billNo`,`PName` `name`,`ContactNo` `contact` FROM `tblLabBills` 
        WHERE `HosID`=? AND (`BillNo` LIKE ? OR `PName` LIKE ? OR `ContactNo` LIKE ?) ORDER By `BillMonth`,`BillNo` DESC
        QUERY;

        return $this->db->query($query,[$this->hosID,$key.'%','%'.$key.'%',$key.'%'])->getResultObject();
    }

    public function GetBillDates():array
    {
        
        $builder=$this->db->table('tblLabBills');
        $builder->select('BillDate')
        ->distinct()
        ->where('HosID', $this->hosID)
        ->orderBy('BillDate', 'DESC');

        return array_map(fn (object $val):string =>$val->BillDate, $builder->get()->getResultObject());
    }

    public function GetBillNos(string $date):array
    {
        $builder=$this->db->table('tblLabBills t1');

        $builder->join('tblLabBillDetails t21', 't1.BillNo = t21.BillNo AND t1.BillMonth = t21.BillMonth')
        ->select('t1.BillNo,t1.PName')
        ->where(['t1.BillDate' => $date, 't1.HosID' => $this->hosID,])
        ->notLike('t21.Category','RADIOLOGY')
        ->orderBy('t1.BillNo', 'DESC')
        ->distinct('t1.BillNo');
        
        // $builder->select('BillNo,PName')
        // ->where(['BillDate'=>$date,'HosID'=>$this->hosID])
        // ->orderBy('BillNo', 'DESC');

        return array_map('array_values', $builder->get()->getResultArray());
    }

    public function GetLabDetails(int $month, int $billNo):array
    {
        if (!$this->ExistInLab($month, $billNo)) {
            return $this->GetBillData($month, $billNo);
        } else {
            return $this->GetLabResult($month, $billNo);
        }
    }

    private function ExistInLab(int $month, int $billNo):bool
    {
        return $this->db->table('tblLabResult')
        ->select('BillNo')
        ->where(['BillMonth'=> $month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        ->notLike('TestName', 'culture')
        ->countAllResults()>0;
    }

    private function GetTestFromProfile($profileName):array
    {
        $res=$this->db->table('tblProfileMaster')
        ->select('TestName')
        ->where(['ProfileName'=>$profileName,'HosID'=>$this->hosID])
        // ->whereNotIn('TestName', 'culture')
        ->get()
        ->getResultObject();
        
        return array_map(fn (object $val):string => $val->TestName, $res);
    }

    private function PopulateTestFields(string $testName):array
    {
        return $this->db->table('tblTestMaster')
        ->select("Category category,TestName test,FieldName field,'' result,parameters,NormalValue normal,Method method,'N' norm,0 selected")
        ->where(['ShortName'=>$testName,'HosID'=>$this->hosID])
        ->notLike('TestName', 'culture')
        ->get()
        ->getResultObject();
    }

    private function GetBillData(string $month, int $billNo):array
    {
        $res =$this->db->table('tblLabBills b')
        ->select('b.PID,b.PName,b.Age,b.Gender,b.Consultant,d.Category,d.TestName,d.MainCategory')
        ->join('tblLabBillDetails d', 'b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND b.HosID=d.HosID')
        ->where(['b.BillMonth'=> $month,'b.BillNo'=> $billNo,'b.HosID'=> $this->hosID])
        ->get()
        ->getResultObject();
        
        $data=['fields'=>[]];
        $row = $res[0];
        $data['data'] = ['id'=>$row->PID, 'name'=>$row->PName,'age'=>$row->Age,'gender'=>$row->Gender,'consultant'=> $row->Consultant,'isSaved'=> false];

        $testNames = [];
        foreach ($res as $val) {
            if ($val->MainCategory==='Test') {
                $testNames[] = $val->TestName;
            } elseif ($val->MainCategory==='Profile') {
                array_push($testNames, ...$this->GetTestFromProfile($val->TestName));
            }
        }
        foreach (array_unique($testNames) as $test) {
            $tempData=$this->PopulateTestFields($test);
            if (!empty($tempData)) {
                array_push($data['fields'], ...$tempData);
            }
        }
        return $data;
    }

    private function GetOPResultData(string $month, int $billNo):object
    {
        return  $row=$this->db->table('tblLabBills')
        ->select('PID id,PName name,age,gender,consultant')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        ->get()
        ->getRowObject();
    }

    private function GetLabResult(string $month, int $billNo):array
    {
        $data = array();
        $data['data'] = $this->GetOPResultData($month, $billNo);
        $data['data']->isSaved=true;

        $data['fields'] = $this->db->table('tblLabResult r')
        ->select('t.category,r.TestName test,r.FieldName field,r.Result result,t.parameters,t.NormalValue normal,t.Method method,r.ResultType norm,r.IsSelected selected')
        ->join('tblTestMaster t', 't.TestName=r.TestName AND t.FieldName=r.FieldName AND t.HosID=r.HosID', 'INNER')
        ->where(['r.BillMonth'=>$month,'r.BillNo'=>$billNo,'r.HosID'=>$this->hosID])
        ->orderBy('Alignment', 'ASC')
        ->get()
        ->getResultObject();

        return $data;
    }

    public function SaveLabResult(object $data, array $fields, string $userName):string
    {
        $this->db->table('tblLabResult')
        ->where(['BillMonth'=>$data->month,'BillNo' => $data->billNo,'HosID'=>$this->hosID])
        ->delete();

        $insertVal = [];
        $date=date('Y-m-d');
        $time=date('H:i:s');
        foreach ($fields as $i => $val) {
            $insertVal[] =
            [
                'BillMonth'=>$data->month,'BillNo' => $data->billNo,'Category' => $val->category,'TestName' => $val->test,
                'FieldName' => $val->field,'Result' => $val->result,'ResultType'=>$val->norm,
                'IsSelected' => $val->selected,'RptDate' => $date,'RptTime'=>$time,
                'ReportedBy'=>$userName,'Alignment'=>$i,'HosID' => $this->hosID
            ];
        }

        $this->db->table('tblLabResult')
        ->insertBatch($insertVal);

        return 'Lab Result Saved';
    }

    public function DeleteLabResult(int $month, int $billNo):string
    {
        $this->db->table('tblLabResult')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        ->delete();

        return 'Lab Result Deleted';
    }
}
