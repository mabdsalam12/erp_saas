<?php
$voucher_id = intval($_POST['voucher_id']);
$v = $db->get_rowData('a_voucher_entry','id',$voucher_id);
$ledger = $db->selectAll('a_ledger_entry',"where voucher_id=$voucher_id");
if(empty($v|| $ledger)){$error=fl();setMessage(63,'voucher');}
if(!in_array($v['type'],REMOVE_ABLE_VOUCHER_TYPE)){$error=fl();setMessage(63,'voucher');}
else{
    $db->transactionStart();
    foreach($ledger as $l){
        $delete = $db->delete('a_ledger_entry',['id'=>$l['id']],'array',$jArray);
        if(!$delete){$error=fl();setMessage(66);break;}
    }
    if(!isset($error)){
        $delete = $db->delete('a_voucher_entry',['id'=>$voucher_id],'array',$jArray);
        if(!$delete){$error=fl();setMessage(66);}
    }
    $ac=false;
    if(!isset($error)){
        $ac=true;
        $jArray['status']=1;
        setMessage(1,'Voucher removed successfully');
        $general->createLog("voucher_remove_$voucher_id",['v'=>$v,'ledger'=>$ledger]);
    }
    $db->transactionStop($ac);
}



    // $dStatus=$db->permission(90,$bID);
    // $veID=intval($_POST['veID']);
    // $v=$db->get_rowData($general->table(96),'veID',$veID);
    // if(empty($v)){$error=fl();setMessage(63,'Entry remove request');}
    // elseif($v['bID']!=$bID){$error=fl();setMessage(63,'Entry remove request');}
    // else{
    //     $canRemove=false;
    //     if(in_array($v['vType'],REMUVE_ABLE_VOUCHER_TYPE)){{$canRemove=true;}
    //         //$canRemove=false;
    //         if($canRemove==true){
    //             $lEntrys=$db->selectAll($general->table(97),'where veID='.$veID);
    //             $logData['l']=$lEntrys;

    //             if($v['vType']==V_TYPE_RESERVATION){
    //                 $re=$db->get_rowData($general->table(65),'reID',$v['vTypeRef']);
    //                 if(empty($re)){
    //                     $error=fl();setMessage(1,'Refference Reservation not found');
    //                 }
    //                 if(!isset($error)){
    //                     $reAmount=0;
    //                     foreach($lEntrys as $le){
    //                         if($le['leAmountDr']>0){$reAmount=$le['leAmountDr'];}
    //                     }
    //                 }
    //             }
    //         }
    //         if(!isset($error)){
    //             if($canRemove==true){
    //                 $logData=array('v'=>$v);
    //                 $db->transactionStart();
    //                 $delete=$db->delete($general->table(96),array('veID'=>$veID));
    //                 if($delete==true){
    //                     $data   = array('rePay'=>$re['rePay']-$reAmount);
    //                     $where  = array('reID'=>$v['vTypeRef']);
    //                     $update=$db->update($general->table(65),$data,$where);
    //                     $log=$db->actionLogCreate('voucherRemove_veID'.$veID,json_encode($logData));
    //                     $log=$db->actionLogCreate('reservPayRemove_veID'.$veID.'_reID'.$v['vTypeRef'],json_encode($logData));
    //                     if($log==false){$error=fl();setMessage(66);}
    //                 }
    //                 else{$error=fl();setMessage(66);}
    //                 if(!isset($error)){
    //                     $ac=true;
    //                     $jArray['status']=1;
    //                     setMessage(1,'Voucher removed successfully');
    //                 }
    //                 else{
    //                     $ac=false;
    //                 }
    //                 $db->transactionStop($ac);
    //             }
    //             else{
    //                 $error=fl();setMessage(1,'Sorry this entry cannot remove.');
    //             }
    //         }
    //     }
    // }
    // $jArray['m']=show_msg('y');
    // $general->jsonHeader($jArray);
