<?php
    $fcnID    = intval($_POST['fcnID']);
    $fhID     = intval($_POST['fhID']);
    $tcnID    = intval($_POST['tcnID']);
    $thID     = intval($_POST['thID']);
    $amount   = floatval($_POST['amount']);
    $trRefType= intval($_POST['trRefType']);
    $trRefID  = intval($_POST['trRefID']);
    $note     = $_POST['note'];

    if($trRefType==1){
        $cct=$db->get_rowData($general->table(44),'cctID',$trRefID);
        if(empty($cct)){$error=fl();setMessage(63,'Cash Received Request');}
        elseif($cct['bID']!=$bID){$error=fl();setMessage(63,'Cash Received Request');}
        elseif($cct['isActive']!=1){$error=fl();setMessage(1,'This request already processed');}
    }

    if(!isset($error)){
        $fcn=$tkt->counterInfoByID($fcnID);
        $tcn=$tkt->counterInfoByID($tcnID);
        if(empty($fcn)){$error=fl();setMessage(63,'From Counter');}
        elseif($fcn['bID']!=$bID){$error=fl();setMessage(63,'From Counter');}
        else{
            $fh=$acc->headInfoByID($fhID);
            if(empty($fh)){$error=fl();setMessage(63,'From Head');}
            elseif($fh['bID']!=$bID){$error=fl();setMessage(63,'From Head');}
        }
        if(empty($tcn)){$error=fl();setMessage(63,'To Counter');}
        elseif($tcn['bID']!=$bID){$error=fl();setMessage(63,'To Counter');}
        else{
            $th=$acc->headInfoByID($fhID);
            if(empty($th)){$error=fl();setMessage(63,'To Head');}
            elseif($th['bID']!=$bID){$error=fl();setMessage(63,'To Head');}
        }
    }
    if(!isset($error)){
        $fLID=$acc->getLedgerID($fhID,$fcnID);
        if($fLID==false){$error=fl();setMessage(66);}
        $tLID=$acc->getLedgerID($thID,$tcnID);
        if($tLID==false){$error=fl();setMessage(66);}
        $fTransferLID=$acc->getSystemLedger(L_BALANCE_TRANSFER_AHID,$fcnID,$bID);
        if($fTransferLID==false){$error=fl();setMessage(66);}
        $tTransferLID=$acc->getSystemLedger(L_BALANCE_TRANSFER_AHID,$tcnID,$bID);
        if($tTransferLID==false){$error=fl();setMessage(66);}
    }
    if(!isset($error)){
        $lg=array($fTransferLID,$fLID,$tLID,$tTransferLID);
        $dr=array($amount,0,$amount,0);
        $cr=array(0,$amount,0,$amount);
        $pr=$fcn['cnTitle'].' to '.$tcn['cnTitle'].' '.$note;
        $prs=array($pr,$pr,$pr,$pr);
        $db->transactionStart();
        $veID=$acc->newVoucherCreate($fcnID,$bID,TIME,$lg,$dr,$cr,$prs,V_TYPE_BALANCE_TRANSFER,$trRefID,$jArray);
        if($veID){
            if($trRefType==1){
                $data=array(
                    'veID'=>$veID,
                    'isActive'=>0
                );
                $db->arrayUserInfoEdit($data);
                $where=array(
                    'cctID'=>$trRefID
                );
                $update=$db->update($general->table(44),$data,$where);
                if($update==false){$error=fl();setMessage(66);}
            }
            if(!isset($error)){$ac=true;}else{$ac=false;}
            $db->transactionStop($ac);
            if(!isset($error)){
                $jArray['status']=1;
                setMessage(2,'Balance Transfer Success.');
            }
        }
    }
    $jArray['m']=show_msg('y');
    $general->jsonHeader($jArray);
?>