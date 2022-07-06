<?php namespace App\Models\Masters;

class DoctorMaster_Model
{
    private int $hosID;
    protected object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetDoctorsDetails():array
    {
        return $this->db->table('tblDoctorMaster')
        ->select('ID id,DoctorName name,Age age,Gender gender,Contact contact,EmailID email,Address address')
        ->where('HosID', $this->hosID)
        ->orderBy('DoctorName', 'ASC')
        ->get()
        ->getResultObject();
    }

    private function GenerateID():int
    {
        return $this->db->query('select IFNULL(MAX(ID),0)+1 as ID from tblDoctorMaster where HosID=?', $this->hosID)->getRow()->ID;
    }

    public function SaveDoctorDetails(object $details):int
    {
        $id = intval($details->id) !== 0 ? $details->id:$this->GenerateID();

        if (intval($details->id!==0)) {
            $consultant=$this->GetDoctorName($details->id);
            $this->db->table('tblLabBills')
            ->where('Consultant',$consultant)
            ->set('Consultant',$details->name)
            ->update();
        }
        if (intval($details->id)!==0) {
            $this->db->query('delete from tblDoctorMaster where ID=? and HosID=?', [$details->id, $this->hosID]);
        }

        $this->db->table('tblDoctorMaster')
        ->set([
            'ID' => $id, 'DoctorName' => $details->name, 'Age' => $details->age, 'Gender' => $details->gender,
            'Contact' => $details->contact, 'EmailID' => $details->email, 'Address' => $details->address, 'HosID' => $this->hosID
        ])
        ->insert();

        return intval($id);
    }

    public function GetDoctorName(int $id):string
    {
        return $this->db->table('tblDoctorMaster')
        ->select('DoctorName')
        ->where(['ID' => $id, 'HosID' => $this->hosID])
        ->get()
        ->getRow()
        ->DoctorName;
    }

    public function DeleteDoctorDetails(int $id):string
    {
        $builder=$this->db->table('tblCommissionMaster');

        $builder->where(['DoctorName'=>$this->GetDoctorName($id),'HosID'=>$this->hosID])
        ->delete();

        $builder->from('tblDoctorMaster', true)
        ->where(['ID' => $id, 'HosID' => $this->hosID])
        ->delete();
        
        return 'Doctor Details Deleted';
    }
}
