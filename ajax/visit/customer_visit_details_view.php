<?php
    $id = intval($_POST['id']);
    $vl = $db->get_rowData('customer_visit','id',$id);
    if(empty($vl)){$error=fl();setMessage(63,'customer visit');}
    else{
        $customer  = $db->get_rowData('customer','id',$vl['customer_id']);
        $finished_product = $db->selectAll('customer_visit_finished_product','where customer_visit_id='.$id);
        $product_ids=[];
        if(!empty($finished_product)){
            foreach($finished_product as $fp){
                $product_ids[$fp['product_id']]=$fp['product_id'];
            }
        }
        $gift_product = $db->selectAll('customer_visit_gift_product','where customer_visit_id='.$id);
        if(!empty($gift_product)){
            foreach($gift_product as $gp){
                $product_ids[$gp['product_id']]=$gp['product_id'];
            }
        }
        $products=[];
        $units=[];
        if(!empty($products)){
            $products = $db->selectAll('products','where id in('.implode(',',$product_ids).')','id,title,code,unit_id');
            $unit_ids=[];
            foreach($products as $p){
                $unit_ids[$p['unit_id']] = $p['unit_id'];
            }
            $units = $db->selectAll('unit','where id in('.implode(',',$unit_ids).')');
        }
        $vl['entry_time'] = $general->make_date($vl['entry_time'],'time');
        $vl['note'] = $general->content_show($vl['note']);
        $gAr['vl'] = $vl;
        $gAr['customer'] = $customer;
        $gAr['finished_product'] = $finished_product;
        $gAr['gift_product'] = $gift_product;
        $gAr['products'] = $products;
        $gAr['units'] = $units;
        $jArray['html']     = $general->fileToVariable(__DIR__.'/customer_visit_details_view.phtml');
        $jArray['status'] = 1;
    }

