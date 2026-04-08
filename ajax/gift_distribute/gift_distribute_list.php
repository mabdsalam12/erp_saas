<?php
    $user_id = intval($_POST['user_id']);
    $base_id = intval($_POST['base_id']);
    $product_id = intval($_POST['product_id']);
    $product_type = intval($_POST['product_type']);
    $dRange = $_POST['dRange'];
    if($_POST['list_type']=='item'){
        $list_type='item';
    }
    else if($_POST['list_type']=='base'){
        $list_type='base';
    }
    else{
        $list_type='invoice';
    }
    $general->getFromToFromString($dRange,$from,$to);
    $q=['date between '.$from.' and '.$to];
    if($user_id>0){
        $q[]='user_id='.$user_id;
    }
    if($base_id>0){
        $q[]='base_id='.$base_id;
    }
    if($product_type!=-1){
        $q[]='product_type='.$product_type;
    }
    $list = $db->selectAll('gift_distribute','where '.implode(' and ',$q).' ORDER BY date DESC','','array',$jArray);
    $rData=[];
    $sr=1;
    $total_quantity=0;
    $total_tp=0;
    if(!empty($list)){
        $ids=[];
        $user_ids=[];
        foreach($list as $l){
            $ids[]=$l['id']   ;
            $user_ids[$l['user_id']] = $l['user_id'];
        }
        $gift_product = $db->selectAll('gift_distribute_product','where gift_distribute_id in('.implode(',',$ids).')');
        $product_ids=[];
        $gift_products=[];
        foreach($gift_product as $p){
            $product_ids[$p['product_id']]  = $p['product_id'];
            if(!isset($gift_products[$p['gift_distribute_id']])){
                $gift_products[$p['gift_distribute_id']]=[];
            }
            $gift_products[$p['gift_distribute_id']][]=$p;
        }
        $products = $db->selectAll('products','where id in('.implode(',',$product_ids).')','id,title');
        $general->arrayIndexChange($products,'id');
        $users = $db->selectAll('users','where id in('.implode(',',$user_ids).')','id,name');
        $general->arrayIndexChange($users,'id');
        if($list_type=='base'){
            $base_data=[];
        }
        foreach($list as $l){
            $tp=0;
            $quantity=0;
            $product_data=[];
            $base_title='';
            $base=$smt->base_info_by_id($l['base_id']);
            if(!empty($base)){
                $base_title=$base['title'];
            }
            $invoice_total=0;
            foreach($gift_products[$l['id']] as $p){
                if($product_id>0&&$p['product_id']!=$product_id)continue;
                $pr=$products[$p['product_id']];
                //$product_data[]=$products[$p['product_id']]['title'].'->'.$p['quantity'];
                $amount=$p['quantity']*$p['tp'];
                $total_tp+=$amount;
                $invoice_total+=$amount;
                if($list_type=='item'){
                    $rData[]=[
                        's'=>$sr++,
                        'd'=>date('d/m/Y',$l['date']),
                        'b'=>$base_title,
                        'u'=>$users[$l['user_id']]['name']??'',
                        'i'=>$pr['title'],
                        'q'=>$p['quantity'],
                        't'=>$p['tp'],
                        'a'=>$amount,
                        'o'=>'<button onclick="gift_distribute_details_view('.$l['id'].')" class="btn btn-success">Details</button>',
                        'p'=>'<a href="'.URL.'/?print=gift_distribute&id='.$l['id'].'" target="_blank" class="btn btn-info">Print</a>'
                    ];
                }
                elseif($list_type=='base'){
                    if(!isset($base_data[$l['base_id']])){
                        $base_data[$l['base_id']]=[
                            'id'=>$l['base_id'],
                            'title'=>$base_title,
                            'tp'=>0
                        ];
                    }
                    $base_data[$l['base_id']]['tp']+=$amount;
                }
                $quantity+=$p['quantity'];
            }
            
            
            $total_quantity+=$quantity;
            if($list_type=='invoice'){
                $rData[]=[
                    's'=>$sr++,
                    'd'=>date('d/m/Y',$l['date']),
                    'b'=>$base_title,
                    'u'=>$users[$l['user_id']]['name']??'',
                    //'p'=>implode('<br>',$product_data),
                    'q'=>$quantity,
                    't'=>$invoice_total ,
                    'o'=>'
                    <button onclick="gift_distribute_details_view('.$l['id'].')" class="btn btn-success">Details</button>
                    <button onclick="gift_distribute_remove('.$l['id'].')" class="btn btn-danger">Remove</button>
                    ',
                    'p'=>'<a href="'.URL.'/?print=gift_distribute&id='.$l['id'].'" target="_blank" class="btn btn-info">Print</a>'
                ];     
            }
        }
        
        if($list_type=='base'){
            foreach($base_data as $bd){
                $rData[]=[
                    's'=>$sr++,
                    'b'=>$bd['title'],
                    't'=>$bd['tp']
                ];              }
        }
    }
    $general->arraySortByColumn($rData,'b');
    if($list_type=='invoice'){
        $rData[]=[
            's'=>'',
            'd'=>['t'=>'Total','b'=>1],
            'b'=>['t'=>''],
            'u'=>['t'=>''],
        // 'p'=>['t'=>''],
            'q'=>['t'=>$general->numberFormat($total_quantity,0),'b'=>1],
            't'=>['t'=>$general->numberFormat($total_tp,0),'b'=>1],
            'p'=>['t'=>'','b'=>1],
        ];
    }
    elseif($list_type=='base'){
        $rData[]=[
            's'=>'',
            'b'=>['t'=>'Total','b'=>1],
            't'=>['t'=>$general->numberFormat($total_tp,0),'b'=>1],
        ];
    }
    else{
        $rData[]=[
            's'=>'',
            'd'=>['t'=>'Total','b'=>1],
            'b'=>['t'=>''],
            'u'=>['t'=>''],
        // 'p'=>['t'=>''],
            'q'=>['t'=>$general->numberFormat($total_quantity,0),'b'=>1],
            'a'=>['t'=>$general->numberFormat($total_tp,0),'b'=>1],
            'p'=>['t'=>'','b'=>1],
        ];
    }
    if($list_type=='item'){
        $head=[
            array('title'=>"SL"         ,'key'=>'s','hw'=>5),
                array('title'=>"Date"       ,'key'=>'d'),
                array('title'=>"Base"       ,'key'=>'b'),
                array('title'=>"User"       ,'key'=>'u'),
                array('title'=>"Item"       ,'key'=>'i'),
                array('title'=>"TP"         ,'key'=>'t','al'=>'r'),
                array('title'=>"Quantity"   ,'key'=>'q','al'=>'r'),
                array('title'=>"Total"      ,'key'=>'a','al'=>'r'),
                array('title'=>"Operation"  ,'key'=>'o','al'=>'r'),
                array('title'=>"Print"  ,'key'=>'p','al'=>'r'),
        ];
    }
    elseif($list_type=='base'){
        $head=[
            array('title'=>"SL"         ,'key'=>'s','hw'=>5),
                array('title'=>"Base"   ,'key'=>'b'),
                array('title'=>"TP"     ,'key'=>'t','al'=>'r')
        ];
    }
    else{
        $head=[
            array('title'=>"SL"         ,'key'=>'s','hw'=>5),
                array('title'=>"Date"       ,'key'=>'d'),
                array('title'=>"Base"       ,'key'=>'b'),
                array('title'=>"User"       ,'key'=>'u'),
                array('title'=>"TP"         ,'key'=>'t','al'=>'r'),
                array('title'=>"Quantity"  ,'key'=>'q','al'=>'r'),
                array('title'=>"Operation"  ,'key'=>'o','al'=>'r'),
                array('title'=>"Print"  ,'key'=>'p','al'=>'r'),
        ];
    }
    
    $fileName='gift_distribute_list'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'gift_distribute_list',
        'title'     => 'Gift distribute List',
        'info'      => [],
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;