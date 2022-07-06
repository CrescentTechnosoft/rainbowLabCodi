<?php namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Patients_Model;

class Patients extends Controller
{
    private object $session;
    private Patients_Model $m;
    public function __construct()
    {
        $this->session=session();
        if (!$this->session->has('PID')) {
            die('Login to Continue');
        }
        $this->m=new Patients_Model;
    }

    public function index():void
    {
        $this->response->setContentType('application/json');
        echo json_encode($this->m->GetResults($this->session->get('PID')));
    }
    
    public function DownloadLabReport(string $encMonth, string $encBillNo):void
    {
        $month=\base64_decode($encMonth);
        $billNo=\base64_decode($encBillNo);
        $data=$this->m->GetLabData($month, $billNo,$this->session->get('PID'));
            
        if (empty($data['LabData']) || \is_null($data['OPData'])) {
            echo "<script>alert('No Details Found!!!');window.close();</script>";
        } else {
            $data['HeaderType']='WH';

            $this->response->setContentType('application/pdf');
            $this->response->setHeader('Content-Encoding', 'br');
            $this->response->setHeader('Content-Disposition', 'attachment; filename="Bill No '.$billNo.'.pdf"');
            echo \brotli_compress(\view('PrintPages/LabReport/LabReport', $data));
        }
    }

    public function Logout():void
    {
        $this->session->remove('PID');
    }
}
