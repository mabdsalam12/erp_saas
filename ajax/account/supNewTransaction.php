<?php
$supID  = intval($_POST['supID']);
$date  = strtotime($_POST['date']);
$type   = intval($_POST['type']);
$pay    = floatval($_POST['pay']);
$bank_id = floatval($_POST["bank_id"]);

$note   = $_POST['note'];
$sup=$smt->supplierInfoByID($supID);      
$cash_accounts=$acc->get_all_cash_accounts();       
if(empty($sup)){$error=fl();setMessage(63,'Supplier');}
elseif($date<strtotime('-1 year')){$error=fl();setMessage(63,'Date');}
elseif($pay<1){$error=fl();setMessage(63,'Transaction amount');}
elseif($type!=DEBIT&&$type!=CREDIT){$error=fl();setMessage(63,'Transaction type');}
elseif(!isset($cash_accounts[$bank_id])){$error=fl(); setMessage(63,'Bank');}
if(!isset($error)){
    if($date==TODAY_TIME){
        $date=TIME;
    }
    $db->transactionStart();
    //$cashHead=$acc->getSystemHead(AH_CASH);
    //if($cashHead==false){$error=fl();setMessage(66);}
    
    $supHead=$acc->getSupplierHead($sup);
    if($supHead==false){$error=fl();setMessage(66);}
    $mainExpHead= $acc->getSystemHead(AH_MAIN_EXPENSE);
    if($mainExpHead==false){$error=fl();setMessage(66);}
    if($type==DEBIT){
        $debit=$bank_id;
        $credit=$supHead;
        $vType=V_T_SUPPLIER_PAYMENT;
        $note='Receive from '.$sup['name'].' '.$note;
    }
    else{
        $vType=V_T_SUPPLIER_PAYMENT;
        $debit=$supHead;
        $credit=$bank_id;
        $note='Pay to '.$sup['name'].' '.$note;
    }
    if(!isset($error)){
        $voucher=$acc->newVoucher(0,$vType,$pay,$debit,$credit,$date,$note,$supID);
        if($voucher==false){$error=fl();setMessage(66);}
    }

    if(!isset($error)){
        $ac=true;
        $jArray['status']=1;
        setMessage(2,'Transaction done for '.$sup['name']);
    }
    else{
        $ac=false;
    }
    $db->transactionStop($ac);
}