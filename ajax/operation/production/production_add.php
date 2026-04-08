<?php      
    $general->createLog('production',$_POST);
    $target_product_id  = intval($_POST['target_product_id']);
    $date               = strtotime($_POST['date']);
    $batch              = $_POST['batch'];
    $pmo_no              = $_POST['pmo_no']??'';
    $targetQuantity     = intval($_POST['targetQuantity']);
    $yield              = intval($_POST['yield']);
    $retention_sample   = intval($_POST['retention_sample']);
    $extraCost          = floatval($_POST['extraCost']);
    $products           = $_POST['products'];
    $note               = $_POST['note'];
    $production_type    = intval($_POST['production_type']);   

    if($production_type==PRODUCT_TYPE_PACKAGING){
        $manufacture_product_id  = intval($_POST['manufacture_product_id']);
        $spend_qty          = intval($_POST['spend_qty']);
        $manufacture_product= $smt->productInfoByID($manufacture_product_id);
    }
    $target_product = $db->get_rowData('products','id',$target_product_id);
    if(empty($target_product)){$error=fl();setMessage(36,'Targeted Product');}
    elseif($date<strtotime('-2 years')){$error=fl();setMessage(63,'date');}
    else if(empty($batch)){$error=fl();setMessage(36,'batch Product');}
    else if($targetQuantity<1){$error=fl();setMessage(63,'Targeted Quantity');}
    else if(!in_array($production_type,[PRODUCT_TYPE_PACKAGING,PRODUCT_TYPE_MANUFACTURING,PRODUCT_TYPE_RE_PACKAGING])){$error=fl();setMessage(63,'request');}
    else if($yield<1){$error=fl();setMessage(63,'yield Quantity');}
    else if(empty($products)){$error=fl();setMessage(63,'Products');}
    else if($extraCost<0){$error=fl();setMessage(63,'Extra cost');}
    else if($retention_sample>$yield){$error=fl();setMessage(63,'Retention sample');}


    if(!isset($error)){
        $source_type=PRODUCT_TYPE_RAW;
        if($production_type==PRODUCT_TYPE_PACKAGING ){
            $source_type=PRODUCT_TYPE_PACKAGING;
            if(empty($manufacture_product)){$error=fl();setMessage(36,'manufacture product');}
            else if($manufacture_product['stock']<$spend_qty){$error=fl();set_message($manufacture_product['title'].' do not have enough stock');}
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
        $product=$db->selectAll('products','where id in('.implode(',',$pIDs).') and type='.$source_type,'id,category_id,stock,unit_cost,sale_price');
        $general->arrayIndexChange($product,'id');
        foreach($products as $pID=>$d){
            $p = @$product[$pID];
            if(!array_key_exists($pID,$pProduct) && !isset($error)){
                if(!isset($product[$pID])){$error=fl();setMessage(1,'Some of invalid item select.');}
                else{
                    if($d['qty']<=0){$error=fl();setMessage(63,'Quantity for '.$p['title']);}
                    if($d['extra_cost']<0){$error=fl();setMessage(63,'extra cost for '.$p['title']);}
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
            if($production_type==PRODUCT_TYPE_PACKAGING){
                $total+=$manufacture_product['unit_cost']*$spend_qty;
            }

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
                'type'              => $production_type
            ];
            if($production_type==PRODUCT_TYPE_PACKAGING){
                $data['manufacture_product_id']         = $manufacture_product_id;
                $data['manufacture_product_quantity']   = $spend_qty;
                $data['manufacture_product_cost']        = $manufacture_product['unit_cost']*$spend_qty;
            }
            $build_quantity=$data['yield']-$data['retention_sample'];
            $jArray[fl()]=$data;
            $db->arrayUserInfoAdd($data);
            $id=$db->insert('production_product',$data,true,'array',$jArray);
            $jArray[fl()]=$id;
            if(!$id){$error=fl(); setMessage(66);}
            else{
                if(PROJECT=='project1'){
                    $batch_no = $batch;
                }
                else{
                    $batch_no = $db->setAutoCode('production',$id,$batch);
                }
                
                $update = $db->update('production_product',['batch_no'=>$batch_no],['id'=>$id],'array',$jArray);
                if(!$update){$error=fl();setMessage(66);}
                else{
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
                }
                $log=$smt->productStockChangeLog($target_product_id,$build_quantity,ST_CH_PRODUCTION,$id,$date,$jArray);
                if($log==false){$error=fl();setMessage(66);}
                else{
                    $update=$smt->update_product_closing_stock($target_product_id,$jArray);
                    if($update==false){$error=fl();setMessage(66);}
                    $cost=0;
                    if($production_type==PRODUCT_TYPE_PACKAGING){
                        $cost=($manufacture_product['unit_cost']*$spend_qty)+$data['total_cost'];
                    }
                    else{
                        $cost=$data['total_cost'];
                    }
                    $jArray[fl()]=$cost;
                    // $nextQty=$target_product['stock']+$build_quantity;
                    

                    $old_unit_cost=0;
                    if($target_product['stock']>0&&$target_product['unit_cost']>0){
                        $old_unit_cost=$target_product['stock']*$target_product['unit_cost'];
                    }
                    $old_total_cost=$old_unit_cost+$cost;
                    $new_total_stock = $target_product['stock'];
                    if($production_type!=PRODUCT_TYPE_RE_PACKAGING){
                        $new_total_stock=$target_product['stock']+$build_quantity;
                    }
                    $unit_cost=0;
                        $jArray[fl()]=$old_total_cost;
                        $jArray[fl()]=$new_total_stock;
                    if($old_total_cost>0&&$new_total_stock>0){
                        $unit_cost =round(($old_total_cost/$new_total_stock),6); 
                    }
                    else if($cost>0){
                        $jArray[fl()]=$cost;
                        $unit_cost=$cost/$build_quantity;
                    }
                    
                    $where=['id'=>$target_product_id];
                    $jArray[fl()]=$unit_cost;
                    $product_price_log = $db->product_price_log($target_product_id,['sale_price'=>$target_product['sale_price'],'unit_cost'=>$unit_cost],$jArray);
                    if($product_price_log==false){$error=fl();setMessage(66);}

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
            $ac = false;
            if(!isset($error)){
                $ac=true;
                $jArray['status']=1;
                setMessage(29,'Production');
            }
            $db->transactionStop($ac);
        }
    }
    if(isset($error)){
        $jArray[fl()]=$error;
    }
    $general->createLog('production',$jArray);