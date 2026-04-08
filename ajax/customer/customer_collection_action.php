<?php
$general->createLog('customer_collection_action',$_POST);
$id=intval($_POST['id']);
$db->transactionStart();
$action =$acc->confirmCustomerDeposit($id,$jArray);
$jArray[fl()]=$action;
if($action['status']==1){
    $ac=true;
    setMessage(2,$action['message']);
    $jArray['status']=1;
}
else{
    $ac=false;
    setMessage(1,$action['message']);
}
$db->transactionStop($ac);
$general->createLog('customer_collection_action',$jArray);
