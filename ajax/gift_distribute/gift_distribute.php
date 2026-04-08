<?php
    $general->createLog('gift_distribute', $_POST);
    $user_id        = intval($_POST['user_id']);
    $base_id        = intval($_POST['base_id']);
    $product_type   = intval($_POST['product_type']);
    $products       = $_POST['products'];
    $date           = strtotime($_POST['date']);
    $user           = $db->userInfoByID($user_id);
    $base           = $db->baseInfoByID($base_id);
    if($user_id<=0){$error=fl();setMessage(1,'Select a user');}
    elseif(empty($user)){$error=fl(); setMessage(63,'user');}
    elseif(empty($base)){$error=fl();setMessage(63,'Base');}
    elseif($user['type']!=USER_TYPE_MPO){$error=fl(); setMessage(63,'user');}
    elseif($date<strtotime('-2 years')){$error=fl();setMessage(63,'Purchase date');}
    elseif(empty($products)){$error=fl();setMessage(63,'Products');}
    else{
        $products_ids=[];
        foreach($products as $p){$products_ids[]=$p['product_id'];}
        $product = $db->selectAll('products','where type='.$product_type.' and id in('.implode(',',$products_ids).')','id,title,sale_price,unit_id,stock,data','array',$jArray);
        if(!empty($product)){
            $general->arrayIndexChange($product,'id');
        }
        
        $jArray[fl()] = $product;
        $sProduct = [];
        $total_qyt=0;
        foreach($products as  $ps){
            $product_id = $ps['product_id'];
            $p = $product[$product_id]??[];  
            if(empty($p)) {$error=fl(); setMessage(1,'Products'); break;}
            elseif(array_key_exists($product_id,$sProduct)){$error=fl();setMessage(63,$p['title']); break;}
            elseif($ps['qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']); break;}
            elseif($p['stock']<=0){$error=fl();setMessage(1,$p['title'].' stock out!'); break;}
            elseif($p['stock']<$ps['qty']){$error=fl();setMessage(1,$p['title'].' stock out!'); break;}
            else{ 
                $qty = $ps['qty'];
                $total_qyt+=$qty;

                $sProduct[$product_id]=[
                    'product_id'=>$product_id,
                    'quantity'=>$qty,
                    'tp'=>$p['sale_price'],
                ];
            }
        }
        if(!isset($error)){
            $db->transactionStart();
            $data = [
                'user_id'   =>$user_id,
                'base_id'   =>$base_id,
                'product_type'=>$product_type,
                'date'      =>$date,
                'note'      =>'',
            ];
            $db->arrayUserInfoAdd($data);
            $id = $db->insert('gift_distribute',$data,true);
            if(!$id){$error=fl(); setMessage(66);}
            else{
                //$invoice_no = $db->setAutoCode('sale_invoice_no',$sID);
                //if($invoice_no==false){$error=fl();setMessage(66);}
                foreach($sProduct as $product_id=>$data){
                    $data['gift_distribute_id']=$id;
                    $gift_distribute_product_id=$db->insert('gift_distribute_product',$data,true);
                    if($gift_distribute_product_id){
                        $p = $smt->productInfoByID($product_id,false);
                        $log=$smt->productStockChangeLog($product_id,$data['quantity'],ST_CH_DISTRIBUTE,$gift_distribute_product_id,$date);  
                        if($log==false){$error=fl();setMessage(66);}
                        $update=$smt->update_product_closing_stock($product_id,$jArray);
                        if($update==false){$error=fl();setMessage(66);}
                        // $nextQty=$p['stock'];
                        // $nextQty-=$data['quantity'];
                        // $where=['id'=>$product_id];
                        // $update=$db->update('products',['stock'=>$nextQty],$where);
                        // if($update==false){$error=fl();setMessage(66);}
                    }
                }

            }

            $ac = false; 
            if(!isset($error)){
                $jArray['status'] = 1;
                $ac = true;
                setMessage(2,'Products gift distribute successfully');
            }
            $db->transactionStop($ac);


        }
    }
    $general->createLog('gift_distribute', $jArray);