<?php
    $id = intval($_POST['id']);
    $rq = $db->get_rowData('purchase_requisition','id',$id);
    if(empty($rq)){$error=fl();setMessage(63,'purchase requisition');}
    else{
        $product_data = $db->selectAll('purchase_requisition_details','where purchase_requisition_id='.$id);
        $general->arrayIndexChange($product_data,'product_id');
        
        $rq['date'] = $general->make_date($rq['date'],'st');
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
            }
        }
        $rq['user']=$db->userInfoByID($rq['createdBy']);
        $gAr['rq'] = $rq;
        $gAr['product_details'] = $product_details;
        $jArray['html']     = $general->fileToVariable(__DIR__.'/purchase_requisition_details_view.phtml');
        $jArray['status'] = 1;
    }