<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__."/../class/general.php";
include __DIR__."/../class/db.php";
include __DIR__."/../class/somiti.php";
include __DIR__."/../class/acc.php";
include __DIR__."/../class/messages.php";
include __DIR__."/../class/LanguageManager.php";
include __DIR__."/../class/Language_call.php";
$general= new General();
$db     = new DB($general);
$smt    = new SMT($general,$db);
$acc    = new ACC($general,$db,$smt);

LanguageManager::getInstance()->setLang();
include __DIR__."/../init.php";

include_once ROOT_DIR."/class/bKash.php";
$jArray=[];
$general->createLog('bKash_return',$_GET);


$paymentID = $_GET['paymentID'];
$transaction=$db->get_rowData('monthly_bill_transaction','paymentID',$paymentID);
$jArray[fl()]=$transaction;
if(!empty($transaction)&&$transaction['pay_time']==0){
    
    $bkash=new BKASH();
    $check=$bkash->checkInvoice($paymentID,$jArray);
    $jArray[fl()]=$check;
    if($check['status']==1){
        $r=$check['response'];
        $tr_data=$general->getJsonFromString($transaction['data']);
        $tr_data['bkash_response']=$r;
        $data=[
            'transaction_id'=> $r['trxID'],
            'payerAccount'  => $r['payerAccount'],
            'pay_time'      => TIME,
            'data'          => json_encode($tr_data)
        ];
        $where=['id'=>$transaction['id']];
        $update=$db->update('monthly_bill_transaction',$data,$where,'array',$jArray);
        $data=[
            'pay_date'=>TIME
        ];
        $where=['id'=>$transaction['bill_id']];
        $update=$db->update('monthly_bill',$data,$where,'array',$jArray);
    }
}

$general->createLog('bKash_return',$jArray);
$general->redirect(URL.'/?mdl=monthly-bill');