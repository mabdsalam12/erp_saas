<?php
$request = $_POST??[];
include_once ROOT_DIR.'/class/Contra_voucher.php';
$contra_voucher = new Contra_voucher($general,$db,$acc);
try {
    $contra_voucher->add($request);
    $jArray['status']=1;
    setMessage(2,'Contra voucher added successfully');
} catch (Exception $e) {
    $error = fl();
    setMessage(1, $e->getMessage());
}



// $date               = strtotime($_POST['date']);
// $debit              = intval($_POST['debit']);
// $credit             = intval($_POST['credit']);
// $amount             = floatval($_POST['amount']);
// $reference          = $_POST['reference'];
// $transaction_charge = floatval($_POST['transaction_charge']);
// $note               = $_POST['note'];

// $source_ledgers=$acc->get_all_cash_accounts($jArray);

// if($date<strtotime('-1 year')){
//     $error=fl();setMessage(1,'Invalid date');
// }
// else if(!array_key_exists($debit,$source_ledgers)){
//     $error=fl();setMessage(1,'Invalid debit ledger');
// }
// else if(!array_key_exists($credit,$source_ledgers)){
//     $error=fl();setMessage(1,'Invalid credit ledger');
// }
// else if($amount<=0){
//     $error=fl();setMessage(1,'Invalid amount');
// }
// else if($transaction_charge<0){
//     $error=fl();setMessage(1,'Invalid transaction charge');
// }

// if(!isset($error)){
//     if($date==TODAY_TIME){
//         $date=TIME;
//     }
//     $data=[
//         'debit'             => $debit,
//         'credit'            => $credit,
//         'amount'            => $amount,
//         'reference'         => $reference,
//         'transaction_charge'=> $transaction_charge,
//         'time'              => $date,
//         'note'              => $note,
//     ];
//     $db->arrayUserInfoAdd($data);
//     $db->transactionStart();
//     $contra_id=$db->insert('contra_voucher',$data,true,'array',$jArray);
//     if($contra_id){
//         $voucher=$acc->voucher_create(V_T_CONTRA,$amount,$debit,$credit,$date,$note,$contra_id,0,$jArray);
//         if($voucher==false){
//             $error=fl();setMessage(66);
//         }
//         if($transaction_charge>0){
//             $charge_head=$acc->getSystemHead(AH_CONTRA_TRANSACTION_CHARGE);
//             $voucher=$acc->voucher_create(V_T_CONTRA_TR_CHARGE,$transaction_charge,$charge_head,$credit,$date,$note,$contra_id,0,$jArray);
//             if($voucher==false){
//                 $error=fl();setMessage(66);
//             }
//         }
//     }
//     else{
//         $error=fl();setMessage(66);
//     }
//     if(!isset($error)){
//         $ac=true;
//         $jArray['status']=1;
//         setMessage(2,'Contra voucher added successfully');
//     }
//     else{
//         $ac=false;
//     }
//     $db->transactionStop($ac);
// }