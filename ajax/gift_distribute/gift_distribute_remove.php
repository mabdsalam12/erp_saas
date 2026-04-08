<?php
$general->createLog('gift_distribute_remove',$_POST);
    $id = intval($_POST['id']);
    $gift = $db->get_rowData('gift_distribute','id',$id);
    if(!empty($gift)){
        $gift_product = $db->selectAll('gift_distribute_product','where gift_distribute_id ='.$id);
        $db->transactionStart();
        foreach($gift_product as $gp){  
            $p = $smt->productInfoByID($gp['product_id'],false);
            $nextQty=$p['stock']+$gp['quantity'];
            $where=['id'=>$p['id']];
            $update=$db->update('products',['stock'=>$nextQty],$where,'array',$jArray);
            if($update==false){$error=fl();setMessage(66);break;}
            $sl=$db->getRowData('product_stock_log','where reference_id ='.$gp['id'].' and change_type='.ST_CH_DISTRIBUTE);

            if(!empty($sl)){
                $delete=$db->delete('product_stock_log',['id'=>$sl['id']],'array',$jArray);
                if($delete==false){$error=fl();setMessage(66);break;}
            }
            else{$error=fl();setMessage(66);break;}

            $delete=$db->delete('gift_distribute_product',['id'=>$gp['id']],'array',$jArray);
                if($delete==false){$error=fl();setMessage(66);break;}
        }
        if(!isset($error)){
            $delete=$db->delete('gift_distribute',['id'=>$id],'array',$jArray);
                if($delete==false){$error=fl();setMessage(66);}
        }
        if(!isset($error)){
            $ac=true;
            $jArray['status']=1;
            setMessage(2,'Gift distribute removed');
        }
        else{
            $ac=false;
        }
        $db->transactionStop($ac);
    }

    $general->createLog('gift_distribute_remove',$jArray);