<?php
$general->createLog('recoverable_collection_delete',$_POST);
$id = intval($_POST['id']);
$recoverable = $db->get_rowData('recoverable_collection','id',$id);
if(empty($recoverable)){
    $error=fl();
    setMessage(63,'recoverable');
}
else{
    $db->transactionStart();
    $voucher_details = $acc->voucherDetails([V_T_NEW_RECOVERABLE_ENTRY,V_T_NEW_RECOVERABLE_COLLECTION],$id);
    if(!empty($voucher_details)){
        $jArray[fl()]=$recoverable;
        $jArray[fl()]=$voucher_details;
        $voucher_id = current($voucher_details)['id']??0;
        $jArray[fl()]=$voucher_id;
        if($voucher_details>0){
            $delete = $acc->voucher_delete($voucher_id);
            if(!$delete){$error=fl(); setMessage(66);}
        }
    }
    
    
    $delete = $db->delete('recoverable_collection',['id'=>$id],'array',$jArray);
    $ac=false;
    if(!isset($error)){
        $ac=true;
        $jArray['status']=1;
    }
    $db->transactionStop($ac);
}
$general->createLog('recoverable_collection_delete',$jArray);