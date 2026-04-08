<?php
  $id = intval($_POST['id']);
  $order = $db->get_rowData('purchase_order','id',$id);
  if(empty($order)){$error=fl();setMessage(63,'purchase order');}
  else{
      $order_product = $db->selectAll('purchase_order_details','where purchase_order_id='.$id);
      $product=[];
      $units=$smt->getAllUnit();
      if(!empty($order_product)){
          $product_ids=[];
          foreach($order_product as $od){
               $product_ids[]=$od['product_id'];
          }
          $product = $db->selectAll('products','where id in('.implode(',',$product_ids).')','id,title');
          $general->arrayIndexChange($product);
          
      }
      $gAr['date'] = $general->make_date($order['date'],'st');
      $gAr['units'] = $units;
      $gAr['order'] = $order;
      $gAr['order_product'] = $order_product;
      $gAr['product'] = $product;
      $jArray['html']     = $general->fileToVariable(__DIR__.'/purchase_order_details_view.phtml');
      $jArray['status'] = 1;
      
  }