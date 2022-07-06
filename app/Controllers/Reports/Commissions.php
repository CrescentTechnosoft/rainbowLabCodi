<?php namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\Reports\CommissionReport_Model;

class Commissions extends BaseController
{
    private $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser('Rep');
        $this->m= new CommissionReport_Model($this->session->get('hosID'));
    }

    public function index()
    {
        $this->response->setContentType('application/json');
        echo \json_encode($this->m->GetConsultantNames());
    }

    public function Get(string $cons='', string $startDate='', string $endDate='')
    {
        $decStart=base64_decode($startDate);
        $decEnd=base64_decode($endDate);
        $decCons=base64_decode($cons);

        $start=\DateTime::createFromFormat('Y-m-d', $decStart)->format('d/m/Y');
        $end=\DateTime::createFromFormat('Y-m-d', $decEnd)->format('d/m/Y');
        $data=[
            'Bills'=>$this->m->GetBills($decStart, $decEnd, $decCons),
            'Commission'=>$this->m->GetCommission($decCons),
            'Header'=>"Referral Reports From $start to $end"
        ];

        $this->response->setContentType('application/pdf');
        $this->response->setHeader('Content-Encoding', 'br');

        if (!is_null($this->request->getGet('base'))) {
            echo \brotli_compress(\base64_encode(view('Reports/CommissionReport', $data)));
        } else {
            echo \brotli_compress(view('Reports/CommissionReport', $data));
        }
    }
}
