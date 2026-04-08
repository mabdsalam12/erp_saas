<?php
$id=intval($_POST['id']);
$paid=floatval($_POST['paid']);
$note=$_POST['note'];
$return=$db->get_rowData('purchase_return','purrID',$id);
if(empty($return)){$error=fl();setMessage(63,'paid request');}
elseif($return['isPaid']==1){$error=fl();setMessage(1,'This invoice already paid');}
elseif($paid>$return['subTotal']||$paid<1){$error=fl();setMessage(63,'paid amount');}
elseif($note==''){$error=fl();setMessage(1,'Note is required');}


if(!isset($error)){
    $sup=$smt->supplierInfoByID($return['supID']);
    $supHead=$acc->getSupplierHead($sup);
    if($supHead==false){$error=fl();setMessage(66);}
    $returnHead=$acc->getSystemHead(AH_PUR_RETURN);
    if($returnHead==false){$error=fl();setMessage(66);}
    $cashHead=$acc->getSystemHead(AH_CASH);
    if($cashHead==false){$error=fl();setMessage(66);}
    if(!isset($error)){
        $discount=$return['subTotal']-$paid;
        $data=[
            'isPaid'    => 1,
            'discount'  => $discount,
            'netTotal'  => $paid
        ];
        $db->arrayUserInfoEdit($data);
        $where=['purrID'=>$id];
        
        $db->transactionStart();
        $update=$db->update('purchase_return',$data,$where);
        if($update){
            $voucher=$acc->newVoucher(SECTION_DEALER,V_T_PURCHASE_RETURN,$paid,$supHead,$returnHead,$return['purrDate'],'Purchase return to '.$sup['supName'],$id);
            if($voucher==false){$error=fl();setMessage(66);}
            $voucher=$acc->newVoucher(SECTION_DEALER,V_T_SUPPLIER_PAYMENT,$paid,$cashHead,$supHead,TIME,$note,$return['supID']);
            if($voucher==false){$error=fl();setMessage(66);}
        }
        else{
            $error=fl();setMessage(66);
        }
        if(!isset($error)){
            $ac=true;
            $jArray['status']=1;
            setMessage(2,'Payment done for '.$sup['supName']);
        }
        else{
            $ac=false;
        }
        $db->transactionStop($ac);
    }
}
?>
