<?php
$supplier_id=intval($_POST['supplier_id']);
$dRange=$_POST['dRange'];
$reportInfo=["Date: $dRange"];
$general->getFromToFromString($dRange,$from,$to);


include_once ROOT_DIR.'/class/Voucher_list.php';
$voucher_list=new Voucher_list($smt,$acc);
$request=[
    'dRange'=>$dRange
];
$ledger_id=0;
if($supplier_id>0){
    $sup=$smt->supplierInfoByID($supplier_id);
    if($sup){
        $ledger_id=$acc->getSupplierHead($sup);
    }
}
$request['ledger_id']=$ledger_id;
$request['type']=V_T_SUPPLIER_PAYMENT;
$list=$voucher_list->get_list($request);
$jArray[fl()]=$list;
$reportInfo=[];
if($list['status']==1){
    $jArray['status']=1;
    $jArray['html']=$list['html'];
}