<?php
$general->createLog('production_delete',$_POST);
$id = intval($_POST['id']);
$production = $db->get_rowData('production_product','id',$id);
$jArray[fl()]=$production;
if(empty($production)){$error=fl();setMessage(63,'production');}
else{
    if($production['type']==PRODUCT_TYPE_MANUFACTURING){
        $can_delete=$db->permission(7);
    }
    else{
        $can_delete=$db->permission(8);
    }
    if($can_delete==false){
        $error=fl();setMessage(1,'You can not delete production');
    }
}
if(!isset($error)){
    $production_product_source = $db->selectAll('production_product_source',"where production_id=$id");
    $jArray[fl()]=$production_product_source;
    $product_ids=[];
    foreach($production_product_source as $sp){
        $product_ids[] = $sp['product_id'];
    }


    $products = $db->selectAll('products','where id in('.implode(',',$product_ids).')','id,stock,unit_cost');
    $jArray[fl()]=$products;
    $general->arrayIndexChange($products);
    $db->transactionStart();
    foreach($production_product_source as $sp){
        if(!isset($products[$sp['product_id']])){$error=fl();setMessage(66);break;}
        $p = $products[$sp['product_id']]; 
        $nextQty=$p['stock'];
        $nextQty+=$sp['quantity'];
        $where=['id'=>$p['id']];
        $update=$db->update('products',['stock'=>$nextQty],$where,'array',$jArray);
        if($update==false){$error=fl();setMessage(66);break;}
        $delete=$db->runQuery('delete from product_stock_log where reference_id ='.$sp['id'].' and change_type='.ST_CH_PRODUCTION_SOURCE,'array',$jArray);
        if(!$delete){$error=fl();setMessage(66);break;}
        
    }
    if(!isset($error)){
        $p=$smt->productInfoByID($production['product_id'],false);
        $nextQty=$p['stock']-($production['yield']-$production['retention_sample']);
        $where=['id'=>$p['id']];
        $update=$db->update('products',['stock'=>$nextQty],$where,'array',$jArray);
        if($update==false){$error=fl();setMessage(66);}
        $delete=$db->runQuery('delete from product_stock_log where reference_id ='.$id.' and change_type='.ST_CH_PRODUCTION,'array',$jArray);
        if(!$delete){$error=fl();setMessage(66);}
    }
    if(!isset($error)){
        if($production['manufacture_product_id']>0){
            $p=$smt->productInfoByID($production['manufacture_product_id'],false);
            $nextQty=$p['stock']+$production['manufacture_product_quantity'];
            $where=['id'=>$p['id']];
            $update=$db->update('products',['stock'=>$nextQty],$where,'array',$jArray);
            if($update==false){$error=fl();setMessage(66);}
            $delete=$db->runQuery('delete from product_stock_log where reference_id ='.$production['id'].' and change_type='.ST_CH_PRODUCTION_SOURCE_MAN,'array',$jArray);
            if(!$delete){$error=fl();setMessage(66);}
        }
    }
    if(!isset($error)){
        $delete=$db->delete('production_product_source',['production_id'=>$production['id']]);
        if(!$delete){$error=fl();setMessage(66);}
        $delete=$db->delete('production_product',['id'=>$production['id']]);
        if(!$delete){$error=fl();setMessage(66);}
    }
    if(!isset($error)){
        $ac=true;
        $jArray['status']=1;
        setMessage(2,'Delete successfully');
    }
    else{
        $ac=false;
    }
    $jArray[fl()]=$ac;
    $db->transactionStop($ac);

}
$general->createLog('production_delete',$jArray);