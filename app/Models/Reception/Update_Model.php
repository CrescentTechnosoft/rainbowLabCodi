<?php namespace App\Models\Reception;

class Update_Model
{
    private int $hosID;
    private object $db;

    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetPatientDetails(int $id):?object
    {
        return $this->db->table('tblRegistration')
        ->select('PID id,Saluation salutation,PName name,Age age,Gender gender,Mobile contact,EmailID email,DOB dob,Address address,Consultant consultant')
        ->where(['PID'=>$id,'HosID'=>$this->hosID])
        ->get()
        ->getRowObject();
    }

    public function GetConsultants():array
    {
        $builder=$this->db->table('tblDoctorMaster');
        $builder->select('DoctorName')
        ->distinct()
        ->where('HosID', $this->hosID)
        ->orderBy('DoctorName');

        return array_map(fn (object $val):string =>$val->DoctorName, $builder->get()->getResultObject());
    }

    public function UpdatePatient(object $data):string
    {
        $this->db->table('tblRegistration')
        ->set([
            'Saluation'=>$data->salutation,
            'PName'=>$data->name,
            'Age'=>$data->age,
            'Gender'=>$data->gender,
            'DOB'=>$data->dob,
            'Mobile'=>$data->contact,
            'EmailID'=>$data->email,
            'Address'=>$data->address,
            'Consultant'=>$data->consultant
        ])
        ->where(['PID'=>$data->id,'HosID'=>$this->hosID])
        ->update();

        return 'Patient Details Updated';
    }
}
