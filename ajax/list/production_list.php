<?php
    $dRange = $_POST['dRange'];
    $type = intval($_POST['type']);
    $product_id = intval($_POST['product_id']);
    $general->getFromToFromString($dRange,$from,$to);
    $q=['date between '.$from.' and '.$to];
    if($type>0){
        if($type==PRODUCT_TYPE_PACKAGING){
            $q[] = "type in(".PRODUCT_TYPE_PACKAGING.",".PRODUCT_TYPE_RE_PACKAGING.")";
        }
        else{
            $q[]="type=$type";
        }
    }
    if($product_id>0){
        $q[]="product_id=$product_id";
    }
    if($type==PRODUCT_TYPE_MANUFACTURING){
        $can_show_price=$db->permission(5);
    }
    else{
        $can_show_price=$db->permission(6);
    }
    $production = $db->selectAll('production_product','where '.implode(' and ',$q));
    $rData=[];
    $sr=1;
    $total=0;
    $total_target_quantity=0;
    $total_yield_quantity=0;
    if(!empty($production)){
        $product_ids=[];
        foreach($production as $pr){
            $product_ids[$pr['product_id']] = $pr['product_id'];
            $product_ids[$pr['manufacture_product_id']] = $pr['manufacture_product_id'];
        }
        $products = $db->selectAll('products','where id in('.implode(',',$product_ids).')','id,title');
        $general->arrayIndexChange($products);
        $module = 'manufacture';
        if($pr['type']==PRODUCT_TYPE_PACKAGING){
             $module = 'packaging';
        }
        else if($pr['type']==PRODUCT_TYPE_RE_PACKAGING){
             $module = 're-packaging';
        }
        foreach($production as $pr){
            $total+=$pr['total_cost'];
            $total_target_quantity+=$pr['quantity'];
            $total_yield_quantity+=$pr['yield'];
            $rData[]=[
                's'=>$sr++,
                'd'=>$general->make_date($pr['date']),
                'e'=>$general->make_date($pr['createdOn']),
                'bc'=>$pr['batch_no'],
                't'=>($pr['type']==PRODUCT_TYPE_PACKAGING)?'Packaging':'Manufacture',
                'tp'=>$products[$pr['product_id']]['title']??'',
                'mp'=>$products[$pr['manufacture_product_id']]['title']??'',
                'mpq'=>$pr['manufacture_product_quantity'],
                'y'=>$pr['yield'],
                'tq'=>$pr['quantity'],
                'tc'=>$general->numberFormat($pr['total_cost']),
                'a'=>'<button onclick="production_details_view('.$pr['id'].')" class="btn btn-success">Details</button><a href="'.URL.'?mdl='.$module.'&edit='.$pr['id'].'" class="btn btn-info">Edit</a>',
            ];
        }
    }
    if($can_show_price){
        $rData[]= [
            's'=>['t'=>''],
            'd'=>['t'=>'Total','col'=>6,'b'=>1],
            'bc'=>false,
            't'=>false,
            'tp'=>false,
            'mp'=>false,
            'mpq'=>false,
            'e'=>false,
            'tq'=>['t'=>$general->numberFormat($total_target_quantity,0),'b'=>1],
            'y'=>['t'=>$general->numberFormat($total_yield_quantity,0),'b'=>1],
            'tc'=>['t'=>$general->numberFormat($total),'b'=>1],
            'a'=>['t'=>''],
        ];
    }
    $fileName='production_list'.TIME.rand(0,999).'.txt';
    $head=[
        array('title'=>"#"                  ,'key'=>'s','hw'=>5),
        array('title'=>"Date"               ,'key'=>'d'),
        array('title'=>"Entry date"       ,'key'=>'e'),
        array('title'=>"Batch"              ,'key'=>'bc'),
        array('title'=>"Type"               ,'key'=>'t'),
        array('title'=>"Target Product"     ,'key'=>'tp'),
        array('title'=>"Manufacturing product"  ,'key'=>'mp'),
        array('title'=>"Target Quantity"    ,'key'=>'tq','al'=>'r'), 
        array('title'=>"Yield"              ,'key'=>'y','al'=>'r'),
    ];
    if($can_show_price==1){
        $head[]=['title'=>"Net cost"           ,'key'=>'tc' ,'al'=>'r'];
    }
    $head[]=['title'=>"Action"            ,'key'=>'a'];
    $report_data=[
        'name'      => 'production_list',
        'title'     => 'Production list',
        'fileName'  => $fileName,
        'head'      =>$head,
        'data'      =>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
