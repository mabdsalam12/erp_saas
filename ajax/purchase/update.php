<?php
    $general->createLog('purchase_edit',$_POST);
    $id = intval($_POST['id']);
    $purchase = $db->get_rowData('purchase','id',$id);
    $supplier_id = intval($_POST['supplier_id']);
    $type       = intval($_POST['type']);
    $discount   = floatval($_POST['discount']);
    $VAT        = floatval($_POST['VAT']);
    $AIT        = floatval($_POST['AIT']);
    $bill_date    = strtotime($_POST['bill_date']);
    $supplier_no   = $_POST['supplier_no']??'';
    $challan_no   = $_POST['challan_no']??'';
    $challan_date   = strtotime($_POST['challan_date']);
    $po_no      = $_POST['po_no']??'';
    $mrr_code   = $_POST['mrr_code']??'';
    $mrr_date   = strtotime($_POST['mrr_date']);
    $products   = $_POST['products'];
    $note       = $_POST['note']??'';

    $zero_price = $_POST['zero_price']==1?1:0;
    if($zero_price==1){
        $VAT=0;
        $AIT=0;
        $discount=0;
    }

    $sup=$smt->supplierInfoByID($supplier_id);
    if(empty($purchase)){$error=fl();setMessage(63,'Supplier');}
    elseif(empty($sup)){$error=fl();setMessage(63,'Supplier');}
    elseif($bill_date<strtotime('-2 years')){$error=fl();setMessage(63,'bill date');}
    elseif($bill_date>strtotime('2 years')){$error=fl();setMessage(63,'bill date');}
    elseif($challan_date<strtotime('-2 years')){$error=fl();setMessage(63,'Challan date');}
    elseif($challan_date>strtotime('2 years')){$error=fl();setMessage(63,'Challan date');}
    elseif($mrr_date<strtotime('-2 years')){$error=fl();setMessage(63,'MRR date');}
    elseif($mrr_date>strtotime('2 years')){$error=fl();setMessage(63,'MRR date');}
    elseif(empty($products)){$error=fl();setMessage(63,'Products');}
    elseif($VAT<0){$error=fl();setMessage(63,'VAT');}
    elseif($AIT<0){$error=fl();setMessage(63,'AIT');}
    else{
    if(!isset($error)){
        $product=$db->selectAll('products','where id in('.implode(',',array_keys($products)).') and type='.$type,'id,stock,unit_cost');
        $general->arrayIndexChange($product,'id');
        $purchase_details = $db->selectAll('purchase_details','where purchase_id='.$id);

        $purchaseDetails=[];
        $sdIDs=[];
        foreach($purchase_details as $sd){
            if(isset($product[$sd['product_id']])){
                $product[$sd['product_id']]['stock']-=$sd['quantity'];
            }
            $purchaseDetails[$sd['product_id']] = $sd;
            $sdIDs[$sd['product_id']] = $sd['id'];
        }
        $productStockLog = $db->selectAll('product_stock_log','where reference_id in('.implode(',',$sdIDs).') and change_type='.ST_CH_PURCHASE,'');
        $general->arrayIndexChange($productStockLog,'product_id');

        $pProduct=[];
        $oldProducts=[];
        $subTotal=0;

        foreach($products as $pID=>$d){
            //$p=$smt->productInfoByID($pID);
            if(!array_key_exists($pID,$pProduct)){
                if(!isset($product[$pID])){$error=fl();setMessage(1,'Some of invalid product');}
                else{
                    $p = $product[$pID];
                    if($zero_price==0&&$d['unitPrice']<=0){$error=fl();setMessage(63,'Unit price for '.$p['title']);}
                    elseif($d['qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']);}
                }
                if(!isset($error)){
                    $unit_price=$d['unitPrice'];
                    if($zero_price==1){
                        $unit_price=0;
                        $total=0;
                    }
                    else{
                        $total=$unit_price*$d['qty'];
                    }
                    $subTotal+=$total;
                    $pProduct[$pID]=[
                        'product_id'=> $pID,
                        'quantity'  => intval($d['qty']),
                        'unit_price'=>$unit_price,
                        'time'      => $bill_date,
                    ];
                    if(isset($purchaseDetails[$pID])){
                        $oldProducts[$pID]=$purchaseDetails[$pID];
                        unset($sdIDs[$pID]);
                    }
                }
            }else{$error=fl();setMessage(69,$p['title']);}
        }
        if(!isset($error)){
            $netAmount=$subTotal-$discount;
            $netAmount-=$VAT;
            $netAmount-=$AIT;
            if(empty($pProduct)){$error=fl();setMessage(111,'Product List');}
            elseif($zero_price==0&&$netAmount<=0){$error=fl();setMessage(63,'Discount');}
        }
    }
    if(!isset($error)){
        $db->transactionStart();
        $data=[
            'supplier_id'     => $supplier_id,
            'supplier_invoice_no'  => $supplier_no,
            'type'   => $type,
            'date'   => $bill_date,
            'challan_no'   => $challan_no,
            'challan_date'   => $challan_date,
            'po_no'         => $po_no,
            'mrr_code'   => $mrr_code,
            'mrr_date'   => $mrr_date,
            'sub_total'  => $subTotal,
            'discount'  => $discount,
            'VAT'  => $VAT,
            'AIT'  => $AIT,
            'total'  => $netAmount,
            'remarks'   => $note,
        ];
        $db->arrayUserInfoEdit($data);
        $where=['id'=>$id];
        $update=$db->update('purchase',$data,$where,'array',$jArray);
        if($update!=false){


            foreach($pProduct as $data){
                $product_id=$data['product_id'];
                $p=$product[$product_id];

                if($p['stock']>0&&$p['unit_cost']>0){
                    $unit_cost =round( (($p['stock']*$p['unit_cost'])+($data['quantity']*$data['unit_price']))/($p['stock']+$data['quantity']),2);
                }
                else{
                    $unit_cost = $data['unit_price'];
                }
                //$unit_cost =round( (($p['stock']*$p['unit_cost'])+($data['quantity']*$data['unit_price']))/($p['stock']+$data['quantity']),2);
                $nextQty=$p['stock'];
                if(isset($oldProducts[$product_id])){
                    $sd = $oldProducts[$product_id];
                    $psl = $productStockLog[$product_id];
                    $deleteStock[$product_id]  =$psl;
                    unset($productStockLog[$product_id]);
                    $psclData=[
                        'product_id'    => $product_id,
                        'quantity'      => $data['quantity'],
                        'action_time'    => $bill_date,
                    ];
                    $where = ['id'=>$psl['id']];
                    $update = $db->update('product_stock_log',$psclData,$where,'array',$jArray);
                    if($update==false){$error=fl();setMessage(66); break;}
                    $where = ['id'=>$sd['id']];
                    $update=$db->update('purchase_details',$data,$where);
                    if($update==false){$error=fl();setMessage(66); break;}
                }
                else{
                    $data['purchase_id']=$id;
                    $purchase_details_id=$db->insert('purchase_details',$data,true);
                    if($purchase_details_id==false){$error=fl();setMessage(66);break;}
                    $log=$smt->productStockChangeLog($product_id,$data['quantity'],ST_CH_PURCHASE,$purchase_details_id,$bill_date);
                    if($log==false){$error=fl();setMessage(66);break;}

                }
                $nextQty+=$data['quantity'];
                $where=['id'=>$product_id];
                $update=$db->update('products',['stock'=>$nextQty,'unit_cost'=>$unit_cost],$where);
                if($update==false){$error=fl();setMessage(66);break;}
                $update=$smt->update_product_closing_stock($product_id,$jArray);
                if($update==false){$error=fl();setMessage(66);}
            }
            $jArray[fl()] =   $sdIDs;
            $jArray[fl()] =   $productStockLog;
            if(!isset($error)){
                if(!empty($sdIDs)){

                    $delete=$db->runQuery('DELETE FROM `purchase_details` WHERE id in('.implode(',',$sdIDs).')');
                    if(!$delete){$error=fl();setMessage(66);}
                }
            }
            if(!isset($error)){
                $supHead=$acc->getSupplierHead($sup);
                if($supHead==false){$error=fl();setMessage(66);}
                $purchaseHead=$acc->getSystemHead(AH_PURCHASE);
                if($purchaseHead==false){$error=fl();setMessage(66);}
            }
            if(!isset($error)){
                if(!empty($productStockLog)){
                    $pslIDs=[];
                    foreach($productStockLog as $psl){
                        $pslIDs[$psl['id']] = $psl['id'];
                    }
                    $delete=$db->runQuery('DELETE FROM `product_stock_log` WHERE id in('.implode(',',$pslIDs).') and change_type='.ST_CH_PURCHASE);
                    if(!$delete){$error=fl();setMessage(66);}
                }
            }
            if(!isset($error)){
                $remarks = 'Purchase from '.$sup['name'].' '.$supplier_no;
                $v = $acc->voucherDetails(V_T_PURCHASE,$id);
                if(!empty($v) && $netAmount>0){
                    $v = end($v);
                    $voucher = $acc->voucherEdit($v['id'],$netAmount,$remarks,0,$supHead,$bill_date);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
                elseif(empty($v)&& $netAmount>0){
                    
                    
                    $voucher=$acc->newVoucher(0,V_T_PURCHASE,$netAmount,$purchaseHead,$supHead,$remarks,$id);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
                elseif(!empty($v)){
                    $v = end($v);
                    $delete=$acc->voucher_delete($v['id']);
                    if(!$delete){$error=fl();setMessage(66);}
                }
                $v = $acc->voucherDetails(V_T_VAT_PURCHASE,$id);
                if(!empty($v) && $VAT>0){
                    $v = end($v);
                    $voucher = $acc->voucherEdit($v['id'],$VAT,$remarks.' VAT',$supHead);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
                elseif(empty($v)&& $VAT>0){
                    $supHead=$acc->getSupplierHead($sup);
                    if($supHead==false){$error=fl();setMessage(66);}
                    $vat_head=$acc->getSystemHead(AH_VAT_PURCHAS);
                    if($vat_head==false){$error=fl();setMessage(66);}
                    $voucher=$acc->newVoucher(0,V_T_VAT_PURCHASE,$VAT,$supHead,$vat_head,$bill_date,$remarks.' VAT',$id);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
                elseif(!empty($v)){
                    $v = end($v);
                    $delete=$acc->voucher_delete($v['id']);
                    if(!$delete){$error=fl();setMessage(66);}
                }
                
                $v = $acc->voucherDetails(V_T_AIT_PURCHASE,$id);
                if(!empty($v) && $AIT>0){
                    $v = end($v);
                    $voucher = $acc->voucherEdit($v['id'],$AIT,$remarks.' VAT');
                    if($voucher==false){$error=fl();setMessage(66);}
                }
                elseif(empty($v)&& $AIT>0){
                    $supHead=$acc->getSupplierHead($sup);
                    if($supHead==false){$error=fl();setMessage(66);}
                    $ait_head=$acc->getSystemHead(AH_AIT_PURCHAS);
                    if($vat_head==false){$error=fl();setMessage(66);}
                    $voucher=$acc->newVoucher(0,V_T_AIT_PURCHASE,$AIT,$supHead,$ait_head,$bill_date,$remarks.' AIT',$id);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
                elseif(!empty($v)){
                    $v = end($v);
                    $delete=$acc->voucher_delete($v['id']);
                    if(!$delete){$error=fl();setMessage(66);}
                }
            }
        }else{$error=fl();setMessage(66);}
    }
    
     if(!isset($error)){
            $ac=true;
            $jArray['status']=1;
            setMessage(30,'purchase');
        }
        else{$ac=false;}
        $db->transactionStop($ac);
    }
    $general->createLog('purchase_edit',$jArray);