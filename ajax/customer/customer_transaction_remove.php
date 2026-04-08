<?php
$general->createLog('customer_transaction_remove',$_POST);
$voucher_id=intval($_POST['voucher_id']);
$voucher=$db->get_rowData('a_voucher_entry','id',$voucher_id);
$remove_types=[
    V_T_RECEIVE_FROM_CUSTOMER,
    V_T_CUSTOMER_COLLECTION_DISCOUNT,
    V_T_CUSTOMER_YEARLY_DISCOUNT,
    V_T_CUSTOMER_BAD_DEBT,
    V_T_RECOVERABLE_ENTRY,
    V_T_NEW_RECOVERABLE_ENTRY,
    V_T_PAY_TO_CUSTOMER
];
if(!empty($voucher)&&in_array($voucher['type'],$remove_types)){
    $db->transactionStart();
    $delete = $acc->voucher_delete($voucher_id);
    
    if($delete==false){$error=fl();setMessage(66);}
    if($voucher['type']==V_T_NEW_RECOVERABLE_ENTRY){
        $delete = $db->delete('recoverable_collection',['id'=>$voucher['reference']]);
        if($delete==false){$error=fl();setMessage(66);}
    }
    if(!isset($error)){
        $ac=true;
        $jArray['status']=1;
    }
    else{
        $ac=false;
    }
    $db->transactionStop($ac);
}
else{
    // $jArray[fl()]=$remove_types;
    // $jArray[fl()]=$voucher['type'];
}
$general->createLog('customer_transaction_remove',$jArray);