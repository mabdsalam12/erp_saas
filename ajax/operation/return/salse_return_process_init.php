<?php
    $id = intval($_POST['id']);
    $sale = $db->get_rowData('sale_return','id',$id);
    if(empty($sale)){$error=fl();setMessage(63,'sale return');}
    else{
        $product_data = $db->selectAll('sale_return_details','where sale_return_id='.$id);
        $general->arrayIndexChange($product_data,'product_id');
        $customer = $smt->customerInfoByID($sale['customer_id']);
        $sale['customer'] = $customer['name'].'('.$customer['code'].')';
        $sale['invoice_date'] = $general->make_date($sale['invoice_date'],'st');
        $sale['approved_date'] = $general->make_date($sale['approved_date'],'st');
        $sale['discount'] = $general->numberFormat($sale['discount']);
        $sale['sub_total'] = $general->numberFormat($sale['sub_total']);
        $sale['return_amount'] = $general->numberFormat($sale['return_amount']);
        $product_details=[];
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
            }
        }
        $gAr['sale'] = $sale;
        //$jArray[fl()]=$gAr
        $gAr['product_details'] = $product_details;
        $jArray['html']     = $general->fileToVariable(__DIR__.'/sales_return_process_init.phtml');
        $jArray['status'] = 1;
    }