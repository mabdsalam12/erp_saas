<?php
    $pay_type    = intval($_POST['pay_type']);
    $cID        = intval($_POST['cID']);
    $base_id        = intval($_POST['base_id']);
    $products   = $_POST['products'];
    $note   = $_POST['note']??'';
    $invoice_date   = strtotime($_POST['invoice_date']);
    $approved_date   = strtotime($_POST['approved_date']);
    $discount   = floatval($_POST['discount']);
    $c          = $smt->customerInfoByID($cID);
    $base          = $db->baseInfoByID($base_id);
    if($cID<=0){$error=fl();setMessage(1,'Select a customer');}
    elseif($base_id<=0){$error=fl();setMessage(1,'Select a Base');}
    elseif(empty($base)){$error=fl();setMessage(63,'Base');}
    elseif(empty($c)){$error=fl(); setMessage(63,'customer');}
    elseif($c['base_id']!=$base_id){$error=fl();setMessage(63,'Base');}
    elseif($invoice_date<strtotime('-2 years')){$error=fl();setMessage(63,'incoice date');}
    elseif($invoice_date>$approved_date){$error=fl();setMessage(63,'approved date');}
    elseif($pay_type!=PAY_TYPE_CASH&&$pay_type!=PAY_TYPE_CREDIT){$error=fl();setMessage(63,'pay type');}
    elseif(empty($products)){$error=fl();setMessage(63,'Products');}
    else{
        if($invoice_date==TODAY_TIME)$invoice_date=TIME;
        if($approved_date==TODAY_TIME)$approved_date=TIME;
        $product = $db->selectAll('products','where type in('.PRODUCT_TYPE_FINISHED.') and id in('.implode(',',array_keys($products)).')','id,title,sale_price,unit_id,stock,data,VAT');
        $general->arrayIndexChange($product,'id');
        $sProduct = [];
        $subTotal=0;
        $total_vat=0;
        foreach($products as  $pID=>$ps){
            $ps['qty '] = intval($ps['qty']);
            $ps['salePrice '] = floatval($ps['salePrice']);
            $p = @$product[$pID];  
            $data = $general->getJsonFromString($p['data']);
            if(!isset($products[$pID])) {$error=fl(); setMessage(1,'Products'); break;}
            elseif(array_key_exists($pID,$sProduct)){$error=fl();setMessage(69,$p['title']); break;}
            elseif($ps['qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
            elseif($ps['salePrice']<0){$error=fl();setMessage(1,$p['title'].' invalid TP!'); break;}
            else{ 
                $total = $ps['qty']*$ps['salePrice'];
                $subTotal+=$total;
                $sProduct[$pID]=[
                    'product_id'=> $pID,
                    'quantity'=> $ps['qty'],
                    'unit_price'=> $ps['salePrice'],
                ];
            }
        }
        if(!isset($error)){
            if($subTotal<$discount){$error = fl(); setMessage(1,'discount');}
            else{
                $db->transactionStart();
                $customer_head=$acc->getCustomerHead($c);
                $customer_balance=$acc->headBalance($customer_head);
                $sale_data=[
                    'customer_closing_balance'=>$customer_balance
                ];
                $total = $subTotal-$discount;
                $data = [
                    'customer_id'   => $cID,
                    'pay_type'      => $pay_type,
                    'invoice_date'  => $invoice_date,
                    'approved_date' => $approved_date,
                    'sub_total'     => $subTotal,
                    'discount'      => $discount,
                    'return_amount' => $total,
                    'note'          => $note,
                    'base_id'       => $base_id,
                ];
                $db->arrayUserInfoAdd($data);
                $id = $db->insert('sale_return',$data,true);
                if(!$id){$error=fl(); setMessage(66);}
                else{
                    $db->old_sale_return_code_generator();
                    $code = $db->setAutoCode('sale_return',$id);
                    if(!$code){$error=fl(); setMessage(66);}
                    else{   
                        foreach($sProduct as $pID=>$data){
                            $data['sale_return_id']=$id;
                            $sdID=$db->insert('sale_return_details',$data,true);
                            if(!$sdID){
                                $error=fl(); setMessage(66); break;
                            }
                        }
                        if(!isset($error)){
                            $return_head=$acc->getSystemHead(AH_SALE_RETURN);
                            if($return_head==false){$error=fl();setMessage(66);}
                            $customer_head=$acc->getCustomerHead($c);
                            if($customer_head==false){$error=fl();setMessage(66);}

                        }


                        if(!isset($error) && $total>0){
                            $customerAmount=$total;
                            $particular='Sale return '.$c['name'].' '.$id.'_'.$code;
                            $voucher=$acc->newVoucher(0,V_T_SALE_RETURN,$total,$return_head,$customer_head,$approved_date,$particular,$id);
                            if($voucher==false){$error=fl();setMessage(66);}

                        }
                    }



                }

                $ac = false; 
                if(!isset($error)){
                    $jArray['status'] = 1;
                    $jArray['return_id'] = $id;
                    $ac = true;
                    setMessage(2,'Products Return successfully');
                }
                $db->transactionStop($ac);
            }
        }
}