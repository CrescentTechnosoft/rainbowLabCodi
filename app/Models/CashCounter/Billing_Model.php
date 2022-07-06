<?php

namespace App\Models\CashCounter;

class Billing_Model {

    private int $hosID;
    protected object $db;

    public function __construct(int $hosID) {
        $this->hosID = $hosID;
        $this->db = \Config\Database::connect();
    }

    public function GetPatientsList(string $key): array {
        return $this->db->table('tblRegistration')
                        ->select('PID id,PName name,Mobile contact')
                        ->distinct()
                        ->where('HosID', $this->hosID)
                        ->like('PID', $key, 'after')
                        ->orLike('PName', $key, 'after')
                        ->orLike('Mobile', $key, 'after')
                        ->get()
                        ->getResultArray();
    }

    public function GetPID(): array {
        $res = $this->db->table('tblRegistration')
                ->select('PID')
                ->where('HosID', $this->hosID)
                ->orderBy('PID', 'DESC')
                ->limit(250)
                ->get()
                ->getResultObject();

        return array_map(fn(object $val): int => (int) $val->PID, $res);
    }

    public function GetTests(): array {
        $builder = $this->db->table('tblTestMaster');
        $builder->select('ShortName')
                ->distinct()
                ->where('HosID', $this->hosID);

        return array_map(fn(object $val): string => $val->ShortName, $builder->get()->getResultObject());
    }

    public function GetProfiles(): array {
        $builder = $this->db->table('tblProfileMaster');
        $builder->select('ProfileName')
                ->distinct()
                ->where('HosID', $this->hosID);

        return array_map(fn(object $val): string => $val->ProfileName, $builder->get()->getResultObject());
    }

    public function GetFeesList(): array {
        $builder = $this->db->table('tblFeesMaster');
        $builder->select('FeesName')
                ->distinct()
                ->where('HosID', $this->hosID)
                ->get()
                ->getResultArray();

        return array_map(fn(object $val): string => $val->FeesName, $builder->get()->getResultObject());
    }

    public function GetConsultants(): array {
        $builder = $this->db->table('tblDoctorMaster');
        $builder->select('DoctorName')
                ->distinct()
                ->where('HosID', $this->hosID)
                ->orderBy('DoctorName');

        return array_map(fn(object $val): string => $val->DoctorName, $builder->get()->getResultObject());
    }

    public function GetPtDetails(int $id): object {
        return $this->db->table('tblRegistration')
                        ->select('CONCAT(Saluation,\'.\',PName) name,Age age,Gender gender,Mobile contact,Consultant consultant')
                        ->where(['PID' => $id, 'HosID' => $this->hosID])
                        ->get()
                        ->getRowObject();
    }

    public function GetTestFees(string $test): object {
        return $this->db->table('tblTestMaster')
                        ->select('Category category,Fees fees')
                        ->where(['ShortName' => $test, 'HosID' => $this->hosID])
                        ->get()
                        ->getRow();
    }

    public function GetProfileFees(string $profile): object {
        return $this->db->table('tblProfileMaster')
                        ->select('\'Profile\' category,Fees fees')
                        ->where(['ProfileName' => $profile, 'HosID' => $this->hosID])
                        ->get()
                        ->getRow();
    }

    public function GetFees(string $fees): object {
        return $this->db->table('tblFeesMaster')
                        ->select('Category category,Fees fees')
                        ->where(['FeesName' => $fees, 'HosID' => $this->hosID])
                        ->get()
                        ->getRow();
    }

    private function GenerateBillNo(int $month): int {
        $row = $this->db->table('tblLabBills')
                ->selectMax('BillNo')
                ->where(['BillMonth' => $month, 'HosID' => $this->hosID])
                ->get()
                ->GetRow();
        return is_null($row) ? 1 : ((int) $row->BillNo) + 1;
    }

