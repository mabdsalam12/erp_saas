<?php
$biID     = intval($_POST['biID']);
$bwAmount = intval($_POST['bwAmount']);
$bwChqNo  = $_POST['bwChqNo'];
$bwPayTo  = $_POST['bwPayTo'];
$bwChqDate= strtotime($_POST['bwChqDate']);
$bi       = $db->get_rowData($general->table(102),'biID',$biID);
if(empty($bi)){$error=fl();setMessage(63,'Bank');}
elseif($bi['bID']!=$bID){$error=fl();setMessage(63,'Bank');}
elseif($bwAmount<=0){$error=fl();setMessage(63,'Amount');}
$cnID=$tkt->getHeadOfficeCounter($b);
if($cnID==false){$error=fl();setMessage(66);}
$cashLedger = $acc->getSystemLedger(AH_CASH,$cnID,$bID);
if($cashLedger==false){$error=fl();setMessage(66); }

$bankLedger = $acc->getLedgerID($acc->getBankHead($bi), $cnID);
if($bankLedger==false){$error=fl();setMessage(66); }

$db->transactionStart();

$lg=array($cashLedger,$bankLedger);
$dr=array($bwAmount,0);
$cr=array(0,$bwAmount);

$pr='Withdraw from '.$bi['biBank'];
$prs=array($pr,$pr);

if(!isset($error)){
    $data=array(
        'biID'      => $biID,
        'bID'       => $bID,
        'bwAmount'  => $bwAmount,
        'bwChqNo'   => $bwChqNo,
        'bwChqDate' => $bwChqDate,
        'bwPayTo'   => $bwPayTo
    );
    $db->arrayUserInfoAdd($data);

    $bwID= $db->insert($general->table(89),$data, 'getId');
    if($bwID!=false){
        $lg=array($cashLedger,$bankLedger);
        $newVoucherCreated = $acc->newVoucherRevisedCreate($cnID,$bID,TIME,$lg,$dr,$cr,$prs,V_BANK_WITHDRAW,$bwID,1);
        if($newVoucherCreated){
            $jArray['status']=1;
            setMessage(29,'Bank Withdraw');
        }
        else{
            $error=fl();setMessage(66);
        }
    }
    else{
        $error=fl();setMessage(66);
    }
}

if(isset($error)){
    $db->transactionStop(false);
    $jArray['status'] = 0;
}else{
    $db->transactionStop(true);
}

$jArray['m']=show_msg('y');
$general->jsonHeader($jArray);
?>