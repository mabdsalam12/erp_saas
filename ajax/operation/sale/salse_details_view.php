<?php
    $id = intval($_POST['id']);
    $sale = $db->get_rowData('sale','id',$id);
    if(empty($sale)){$error=fl();setMessage(63,'sale');}
    else{
        $product_data = $db->selectAll('sale_products','where sale_id='.$id);
        $jArray[fl()]=$product_data;
        $general->arrayIndexChange($product_data,'product_id');
        $customer = $smt->customerInfoByID($sale['customer_id']);
        $pay_type=$db->get_rowData('pay_types','id',$sale['pay_type']);
        $sale['customer'] = $customer['name'].'('.$customer['code'].')';
        $sale['date'] = $general->make_date($sale['date'],'st');
        $sale['collection_date'] = $general->make_date($sale['collection_date'],'st');
        $sale['pay_type_name']=$pay_type['name'];
        $prodcut_datails=[];
        if(!empty($product_data)){
            $prodcuts = $db->selectAllByID('products','id',array_keys($product_data));
            $unit_ids = [];
            foreach($prodcuts as $p){
                $unit_ids[$p['unit_id']] = $p['unit_id'];
            }
            $units = $db->selectAllByID('unit','id',$unit_ids);
            foreach($product_data as $product_id=> $pd){
                $p = $prodcuts[$product_id];
                $prodcut_datails[$product_id]=$pd;
                $prodcut_datails[$product_id]['product']=$p['code'].' '.$p['title'];
                $prodcut_datails[$product_id]['unit']=$units[$p['unit_id']]['title']??'';
            }
        }
        $gAr['sale'] = $sale;
        $gAr['sale_data']=$general->getJsonFromString($sale['data']);
        //$jArray[fl()]=$gAr
        $gAr['product_details'] = $prodcut_datails;
        $jArray['html']     = $general->fileToVariable(__DIR__.'/salse_details_view.phtml');
        $jArray['status'] = 1;
    }