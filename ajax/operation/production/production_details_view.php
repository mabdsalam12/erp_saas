<?php
    $id = intval($_POST['id']);
    $production = $db->get_rowData('production_product','id',$id);
    if(empty($production)){$error=fl();setMessage(63,'production');}
    else{

        if($production['type']==PRODUCT_TYPE_MANUFACTURING){
            $can_show_price=$db->permission(5);
            $can_delete=$db->permission(7);
        }
        else{
            $can_show_price=$db->permission(6);
            $can_delete=$db->permission(8);
        }

        $production_product = $db->selectAll('production_product_source','where production_id='.$id);
        $product_ids=[
            $production['product_id']=>$production['product_id'],
            $production['manufacture_product_id']=>$production['manufacture_product_id'],
        ];
        if(!empty($production_product)){
            foreach($production_product as $pp){
                $product_ids[$pp['product_id']] = $pp['product_id'];
            }
        }
        $products = $db->getProductData('and id in('.implode(',',$product_ids).')');
        $product_details=[];
        if(!empty($production_product)){
            foreach($production_product as $pp){
                $pp['product']= $products[$pp['product_id']]['t']??'';
                $pp['unit']= $products[$pp['product_id']]['u']??'';
                $pp['unit_cost']= $general->numberFormat($pp['unit_cost']);
                $pp['extra_cost']= $general->numberFormat($pp['extra_cost']);
                $pp['total_cost']= $general->numberFormat($pp['total_cost']);
                
                $product_details[]=$pp;
            }
        }
        $gAr['production_unit_cost'] = round($production['total_cost']/($production['yield']-$production['retention_sample']),2);
        $production['product_cost']=$general->numberFormat($production['product_cost']);
        $production['extra_cost']=$general->numberFormat($production['extra_cost']);
        $production['total_cost']=$general->numberFormat($production['total_cost']);
        $production['date']=$general->make_date($production['date']);
        $production['entry']=$general->make_date($production['createdOn'],'time');
        $production['type']=($production['type']==PRODUCT_TYPE_PACKAGING)?'Packaging':'Manufacture';
        $production['product']=$products[$production['product_id']]['t']??''; 
        $production['unit']=$products[$production['product_id']]['u']??''; 
        
        $production['manufacture_product']=$products[$production['manufacture_product_id']]['t']??''; 
        $production['manufacture_unit']=$products[$production['manufacture_product_id']]['u']??''; 
        $gAr['production'] = $production;
        
        $gAr['can_show_price'] = $can_show_price;
        $jArray[fl()]=$production;
        $gAr['product_details'] = $product_details;
        $gAr['can_delete'] = $can_delete;
        $jArray['html']     = $general->fileToVariable(__DIR__.'/production_details_view.phtml');
        $jArray['status'] = 1;
    }
