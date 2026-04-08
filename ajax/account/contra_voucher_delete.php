<?php
$general->createLog('contra_voucher_delete',$_POST);
$id = intval($_POST['id']);
include_once ROOT_DIR.'/class/Contra_voucher.php';
$contra_voucher = new Contra_voucher($general,$db,$acc);
try {
    $contra_voucher->delete($id);
    $jArray['status']=1;
    setMessage(2,'Contra voucher delete successfully');
    $log=$contra_voucher->getLog();
    $jArray[fl()]=$log;
} catch (Exception $e) {
    exception_error_return($e,$contra_voucher,fl(),$jArray);
}
$general->createLog('contra_voucher_delete',$jArray);