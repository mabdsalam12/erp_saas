<?php
    $id = intval($_POST['id']);
    $gift = $db->get_rowData('gift_distribute','id',$id);
    if(!empty($gift)){
        $gift_product = $db->selectAll('gift_distribute_product','where gift_distribute_id ='.$id);
        $general->arrayIndexChange($gift_product,'product_id');
        $prodcuts = $db->getProductData(' and id in('.implode(',',array_keys($gift_product)).')');
        $gift['user']=$db->userInfoByID($gift['user_id'])['name']??'';
        $gift['base']=$smt->base_info_by_id($gift['base_id'])['title']??'';
        $gift['date']=$general->make_date($gift['date']);
        $tTotal = 0 ;
        $total_quantity = 0 ;
        foreach($gift_product as $product_id=>$gp){  
            $p = $prodcuts[$product_id];
            $gift_product[$product_id]['tp']=$general->numberFormat($gp['tp']);
            $gift_product[$product_id]['product']=$p['t']??'';
            $gift_product[$product_id]['unit']=$p['u']??'';
            $total = $gp['tp']*$gp['quantity'];
            $gift_product[$product_id]['total']=$general->numberFormat($total);
            $tTotal+= $total;
            $total_quantity+= $gp['quantity'];
        }
        $gift['tTotal'] = $general->numberFormat($tTotal);
        $gift['total_quantity'] = $total_quantity;
        $gAr['gift']=$gift;
        $gAr['gift_product']=$gift_product;
        $jArray['html']     = $general->fileToVariable(__DIR__.'/gift_distribute_details_view.phtml');
        $jArray['status'] = 1;
    }
