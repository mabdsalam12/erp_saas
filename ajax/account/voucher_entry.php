<?php
  $request = $_POST??[];
  include_once ROOT_DIR.'/class/Voucher_entry.php';
  $voucher_entry = new Voucher_entry($general,$db,$request);
  try{
    $voucher_entry->voucher_entry();
    setMessage(2,'voucher entry');
    $jArray['status']=1;
  }
  catch(Exception $e){
    exception_error_return($e,$voucher_entry,fl(),$jArray);
    $error=1;
  }
$jArray[fl()]=$voucher_entry->getLog();