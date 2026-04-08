<?php
$trDate = strtotime($_POST["date"]);
$base_id= intval($_POST["base_id"]);
$debit  = intval($_POST["debit"]);
$credit = intval($_POST["credit"]);
$amount = floatval($_POST["amount"]);
$note = $_POST['note']??'';
$vType  = intval($_POST['type']);

$source_ledgers=$acc->get_all_cash_accounts($jArray);
$base = $db->allBase_for_voucher();
$columnID = $vType==V_T_INCOME?'for_income':'for_expense';
$general->arrayIndexChange($heads,'id');
$heads=$db->selectAll('a_ledgers','where isActive=1 and '.$columnID.'=1','','array',$jArray);
$general->arrayIndexChange($heads);
if($vType==V_T_INCOME){
    $columnID='for_income';
    $debit_ledgers=$source_ledgers;
    $credit_ledgers=$heads;
    $page_title='Income';
}
else{
    $columnID='for_expense';
    $page_title='Expense entry';
    $debit_ledgers=$heads;
    $credit_ledgers=$source_ledgers;
}
if($vType!=V_T_INCOME&&$vType!=V_T_EXPENSE){setMessage(66);$error=fl();}
elseif(!array_key_exists($debit,$debit_ledgers)){setMessage(36,$page_title.' Ledger');$error=fl();}
elseif(!array_key_exists($credit,$credit_ledgers)){setMessage(36,$page_title.' Ledger');$error=fl();}
elseif(!isset($base[$base_id])){setMessage(36,$page_title.' Ledger');$error=fl();}
elseif($trDate<strtotime('-2 year')){$error=fl();setMessage(63,'Date');$error=fl();}
elseif($amount<=0){setMessage(36,'Amount');$error=fl();}
if(!isset($error)){
    $db->transactionStart();
    $extraData=['base_id'=>$base_id];
    $veID=$acc->voucher_create($vType,$amount,$debit,$credit,$trDate,$note,extraData:$extraData);
    if($veID==false){$error=fl();setMessage(66);}
    $ac = !isset($error);
    $db->transactionStop($ac);
    if($ac){
        setMessage(29,$page_title);
        $jArray['status']=1;
    }
}