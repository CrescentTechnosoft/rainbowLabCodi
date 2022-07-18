<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AddlModels\Email_Model;

class Emails extends BaseController
{
    private Email_Model $m;
    public function __construct()
    {
        parent::__construct();
        $this->ValidateUser();
        $this->m=new Email_Model($this->session->get('hosID'));
    }

    public function SendLabReport()
    {
        $month=$this->request->getPost('month');
        $billNo=$this->request->getPost('billNo');
        $header=$this->request->getPost('header');

        $emailID=$this->m->GetEmailID($month, $billNo);
        $this->response->setContentType('application/json');

        if ($emailID==='' || \filter_var($emailID, \FILTER_VALIDATE_EMAIL)===false) {
            echo '{"status":false,"message":"Invalid Email ID.\nCheck the Email ID in Registration!!!"}';
        } else {
            $data=$this->m->GetLabData($month, $billNo);
            
            if (empty($data['LabData']) || \is_null($data['OPData'])) {
                echo '{"status":false,"message":"No Data Found!!!"}';
            } else {
                $email=\CodeIgniter\Config\Services::email();

                $email->setTo($emailID)
                ->setSubject('Lab Report')
                ->setMessage('<h4>Lab Report From Rainbow Scans</h4>');

                $filePath=\WRITEPATH.'temp/File'.rand(10, 100).'.pdf';
                $file=\fopen($filePath, 'w');
                \fclose($file);

                $data['type']='F';
                $data['name']=$filePath;
                $data['HeaderType']=$header;
                view('PrintPages/LabReport/LabReport', $data);
                
                $email->attach($filePath,'','Lab Report.pdf');

                
                if ($email->send()) {
                    echo '{"status":true,"message":"Report Sent!!!"}';
                } else {
                    echo $email->printDebugger(['headers']);
                    echo '{"status":false,"message":"An Error Occured while Sending the Report!!!"}';
                }
                \unlink($filePath);
            }
        }
    }
    public function SendLabReportWhatsapp(){
        $month=$this->request->getPost('month');
        $billNo=$this->request->getPost('billNo');
        $header=$this->request->getPost('header');
        $data['type']='F';
        $data=$this->m->GetLabDataWhatsapp($month, $billNo,$header);

        view('PrintPages/LabReport/LabReportWhatsapp', $data);
        $filename = $data['OPData']->PID.$data['OPData']->PName;
        $contact_no=$data['OPData']->ContactNo;
        $report = APPPATH.('whatsapp/' . $filename . '.pdf');
        if ($contact_no !== '' && preg_match('/^[0-9]{10}+$/', $contact_no)) {
            // sending Whatsapp
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.whatsdesk.in/v4/filefromdisk.php',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('data' => new \CURLFILE($report), 'key' => 'cbOFmCvBJqsxbFHoDn', 'number' => '91' . $contact_no, 'caption' => 'this is test caption'),
            ));
            $this->response->setContentType('application/json');
            $response = curl_exec($curl);

            curl_close($curl);
            
            echo $response;

            
        }
       
    }

    public function SendScanReport()
    {
        $month=$this->request->getPost('month');
        $billNo=$this->request->getPost('billNo');
        $header=$this->request->getPost('header');
        $test=$this->request->getPost('testName');

        $emailID=$this->m->GetEmailID($month, $billNo);
        $this->response->setContentType('application/json');

        if ($emailID==='' || \filter_var($emailID, \FILTER_VALIDATE_EMAIL)===false) {
            echo '{"status":false,"message":"Invalid Email ID.\nCheck the Email ID in Registration!!!"}';
        } else {
            $data=$this->m->GetScanData($month, $billNo, $test);
            
            if (empty($data['LabData']) || \is_null($data['OPData'])) {
                echo '{"status":false,"message":"No Data Found!!!"}';
            } else {
                $email=\CodeIgniter\Config\Services::email();

                $email->setTo($emailID)
                ->setSubject('Lab Scan Report')
                ->setMessage('<h4>Lab Scan Report From Rainbow Scans</h4>');
                $filename = $data['OPData']->BillNo;
                $filePath=\WRITEPATH.'temp/'.$filename.'.pdf';
                //$filePath="";
                // $file=\fopen($filePath, 'w');
                // \fclose($file);

                // $data['type']='F';
                // $data['name']=$filePath;
                $data['header']=$header;
                view('PrintPages/LabReport/ScanResult', $data);
                
                $email->attach($filePath,'','Lab Report.pdf');

                
                if ($email->send()) {
                    echo '{"status":true,"message":"Report Sent!!!"}';
                } else {
                    echo $email->printDebugger(['headers']);
                    echo '{"status":false,"message":"An Error Occured while Sending the Report!!!"}';
                }
                // \unlink($filePath);
            }
        }
    }
    public function SendScanReportWhatsapp(){
        $month=$this->request->getPost('month');
        $billNo=$this->request->getPost('billNo');
        $header=$this->request->getPost('header');
        $test=$this->request->getPost('testName');
        $data['type']='F';
        $data=$this->m->GetScanDataWhatsapp($month, $billNo,$test,$header);
        $data['header']=$header;
        view('PrintPages/LabReport/ScanResultWhatsapp', $data);
        $filename = $data['OPData']->BillNo;
        $contact_no=$data['OPData']->ContactNo;
        $report = APPPATH.('whatsapp/' . $filename . '.pdf');
        if ($contact_no !== '' && preg_match('/^[0-9]{10}+$/', $contact_no)) {
            // sending Whatsapp
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.whatsdesk.in/v4/filefromdisk.php',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('data' => new \CURLFILE($report), 'key' => 'cbOFmCvBJqsxbFHoDn', 'number' => '91' . $contact_no, 'caption' => 'this is test caption'),
            ));
            $this->response->setContentType('application/json');
            $response = curl_exec($curl);

            curl_close($curl);
            
            echo $response;

            
        }else{
            return 'check';
        }
       
    }
}
