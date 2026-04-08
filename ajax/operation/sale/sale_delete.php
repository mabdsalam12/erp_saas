<?php
    $general->createLog('sale_delete',$_POST);
    $id = intval($_POST['id']);
    $sale = $db->get_rowData('sale','id',$id);
    if(empty($sale)){$error=fl(); setMessage(63,'sale');}
    else{
        $sale_product = $db->selectAll('sale_products','where sale_id='.$id);
        $sale_voucher = $acc->voucherDetails(V_T_SALE_CASH_CUSTOMER,$id);
        $voucher_ids=[];
        if(!empty($sale_voucher)){
            $voucher_ids[] = end($sale_voucher)['id'];
        }
        $vat_voucher = $acc->voucherDetails(V_T_SALE_VAT,$id);
        if(!empty($vat_voucher)){
            $voucher_ids[] = end($vat_voucher)['id'];
        }
        $db->transactionStart();
        if(!empty($voucher_ids)){
            $delete_voucher = $acc->voucher_delete($voucher_ids);
            if(!$delete_voucher){$error=fl();setMessage(66);}
        }
        if(!empty($sale_product)){
            $ids=[];
            $product_ids=[];
            foreach($sale_product as $sp){
                $ids[$sp['id']] = $sp['id']; 
                $product_ids[] = $sp['product_id'];
            }
            $products = $db->selectAll('products','where id in('.implode(',',$product_ids).')','id,stock,unit_cost');
            $general->arrayIndexChange($products);
            foreach($sale_product as $sp){
                if(!isset($products[$sp['product_id']])){$error=fl();setMessage(66);break;}
                $p = $products[$sp['product_id']]; 
                $nextQty=$p['stock'];
                $nextQty+=$sp['total_qty'];
                $where=['id'=>$p['id']];
                $update=$db->update('products',['stock'=>$nextQty/*,'unit_cost'=>$unit_cost*/],$where);
                if($update==false){$error=fl();setMessage(66);break;}
            }

            $delete=$db->runQuery('delete from product_stock_log where reference_id in('.implode(',',$ids).') and change_type='.ST_CH_SALE);
            if(!$delete){$error=fl();setMessage(66);}

            $delete=$db->runQuery('delete from sale_products where id in('.implode(',',$ids).')');
            if(!$delete){$error=fl();setMessage(66);}
        }
        $delete = $db->delete('sale',['id'=>$id]);
        if(!$delete){$error=fl();setMessage(66);}
        $ac=false;
        if(!isset($error)){
            $ac=true;
            $jArray['status'] = 1;
            setMessage(14,'Sale');
        }
        $db->transactionStop($ac);
    }
    $general->createLog('sale_delete',$jArray);
