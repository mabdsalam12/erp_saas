<?php
    $id = intval($_POST['id']);
    $sale = $db->get_rowData('sale_return','id',$id);
    if(empty($sale)){$error=fl();setMessage(63,'sale');}
    else{
        $product_data = $db->selectAll('sale_return_details','where sale_return_id='.$id);
        $process_data = $db->selectAll('sale_return_process_data','where sale_return_id='.$id);
        $product_ids=[];
        foreach($product_data as $p){
            $product_ids[$p['product_id']]=$p['product_id'];
        }
        foreach($process_data as $p){
            $product_ids[$p['product_id']]=$p['product_id'];
        }
        $general->arrayIndexChange($product_data,'product_id');
        $customer               = $smt->customerInfoByID($sale['customer_id']);
        $sale['customer']       = $customer['name'].'('.$customer['code'].')';
        $sale['invoice_date']   = $general->make_date($sale['invoice_date'],'st');
        $sale['approved_date']  = $general->make_date($sale['approved_date'],'st');
        $sale['process_date']  = '';
        $sale['discount']       = $general->numberFormat($sale['discount']);
        $sale['sub_total']      = $general->numberFormat($sale['sub_total']);
        $sale['return_amount']  = $general->numberFormat($sale['return_amount']);
        $product_details=[];
        $process_details=[];
        if(!empty($product_ids)){
            $products = $db->selectAllByID('products','id',$product_ids);
            $unit_ids = [];
            foreach($products as $p){
                $unit_ids[$p['unit_id']] = $p['unit_id'];
            }
        }
        else{
            $products=[];
        }
        if(!empty($process_data)){
            foreach($process_data as $pd){
                $sale['process_date']  = $general->make_date($pd['date'],'st');
                $product_id=$pd['product_id'];
                if(!isset($process_details[$product_id])){
                    $p = $products[$product_id];
                    $process_details[$product_id]=[
                        SALE_RETURN_PROCESS_TYPE_GOOD=>0,
                        SALE_RETURN_PROCESS_TYPE_DAMAGE=>0,
                        SALE_RETURN_PROCESS_TYPE_EXPIRY=>0
                    ];
                }
                $process_details[$product_id][$pd['type']]=$pd['quantity'];
            }
        }
        if(!empty($product_data)){
            $products = $db->selectAllByID('products','id',array_keys($product_data));
            $unit_ids = [];
            foreach($products as $p){
                $unit_ids[$p['unit_id']] = $p['unit_id'];
            }
            $units = $db->selectAllByID('unit','id',$unit_ids);
            foreach($product_data as $product_id=> $pd){
                $p = $products[$product_id];
                $product_details[$product_id]=$pd;
                $product_details[$product_id]['product']=$p['code'].' '.$p['title'];
                $product_details[$product_id]['unit']=$units[$p['unit_id']]['title']??'';
                $product_details[$product_id]['total']=$general->numberFormat($pd['unit_price']*$pd['quantity']);
                $product_details[$product_id]['unitPrice']=$general->numberFormat($pd['unit_price']);
                if(isset($process_details[$product_id])){
                    $product_details[$product_id][SALE_RETURN_PROCESS_TYPE_GOOD]=$process_details[$product_id][SALE_RETURN_PROCESS_TYPE_GOOD];
                    $product_details[$product_id][SALE_RETURN_PROCESS_TYPE_DAMAGE]=$process_details[$product_id][SALE_RETURN_PROCESS_TYPE_DAMAGE];
                    $product_details[$product_id][SALE_RETURN_PROCESS_TYPE_EXPIRY]=$process_details[$product_id][SALE_RETURN_PROCESS_TYPE_EXPIRY];
                }
                else{
                    $product_details[$product_id][SALE_RETURN_PROCESS_TYPE_GOOD]=0;
                    $product_details[$product_id][SALE_RETURN_PROCESS_TYPE_DAMAGE]=0;
                    $product_details[$product_id][SALE_RETURN_PROCESS_TYPE_EXPIRY]=0;
                }
            }
        }
        
        $gAr['sale'] = $sale;
        $jArray[fl()]=$product_details;
        $gAr['product_details'] = $product_details;
        $jArray['html']     = $general->fileToVariable(__DIR__.'/salse_return_details_view.phtml');
        $jArray['status'] = 1;
    }