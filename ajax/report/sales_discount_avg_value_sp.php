<?php
    $dRange = $_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $rData=[];
    $serial=1;
    $products=$db->selectAll('products','where isActive=1 and type='.PRODUCT_TYPE_FINISHED.' order by code asc');

    $q=[];
    $q[]="date between $from and $to";
    $sales=$db->selectAll('sale','where '.implode(' and ',$q),'id','array',$jArray);
    $ids=[];
    foreach($sales as $s){
        $ids[$s['id']]=$s['id'];
    }
    $sale_products=[];
    if(!empty($ids)){
        $sale_products = $db->selectAll('sale_products','where sale_id in('.implode(',',$ids).')','product_id,sale_qty');
    }
    $sale_return = $db->selectAll('sale_return_process_data','where '.implode(' and ',$q),'id','array',$jArray);
    $return_details=[];
    if(!empty($sale_return)){
        $ids=[];
        $general->getIDsFromArray($sale_return,'id',$ids);
        $return_products_details=$db->selectAll('sale_return_process_data','where sale_return_id in('.implode(',',$ids).')','product_id,quantity,type');
        foreach($return_products_details as $rpd){
            if($rpd['type']==SALE_RETURN_PROCESS_TYPE_GOOD){
                continue;
            }
            if(!isset($return_details[$rpd['product_id']])){
                $return_details[$rpd['product_id']]=[
                    SALE_RETURN_PROCESS_TYPE_DAMAGE=>0,
                    SALE_RETURN_PROCESS_TYPE_EXPIRY=>0
                ];
            }
            $return_details[$rpd['product_id']][$rpd['type']]+=$rpd['quantity'];
        }

    }
    


    $product_sales=[];
    $product_returns=[];
    foreach($sale_products as $sp){
        if(!isset($product_sales[$sp['product_id']])){
            $product_sales[$sp['product_id']]=0;
        }
        $product_sales[$sp['product_id']]+=$sp['sale_qty'];
    }


    $q=[];
    $q[]="approved_date between $from and $to";
    $returns=$db->selectAll('sale_return','where '.implode(' and ',$q),'id','array',$jArray);
    $ids=[];
    foreach($returns as $s){
        $ids[$s['id']]=$s['id'];
    }
    $sale_products=[];
    if(!empty($ids)){
        $sale_products = $db->selectAll('sale_return_details','where sale_return_id in('.implode(',',$ids).')','product_id,quantity');
    }
    
    foreach($sale_products as $sp){
        if(!isset($product_returns[$sp['product_id']])){
            $product_returns[$sp['product_id']]=0;
        }
        $product_returns[$sp['product_id']]+=$sp['quantity'];
    }
    $total_sale=0;
    $total_return=0;
    $total_damage=0;
    $total_expiry=0;
    $total_total=0;
    if($products){
        foreach($products as $p){
            $sale_quantity=$product_sales[$p['id']]??0;
            $return_quantity=$product_returns[$p['id']]??0;
            $return_percent=0;
            $damage_percent=0;
            $expiry_percent=0;
            $total_percent=0;
            $return_damage= $return_details[$p['id']][SALE_RETURN_PROCESS_TYPE_DAMAGE]??0;
            $return_expiry= $return_details[$p['id']][SALE_RETURN_PROCESS_TYPE_EXPIRY]??0;
            $total = $sale_quantity-$return_quantity;
            if($sale_quantity>0){
                if($return_quantity>0){
                    $return_percent=round($general->percentageOf($sale_quantity,$return_quantity),2);
                }
                if($return_damage>0){
                    $damage_percent=round($general->percentageOf($sale_quantity,$return_damage),2);
                }
                if($return_expiry>0){
                    $expiry_percent=round($general->percentageOf($sale_quantity,$return_expiry),2);
                }
                if($total>0){
                    $total_percent=round($general->percentageOf($sale_quantity,$total),2);
                }
            }
            $total_sale+=$sale_quantity;
            $total_return+=$return_quantity;
            $total_damage+=$return_damage;
            $total_expiry+=$return_expiry;
            $total_total+=$total;
            $data=[
                's'=>$serial++,
                'c'=>$p['code'],
                'p'=>$p['title'],
                'sq'=>$sale_quantity,
                'rq'=>$return_quantity,
                'prq'=>$return_percent,
                'dq'=>$return_damage,
                'pdq'=>$damage_percent,
                'eq'=>$return_expiry,
                'qeq'=>$expiry_percent,
                't'=>$total,
                'pt'=>$total_percent
            ];
            $rData[]=$data;
        }
    }


    $rData[]=[
        's'=>['t'=>''],
        'p'=>['t'=>'Total','b'=>1],
        'sq'=>['t'=>$total_sale,'al'=>'r','b'=>1],
        'rq'=>['t'=>$total_return,'al'=>'r','b'=>1],
        'prq'=>['t'=>''],
        'dq'=>['t'=>$total_damage,'al'=>'r','b'=>1],
        'pdq'=>['t'=>''],
        'eq'=>['t'=>$total_expiry,'al'=>'r','b'=>1],
        'peq'=>['t'=>''],
        't'=>['t'=>$total_total,'al'=>'r','b'=>1],
        'pt'=>['t'=>''],
    ];


    $head=[
        ['title'=>'SL'                  ,'key'=>'s','hw'=>5],
        ['title'=>'Code'                ,'key'=>'c'],
        ['title'=>'Name of product'     ,'key'=>'p'],
        ['title'=>'Sale quantity'       ,'key'=>'sq','al'=>'r'],
        ['title'=>'Return quantity'     ,'key'=>'rq','al'=>'r'],
        ['title'=>'%Return quantity'    ,'key'=>'prq','al'=>'r'],
        ['title'=>'Damage quantity'     ,'key'=>'dq','al'=>'r'],
        ['title'=>'%Damage quantity'    ,'key'=>'pdq','al'=>'r'],
        ['title'=>'Expiry quantity'     ,'key'=>'eq','al'=>'r'],
        ['title'=>'%Expiry quantity'    ,'key'=>'qeq','al'=>'r'],
        ['title'=>'Total'               ,'key'=>'t','al'=>'r'],
        ['title'=>'%Total'              ,'key'=>'pt','al'=>'r'],
    ];
    $fileName='sales_discount_avg_value_sp'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'sales_discount_avg_value_sp',
        'title'     => 'Sales discount & Avg Sales Value (SP) ',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;