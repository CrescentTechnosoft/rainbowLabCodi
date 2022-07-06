<?php namespace App\Models\Lab;

use CodeIgniter\Model;

class PendingReports_Model extends Model
{
    private $hosID;
    protected $db;
    
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

        return array_map(function ($val) {
            return $val->BillMonth;
        }, $builder->get()->getResultObject());
    }

    public function GetPendingBills(string $month):array
    {
        return ['tests'=>$this->GetPendingList($month),'cultures'=>$this->GetCultureList($month)];
    }

    public function GetName(string $month, array $billNos):array
    {
        return $this->db->table('tblLabBills')
        ->select('billNo,PName name,age,gender,ContactNo contact')
        ->where(['BillMonth'=>$month,'HosID'=>$this->hosID])
        ->whereIn('BillNo', $billNos)
        ->orderBy('BillNo')
        ->get()
        ->getResultObject();
    }

    public function CheckTestInProfile($profile)
    {
        return $this->db->table('tblProfileMaster')
        ->select('TestName')
        ->where(['ProfileName'=>$profile,'HosID'=>$this->hosID])
        ->notLike('TestName', 'culture', 'both')
        ->countAllResults();
    }

    public function GetPendingList(string $month)
    {
        $bills =$this->db->table('tblLabBills b')
        ->select('b.BillNo,d.MainCategory,d.TestName')
        ->join('tblLabBillDetails d', 'b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND b.HosID=d.HosID', 'INNER')
        ->where(['b.BillMonth'=>$month,'b.HosID'=>$this->hosID])
        ->where("((d.MainCategory='Test' AND d.TestName NOT LIKE '%culture%') OR d.MainCategory='Profile')")
        ->get()
        ->getResultObject();

        $billNos=array();
        foreach ($bills as $bill) {
            if (intval($bill->MainCategory)===0) {
                $billNos[]=$bill->BillNo;
            } elseif (intval($bill->MainCategory)===1) {
                if ($this->CheckTestInProfile($bill->TestName)>0) {
                    $billNos[] = $bill->BillNo;
                }
            }
        }

        $builder=$this->db->table('tblLabResult');
        $builder->select('BillNo')
        ->where(['BillMonth'=>$month,'HosID'=>$this->hosID]);

        $result = array_map(function ($val) {
            return $val->BillNo;
        }, $builder->get()->getResultObject());

        $diff = array_diff($billNos, $result);
        $unique = array_unique(array_values($diff));
        sort($unique);
        return count($unique)>0?$this->GetName($month, $unique):[];
    }
    
    
    
    public function CheckCultureInProfile(string $profile):int
    {
        return $this->db->table('tblProfileMaster')
        ->select('TestName')
        ->where(['ProfileName'=>$profile,'HosID'=>$this->hosID])
        ->like('TestName', 'culture', 'both')
        ->countAllResults();
    }

    public function GetCultureList(string $month):array
    {
        $bills =$this->db->table('tblLabBills b')
        ->select('b.BillNo,d.MainCategory,d.TestName')
        ->join('tblLabBillDetails d', 'b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND b.HosID=d.HosID', 'INNER')
        ->where(['b.BillMonth'=>$month,'b.HosID'=>$this->hosID])
        ->where("((d.MainCategory='Test' AND d.TestName LIKE '%culture%') OR d.MainCategory='Profile')")
        ->get()
        ->getResultObject();

        $billNos=array();
        foreach ($bills as $bill) {
            if (intval($bill->MainCategory)===0) {
                $billNos[]=$bill->BillNo;
            } elseif (intval($bill->BillNo)===1) {
                if ($this->CheckCultureInProfile($bill->TestName)>0) {
                    $billNos[] = $bill->BillNo;
                }
            }
        }

        $builder=$this->db->table('tblCultureReport');
        $builder->select('BillNo')
        ->where(['BillMonth'=>$month,'HosID'=>$this->hosID]);

        $result = array_map(function ($val) {
            return $val->BillNo;
        }, $builder->get()->getResultObject());

        $diff = array_diff($billNos, $result);
        $unique = array_unique(array_values($diff));
        sort($unique);
        return count($unique)>0?$this->GetName($month, $unique):[];
    }
}
