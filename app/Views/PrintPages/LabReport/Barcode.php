<?php
include(APPPATH.'Libraries/code128.php');

$barcode=new code128('P','mm',[50,60]);
$barcode->AddPage();
$barcode->Code128(2,2,$data->BillMonth.$data->BillNo,40,10);
$barcode->SetFont('times','',9);

$name=$data->PName.'('.$data->Age.'/'.substr($data->Gender,0,1).')';

$barcode->Text(2,15,$name);

$barcode->AddPage();
$barcode->Code128(2,2,$data->BillMonth.$data->BillNo,40,10);
$barcode->SetFont('times','',9);

$name=$data->PName.'('.$data->Age.'/'.substr($data->Gender,0,1).')';

$barcode->Text(2,15,$name);

$barcode->Output();
// <script src="<?=base_url('public/JsBarcode/dist/barcodes/JsBarcode.code128.min.js')?>"></script>
