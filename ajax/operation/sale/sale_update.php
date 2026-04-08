<?php
$general->createLog('sale_update',$_POST);
$manage_order_number_and_date = $db->get_company_settings('manage_order_number_and_date');
    $not_check_cedit_limit = 0;
    if($db->permission(103)){
        $not_check_cedit_limit = intval($_POST['not_check_cedit_limit']);
    }
    $id = intval($_POST['id']);
    $s = $db->get_rowData('sale','id',$id);
    $products   = $_POST['products'];
    $discount   = floatval($_POST['discount']);

    $pay_type    = intval($_POST['pay_type']);
    $cID        = intval($_POST['cID']);
    $due_day        = intval($_POST['due_day']);
    $base_id        = intval($_POST['base_id']);
    $note   = $_POST['note']??'';
    $saleDate   = strtotime($_POST['saleDate']);
    if($manage_order_number_and_date){
        $order_no = $_POST['order_no'];
        $order_date = strtotime($_POST['order_date']??date('d-m-Y'));
    }

    $c          = $smt->customerInfoByID($cID);    
    $base          = $db->baseInfoByID($base_id);
    if(empty($s)){$error=fl(); setMessage(63,'sale');}
    elseif($cID<=0){$error=fl();setMessage(1,'Select a customer');}
    elseif($base_id<=0){$error=fl();setMessage(1,'Select a Base');}
    elseif(empty($base)){$error=fl();setMessage(63,'Base');}
    elseif($due_day<0){$error=fl();setMessage(63,'Due day');}
    elseif(empty($c)){$error=fl(); setMessage(63,'customer');}
    elseif($c['base_id']!=$base_id){$error=fl();setMessage(63,'Base');}
    elseif($saleDate<strtotime('-2 years')){$error=fl();setMessage(63,'sale date');}
    elseif($pay_type!=PAY_TYPE_CASH&&$pay_type!=PAY_TYPE_CREDIT){$error=fl();setMessage(63,'pay type');}
    elseif(empty($products)){$error=fl();setMessage(63,'Products');}
    else{
        //$c          = $smt->customerInfoByID($s['customer_id']);
        $saleDetail = $db->selectAll('sale_products','where sale_id='.$id);
        if($s['product_type']==PRODUCT_TYPE_MANUFACTURING){
            $product = $db->selectAll('products','where type in('.PRODUCT_TYPE_MANUFACTURING.','.PRODUCT_TYPE_PACKAGING.') and id in('.implode(',',array_keys($products)).')','id,title,sale_price,unit_id,stock,data,VAT,unit_cost');
        }
        else{
            $product = $db->selectAll('products','where type in('.PRODUCT_TYPE_FINISHED.','.PRODUCT_TYPE_GIFT_ITEM.') and id in('.implode(',',array_keys($products)).')','id,title,sale_price,unit_id,stock,data,VAT,unit_cost');
        }

        
        $general->arrayIndexChange($product);
        $saleDetails=[];
        $sdIDs=[];
        foreach($saleDetail as $sd){
            if(isset($product[$sd['product_id']])){
                $product[$sd['product_id']]['stock']+=$sd['total_qty'];
            }
            $saleDetails[$sd['product_id']] = $sd;
            $sdIDs[$sd['product_id']] = $sd['id'];
        }
        $jArray[fl()] =  $saleDetails;
        $jArray[fl()] =  $products;
        $productStockLog = $db->selectAll('product_stock_log','where reference_id in('.implode(',',$sdIDs).') and change_type='.ST_CH_SALE);
        $general->arrayIndexChange($productStockLog,'product_id');
        //$general->printArray($productStockLog);


        $sProduct = [];
        $sub_total=0;
        $total_vat=0;
        $total_cost=0;
        $total_amount=0;
        $product_total_discount=0;
        $oldProducts=[];
        $remove_products=[];
        foreach($products as  $pID=>$ps){
            $p = @$product[$pID];
            if(!isset($products[$pID])) {$error=fl(); setMessage(1,'Products'); break;}
            elseif(array_key_exists($pID,$sProduct)){$error=fl();setMessage(69,$p['title']); break;}
            elseif($ps['total_qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
            elseif($ps['qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
            elseif($ps['free_qty']<0){$error=fl();setMessage(63,'free Quantity for '.$p['title']); break;}
            //elseif($p['stock']<=0){$error=fl();setMessage(1,$p['title'].' stock out!'); break;}
            //elseif($p['stock']<$ps['total_qty']){$error=fl();setMessage(1,$p['title'].' stock out!'); break;}
            elseif($p['sale_price']<$ps['salePrice']){$error=fl();setMessage(1,$p['title'].' invalid sale price!'); break;}
            else{ 

                $product_discount = ($p['sale_price']-$ps['salePrice'])*$ps['qty'];
                $product_total_discount+=$product_discount;
                $jArray[fl()] =$ps['discount']; 
                $jArray[fl()] =$product_discount; 
                $sale_qty = $ps['qty'];
                $free_qty = $ps['free_qty'];
                $total_qty = $ps['qty'];
                $total_qty+=$free_qty;

                $product_price = $sale_qty*$p['sale_price'];
                $total_cost += $sale_qty*$p['unit_cost'];
                $product_total = $product_price-$ps['discount'];
                if($total_qty!=$ps['total_qty']){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
                elseif($free_qty!=$ps['free_qty']){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
                //elseif($ps['discount']!=$product_discount){$error=fl();setMessage(63,'Discount for '.$p['title']); break;}
                elseif($product_total<0){$error=fl();setMessage(63,'Total for '.$p['title']); break;}
                $VAT=floatval($p['VAT']);
                $vat_amount=0;
                if($VAT>0&&$product_total>0){
                    $vat_amount=round($VAT / 100 * $product_total,2);
                }
                $product_total+=$vat_amount;
                $total_vat=$vat_amount;
                $sub_total+=$product_price;
                $total_amount+=$product_total;
                $sProduct[$pID]=[
                    'product_id'=> $pID,
                    'sale_qty'  => $sale_qty,
                    'free_qty'  => $free_qty,
                    'total_qty' => $total_qty,
                    'unit_price'=> $p['sale_price'],
                    'sub_total' => $product_price,
                    'discount'  => floatval($ps['discount']),
                    'VAT'       => floatval($vat_amount),
                    'total'     => $product_total,
                    'unit_cost' => $p['unit_cost']
                ];

                if(isset($saleDetails[$pID])){
                    $oldProducts[$pID]=$saleDetails[$pID];
                    unset($sdIDs[$pID]);
                }
            }
        }
        // if(!isset($error)){
        //     foreach($saleDetails as $pID=>$op){
        //         if(!isset($sProduct[$pID])){
        //             $remove_products[$pID]=$productStockLog[$pID];
        //         }
        //         else{
        //             $jArray[fl()][]=$sProduct[$pID];
        //         }
        //     }
        //     $error=1;
        // }
        $jArray[fl()]=$remove_products;
        if(!isset($error)){
            $total = $sub_total-$discount-$product_total_discount;
            if($sub_total<=$discount){$error = fl(); setMessage(63,'discount');}
            elseif($total<0){$error = fl(); setMessage(63,'discount');}
            else{
                $cData = $general->getJsonFromString($c['data']);
                $customer_head=$acc->getCustomerHead($c);
                $customer_balance=$acc->headBalance($customer_head);
                $customer_due_data = $acc->customer_due_details($cID);
                $balance =$customer_due_data['customer_balance']+floatval(@$cData['credit_limit'])-$total;
                $jArray[fl()] = $customer_due_data;
                $jArray[fl()] = $total;
                $jArray[fl()] = $balance;
                $jArray[fl()] = $cData;
                if($not_check_cedit_limit==0){
                    if($total>$s['total']){
                        if($balance<0){
                            $error=fl();setMessage(1,'Credit limit over');
                        }
                        else if($customer_due_data['due_date']>0&&$customer_due_data['due_date']<TIME){$error=fl();setMessage(1,"You don't have enough balance");}
                    }
                }
            }
            if(!isset($error)){
                $db->transactionStart();
                //$sale_data = $general->getJsonFromString($s['data']);//কাস্টমার ব্যালেন্স আপডেট হবে না। আপডেট করলে সমস্যা হবে। //$sale_data['customer_closing_balance']=$customer_balance;
                $data = [
                    'customer_id'   => $cID,
                    'pay_type'      => $pay_type,
                    'date'          => $saleDate,
                    'sub_total'     => $sub_total,
                    'discount'      => $product_total_discount,
                    'extra_discount'=> $discount,
                    'VAT'           => $total_vat,
                    'total'         => $total,
                    //'data'          => json_encode($sale_data),
                    'cost'          => $total_cost,
                    'note'          => $note,
                    'collection_date'=>strtotime("+$due_day day",$saleDate),
                    'base_id'        => $base_id,
                ];
                if($manage_order_number_and_date){
                    $data['order_no'] = $order_no;
                    $data['order_date'] = $order_date;
                }
                $db->arrayUserInfoEdit($data);
                $where = ['id'=>$id];
                $update = $db->update('sale',$data,$where);
                if(!$update){$error=fl(); setMessage(66);}
                else{
                    $deleteStock=[];
                    $pslIDs=[];
                    $jArray[fl()] = $oldProducts;
                    foreach($sProduct as $pID=>$data){
                        $p = $smt->productInfoByID($pID,false);
                        $nextQty=$p['stock'];
                        if(isset($oldProducts[$pID])){
                            $jArray[fl()][] = $pID;
                            $sd = $oldProducts[$pID];
                            $psl = $productStockLog[$pID];
                            $deleteStock[$pID]  =$psl;
                            unset($productStockLog[$pID]);
                            $psclData=[
                                'product_id'    => $pID,
                                'quantity'      => -$data['total_qty'],
                                'entry_time'    => $s['date'],
                                'action_time'   => $saleDate
                            ];
                            $where = ['id'=>$psl['id']];
                            $update = $db->update('product_stock_log',$psclData,$where,'array',$jArray);
                            if($update==false){$error=fl();setMessage(66); break;}
                            $where = ['id'=>$sd['id']];
                            $update=$db->update('sale_products',$data,$where);
                            if($update==false){$error=fl();setMessage(66); break;}
                        }
                        else{
                            $data['sale_id']=$id;
                            $sdID=$db->insert('sale_products',$data,true);
                            if($sdID==false){$error=fl();setMessage(66); break;}
                            $log=$smt->productStockChangeLog($pID,$data['total_qty'],ST_CH_SALE,$sdID,$sale_date);
                            if($log==false){$error=fl();setMessage(66); break;}
                        }
                        $update=$smt->update_product_closing_stock($pID,$jArray);
                        if($update==false){$error=fl();setMessage(66);}
                        // $nextQty-=$data['total_qty'];
                        // $where=['id'=>$pID];
                        // $update=$db->update('products',['stock'=>$nextQty],$where);
                        // if($update==false){$error=fl();setMessage(66);}
                    }
                    $jArray[fl()] =   $sdIDs;
                    $jArray[fl()] =   $productStockLog;
                    if(!isset($error)){
                        if(!empty($sdIDs)){
                            foreach($sdIDs as $pID=>$sdID){
                                $p=$smt->productInfoByID($pID,false);
                                $sd=$saleDetails[$pID];
                                $jArray[fl()][]=$p;
                                $new_stock=$p['stock']+$sd['total_qty'];
                                $jArray[fl()][]=$new_stock;
                                $data=[
                                    'stock'=>$new_stock
                                ];
                                $where=['id'=>$pID];
                                $update=$db->update('products',$data,$where);
                                if(!$update){$error=fl();setMessage(66);}
                            }
                            $delete=$db->runQuery('DELETE FROM `sale_products` WHERE id in('.implode(',',$sdIDs).')','array',$jArray);
                            if(!$delete){$error=fl();setMessage(66);}
                        }
                        else{
                            $jArray[fl()]=1;
                        }
                    }
                    else{
                        $jArray[fl()]=$error;

                    }
                    if(!isset($error)){
                        if(!empty($productStockLog)){
                            $pslIDs=[];
                            foreach($productStockLog as $psl){
                                $pslIDs[$psl['id']] = $psl['id'];
                            }
                            $delete=$db->runQuery('DELETE FROM `product_stock_log` WHERE id in('.implode(',',$pslIDs).')','array',$jArray);
                            if(!$delete){$error=fl();setMessage(66);}
                        }
                    }
                    //$error=fl();
                    $customer_head=$acc->getCustomerHead($c);
                    if($customer_head==false){$error=fl();setMessage(66);}
                    $jArray[fl()]=$customer_head;

                    if(!isset($error)){
                        $customerAmount=$total;
                        $v = $acc->voucherDetails(V_T_SALE_CASH_CUSTOMER,$id);
                        $v = end($v);
                        $particular='Sale '.$c['name'].' '.$s['invoice_no'];
                        $voucher = $acc->voucherEdit($v['id'],$customerAmount,$particular,$customer_head,0,$saleDate);
                        if($voucher==false){$error=fl();setMessage(66);}
                        
                        
                        
                        
                        $v = $acc->voucherDetails(V_T_SALE_VAT,$id);
                        if($vat_amount>0){
                            if(empty($v)){
                                $vat_head=$acc->getSystemHead(AH_VAT_SALE);
                                if($vat_head==false){$error=fl();setMessage(66);}
                                $particular='Sale '.$c['name'].' '.$s['invoice_no'];
                                $voucher=$acc->voucher_create(V_T_SALE_VAT,$vat_amount,0,$vat_head,$saleDate,$particular,$sID);
                                if($voucher==false){$error=fl();setMessage(66);}
                            }
                            else{
                                $v = end($v);
                                $voucher = $acc->voucherEdit($v['id'],$vat_amount,$v['note'],0,0,$saleDate);
                                if($voucher==false){$error=fl();setMessage(66);}
                            }
                        }
                        else{
                            if(!empty($v)){
                                $v = end($v);
                                $delete=$acc->voucher_delete($v['id']);
                                if(!$delete){$error=fl();setMessage(66);}
                            }
                        }
                        
                    }

                }
                $ac = false; 
                if(!isset($error)){
                    $jArray['status'] = 1;
                    $ac = true;
                    setMessage(2,'Sale update successfully');
                }
                $db->transactionStop($ac);
            }
        }
    }

    $general->createLog('sale_update',$jArray);