<?php
$id= intval($_POST['id']);
if(0<$id){
    $person=$smt->personInfoByID($id);
    $ledger_id=$acc->getPersonHead($person);
    $balance=$acc->headBalance($ledger_id);
    $jArray['status']=1;
    $jArray['balance']=intval($balance);
    $jArray['mobile']=$person['mobile'];
}


