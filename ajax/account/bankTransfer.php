<?php
//$cnID=$userData['cnID'];
$cnID=$b['bHeadOffice'];

if(empty($cnID)){$error=fl();setMessage(1,'You cannot transfer amount');}

if(!isset($error)){
    $amount   = floatval($_POST['amount']);
    $note     = $_POST['note'];
    $biID     = floatval($_POST['biID']);
    if($amount<=0){$error=fl();setMessage(63,'Amount');}
    if($note==''){$error=fl();setMessage(36,'Note');}
    $bi=$db->get_rowData($general->table(102),'biID',$biID);
    if(empty($bi)){$error=fl();setMessage(63,'Bank');}
    elseif($bi['bID']!=$bID){$error=fl();setMessage(63,'Bank');}
    elseif($bi['isActive']!=1){$error=fl();setMessage(63,'Bank');}


    if(!isset($error)){
        $revised=$acc->isRevised($bID);
        $db->transactionStart();
        $fLID=$acc->getSystemLedger(AH_CASH,$cnID,$bID);
        if($fLID==false){$error=fl();setMessage(66); }

        $bankHeadID=$acc->getBankHead($bi);
        $tLID=$acc->getLedgerID($bankHeadID,$cnID);
        if($tLID==false){$error=fl();setMessage(66);}

        $tTransferLID=$acc->getSystemLedger(L_BALANCE_TRANSFER_AHID,$cnID,$bID);
        if($tTransferLID==false){$error=fl();setMessage(66);}

        if(!isset($error)){
            $lg=array($tLID,$fLID);
            $dr=array($amount,0);
            $cr=array(0,$amount);
            $pr='Transfer '.$b['bTitle'].' to '.$bi['biBank'].' '.$note;
            $prs=array($pr,$pr);

            $veID=$acc->newVoucherRevisedCreate($cnID,$bID,TIME,$lg,$dr,$cr,$prs,V_TYPE_ADMIN_BANK_DEPOSIT,$biID,$revised);

            if(is_int($veID)){
                $h=$acc->getSystemHead(AH_CASH,$bID,$jArray);
                $jArray['balance']=$general->numberFormat($acc->closingBalance(strtotime('+5 minute'),$h['hID'],$fLID,$cnID,$jArray));
                $jArray['status']=1;
                if($revised==1){
                    setMessage(2,'Balance Transfer successfully.');
                }
                else{
                    setMessage(2,'Balance Transfer pending for approval.');
                }

            }else{$error=fl();setMessage(66);}
        }

        if(!isset($error)){$ac=true;}else{$ac=false;}
        $db->transactionStop($ac);
        $jArray['m']=show_msg('y');
        $general->jsonHeader($jArray);
    }
}
if(isset($error)){setErrorMessage($error);}
?>
