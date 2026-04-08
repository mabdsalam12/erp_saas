<?php
    $general->createLog('purchase_delete',$_POST);
    $id = intval($_POST['id']);
    $purchase = $db->get_rowData('purchase','id',$id);
    if(empty($purchase)){$error=fl();setMessage(63,'purchase');}
    else{
        $purchase_products      = $db->selectAll('purchase_details','where purchase_id='.$id);
        $purchase_voucher       = $acc->voucherDetails(V_T_PURCHASE,$id);
        $purchase_vat_voucher   = $acc->voucherDetails(V_T_VAT_PURCHASE,$id);
        $purchase_all_voucher   = $acc->voucherDetails(V_T_AIT_PURCHASE,$id);
        $voucher_ids=[];
        if(!empty($purchase_voucher)){
            $voucher_ids[]= end($purchase_voucher)['id'];
        }if(!empty($purchase_vat_voucher)){
            $voucher_ids[]= end($purchase_vat_voucher)['id'];
        }if(!empty($purchase_all_voucher)){
            $voucher_ids[]= end($purchase_all_voucher)['id'];
        }
        $db->transactionStart();
        if(!empty($voucher_ids)){
            $delete_voucher = $acc->voucher_delete($voucher_ids);
            if(!$delete_voucher){$error=fl();setMessage(66);}
        }
        if(!empty($purchase_products)){
            $product_ids=[];
            $ids=[];
            foreach($purchase_products as $pp){
                $ids[$pp['id']] = $pp['id']; 
                $product_ids[]=$pp['product_id'];
            }
            $products = $db->selectAll('products','where id in('.implode(',',$product_ids).')','id,stock');
            $general->arrayIndexChange($products);
            foreach($purchase_products as $pp){
                if(!isset($products[$pp['product_id']])){$error=fl();setMessage(66);break;}
                $p = $products[$pp['product_id']]; 
                
                $nextQty= $p['stock']-$pp['quantity'];
                $where  = ['id'=>$p['id']];
                /*এটা আমি বুঝিনাই এখানে কি হিসাব হইছে*/
                //$unit_cost =round( (($p['stock']*$p['unit_cost'])+($pp['quantity']*$pp['unit_cost']))/($p['stock']+$pp['quantity']),2); //

                $update=$db->update('products',['stock'=>$nextQty,/*'unit_cost'=>$unit_cost*/],$where);
                if($update==false){$error=fl();setMessage(66);break;}
            }
            $delete=$db->runQuery('delete from product_stock_log where reference_id in('.implode(',',$ids).') and change_type='.ST_CH_PURCHASE);
            if(!$delete){$error=fl();setMessage(66);}
            $delete=$db->runQuery('delete from purchase_details where id in('.implode(',',$ids).')');
            if(!$delete){$error=fl();setMessage(66);}
        }
        $delete = $db->delete('purchase',['id'=>$id]);
        if(!$delete){$error=fl();setMessage(66);}
        $ac=false;
        if(!isset($error)){
            $ac=true;
            $jArray['status'] = 1;
            setMessage(14,'Purchase');
        }
        $db->transactionStop($ac);
}