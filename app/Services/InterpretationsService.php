<?php
namespace App\Services;

class InterpretationsService{
    public $commissions;
    public function GetCommissionAmount(string $testName, float $fees):float
    {
        $commission=0;
        foreach ($this->commissions as $comm) {
            if ($comm->TestName===$testName) {
                $amt=floatval($comm->CommissionAmount);
                $commission=$comm->CommissionType==='Percentage'?(($fees/100)*$amt):$amt;
                break;
            }
        }
        return $commission;
    }

    public function GetCommission(string $testName):array
    {
        $commission=[0,'Amount'];
        foreach ($this->commissions as $comm) {
            if ($comm->TestName===$testName) {
                $commission=[$comm->CommissionAmount,$comm->CommissionType];
                break;
            }
        }
        return $commission;
    }

    public function GetName(int $billNo, string $billDate, array $bills):object
    {
        $all= array_values(array_filter($bills, fn (object $val):bool =>(int)$val->BillNo===$billNo && $val->BillDate===$billDate));
        return $all[0];
    }
}