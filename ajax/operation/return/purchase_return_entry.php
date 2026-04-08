<?php
    $prodcut_type    = intval($_POST['prodcut_type']);
    $supplyer_id    = intval($_POST['supplyer_id']);
    $products   = $_POST['products']??[];
    $note   = $_POST['note']??'';
    $date   = strtotime($_POST['date']);
    $discount   = floatval($_POST['discount']);
    $product_types = $smt->get_all_product_type();
    $supplyer = $db->get_rowData('suppliers','id',$supplyer_id);
    if(!isset($product_types[$prodcut_type])){$error=fl();setMessage(63,'product type');}
    elseif(empty($supplyer)){$error=fl();setMessage(63,'supplyer');}
    elseif($date<strtotime('-2 years')){$error=fl();setMessage(63,'incoice date');}
    elseif(empty($products)){$error=fl();setMessage(63,'Products');}
    else{

        $product = $db->selectAll('products','where type ='.$prodcut_type.' and id in('.implode(',',array_keys($products)).')','id,title,sale_price,unit_id,stock,data,VAT');
        $general->arrayIndexChange($product);
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
                $total = $subTotal-$discount;
                $data = [
                    'supplier_id'   => $supplyer_id,
                    'date'          => $date,
                    'sub_total'     => $subTotal,
                    'discount'      => $discount,
                    'return_amount' => $total,
                    'note'          => $note,
                ];
                $db->arrayUserInfoAdd($data);
                $id = $db->insert('purchase_return',$data,true);
                if(!$id){$error=fl(); setMessage(66);}
                else{
                    foreach($sProduct as $pID=>$data){
                        $data['purchase_return_id']=$id;
                        $sdID=$db->insert('purchase_return_details',$data,true);
                        if(!$sdID){
                            $error=fl(); setMessage(66); break;
                        }
                        else{
                            $p = $product[$pID];
                            $nextQty=$p['stock'];
                            $log=$smt->productStockChangeLog($pID,$data['quantity'],ST_CH_PURCHASE_RETURN,$id,TIME);
                            if($log==false){$error=fl();setMessage(66);}
                            $update=$smt->update_product_closing_stock($pID,$jArray);
                            if($update==false){$error=fl();setMessage(66);}
                            // $nextQty-=$data['quantity'];
                            // $where=['id'=>$pID];
                            // $update=$db->update('products',['stock'=>$nextQty],$where);
                            // if($update==false){$error=fl();setMessage(66);}

                        }
                    }
                    if(!isset($error)){
                        $purchaseHead=$acc->getSystemHead(AH_PURCHASE);
                        if($purchaseHead==false){$error=fl();setMessage(66);}


                        $supHead=$acc->getSupplierHead($supplyer);
                        if($supHead==false){$error=fl();setMessage(66);}



                        $cashHead=$acc->getSystemHead(AH_CASH);
                        if($cashHead==false){$error=fl();setMessage(66);}

                    }


                    if(!isset($error) && $total>0){
                        $customerAmount=$total;
                        $particular='Purchase return '.$id;
                        $voucher=$acc->voucher_create(V_T_PURCHASE_RETURN,$customerAmount,$purchaseHead,$supHead,TIME,$particular,$id);
                        if($voucher==false){$error=fl();setMessage(66);}
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