<?php

namespace App\Models\Lab;

class ScanResult_Model
{
    private int $hosID;

    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected object $db;

    public function __construct(int $hosID)
    {
        $this->hosID = $hosID;
        $this->db = \Config\Database::connect();
    }

    public function GetPatientsList(string $key): array
    {
        $query = <<<QUERY
        SELECT DISTINCT `BillDate` `date`,`BillMonth` `month`,`BillNo` `billNo`,`PName` `name`,`ContactNo` `contact` FROM `tblLabBills` 
        WHERE `HosID`=? AND (`BillNo` LIKE ? OR `PName` LIKE ? OR `ContactNo` LIKE ?) ORDER By `BillMonth`,`BillNo` DESC
        QUERY;

        return $this->db->query($query, [$this->hosID, $key . '%', '%' . $key . '%', $key . '%'])->getResultObject();
    }

    public function GetBillDates(): array
    {

        $builder = $this->db->table('tblLabBills');
        $builder->select('BillDate')
            ->distinct()
            ->where('HosID', $this->hosID)
            ->orderBy('BillDate', 'DESC');

        return array_map(fn (object $val): string => $val->BillDate, $builder->get()->getResultObject());
    }

    public function GetBillNos(string $date,): array
    {
        $builder = $this->db->table('tblLabBills t1');
        $builder->join('tblLabBillDetails t21', 't1.BillNo = t21.BillNo AND t1.BillMonth = t21.BillMonth')
            ->select('t1.BillNo,t1.PName')
            ->where(['t1.BillDate' => $date, 't1.HosID' => $this->hosID, 't21.Category' => 'RADIOLOGY'])
            ->orderBy('t1.BillNo', 'DESC')
            ->distinct('t1.BillNo');
        return array_map('array_values', $builder->get()->getResultArray());
    }

    public function GetLabDetails($month, $billNo): array
    {
        if (!$this->ExistInLab($month, $billNo)) {
            return $this->GetBillData($month, $billNo);
        } else {
            return $this->GetLabResult($month, $billNo);
        }
    }

    private function ExistInLab($month,  $billNo): bool
    {
        return $this->db->table('tblScanResult')
            ->select('BillNo')
            ->where(['BillMonth' => $month, 'BillNo' => $billNo, 'HosID' => $this->hosID])
            ->like('Category', 'RADIOLOGY')
            // ->notLike('Category', '')
            ->countAllResults() > 0;
    }

    // private function GetTestFromProfile($profileName): array
    // {
    //     $res = $this->db->table('tblProfileMaster')
    //         ->select('TestName')
    //         ->where(['ProfileName' => $profileName, 'HosID' => $this->hosID])
    //         ->whereNotIn('TestName', 'culture')
    //         ->get()
    //         ->getResultObject();

    //     return array_map(fn (object $val): string => $val->TestName, $res);
    // }

    // private function PopulateTestFields(string $testName): array
    // {
    //     return $this->db->table('tblTestMaster')
    //         ->select("Category category,TestName test,FieldName field,'' result,parameters,NormalValue normal,Method method,'N' norm,0 selected")
    //         ->where(['ShortName' => $testName, 'HosID' => $this->hosID])
    //         ->notLike('TestName', 'culture')
    //         ->get()
    //         ->getResultObject();
    // }

    private function GetBillData($month, $billNo): array
    {
        $res = $this->db->table('tblLabBills b')
            ->select('b.PID,b.PName,b.Age,b.Gender,b.Consultant,d.Category,d.TestName,')
            ->join('tblLabBillDetails d', 'b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND b.HosID=d.HosID')
            ->where(['b.BillMonth' => $month, 'b.BillNo' => $billNo, 'b.HosID' => $this->hosID])
            ->get()
            ->getResultObject();


        $testName=array();
        foreach($res as $test){
          array_push($testName , $test->TestName);
        }
        // $data = ['fields' => []];
        $row = $res[0];
        $data['testNames']= $testName;
        $data['data'] = [
            'id' => $row->PID,
            'name' => $row->PName,
            'age' => $row->Age,
            'gender' => $row->Gender,
            'consultant' => $row->Consultant,
            'isSaved' => false,
            'category' =>  $row->Category
        ];


        return $data;
    }

    private function GetOPResultData(string $month, int $billNo): object
    {
        return  $row = $this->db->table('tblLabBills')
            ->select('PID id,PName name,age,gender,consultant')
            ->where(['BillMonth' => $month, 'BillNo' => $billNo, 'HosID' => $this->hosID])
            ->get()
            ->getRowObject();
    }

    private function GetLabResult(string $month,  $billNo): array
    {
        $data = array();
        $data['data'] = $this->GetOPResultData($month, $billNo);
        $data['data']->isSaved = true;

        $testName=array();
        $test= $this->db->table('tblScanResult r')
            ->select('r.TestName ,r.Category')
            ->where(['r.BillMonth' => $month, 'r.BillNo' => $billNo, 'r.HosID' => $this->hosID])
            ->get()
            ->getResultObject();
        array_push($testName,$test[0]->TestName);
        $data['testNames'] = $testName;

        return $data;
    }
    public function getTestDetails($month, $billNo ,$testName){

        // $test= $this->db->table('tblScanResult r')
        //     ->select('r.Category category, r.Result result,r.Remarks remarks')
        //     ->where(['r.BillMonth' => $month, 'r.BillNo' => $billNo, 'r.TestName' => $testName])
        //     ->get()
        //     ->getResultObject();
        if (!$this->ExistInLab($month, $billNo)) {
            $test= $this->db->table('tblTestMaster r')
            ->select('r.Comments as comments,r.Category category ')
            ->where(['r.ShortName' =>'US COMPLETE ABDOMEN','r.HosID'=>$this->hosID])
            ->get()
            ->getResultObject();
            // print_r($test);
            return $test[0];
        } else {
            $test= $this->db->table('tblScanResult r')
            ->select('r.Category category, r.Result comments')
            ->where(['r.BillMonth' => $month, 'r.BillNo' => $billNo, 'r.TestName' => $testName])
            ->get()
            ->getResultObject();
            return $test[0];

        }
       
    }

    public function SaveResult($month,$data,$userName)
    {
   
        $this->db->table('tblScanResult')
            ->where(['BillMonth' => $month, 'BillNo' => $data->billNo, 'HosID' => $this->hosID])
            ->delete();

        $insertVal = [];
        $date = date('Y-m-d');
        $time = date('H:i:s');
            $insertVal[] =
                [
                    'BillMonth' => $month, 'BillNo' => $data->billNo, '
                    Category' => $data->category, 'TestName' => $data->testName,
                    'Result' => $data->result,
                    'Remarks' => $data->remarks,
                    'RptDate' => $date, 'RptTime' => $time,
                    'ReportedBy' => $userName,  'HosID' => $this->hosID
                ];
        $this->db->table('tblScanResult')
            ->insertBatch($insertVal);

        return 'Lab Result Saved';
    }

    public function DeleteLabResult( $month,  $billNo): string
    {
        $this->db->table('tblScanResult')
            ->where(['BillMonth' => $month, 'BillNo' => $billNo, 'HosID' => $this->hosID])
            ->delete();

        return 'Lab Result Deleted';
    }
}
