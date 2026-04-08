<?php
    $dRange=$_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $base_id  = intval($_POST['base_id']);
    $type=$_POST['type']=='a'?'a':'q';
    $base=[];
    
    $bq=[];
    $q=[];
    if($base_id>0){
        $q[]="base_id=$base_id";
        $bq[]="id=$base_id";
        $base=$smt->base_info_by_id($base_id);
        if($base){
            $reportInfo[]="Base: $base[title]";
        }
    }
    $sq='';
    if(!empty($bq)){
        $sq="where ".implode(" and ",$bq);
    }
    $base = $db->selectAll('base',"$sq order by title asc",'id,title');


    $q[]="date between $from and $to";
    $q[]='product_type=0';

    $sales=$db->selectAll('sale','where '.implode(' and ',$q).' order by date asc','','array',$jArray);
    $general->arrayIndexChange($sales,'id');
    $tSubTotal=0;
    $tDiscount=0;
    $tTotal=0;
    $rData=[];
    if(!empty($sales)){
        $sIDs=[];
        $base_ids=[];
        $sale_ids=[];
        foreach($sales as $s){
            $sale_ids[$s['id']]=$s['id'];
            $cIDs[$s['customer_id']]=$s['customer_id'];
            $base_ids[$s['base_id']]=intval($s['base_id']);
        }
        
        $sale_products = $db->selectAll('sale_products','where sale_id in('.implode(',',$sale_ids).')','','array',$jArray);
        $sale_wise_products=[];
        $sale_details=[];
        $product_ids=[];
        foreach($sale_products as $sp){
            $s=$sales[$sp['sale_id']];
            $product_ids[]=$sp['product_id'];
            if(!isset($sale_wise_products[$sp['product_id']])){
                $sale_wise_products[$sp['product_id']]=[];
            }
            if(!isset($sale_wise_products[$sp['product_id']][$s['base_id']])){
                $sale_wise_products[$sp['product_id']][$s['base_id']]=0;
            }

            $column_name=$type=='a'?'total':'sale_qty';

            $sale_wise_products[$sp['product_id']][$s['base_id']]+=$sp[$column_name];
            //$sale_wise_products[$sp['product_id']][$s['base_id']]+=$sp[$column_name];
        }
        $products=$db->selectAll('products','where id in('.implode(',',$product_ids).')');
        $general->arrayIndexChange($products);

        $serial=1;
        $base_total=[];
        foreach($sale_wise_products as $product_id=>$sp){
            $p=$products[$product_id];
            $data=[
                's'=>$serial++,
                'p'=>$p['title']
            ];
            $total=0;
            foreach($base as $b){
                if(isset($sp[$b['id']])){
                    $total+=$sp[$b['id']];
                    $data['b'.$b['id']]=$sp[$b['id']];
                    if(!isset($base_total[$b['id']])){
                        $base_total[$b['id']]=0;
                    }
                    $base_total[$b['id']]+=$sp[$b['id']];
                }
                else{
                    $data['b'.$b['id']]=0;
                }
            }
            $data['t']=$total;
            $rData[]=$data;
        }
        $data=[
            's'=>'',
            'p'=>['t'=>'Total','b'=>1],
        ];
        $total=0;
        foreach($base as $b){
            if(isset($base_total[$b['id']])){
                $data['b'.$b['id']]=['t'=>$base_total[$b['id']],'b'=>1];
                $total+=$base_total[$b['id']];
            }
            else{
                $data['b'.$b['id']]=0;
            }
        }
        $data['t']=$total;
        $rData[]=$data;
    }
    $head=[
        ['title'=>'SL','key'=>'s','hw'=>5],
        ['title'=>'Product','key'=>'p'],
    ];
    foreach($base as $b){
        $head[]=['title'=>$b['title'],'key'=>'b'.$b['id'],'al'=>'r'];
    }
    $head[]=['title'=>'Total','key'=>'t','al'=>'r'];

    $fileName='item_wise_sale_'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'saleReport',
        'title'     => 'Sale List',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
