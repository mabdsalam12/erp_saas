<?php 
$general->createLog('contra_voucher_edit',$_POST);
$request = $_POST??[];
include_once ROOT_DIR.'/class/Contra_voucher.php';
$contra_voucher = new Contra_voucher($general,$db,$acc);
try {
    $contra_voucher->update($request);
    $jArray['status']=1;
    setMessage(2,'Contra voucher update successfully');
    $log=$contra_voucher->getLog();
    $jArray[fl()]=$log;
} catch (Exception $e) {
    exception_error_return($e,$contra_voucher,fl(),$jArray);
}
$general->createLog('contra_voucher_edit',$jArray);