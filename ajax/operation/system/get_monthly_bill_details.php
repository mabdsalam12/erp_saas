<?php
$id=intval($_POST['id']);
$bill=$db->get_rowData('monthly_bill','id',$id);
if($bill){
    $bill_details=$db->selectAll('monthly_bill_transaction',"where bill_id=$id order by id desc");
    $gAr['bill'] = $bill;
    $gAr['bill_details'] = $bill_details;
    $jArray['html']     = $general->fileToVariable(__DIR__.'/get_monthly_bill_details.phtml');
    
    
    $jArray['status'] = 1;
}