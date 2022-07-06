<?php namespace App\Models\Lab;

class CultureResult_Model
{
    private int $hosID;
    protected object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetBillMonths():array
    {
        $builder=$this->db->table('tblLabBills');

        $builder->select('BillMonth')
        ->distinct()
        ->where('HosID', $this->hosID)
        ->orderBy('BillMonth', 'DESC');

        return array_map(fn (object $val):int =>(int)$val->BillMonth, $builder->get()->getResultObject());
    }

    private function CheckforCultureInProfile(string $profile):bool
    {
        return $this->db->table('tblProfileMaster')
        ->select('TestName')
        ->where(['ProfileName'=>$profile,'HosID'=>$this->hosID])
        ->like('TestName', 'culture', 'both')
        ->countAllResults()>0;
    }

    public function GetBillNos(int $month):array
    {
        $billData= $this->db->table('tblLabBills b')
        ->select('b.BillNo,d.MainCategory,d.TestName')
        ->distinct()
        ->join('tblLabBillDetails d', 'b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND b.HosID=d.HosID')
        ->where("(d.TestName LIKE '%culture%' OR d.MainCategory='Profile')")
        ->where(['b.BillMonth'=>$month,'b.HosID'=>$this->hosID])
        ->orderBy('BillNo', 'DESC')
        ->get()
        ->getResultObject();

        $billNo=array();
        foreach ($billData as $data) {
            if ($data->MainCategory==='Test') {
                $billNo[]=(int)$data->BillNo;
            } elseif ($data->MainCategory==='Profile') {
                if ($this->CheckforCultureInProfile($data->TestName)) {
                    $billNo[]=(int)$data->BillNo;
                }
            }
        }

        return array_values(array_unique($billNo));
    }

    public function GetMedicines():array
    {
        $builder=$this->db->table('tblAntiBiotics');

        $builder->select('AntiBiotic')
        ->distinct()
        ->where('HosID', $this->hosID);

        return array_map(fn (object $val) =>$val->AntiBiotic, $builder->get()->getResultObject());
    }

    private function GetTestNamesFromProfile(string $profile):array
    {
        $profiles=$this->db->table('tblProfileMaster')
        ->select('TestName')
        ->where(['ProfileName'=>$profile,'HosID'=>$this->hosID])
        ->like('TestName', 'culture')
        ->get()
        ->getResultObject();

        return array_map(function ($val) {
            return $val->TestName;
        }, $profiles);
    }

    public function GetPtDetails(int $month, int $billNo):array
    {
        $billData=$this->db->table('tblLabBills b')
        ->select('b.PID,b.PName,b.Age,b.Gender,b.ContactNo,d.MainCategory,d.TestName')
        ->distinct()
        ->join('tblLabBillDetails d', 'b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND b.HosID=d.HosID')
        ->where("(d.TestName LIKE '%culture%' OR d.MainCategory='Profile')")
        ->where(['b.BillMonth'=>$month,'b.BillNo'=>$billNo,'b.HosID'=>$this->hosID])
        ->get()
        ->getResultObject();

        $row=$billData[0];
        $returnData=$tests=array();

        $returnData['data']=['id'=>$row->PID,'name'=>$row->PName,'age'=>$row->Age,'gender'=>$row->Gender];

        foreach ($billData as $data) {
            if ($data->MainCategory==='Test') {
                $tests[]=$data->TestName;
            } elseif ($data->MainCategory==='Profile') {
                array_push($tests, ...$this->GetTestNamesFromProfile($data->TestName));
            }
        }
        $returnData['tests']=array_unique($tests);
        return $returnData;
    }

    public function GetPtTestDetails(int $month, int $billNo, string $test):array
    {
        $result=$this->db->table('tblCultureReport c')
        ->select('c.*,d.AntiBiotic')
        ->join('tblCultureReportDetails d', 'c.BillMonth=d.BillMonth AND c.BillNo=d.BillNo AND c.TestName=d.TestName AND c.HosID=d.HosID', 'left outer')
        ->where(['c.BillMonth'=>$month,'c.BillNo'=>$billNo,'c.TestName'=>$test,'c.HosID'=>$this->hosID])
        ->get()
        ->getResultObject();
        if (empty($result)) {
            return['antiBiotics'=>[],'data'=>['nature'=>'','culture'=>'Growth','isolate'=>'','colony'=>'','includeIsolate2'=>false,'isSaved'=>false]];
        }
        $first=$result[0];
        $returnData = ['antiBiotics'=>[]];
        $returnData['data']= ['nature'=>$first->Specimen,'culture'=>$first->Culture,'isolate'=>$first->Isolate,'colony'=>$first->Colony,'includeIsolate2'=>(bool)$first->IncludeIsolateTwo,'isSaved'=>true];

        foreach ($result as $res) {
            if (!is_null($res->AntiBiotic)) {
                array_push($returnData['antiBiotics'], array_combine(['name','iso1','iso2'], explode('|', $res->AntiBiotic)));
            }
        }
        return $returnData;
    }

    public function SaveCultureResult(object $data, array $antiBiotics, string $userName):string
    {
        $this->DeleteCultureReport($data->month, $data->billNo, $data->test);
        $this->db->table('tblCultureReport')
        ->set(
            [
            'BillMonth'=>$data->month,'BillNo'=>$data->billNo,'TestName'=>$data->test,
            'RptDate'=>date('Y-m-d'),'RptTime'=>date('H:i:s'),'Specimen'=>$data->nature,
            'Culture'=>$data->culture,'Isolate'=>$data->isolate,'Colony'=>$data->colony,
            'IncludeIsolateTwo'=>$data->includeIsolate2,'HosID'=>$this->hosID
            ]
        )
        ->insert();
        
        $insertVal = [];
        foreach ($antiBiotics as $val) {
            $insertVal[] = ['BillMonth'=>$data->month,'BillNo' => $data->billNo, "TestName" => $data->test, "AntiBiotic" =>implode('|', [$val->name,$val->iso1,$val->iso2]),'HosID'=>$this->hosID];
        }

        if (!empty($insertVal)) {
            $this->db->table('tblCultureReportDetails')
            ->insertBatch($insertVal);
        }

        return 'Culture Report Saved';
    }

    public function DeleteCultureReport(int $month, int $billNo, string $testName):string
    {
        $where=['BillMonth'=>$month,'BillNo'=>$billNo,'TestName'=>$testName,'HosID'=>$this->hosID];
        $this->db->table('tblCultureReport')
        ->where($where)
        ->delete();

        $this->db->table('tblCultureReportDetails')
        ->where($where)
        ->delete();
        return 'Culture Report Deleted';
    }

    public function AddMedicine(string $medicine):string
    {
        $this->db->table('tblAntiBiotics')
        ->set(['AntiBiotic'=>$medicine,'HosID'=>$this->hosID])
        ->insert();

        return 'Anti Biotic Medicine Added';
    }

    public function RemoveMedicine(string $medicine):string
    {
        $this->db->table('tblAntiBiotics')
        ->where(['AntiBiotic'=>$medicine,'HosID'=>$this->hosID])
        ->delete();

        return 'Anti Biotic Medicine Removed';
    }
}
