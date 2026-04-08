<?php
    $dRange = $_POST['dRange'];
    $general->getFromToFromString($dRange,$from,$to);
    $reportInfo=['Date: '.$dRange];
    $can_see_unit_cost=$db->permission(9);
    $product_id = intval($_POST['product_id']);
    $type = intval($_POST['type']);
    $stock_in_out_type=$_POST['stock_in_out_type']=='stock_entry'?'stock_entry':'reject_entry';
    $q=["date between $from and $to"];
    if($product_id>0){
        $q[]="product_id=$product_id";
    }
    if($type>-1){
        $products=$db->selectAll('products','where type='.$type,'id');
        if(!empty($products)){
            $product_ids=[];
            foreach($products as $p){
                $product_ids[$p['id']]=$p['id'];
            }
            $q[] = 'product_id IN ('.implode(',', $product_ids).')';
        } else {
            $product_ids = [];
        }
    }
    if($stock_in_out_type=='stock_entry'){
        $reject_list = $db->selectAll('products_stock_in','where '.implode(' and ',$q).' order by date desc','','array',$jArray);
    }
    else{
        $reject_list = $db->selectAll('reject_products','where '.implode(' and ',$q).' order by date desc','','array',$jArray);
    }
    
    $rData=[];
    $serial=1;
    if(!empty($reject_list)){
        $units=$smt->getAllUnit();
        $product_ids=[];
        $user_ids=[];
        foreach($reject_list as $r){
            $user_ids[$r['createdBy']]=$r['createdBy'];
            $product_ids[$r['product_id']]=$r['product_id'];
        }
        $products=$db->selectAllByID('products','id',$product_ids);
        $users=$db->selectAllByID('users','id',$user_ids);
        $total_amount=0;
        $total_quantity=0;
        foreach($reject_list as $r){
            $total_amount+=$r['total'];
            $total_quantity+=$r['quantity'];
            $p=$products[$r['product_id']];
            $u=$users[$r['createdBy']];
            $rData[]=[
                's'=>$serial++,
                'c'=>$r['code'],
                'p'=>$p['title'],
                'u'=>$units[$p['unit_id']]['title'],
                'a'=>$u['username'],
                'n'=>$r['note'],
                'd'=>$general->make_date($r['date']),
                'q'=>$r['quantity'],
                'up'=>$r['unit_cost'],
                't'=>$r['total'],
                'de'=>'<button onclick="are_you_sure(1,\'Are you sure?\','.$r['id'].',rejectDelete)" class="btn btn-danger rejectDelete_'.$r['id'].'">Delete</button>',
                'table_tr_id'=>'reject_'.$r['id']
            ];
        }
        $rData[]=[
            's'=>'',
            'c'=>['t'=>'Total','b'=>1,'col'=>6],
            'p'=>['t'=>false],
            'a'=>['t'=>false],
            'n'=>['t'=>false],
            'd'=>['t'=>false],
            'u'=>['t'=>false],
            'q'=>['t'=>$total_quantity,'b'=>1],
            'up'=>['t'=>''],
            't'=>['t'=>$total_amount,'b'=>1],
            'de'=>['t'=>''],
        ];
    }
    $head=[
        ['title'=>"#"           ,'key'=>'s','hw'=>5],
        ['title'=>"Code"        ,'key'=>'c'],
        ['title'=>"Product"     ,'key'=>'p'],
        ['title'=>"Add by"      ,'key'=>'a'],
        ['title'=>"Note"        ,'key'=>'n'],
        ['title'=>"Date"        ,'key'=>'d'],
        ['title'=>"Unit"        ,'key'=>'u'],
        ['title'=>"Quantity"    ,'key'=>'q','al'=>'r']
    ];
    if($can_see_unit_cost==1){
        $head[]=['title'=>"Unit cost"   ,'key'=>'up','al'=>'r'];
        $head[]=['title'=>"Total"       ,'key'=>'t','al'=>'r'];
    }
    $head[]=['title'=>"Delete"      ,'key'=>'de'];
    $fileName='reject_list'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'reject_list',
        'title'     => 'Reject list',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
