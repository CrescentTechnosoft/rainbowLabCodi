<?php
namespace App\Models\Reception;

class Registration_Model
{
    private int $hosID;
    private object $db;
    
    public function __construct(int $hosID)
    {
        $this->hosID=$hosID;
        $this->db=\Config\Database::connect();
    }

    public function GetConsultantNames():array
    {
        $builder=$this->db->table('tblDoctorMaster');

        $builder->select('DoctorName')
        ->distinct()
        ->where('HosID', $this->hosID)
        ->orderBy('DoctorName');
        
        return array_map(fn (object $val):string =>$val->DoctorName, $builder->get()->getResultObject());
    }

    private function PatientExists(string $name, string $contact):bool
    {
        return $this->db->table('tblRegistration')
        ->where(['PName'=>$name,'Mobile'=>$contact,'HosID'=>$this->hosID])
        ->countAllResults()>0;
    }

    private function GenerateID():int
    {
        $id=$this->db->table('tblRegistration')
        ->selectMax('PID')
        ->where('HosID', $this->hosID)
        ->get()
        ->getRow()
        ->PID;

        return is_null($id)?1:intval($id)+1;
    }

    public function SaveRegistration(object $data, string $user):string
    {
        if ($this->PatientExists($data->name, $data->contact)) {
            return 'Exists';
        }
        
        $id= $this->GenerateID();

        $builder=$this->db->table('tblRegistration');
        $builder->set([
            'PID'=>$id,
                'Saluation'=>$data->salutation,
                'PName'=>$data->name,
                'Age'=>$data->age,
                'Gender'=>$data->gender,
                'DOB'=>$data->dob,
                'Mobile'=>$data->contact,
                'EmailID'=>$data->email,
                'Address'=>$data->address,
                'RegDate'=>date('Y-m-d'),
                'RegTime'=>date('H:i:s'),
                'Consultant'=>$data->consultant,
                'UserName'=>$user,
                'HosID'=>$this->hosID
        ])->insert();

        return (string)$id;
    }
}
