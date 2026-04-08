<?php
$general->createLog('sale_return_process',$_POST);
    $id = intval($_POST['id']);
    $process_date=strtotime($_POST['process_date']);
    $sale_return_process_data = $_POST['sale_return_process_data']??[];
    $sale_return = $db->get_rowData('sale_return','id',$id);
    $sale_return_process = $db->get_rowData('sale_return_process_data','sale_return_id',$id);
    if(empty($sale_return)){$error=fl();setMessage(63,'sale return');}   
    elseif(!empty($sale_return_process)){$error=fl();setMessage(63,'sale return');}   
    elseif(empty($sale_return_process_data)){$error=fl();setMessage(63,'Product');}   
    else{
        $product_data = $db->selectAll('sale_return_details','where sale_return_id='.$id);
        $general->arrayIndexChange($product_data,'product_id');
        if(count($sale_return_process_data)!=count($product_data)){$error=fl(); setMessage(63,'Product');}
        else{
            $products = $db->selectAll('products','where id in('.implode(',',array_keys($sale_return_process_data)).')','id,stock');
            $general->arrayIndexChange($products);
            $process_data=[];

            $total_good_amount=0;
            $total_damage_amount=0;
            $total_expiry_amount=0;

            foreach($sale_return_process_data as  $product_id=>$d){
                $jArray[fl()][]=$d;
                $d['good'] = intval($d['good']);
                $d['damage'] = intval($d['damage']);
                $d['expiry'] = intval($d['expiry']);
                if(!isset($product_data[$product_id])){$error=fl(); setMessage(63,'Product'); break;}
                $pd = $product_data[$product_id];
                if(($d['good']+$d['damage']+$d['expiry'])!=$pd['quantity']){$error=fl();setMessage(1,'Invalid quantity for '.$d['prodct_title']); break;}
                $data = [
                    'date'          =>$process_date,
                    'sale_return_id'=>$id,
                    'customer_id'   =>$sale_return['customer_id'],
                    'base_id'       =>$sale_return['base_id'],
                    'unit_price'    =>$pd['unit_price'],
                    'product_id'    =>$product_id,
                    'quantity'      =>0,
                    'type'          =>0,
                ];
                if($d['good']>0){
                    $data['quantity']= $d['good'];
                    $data['type']= SALE_RETURN_PROCESS_TYPE_GOOD;
                    $total_good_amount+=$d['good']*$pd['unit_price'];
                    $process_data[]=$data;
                }
                if($d['damage']>0){
                    $data['quantity']= $d['damage'];
                    $total_damage_amount+=$d['damage']*$pd['unit_price'];
                    $data['type']= SALE_RETURN_PROCESS_TYPE_DAMAGE;
                    $process_data[]=$data;
                }
                if($d['expiry']>0){
                    $total_expiry_amount+=$d['expiry']*$pd['unit_price'];
                    $data['quantity']= $d['expiry'];
                    $data['type']= SALE_RETURN_PROCESS_TYPE_EXPIRY;
                    $jArray[fl()][]=[
                        $d,$data
                    ];
                    $process_data[]=$data;
                }
                $jArray[fl()][]=$data;
            }
            if(!isset($error)){
                $db->transactionStart();
                $process_head=$acc->getSystemHead(AH_SALE_RETURN_PROCESS);
                if($process_head==false){$error=fl();setMessage(66);}
                $good_head=$acc->getSystemHead(AH_SALE_RETURN_PROCESS_GOOD);
                if($good_head==false){$error=fl();setMessage(66);}
                $damage_head=$acc->getSystemHead(AH_SALE_RETURN_PROCESS_DAMAGE);
                if($damage_head==false){$error=fl();setMessage(66);}
                $expiry_head=$acc->getSystemHead(AH_SALE_RETURN_PROCESS_EXPIRY);
                if($expiry_head==false){$error=fl();setMessage(66);}
                $jArray[fl()] = $process_data;
                if($total_good_amount>0){
                    $voucher=$acc->voucher_create(V_T_SALE_RETURN_PROCESS,$total_good_amount,$process_head,$good_head,$process_date,'Return Process ID '.$id,$id,0,[],$jArray);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
                if($total_damage_amount>0){
                    $voucher=$acc->voucher_create(V_T_SALE_RETURN_PROCESS,$total_damage_amount,$process_head,$damage_head,$process_date,'Return Process ID '.$id,$id,0,[],$jArray);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
                if($total_expiry_amount>0){
                    $voucher=$acc->voucher_create(V_T_SALE_RETURN_PROCESS,$total_expiry_amount,$process_head,$expiry_head,$process_date,'Return Process ID '.$id,$id,0,[],$jArray);
                    if($voucher==false){$error=fl();setMessage(66);}
                }
                foreach($process_data as $data){
                    if($data['quantity']==0 || $data['type']==0){$error=fl(); setMessage(66); break;}
                    $db->arrayUserInfoAdd($data);
                    $sale_return_process_id = $db->insert('sale_return_process_data',$data,true,'array',$jArray);
                    if(!$sale_return_process_id){$error=fl();setMessage(66);break;}
                    if($data['type']==SALE_RETURN_PROCESS_TYPE_GOOD){
                        $product_id = $data['product_id'];
                        $p = $smt->productInfoByID($product_id,false);
                        $nextQty=$p['stock'];
                        $log=$smt->productStockChangeLog($product_id,$data['quantity'],ST_CH_SALE_RETURN,$sale_return_process_id,$process_date);
                        if($log==false){$error=fl();setMessage(66);break;}
                        $update=$smt->update_product_closing_stock($product_id,$jArray);
                        if($update==false){$error=fl();setMessage(66);break;}
                    }
                }
                $ac=false;
                if(!isset($error)){
                    $ac=true;
                    $jArray['status']=1;
                    setMessage(2,'Sale return process successfully');
                }
                $db->transactionStop($ac);
            }
        }
    }
    $jArray[fl()]=USER_ID;
    $general->createLog('sale_return_process',$jArray);