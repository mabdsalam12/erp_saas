<?php
$general->createLog('bKash_payment_create',$_POST);
$id=$_POST['id'];
$bill=$db->get_rowData('monthly_bill','id',$id);
if($bill){
    if($bill['pay_date']==0&&defined('BKASH_TRANSACTION_PREFIX')){
        $data=[
            'bill_id'=>$id,
            'amount'=>$bill['amount'],
            'createdBy'=>USER_ID,
            'createdOn'=>TIME
        ];
        $transaction_id=$db->insert('monthly_bill_transaction',$data,true);
        if($transaction_id){
            include_once ROOT_DIR."/class/bKash.php";
            $invoice_id=BKASH_TRANSACTION_PREFIX.$transaction_id;
            $bkash=new BKASH();
            $create=$bkash->createInvoice($invoice_id,$bill['amount'],$jArray);
            $jArray[fl()]=$create;
            if($create['status']==1){
                $data=[
                    'paymentID'=>$create['paymentID']
                ];
                $where=[
                    'id'=>$transaction_id
                ];
                $update=$db->update('monthly_bill_transaction',$data,$where);
                if($update){
                    $jArray['status']=1;
                    $jArray['url']=$create['url'];
                }
                else{
                    $jArray[fl()]=1;
                }
                
            }
            else{
                setMessage(1,'Payment create failed: '.$create['message']);$error=fl();
            }
        }
        
        else{
            $jArray[fl()]=1;
        }
    }
    else{
        $jArray[fl()]=1;
    }
}
$general->createLog('bKash_payment_create',$jArray);