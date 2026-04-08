<?php
    $general->createLog('purchaseDelete',$_GET);
    $jArray=[];
    $canRun=false;$echo='No';
    $canRun=true;$echo='N';
    $db->transactionStart();

    foreach($purProducts as $sp){
        if($sp['inStock']!=$sp['quantity']){
            $error=fl();setMessage(1,'This purchase not delete able. Some product already sale from this purchase');
        }
        $p=$smt->productInfoByID($sp['pID'],false);
        $data=['stock'=>$p['stock']-$sp['quantity']];
        $where=['pID'=>$sp['pID']];
        if($canRun==true){
            $update=$db->update($general->table(104),$data,$where,$echo,$jArray);if($update==false){$error=fl();setMessage(66);}
        }

        $stockLog=$db->getRowData($general->table(2),'where pID='.$sp['pID'].' and psType='.PRODUCT_TYPE_GOOD.' and changeType='.ST_CH_PURCHASE.' and refID='.$purID);
        $where=['pslID'=>$stockLog['pslID']];
        if($canRun==true){
            $delete=$db->delete($general->table(2),$where,$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
        }

        $where=['pdID'=>$sp['pdID']];
        if($canRun==true){
            $delete=$db->delete($general->table(12),$where,$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
        }
    }


    if(!isset($error)){
        $saleVouchers=[V_T_PURCHASE];
        foreach($saleVouchers as $vt){
            $voucher=$acc->voucherDetails($vt,$purID);
            //                $general->printArray('$voucher');
            //                $general->printArray($voucher);
            if(!empty($voucher)){
                foreach($voucher as $v){
                    if($canRun==true){
                        $delete=$db->delete($general->table(96),['veID'=>$v['veID']],$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
                        $delete=$db->delete($general->table(97),['veID'=>$v['veID']],$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
                    }
                }
            }
        }

        if($canRun==true){
            $delete=$db->delete($general->table(11),['purID'=>$purID],$echo,$jArray);if($delete==false){$error=fl();setMessage(66);}
        }
    }

    //        $error=fl();
    if(!isset($error)){
        $ac=true;
    }
    else{
        $ac=false;
    }
    if(isset($error)){setErrorMessage($error);}
    $db->transactionStop($ac);
    $general->createLog('purchaseDelete',$jArray);
    if(!isset($error)){
        $general->redirect($sUrl,'Purchase Deleted');
    }
?>