    public function SaveBillDetails(object $data, array $fees, string $userName): array {
        $month = (int) date('Ym');
        $billNo = $this->GenerateBillNo($month);

        $insertData = [
            'BillMonth' => $month, 'BillNo' => $billNo, 'PID' => $data->id, 'PName' => $data->name, 'Age' => $data->age,
            'Gender' => $data->gender, 'ContactNo' => $data->contact, 'Consultant' => $data->consultant,
            'Total' => $data->total, 'TotalDiscount' => $data->discount, 'SubTotal' => $data->subTotal, 'PaidAmount' => $data->paying,
            'InitialPaid' => $data->initPaid, 'DueAmount' => $data->due, 'RefundAmount' => $data->refund, 'BillType' => $data->billType, 'OtherPayments' => $data->otherType,
            'CardNo' => $data->cardNo, 'CardType' => $data->cardType, 'CardExpiry' => $data->cardExpiry, 'Cash' => $data->cash,
            'Card' => $data->card, 'BillDate' => date('Y-m-d'), 'BillTime' => date('H:i:s'), 'BilledBy' => $userName,
            'HosID' => $this->hosID
        ];

        $builder = $this->db->table('tblLabBills');
        $builder->set($insertData)
                ->insert();

        $data = [];
        foreach ($fees as $fee) {
            $data[] = [
                'BillMonth' => $month, 'BillNo' => $billNo, 'MainCategory' => $fee->type, 'Category' => $fee->category,
                'TestName' => $fee->feesType, 'Fees' => floatval($fee->fees), 'Discount' => floatval($fee->discount), 'HosID' => $this->hosID];
        }
        $builder->from('tblLabBillDetails', true)
                ->set($data)
                ->insertBatch();

        return ['month' => $month, 'billNo' => $billNo];
    }

    public function GetMonths(): array {
        $builder = $this->db->table('tblLabBills');
        $builder->select('BillMonth')
                ->distinct()
                ->where('HosID', $this->hosID)
                ->orderBy('BillMonth', 'desc');

        return array_map(fn(object $val): int => (int) $val->BillMonth, $builder->get()->getResultObject());
    }

    public function GetBills(int $month): array {
        return $this->db->table('tblLabBills')
                        ->select('billNo,PName as name')
                        ->where(['BillMonth' => $month, 'HosID' => $this->hosID])
                        ->orderBy('BillNo', 'desc')
                        ->get()->getResultObject();
    }

    public function GetBillDetails(int $month, int $billNo): array {
        $data = array('feesData' => []);

        $result = $this->db->table('tblLabBills b')
                ->select('b.*,d.MainCategory,d.Category,d.TestName,d.Fees,d.Discount')
                ->join('tblLabBillDetails d', 'b.BillMonth=d.BillMonth AND b.BillNo=d.BillNo AND b.HosID=d.HosID', 'INNER')
                ->where(['b.BillMonth' => $month, 'b.BillNo' => $billNo, 'b.HosID' => $this->hosID])
                ->get()
                ->getResultObject();

        $first = $result[0];

        $data['data'] = [
            'txtID' => $first->PID, 'name' => $first->PName, 'age' => $first->Age, 'gender' => $first->Gender,
            'contact' => $first->ContactNo, 'consultant' => $first->Consultant, 'total' => $first->Total,
            'discount' => $first->TotalDiscount, 'subTotal' => $first->SubTotal, 'paying' => $first->PaidAmount,
            'initPaid' => $first->InitialPaid,
            'due' => $first->DueAmount, 'refund' => $first->RefundAmount, 'billType' => $first->BillType,
            'otherType' => $first->OtherPayments, 'cardNo' => $first->CardNo, 'cardType' => $first->CardType,
            'cash' => $first->Cash, 'card' => $first->Card, 'cardExpiry' => $first->CardExpiry
        ];

        foreach ($result as $value) {
            array_push($data['feesData'], ['type' => $value->MainCategory, 'category' => $value->Category, 'feesType' => $value->TestName, 'fees' => $value->Fees, 'discount' => $value->Discount]);
        }

        return $data;
    }

