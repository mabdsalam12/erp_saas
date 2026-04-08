<?php
$trDate = strtotime($_POST["date"]);
//$base_id= intval($_POST["base_id"]);
$doctor_id = intval($_POST["doctor_id"]);
$credit = intval($_POST["credit"]);
$amount = floatval($_POST["amount"]);
$note = $_POST['note'];

$source_ledgers=$acc->get_all_cash_accounts($jArray);
// $base = $db->allBase_for_voucher();
$doctor=$smt->doctor_get($doctor_id);

if(empty($doctor)){setMessage(36,'Doctor');$error=fl();}
elseif(!array_key_exists($credit,$source_ledgers)){setMessage(36,'Credit ledger');$error=fl();}
// elseif(!isset($base[$base_id])){setMessage(36,'bedger');$error=fl();}
elseif($trDate<strtotime('-2 year')){$error=fl();setMessage(63,'Date');$error=fl();}
elseif($amount<=0){setMessage(36,'Amount');$error=fl();}
if(!isset($error)){
    $db->transactionStart();
    $debit=$acc->getSystemHead(AH_DOCTOR_HONORARIUM);
    if($debit==false){$error=fl();setMessage(66);}
    $data=[
        'doctor_id'     =>$doctor_id,
        'base_id'       =>$doctor['base_id'],
        'date'          =>$trDate,
        'amount'        =>$amount,
        'credit_ledger' =>$credit,
        'note'          =>$note
    ];
    $db->arrayUserInfoAdd($data);
    $honorariumID=$db->insert('doctor_honurium',$data);

    if($honorariumID!=false){
        $extraData=['base_id'=>$doctor['base_id']];
        $veID=$acc->voucher_create(
            V_T_DOCTOR_HONORARIUM,
            $amount,
            $debit,
            $credit,
            $trDate,
            $note,
            $honorariumID,
            0,
            $extraData,
            $jArray
        );
        if($veID==false){$error=fl();setMessage(66);}
    }
        
    else{$error=fl();setMessage(66);}

    // $extraData=['base_id'=>$base_id];
    // $veID=$acc->voucher_create($vType,$amount,$debit,$credit,$trDate,$note,extraData:$extraData);
    // if($veID==false){$error=fl();setMessage(66);}
    $ac = !isset($error);
    $db->transactionStop($ac);
    if($ac){
        setMessage(29,'doctor honorarium');
        $jArray['status']=1;
    }
}