<?php      
    $general->createLog('production',$_POST);
    $id = intval($_POST['id']);
    $target_product_id  = intval($_POST['target_product_id']);
    $date               = strtotime($_POST['date']);
    $pmo_no              = $_POST['pmo_no']??'';
    $targetQuantity     = intval($_POST['targetQuantity']);
    $yield              = intval($_POST['yield']);
    $retention_sample   = intval($_POST['retention_sample']);
    $extraCost          = floatval($_POST['extraCost']);
    $products           = $_POST['products'];
    $note               = $_POST['note'];
    $production_type    =  intval($_POST['production_type']); 

    $production_product = $db->selectAll('production_product_source','where production_id='.$id);  
    $general->arrayIndexChange($production_product);

    $production = $db->get_rowData('production_product','id',$id); 

    if($production_type==PRODUCT_TYPE_PACKAGING){
        $manufacture_product_id  = intval($_POST['manufacture_product_id']);
        $spend_qty          = intval($_POST['spend_qty']);
        $manufacture_product= $smt->productInfoByID($manufacture_product_id); 
    }




    $target_product = $db->get_rowData('products','id',$target_product_id);
    if(empty($production)){$error=fl();setMessage(63,'manufacture');}
    elseif($production['type']!=$production_type){$error=fl();setMessage(63,'manufacture');}
    elseif(empty($production_product)){$error=fl();setMessage(66);}
    elseif(empty($target_product)){$error=fl();setMessage(36,'Targeted Product');}
    elseif($date<strtotime('-2 years')){$error=fl();setMessage(63,'date');}
    elseif($targetQuantity<1){$error=fl();setMessage(63,'Targeted Quantity');}
    elseif($yield<1){$error=fl();setMessage(63,'yield Quantity');}
    elseif(empty($products)){$error=fl();setMessage(63,'Products');}
    elseif($extraCost<0){$error=fl();setMessage(63,'Extra cost');}
    elseif($retention_sample>$yield){$error=fl();setMessage(63,'Retention sample');}


    if(!isset($error)){
        $source_type=PRODUCT_TYPE_RAW;
        if($production_type==PRODUCT_TYPE_PACKAGING){
            $source_type=PRODUCT_TYPE_PACKAGING;
            if(empty($manufacture_product)){$error=fl();setMessage(36,'manufacture product');}
            else if(($manufacture_product['stock']+$production['manufacture_product_quantity'])<$spend_qty){$error=fl();set_message($manufacture_product['title'].' do not have enough stock');}
                elseif($spend_qty<=0){
                    $error=fl();setMessage(63,'spend quantity');
                }
        }
        elseif($production_type==PRODUCT_TYPE_RE_PACKAGING){
            $source_type=PRODUCT_TYPE_PACKAGING;
        }
    }
    if(!isset($error)){
        $pProduct=[];
        $subTotal=0;
        $pIDs=[];
        foreach($products as $pID=>$d){
            $pIDs[$pID]=$pID;
        }
        $product=$db->selectAll('products','where id in('.implode(',',$pIDs).') and type='.$source_type,'id,category_id,stock,unit_cost,sale_price,title');
        $general->arrayIndexChange($product,'id');
        // আগের stock এনে রাখলাম যেগুলো একি সেগুলোর জন্য 
        foreach($production_product as $sd){
            if(isset($product[$sd['product_id']])){
                $product[$sd['product_id']]['stock']+=$sd['quantity'];
            }
        }

        foreach($products as $pID=>$d){
            $p = @$product[$pID];
            if(!array_key_exists($pID,$pProduct) && !isset($error)){
                if(!isset($product[$pID])){$error=fl();setMessage(1,'Some of invalid item select.');}
                else{
                    if($d['qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']);}
                    if($d['extra_cost']<0){$error=fl();setMessage(63,'extra cost for '.$p['title']);}
                    if($p['stock']<intval($d['qty'])){$error=fl();setMessage(63,'stock '.$p['title']);}
                    else{
                        $product_total= $p['unit_cost']*$d['qty'];
                        $product_total+= $d['extra_cost'];
                        $subTotal+=$product_total;
                        $pProduct[$pID]=[
                            'product_id'        => $pID,
                            'quantity'          => intval($d['qty']),
                            'unit_cost'         => $p['unit_cost'],
                            'extra_cost'        => $d['extra_cost'],
                            'total_cost'        => $product_total,
                        ];
                    }
                }

            }
            else{$error=fl();setMessage(69,$p['title']);}
        }
        if(!isset($error)){
            $db->transactionStart();
            $total =$subTotal+$extraCost; 

            $data=[
                'product_id'        => $target_product_id,
                'date'              => $date,
                'pmo_no'            => $pmo_no,
                'quantity'          => $targetQuantity,
                'yield'             => $yield,
                'retention_sample'  => $retention_sample,
                'product_cost'      => $subTotal,
                'extra_cost'        => $extraCost,
                'total_cost'        => $total,
                'note'              => $note,
            ];
            $data['manufacture_product_id']         = 0;
            $data['manufacture_product_quantity']   = 0;
            if($production_type==PRODUCT_TYPE_PACKAGING){
                $data['manufacture_product_id']         = $manufacture_product_id;
                $data['manufacture_product_quantity']   = $spend_qty;
            }
            $build_quantity=$data['yield']-$data['retention_sample'];
            $jArray[fl()]=$data;
            $db->arrayUserInfoAdd($data);
            $update=$db->update('production_product',$data,['id'=>$id],'array',$jArray);
            $jArray[fl()]=$id;
            if(!$update){$error=fl(); setMessage(66);}
            else{
                // আগের লগ সব ডিলেট করে দিলাম 
                $delete = $db->delete('product_stock_log',['reference_id'=>$id,'change_type'=>ST_CH_PRODUCTION_SOURCE_MAN]);
                if(!$delete){$error=fl(); setMessage(66);}
                $delete = $db->delete('product_stock_log',['reference_id'=>$id,'change_type'=>ST_CH_PRODUCTION]);
                if(!$delete){$error=fl(); setMessage(66);}

                $delete=$db->runQuery('DELETE FROM `product_stock_log` WHERE reference_id in ('.implode(',',array_keys($production_product)).') and change_type='.ST_CH_PRODUCTION_SOURCE,'array',$jArray);
                if(!$delete){$error=fl(); setMessage(66);}

                $delete=$db->runQuery('DELETE FROM `production_product_source` WHERE id in ('.implode(',',array_keys($production_product)).')','array',$jArray);
                if(!$delete){$error=fl(); setMessage(66);}
                // আগের manufacture এর প্রোডাক্ট যদি টাইপ PRODUCT_TYPE_PACKAGING হয় তার stock ঠিক করে দিলাম 
                if($production['type']==PRODUCT_TYPE_PACKAGING){
                    $nextQty=$manufacture_product['stock']+$production['manufacture_product_quantity'];
                    // যদি সেম হয় তাহলে stock যোগ করে নিচ্ছি
                    if($production['manufacture_product_id']==$manufacture_product_id){
                        $manufacture_product['stock']= $nextQty;
                    }
                    $where=['id'=>$manufacture_product_id];
                    $update=$db->update('products',['stock'=>$nextQty],$where,'array',$jArray);
                    if($update==false){$error=fl();setMessage(66);}
                }
                $general->getIDsFromArray($production_product,'product_id',$product_ids);
                $pr_products = $db->selectAll('products','where id in('.implode(',',$pIDs).') ','id,category_id,stock,unit_cost,sale_price');
                $general->arrayIndexChange($pr_products);
                // আগের quantity দিয়ে stock update 
                foreach($production_product as $sd){
                    if(isset($pr_products[$sd['product_id']])){
                        $p = $product[$sd['product_id']];
                        $p['stock']+=$sd['quantity'];

                        $nextQty= $p['stock']+$sd['quantity'];
                        $where=['id'=>$p['id']];
                        $update=$db->update('products',['stock'=>$nextQty],$where,'array',$jArray);
                        if($update==false){$error=fl();setMessage(66);break;}
                    }
                }

                if(!isset($error)){
                    if($production_type==PRODUCT_TYPE_PACKAGING){
                        $log=$smt->productStockChangeLog($manufacture_product_id,$spend_qty,ST_CH_PRODUCTION_SOURCE_MAN,$id,$date,$jArray);
                        if($log==false){$error=fl();setMessage(66);}
                        else{
                            $update=$smt->update_product_closing_stock($manufacture_product_id,$jArray);
                            if($update==false){$error=fl();setMessage(66);}

                            // $nextQty=$manufacture_product['stock']-$spend_qty;
                            // $where=['id'=>$manufacture_product_id];
                            // $update=$db->update('products',['stock'=>$nextQty],$where,'array',$jArray);
                            // if($update==false){$error=fl();setMessage(66);}
                        }
                    }

                    $log=$smt->productStockChangeLog($target_product_id,$build_quantity,ST_CH_PRODUCTION,$id,$date,$jArray);
                    if($log==false){$error=fl();setMessage(66);}
                    else{
                        $cost=0;
                        if($production_type==PRODUCT_TYPE_PACKAGING){
                            $cost=($manufacture_product['unit_cost']*$spend_qty)+$data['total_cost'];
                        }
                        $jArray[fl()]=$cost;
                        // $nextQty=$target_product['stock'];
                        // if($production_type!=PRODUCT_TYPE_RE_PACKAGING){
                        //     $nextQty+=$build_quantity;
                        // }
                        $unit_cost =round(
                            (
                                ($target_product['stock']*$target_product['unit_cost'])+$cost)
                                /
                                ($target_product['stock']+$build_quantity)
                                ,6); 
                        $jArray[fl()]=$unit_cost;
                        $update=$smt->update_product_closing_stock($target_product_id,$jArray);
                        if($update==false){$error=fl();setMessage(66);}
                        $where=['id'=>$target_product_id];
                        $update=$db->update('products',['unit_cost'=>$unit_cost],$where,'array',$jArray);
                        if($update==false){$error=fl();setMessage(66);}
                        else{
                            foreach($pProduct as $pID=>$data){
                                $p = $smt->productInfoByID($pID,false);
                                $data['production_id'] = $id;
                                $ppsID=$db->insert('production_product_source',$data,true);
                                if(!$ppsID){$error=fl(); setMessage(66);}
                                else{
                                    $log=$smt->productStockChangeLog($pID,$data['quantity'],ST_CH_PRODUCTION_SOURCE,$ppsID,$date,$jArray);
                                    if($log==false){$error=fl();setMessage(66);}
                                    else{
                                        $update=$smt->update_product_closing_stock($pID,$jArray);
                                        if($update==false){$error=fl();setMessage(66);}

                                        // $nextQty=$p['stock'];
                                        // $nextQty-=$data['quantity'];
                                        // $where=['id'=>$pID];
                                        // $update=$db->update('products',['stock'=>$nextQty],$where,'array',$jArray);
                                        // if($update==false){$error=fl();setMessage(66);}
                                    }
                                }

                            }
                        }
                    }
                }
            }
            $ac = false;
            if(!isset($error)){
                $ac=true;
                $jArray['status']=1;
                setMessage(30,'Production');
            }
            $db->transactionStop($ac);
        }
    }
    if(isset($error)){
        $jArray[fl()]=$error;
    }
    $general->createLog('production',$jArray);