<?php
    $general->createLog('sale_return_delete',$_POST);
    $id = intval($_POST['id']);
    $sale_return = $db->get_rowData('sale_return','id',$id);
    if(empty($sale_return)){$error=fl(); setMessage(63,'sale return');}
    else{
        $voucher_types=[
            V_T_SALE_RETURN,
            V_T_SALE_RETURN_PROCESS
        ];
        $sale_return_product = $db->selectAll('sale_return_details','where sale_return_id='.$id);
        $vouchers = $acc->voucherDetails($voucher_types,$id);
        $voucher_ids=[];
        if(!empty($vouchers)){
            foreach($vouchers as $v){
                $voucher_ids[$v['id']] = $v['id'];
            }
        }
        
        $jArray[fl()]=$voucher_ids;
        $db->transactionStart();
        if(!empty($voucher_ids)){
            $delete_voucher = $acc->voucher_delete($voucher_ids,[],$jArray);
            if(!$delete_voucher){$error=fl();setMessage(66);}
        }
        
        $ids=[];
        $product_ids=[];
        foreach($sale_return_product as $sp){
            $ids[$sp['id']] = $sp['id']; 
            $product_ids[] = $sp['product_id'];
        }
        $products = $db->selectAll('products','where id in('.implode(',',$product_ids).')','id,stock,unit_cost');
        $general->arrayIndexChange($products);
        $delete=$db->runQuery("delete from sale_return_details where sale_return_id =$id");
        if(!$delete){$error=fl();setMessage(66);}
        
        $sale_return_process = $db->selectAll('sale_return_process_data',"where sale_return_id=$id");
        if(!empty($sale_return_process)){
            $ids=[];
            foreach($sale_return_process as $sp){
                if(!isset($products[$sp['product_id']])){
                    $error=fl();setMessage(66);break;
                }
                $ids[$sp['id']] = $sp['id']; 
                if($sp['type']==SALE_RETURN_PROCESS_TYPE_GOOD){
                    $p = $products[$sp['product_id']]; 
                    $nextQty=$p['stock']-$sp['quantity'];
                    $where=['id'=>$p['id']];
                    
                    $update=$db->update('products',['stock'=>$nextQty],$where);
                    if($update==false){$error=fl();setMessage(66);break;}
                    $smt->productInfoByID($p['id'],false);
                }
            }
            $delete=$db->runQuery('delete from product_stock_log where reference_id in('.implode(',',$ids).') and change_type='.ST_CH_SALE_RETURN);
            if(!$delete){$error=fl();setMessage(66);}
            $delete=$db->runQuery('delete from sale_return_process_data where id in('.implode(',',$ids).')');
            if(!$delete){$error=fl();setMessage(66);}

        }
        $delete = $db->delete('sale_return',['id'=>$id]);
        if(!$delete){$error=fl();setMessage(66);}

        $ac=false;
        if(!isset($error)){
            $jArray['status'] = 1;
            setMessage(2,'Sale return delete successfully');
            $ac=true;
        }
        $db->transactionStop($ac);
    }

    $general->createLog('sale_return_delete',$jArray);

