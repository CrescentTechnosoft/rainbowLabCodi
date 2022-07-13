<?php namespace App\Models\Masters;

class TestMaster_Model
{
    private int $hosID;
    protected object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetCategories():array
    {
        $builder=$this->db->table('tblTestMaster');
        $builder->select('Category')
        ->distinct()
        ->where('HosID', $this->hosID);
        
        return array_map(fn (object $val):string =>$val->Category, $builder->get()->getResultObject());
    }

    public function GetTestNames():array
    {
        $builder=$this->db->table('tblTestMaster');
        $builder->select('TestName')
        ->distinct()
        ->where('HosID', $this->hosID)
        ->orderBy('TestName');
        
        return array_map(fn (object $val):string =>$val->TestName, $builder->get()->getResultObject());
    }

    public function GetTestFields(string $testName):array
    {
        $returnData=array('fields'=>[]);
        $builder=$this->db->table('tblTestMaster');
        $builder->select('Category,TestName,ShortName,Fees,FieldCategory,rbsDiscount,FieldName,Method,Sample,Units,NormalValue,Comments,Parameters')
        ->distinct()
        ->where(['TestName'=>$testName,'HosID'=>$this->hosID]);

        $result=$builder->get()->getResultObject();
        if (!empty($result)) {
            $first=$result[0];
            
            //Category,TestName,Fees
            $returnData['data']=['category'=>$first->Category,'test'=>$first->TestName,'short'=>$first->ShortName,'fees'=>$first->Fees,'rbsDiscount'=>$first->rbsDiscount];

            //Fields
            foreach ($result as $res) {
                array_push($returnData['fields'], [
                    'fieldCat'=>$res->FieldCategory,'field'=>$res->FieldName,'method'=>$res->Method,'sample'=>$res->Sample,
                    'units'=>$res->Units,'normal'=>$res->NormalValue,'comment'=>$res->Comments,'parameters'=>$res->Parameters]);
            }
        }
        return $returnData;
    }

    public function SaveTest(object $data, array $fields):string
    {
        $this->db->table('tblTestMaster')
        ->where(['TestName' => $data->test, 'HosID' => $this->hosID])
        ->delete();

        $short=$data->short!==''?$data->short:$data->test;

        $insertval = [];
        foreach ($fields as $field) {
            $insertval[] = [
                'Category'=>$data->category,'TestName'=>$data->test,'ShortName'=>$short,'Fees'=>$data->fees,
                'rbsDiscount'=>$data->rbsDiscount,'FieldCategory'=>$field->fieldCat,'FieldName'=>$field->field,'Method'=>$field->method,
                'Sample'=>$field->sample,'Units'=>$field->units,'NormalValue'=>$field->normal,'Comments'=>$field->comment,
                'Parameters'=>$field->parameters,'HosID'=>$this->hosID
            ];
        }

        $this->db->table('tblTestMaster')
        ->insertBatch($insertval);

        return 'Test Details Saved';
    }


    private function GetShortName(string $test):?object
    {
        return $this->db->table('tblTestMaster')
        ->select('ShortName')
        ->where(['TestName'=>$test,'HosID'=>$this->hosID])
        ->get()
        ->getRow();
    }

    public function DeleteTest(string $test):string
    {
        $shortName=$this->GetShortName($test);

        $builder=$this->db->table('tblCommissionMaster');
        if (!is_null($shortName)) {
            $builder->where(['TestName'=>$shortName->ShortName,'HosID'=>$this->hosID])
            ->delete();
        }

        $builder->from('tblTestMaster', true)
        ->where(['TestName'=>$test,'HosID'=>$this->hosID])
        ->delete();

        return 'Test Details Deleted';
    }
}
