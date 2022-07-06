<?php
namespace App\Models;

use \CodeIgniter\Model;

class Website_Model extends Model
{
    protected $db;
    public function __construct()
    {
        $this->db=\Config\Database::connect();
    }

    public function GetTests()
    {
        return array_map(
            'array_values',
            $this->db->table('tblTestMaster')
            ->select('ShortName,Fees')
            ->distinct()
            ->get()
            ->getResultArray()
        );
    }

    public function GetProfiles()
    {
        return array_map(
            'array_values',
            $this->db->table('tblProfileMaster')
            ->select('ProfileName,Fees')
            ->distinct()
            ->get()
            ->getResultArray()
        );
    }
}
