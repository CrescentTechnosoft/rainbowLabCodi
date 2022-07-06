<?php namespace App\Models;

class MyModel extends \CodeIgniter\Model
{
    public function GetData()
    {
        $db=\Config\Database::connect();
        return $db->table('tblTestMaster')
        ->select('TestName,FieldName')
        ->distinct()
        ->where('TestName','CBC')
        ->get();
    }
}
