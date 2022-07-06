<?php

namespace App\Models\CashCounter;

class DueBilling_Model
{
    private int $hosID;
    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct(int $hosID)
    {
        $this->hosID = $hosID;
        $this->db = \Config\Database::connect();
    }

    public function GetBillMonths(): array
    {
        $builder = $this->db->table('tblLabBills');

        $builder->select('BillMonth')
                ->distinct()
                ->where(['HosID' => $this->hosID])
                ->orderBy('BillMonth', 'desc');

        return array_map(fn (object $val): int => (int) $val->BillMonth, $builder->get()->getResultObject());
    }

    public function GetBillNos(int $month): array
    {
        return $this->db->table('tblLabBills')
                        ->select('billNo,PName as name')
                        ->distinct()
                        ->where(['BillMonth' => $month, 'DueAmount>' => 0, 'HosID' => $this->hosID])
                        ->orderBy('BillNo', 'desc')
                        ->get()->getResultObject();
    }

    public function GetPayTypes(): array
    {
        $builder = $this->db->table('tblOtherPayType');
        $builder->select('Others')
                ->distinct()
                ->where('HosID', $this->hosID);

        return array_map(fn (object $val): string => $val->Others, $builder->get()->getResultObject());
    }

    public function GetCardTypes()
    {
        $builder = $this->db->table('tblCardType');
        $builder->select('CardType')
                ->distinct()
                ->where('HosID', $this->hosID);

        return array_map(fn (object $val): string => $val->CardType, $builder->get()->getResultObject());
    }

    public function GetBillDetails(int $month, int $billNo): object
    {
        return $this->db->table('tblLabBills')
                        ->select('PName name,Age age,Gender gender,ContactNo contact,DueAmount due')
                        ->where(['BillMonth' => $month, 'BillNo' => $billNo, 'HosID' => $this->hosID])
                        ->get()
                        ->getRow();
    }

    public function UpdateDueAmount(object $data): string
    {
        $this->db->table('tblLabBills')
                ->set(['PaidAmount' => 'PaidAmount+' . $data->paying, 'DueAmount' => 'DueAmount-' . $data->paying], '', false)
                ->where(['BillMonth' => $data->month, 'BillNo' => $data->billNo, 'HosID' => $this->hosID])
                ->update();

        $this->db->table('tblDueCollected')
                ->set(['BillMonth' => $data->month, 'BillNo' => $data->billNo, 'CollectedDate' => date('Y-m-d'),
                    'CollectedTime' => date('H:i:s'), 'DuePaid' => $data->paying, 'PayType' => $data->payType, 'OtherType' => $data->otherType, 'CardNo' => $data->cardNo,
                    'CardType' => $data->cardType, 'CardExpiry' => $data->cardExpiry, 'HosID' => $this->hosID])
                ->insert();

        return (int) $data->balance === 0 ? 'Due Cleared' : 'Due Updated';
    }


    public function DeleteDue(int $month, int $billNo):array
    {
        $isExists=$this->db->table('tblDueCollected')
        ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
        ->countAllResults()>0;

        if (!$isExists) {
            return ['status'=>false,'message'=>'Bill not Found!!!'];
        } else {
            $duePaid=$this->db->table('tblDueCollected')
            ->where(['BillMonth'=>$month,'BillNo'=>$billNo,'HosID'=>$this->hosID])
            ->get()
            ->getRowObject()
            ->DuePaid;

            $duePaid=floatval($duePaid);
            $this->db->table('tblLabBills')
                ->set(['PaidAmount' => 'PaidAmount-' . $duePaid, 'DueAmount' => 'DueAmount+' . $duePaid], '', false)
                ->where(['BillMonth' => $month, 'BillNo' => $billNo, 'HosID' => $this->hosID])
                ->update();

            $this->db->table('tblDueCollected')
            ->where(['BillMonth' => $month, 'BillNo' => $billNo, 'HosID' => $this->hosID])
            ->delete();

            return ['status'=>true,'message'=>'Bill Deleted'];
        }
    }
}
