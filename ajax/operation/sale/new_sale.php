<?php
    $general->createLog('sale',$_POST);
    $manage_order_number_and_date = $db->get_company_settings('manage_order_number_and_date');
    $not_check_credit_limit = 0;
    if($db->permission(103)){
        $not_check_credit_limit = intval($_POST['not_check_credit_limit']);
    }
    if (intval($_POST['PRODUCT_TYPE']) == PRODUCT_TYPE_OFFER) {
        $product_type = PRODUCT_TYPE_OFFER;
    }elseif(intval($_POST['PRODUCT_TYPE']) == PRODUCT_TYPE_MANUFACTURING) {
        $product_type = PRODUCT_TYPE_MANUFACTURING;
    }
     else {
        $product_type = PRODUCT_TYPE_FINISHED;
    }
    $jArray[fl()]= $product_type;
    $draftID    = intval($_POST['draftID']);
    $pay_type    = intval($_POST['pay_type']);
    $cID        = intval($_POST['cID']);
    $due_day        = intval($_POST['due_day']);
    $base_id        = intval($_POST['base_id']);
    $products   = $_POST['products'];
    $note   = $_POST['note']??'';
    $saleDate   = strtotime($_POST['saleDate']);
    $discount   = floatval($_POST['discount']);
    if($manage_order_number_and_date){
        $order_no = $_POST['order_no'];
        $order_date = strtotime($_POST['order_date']??date('d-m-Y'));
    }
    $c          = $smt->customerInfoByID($cID);
    $base          = $db->baseInfoByID($base_id);
    $pay_types=$db->selectAll('pay_types','where isActive=1');
    $general->arrayIndexChange($pay_types,'id');
    if($cID<=0){$error=fl();setMessage(1,'Select a customer');}
    elseif($base_id<=0){$error=fl();setMessage(1,'Select a Base');}
    elseif(empty($base)){$error=fl();setMessage(63,'Base');}
    elseif($due_day<0){$error=fl();setMessage(63,'Due day');}
    elseif(empty($c)){$error=fl(); setMessage(63,'customer');}
    elseif($c['base_id']!=$base_id){$error=fl();setMessage(63,'Base');}
    elseif($saleDate<strtotime('-2 years')){$error=fl();setMessage(63,'Purchase date');}
    elseif(!isset($pay_types[$pay_type])){$error=fl();setMessage(63,'pay type');}
    elseif(empty($products)){$error=fl();setMessage(63,'Products');}
    else{
        if($saleDate==TODAY_TIME){
            $saleDate=TIME;
        }
        if($product_type==PRODUCT_TYPE_FINISHED||$product_type==PRODUCT_TYPE_OFFER){
            $type_query='and type in('.PRODUCT_TYPE_FINISHED.')';
        }
        else{
            $type_query='and type in('.PRODUCT_TYPE_MANUFACTURING.','.PRODUCT_TYPE_PACKAGING.','.PRODUCT_TYPE_RAW.')';
        }
        $product = $db->selectAll('products','where id in('.implode(',',array_keys($products)).') '.$type_query,'id,title,sale_price,unit_id,stock,data,VAT,unit_cost','array',$jArray);
        $general->arrayIndexChange($product);
        $sProduct = [];
        $sub_total=0;
        $total_vat=0;
        $total_cost=0;
        $total_amount=0;
        $product_total_discount=0;
        foreach($products as  $pID=>$ps){
            $p = @$product[$pID];  
            $jArray[fl()][$pID]=$p;

            $data = $general->getJsonFromString($p['data']);
            if(!isset($products[$pID])) {$error=fl(); setMessage(1,'Products'); break;}
            elseif(array_key_exists($pID,$sProduct)){$error=fl();setMessage(69,$p['title']); break;}
            elseif($ps['total_qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
            elseif($ps['qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
            elseif($ps['free_qty']<0){$error=fl();setMessage(63,'free Quantity for '.$p['title']); break;}
            elseif($p['stock']<=0){$error=fl();setMessage(1,$p['title'].' stock out!'); break;}
            elseif($p['stock']<$ps['total_qty']){$error=fl();setMessage(1,$p['title'].' stock out!'); break;}
            elseif($p['sale_price']<$ps['salePrice']){$error=fl();setMessage(1,$p['title'].' invalid sale price!'); break;}
            else{ 
                $product_discount = ($p['sale_price']-$ps['salePrice'])*$ps['qty'];
                $product_total_discount+=$product_discount;
                $sale_qty = floatval($ps['qty']);
                $free_qty = floatval($ps['free_qty']);
                $total_qty = floatval($ps['qty']);
                $total_qty+=$free_qty;

                $product_price = $sale_qty*$p['sale_price'];
                $total_cost += $sale_qty*$p['unit_cost'];
                $product_total = $product_price-$ps['discount'];
                if($total_qty!=$ps['total_qty']){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
                elseif($free_qty!=$ps['free_qty']){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
                //elseif($ps['discount']!=$product_discount){$error=fl();setMessage(63,'Discount for '.$p['title'].' '.$ps['discount'].' '.$product_discount); break;}এটার আর দরকার নাই মনে হয় সালাম ০১ সেপ্টেম্বর ২০২৪
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
            }
        }
        if(!isset($error)){
            $total = $sub_total-$discount-$product_total_discount;
            if($sub_total<$discount){$error = fl(); setMessage(63,'discount');}
            elseif($total<0){$error = fl(); setMessage(63,'discount');}
            else{
                
                $cData = $general->getJsonFromString($c['data']);
                
                $customer_head=$acc->getCustomerHead($c);
                $customer_balance=$acc->headBalance($customer_head);
                $customer_due_data = $acc->customer_due_details($cID);
                $balance =$customer_due_data['customer_balance']+floatval(@$cData['credit_limit'])-$total;
                $jArray[fl()] = $customer_due_data;
                $jArray[fl()] = $cID;
                $jArray[fl()] = $total;
                $jArray[fl()] = $balance;
                $jArray[fl()] = $cData;
                if($customer_due_data['customer_balance']!=0){//নতুন কাস্টমারের ক্ষেত্রে এই লিমিট লাগবে না।
                    if($not_check_credit_limit==0){
                        if($balance<0){$error=fl();setMessage(1,'Credit limit over');}
                        else if($customer_due_data['due_date']>0&&$customer_due_data['due_date']<TIME){$error=fl();setMessage(1,"Customer credit limit or due date over");}
                    }
                    else{
                        $jArray[fl()]=1;
                    }
                }
                else{
                    $jArray[fl()]=1;
                }

            }
            if(!isset($error)){ 
                $customer_balance=-$customer_due_data['customer_balance'];
                $customer_due_invoice=[];
                if(!empty($customer_due_data['due_data'])){
                    foreach($customer_due_data['due_data'] as $d){
                        $due_pay_type=$d['pay_type']==PAY_TYPE_CASH?'Cash':'Credit';
                        $aging=$general->get_time_difference($saleDate,$d['date'],'d');
                        $customer_due_invoice[]=[
                            'date'      => $d['date'],
                            'pay_type'  => $due_pay_type,
                            'aging'     => $aging,
                            'due'       => $d['due'],
                            'invoice_no'=> $d['invoice_no'],
                        ];
                    }
                }

            }
            if(!isset($error)){ 
                
                $db->transactionStart();
                $sale_data=[
                    'customer_closing_balance'=>$customer_balance,
                    'customer_due_invoice'=>$customer_due_invoice
                ];

                $data = [
                    'customer_id'   => $cID,
                    'product_type'  => $product_type,
                    'pay_type'      => $pay_type,
                    'date'          => $saleDate,
                    'sub_total'     => $sub_total,
                    'discount'      => $product_total_discount,
                    'extra_discount'=> $discount,
                    'VAT'           => $total_vat,
                    'total'         => $total,
                    'data'          => json_encode($sale_data),
                    'cost'          => $total_cost,
                    'note'          => $note,
                    'collection_date'=>strtotime('+'.$due_day.' day',$saleDate),
                    'base_id'        => $base_id,
                ];
                if($manage_order_number_and_date){
                    $data['order_no'] = $order_no;
                    $data['order_date'] = $order_date;
                }
                $jArray[fl()]=$data;
                $jArray[fl()]=$sProduct;
                $db->arrayUserInfoAdd($data);
                $sID = $db->insert('sale',$data,true);
                if(!$sID){$error=fl(); setMessage(66);}
                else{
                    $prefix = $base['code'].'-';
                    $invoice_no = $db->setAutoCode('sale_invoice_no',$sID,$prefix);
                    if($invoice_no==false){$error=fl();setMessage(66);}
                    foreach($sProduct as $pID=>$data){
                        $data['sale_id']=$sID;
                        $sdID=$db->insert('sale_products',$data,true);
                        if($sdID){
                            $p = $smt->productInfoByID($pID,false);
                            $log=$smt->productStockChangeLog($pID,$data['total_qty'],ST_CH_SALE,$sdID,$saleDate);  
                            if($log==false){$error=fl();setMessage(66);}
                            $nextQty=$p['stock'];
                            $nextQty-=$data['total_qty'];
                            $update=$smt->update_product_closing_stock($pID,$jArray);
                            if($update==false){$error=fl();setMessage(66);}
                            // $where=['id'=>$pID];
                            // $update=$db->update('products',['stock'=>$nextQty],$where);
                            // if($update==false){$error=fl();setMessage(66);break;}
                        }
                    }
                    if(!isset($error)){
                        $saleHead=$acc->getSystemHead(AH_DEALER_SALE);
                        if($saleHead==false){$error=fl();setMessage(66);}
                        $cashHead=$acc->getSystemHead(AH_CASH);
                        if($cashHead==false){$error=fl();setMessage(66);}
                        $customer_head=$acc->getCustomerHead($c);
                        if($customer_head==false){$error=fl();setMessage(66);}
                        if($vat_amount>0){
                            $vat_head=$acc->getSystemHead(AH_VAT_SALE);
                            if($vat_head==false){$error=fl();setMessage(66);}
                        }
                    }
                    if(!isset($error)){
                        $sale_amount=$total;
                        if($vat_amount>0){
                            $sale_amount-=$vat_amount;
                        }
                        $particular='Sale '.$c['name'].' '.$invoice_no;
                        $voucher=$acc->voucher_create(V_T_SALE_CASH_CUSTOMER,$sale_amount,$customer_head,$saleHead,$saleDate,$particular,$sID);
                        if($voucher==false){$error=fl();setMessage(66);}
                        if($vat_amount>0){
                            $voucher=$acc->voucher_create(V_T_SALE_VAT,$vat_amount,$customer_head,$vat_head,$saleDate,$particular,$sID);
                            if($voucher==false){$error=fl();setMessage(66);}
                        }
                    }
                }
                if($draftID>0){
                    $data=['isActive'=>2];
                    $where = ['id'=>$draftID ];
                    $update = $db->update('sale_draft',$data,$where);
                    if(!$update){$error=fl(); setMessage(66);}
                }
                $ac = false; 
                if(!isset($error)){
                    if(!empty($c['mobile'])){

                        $variables=[
                            'amount'=>$total
                        ];

                        $sms=$smt->generate_sms('invoice_create',$variables,$c['mobile'],$jArray);

                    }
                }
                if(!isset($error)){
                    $jArray['status'] = 1;
                    $jArray['sale_id'] = $sID;
                    $ac = true;
                    setMessage(2,'Products sale successfully');
                }
                $db->transactionStop($ac);
            }
        }   
}
if(isset($error)){setErrorMessage($error);}
$jArray['m']=show_msg('y');
$general->createLog('sale',$jArray);