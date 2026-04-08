<?php
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
    if(empty($sup)){$error=fl();setMessage(63,'Supplier');}
    //if($purDate<strtotime('-1 month')){$error=fl();setMessage(63,'Purchase date');}
    elseif($bill_date<strtotime('-2 years')){$error=fl();setMessage(63,'bill date');}
    elseif($bill_date>strtotime('2 years')){$error=fl();setMessage(63,'bill date');}
    elseif($challan_date<strtotime('-2 years')){$error=fl();setMessage(63,'Challan date');}
    elseif($challan_date>strtotime('2 years')){$error=fl();setMessage(63,'Challan date');}
    elseif($mrr_date<strtotime('-2 years')){$error=fl();setMessage(63,'MRR date');}
    elseif($mrr_date>strtotime('2 years')){$error=fl();setMessage(63,'MRR date');}
    elseif(empty($products)){$error=fl();setMessage(63,'Products');}
    elseif($VAT<0){$error=fl();setMessage(63,'VAT');}
    elseif($AIT<0){$error=fl();setMessage(63,'AIT');}

    if(!isset($error)){
        $pProduct=[];
        $subTotal=0;
        if($bill_date==TODAY_TIME){$bill_date=TIME;}
        if($challan_date==TODAY_TIME){$challan_date=TIME;}
        if($mrr_date==TODAY_TIME){$mrr_date=TIME;}

        $product=$db->selectAll('products','where id in('.implode(',',array_keys($products)).') and type='.$type,'id,stock,unit_cost,sale_price');
        $general->arrayIndexChange($product,'id'); 
        foreach($products as $pID=>$d){
            //$p=$smt->productInfoByID($pID);
            if(!array_key_exists($pID,$pProduct)){
                if(!isset($product[$pID])){$error=fl();setMessage(1,'Some of invalid product');}
                else{
                    $p = $product[$pID];
                    if($d['unitPrice']<=0){$error=fl();setMessage(63,'Unit price for '.$p['title']);}
                    elseif($d['qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']);}
                }
                if(!isset($error)){
                    $unit_price=floatval($d['unitPrice']);
                    if($zero_price==1){
                        $unit_price=0;
                        $total=0;
                    }
                    else{
                        $total=$unit_price*floatval($d['qty']);
                    }
                    
                    $subTotal+=$total;
                    $pProduct[$pID]=[
                        'product_id'=> $pID,
                        'quantity'  => floatval($d['qty']),
                        'unit_price'=> $unit_price,
                        'time'      => $bill_date,
                    ];
                }
            }else{$error=fl();setMessage(69,$p['title']);}
        }
        if(!isset($error)){
            $netAmount=$subTotal-$discount;
            $netAmount-=$VAT;
            $netAmount-=$AIT;
            if(empty($pProduct)){$error=fl();setMessage(111,'Product List');}
            elseif($netAmount<=0&&$zero_price==0){$error=fl();setMessage(63,'Discount');}
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
        $db->arrayUserInfoAdd($data);
        $purchase_id=$db->insert('purchase',$data,true);
        if($purchase_id!=false){
            $invoiceNo = $db->setAutoCode('purchaseInvoice',$purchase_id);
            $purchase_mrr_no = $db->setAutoCode('purchase_mrr_no',$purchase_id);
            if($invoiceNo==false){$error=fl();setMessage(66);}
            if($purchase_mrr_no==false){$error=fl();setMessage(66);}

            foreach($pProduct as $data){
                $p=$product[$pID];
                $unit_cost =round( (($p['stock']*$p['unit_cost'])+($data['quantity']*$data['unit_price']))/($p['stock']+$data['quantity']),6);
                $product_id=$data['product_id'];
                $data['purchase_id']=$purchase_id;
                $purchase_details_id=$db->insert('purchase_details',$data,true);
                if($purchase_details_id==false){$error=fl();setMessage(66);}

                //$nextQty=$p['stock'];
                $log=$smt->productStockChangeLog($product_id,$data['quantity'],ST_CH_PURCHASE,$purchase_details_id,$bill_date);
                if($log==false){$error=fl();setMessage(66);}
                $update=$smt->update_product_closing_stock($product_id,$jArray);
                if($update==false){$error=fl();setMessage(66);}

                $product_price_log = $db->product_price_log($product_id,['sale_price'=>$p['sale_price'],'unit_cost'=>$unit_cost]);
                if($product_price_log==false){$error=fl();setMessage(66);}
                //$nextQty+=$data['quantity'];
                $where=['id'=>$product_id];
                $update=$db->update('products',['unit_cost'=>$unit_cost],$where);
                if($update==false){$error=fl();setMessage(66);}
            }
        }else{$error=fl();setMessage(66);}


       
        if(!isset($error)){
            $supHead=$acc->getSupplierHead($sup);
            if($supHead==false){$error=fl();setMessage(66);}
            $purchaseHead=$acc->getSystemHead(AH_PURCHASE);
            if($purchaseHead==false){$error=fl();setMessage(66);}
        }
        
        if(!isset($error)){
            $remarks = 'Purchase from '.$sup['name'].' '.$supplier_no;
            if($netAmount>0){
                $voucher=$acc->newVoucher(0,V_T_PURCHASE,$netAmount,$purchaseHead,$supHead,$bill_date,$remarks,$purchase_id);
                if($voucher==false){$error=fl();setMessage(66);}
            }
            if(!isset($error)&&$VAT>0){
                $vat_head=$acc->getSystemHead(AH_VAT_PURCHAS);
                if($vat_head==false){$error=fl();setMessage(66);}
                else{
                    $voucher=$acc->newVoucher(0,V_T_VAT_PURCHASE,$VAT,$supHead,$vat_head,$bill_date,$remarks.' VAT',$purchase_id);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
            }
            if(!isset($error)&&$AIT>0){
                $ait_head=$acc->getSystemHead(AH_AIT_PURCHAS);
                if($vat_head==false){$error=fl();setMessage(66);}
                else{
                    $voucher=$acc->newVoucher(0,V_T_AIT_PURCHASE,$AIT,$supHead,$ait_head,$bill_date,$remarks.' AIT',$purchase_id);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
            }
        }
        if(!isset($error)){
            $ac=true;
            $jArray['status']=1;
            setMessage(29,'purchase');
        }
        else{$ac=false;}
        $db->transactionStop($ac);
    }
    if(isset($error)){setErrorMessage($error);}