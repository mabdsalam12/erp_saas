<?php 
include_once ROOT_DIR.'/class/Voucher_list.php';
$voucher_list=new Voucher_list($smt,$acc);
$list=$voucher_list->get_list($_POST);
//$jArray[fl()]=$list;

if($list['status']==1){
    $jArray['status']=1;
    $jArray['html']=$list['html'];
}