    public function UpdateBillDetails(object $data, array $fees, string $userName): string {
        $builder = $this->db->table('tblLabBillDetails');
        $builder->where(['BillMonth' => $data->month, 'BillNo' => $data->billNo, 'HosID' => $this->hosID])
                ->delete();

        $builder->from('tblLabBills', true)
                ->set([
                    'PID' => $data->txtID, 'PName' => $data->name, 'Age' => $data->age, 'Gender' => $data->gender, 'ContactNo' => $data->contact,
                    'Consultant' => $data->consultant, 'Total' => $data->total, 'TotalDiscount' => $data->discount, 'SubTotal' => $data->subTotal,
                    'PaidAmount' => $data->paying, 'DueAmount' => $data->due, 'RefundAmount' => $data->refund, 'BillType' => $data->billType,
                    'OtherPayments' => $data->otherType, 'CardNo' => $data->cardNo, 'CardType' => $data->cardType,
                    'CardExpiry' => $data->cardExpiry, 'Cash' => $data->cash, 'Card' => $data->card, 'BilledBy' => $userName,
                ])
                ->where(['BillMonth' => $data->month, 'BillNo' => $data->billNo, 'HosID' => $this->hosID])
                ->update();

        $batchData = [];
        foreach ($fees as $detail) {
            $batchData[] = [
                'BillMonth' => $data->month, 'BillNo' => $data->billNo, 'MainCategory' => $detail->type, 'Category' => $detail->category,
                'TestName' => $detail->feesType, 'Fees' => floatval($detail->fees), 'Discount' => floatval($detail->discount), 'HosID' => $this->hosID
            ];
        }
        $builder->from('tblLabBillDetails', true)
                ->insertBatch($batchData);

        return 'Data Updated';
    }

    public function DeleteBillDetails(int $month, int $billNo): string {
        $builder = $this->db->table('tblLabBills');
        $builder->where(['BillMonth' => $month, 'BillNo' => $billNo, 'HosID' => $this->hosID])->delete('', null, false);
        $builder->from('tblLabBillDetails', true)->delete('', null, false);
        $builder->from('tblLabResult', true)->delete('', null, false);
        $builder->from('tblCultureReport', true)->delete('', null, false);
        $builder->from('tblCultureReportDetails', true)->delete('', null, false);

        return 'Data Deleted';
    }

    public function InsertPayType(string $type): string {
        $this->db->table('tblOtherPayType')
                ->set(['Others' => $type, 'HosID' => $this->hosID])
                ->insert();

        return 'Pay Type Added';
    }

    public function DeletePayType(string $type): string {
        $this->db->table('tblOtherPayType')
                ->where(['Others' => $type, 'HosID' => $this->hosID])
                ->delete();

        return 'Pay Type Removed';
    }

    public function GetPayTypes(): array {
        $builder = $this->db->table('tblOtherPayType');
        $builder->select('Others')
                ->distinct()
                ->where('HosID', $this->hosID);

        return array_map(fn(object $val): string => $val->Others, $builder->get()->getResultObject());
    }

    public function InsertCardType(string $type): string {
        $this->db->table('tblCardType')
                ->set(['CardType' => $type, 'HosID' => $this->hosID])
                ->insert();

        return 'Card Type Added';
    }

    public function DeleteCardType(string $type): string {
        $this->db->table('tblCardType')
                ->where(['CardType' => $type, 'HosID' => $this->hosID])
                ->delete();

        return 'Card Type Removed';
    }

    public function GetCardTypes() {
        $builder = $this->db->table('tblCardType');
        $builder->select('CardType')
                ->distinct()
                ->where('HosID', $this->hosID);

        return array_map(fn(object $val): string => $val->CardType, $builder->get()->getResultObject());
    }

}
