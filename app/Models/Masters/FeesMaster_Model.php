<?php namespace App\Models\Masters;

class FeesMaster_Model
{
    private int $hosID;
    private object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetFees():array
    {
        return $this->db->table('tblFeesMaster')
        ->select('id,category,FeesName feesType,fees')
        ->distinct()
        ->where('HosID', $this->hosID)
        ->orderBy('ID', 'DESC')
        ->get()
        ->getResultObject();
    }

    private function GetFeesID():int
    {
        return $this->db->query('select MAX(ID) ID from tblFeesMaster where HosID=?', $this->hosID)->getRow()->ID;
    }

    public function SaveFees(object $data):array
    {
        $this->db->table('tblFeesMaster')
        ->set(['Category' => $data->category, 'FeesName' => $data->feesType,'Fees' => $data->fees, 'HosID' => $this->hosID])
        ->insert();
        return ['message'=>'Fees Saved','id'=>$this->GetFeesID()];
    }

    public function UpdateFees(object $data):string
    {
        $this->db->table('tblFeesMaster')
        ->set(['Category' => $data->category, 'FeesName' => $data->feesType,'Fees' => $data->fees])
        ->where(['ID' => $data->id, 'HosID' => $this->hosID])
        ->update();
        
        return 'Fees Updated';
    }

    public function DeleteFees(int $id):string
    {
        $this->db->table('tblFeesMaster')
        ->where(['ID'=>$id,'HosID'=> $this->hosID])
        ->delete();
        return 'Fees Deleted';
    }
}
