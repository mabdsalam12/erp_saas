<?php
    $dRange=$_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    //$invoice_no = $_POST['invoice_no'];
    $base_id  = intval($_POST['base_id']);
    $customer_id  = intval($_POST['customer_id']);


    if($customer_id>0){
        $customer=$smt->customerInfoByID($customer_id);
        if(!empty($customer)){
            $reportInfo[]='Customer : '.$customer['name'].' ('.$customer['code'].')';
            $q[]='customer_id='.$customer_id;
        }
    }
    else{
        $reportInfo[]='Customer : All';
    }
    if($base_id>0){
        $q[]='base_id='.$base_id;
    }
    //if(!empty($invoice_no)){
    //        $q[] = 'invoice_no like "%'.$invoice_no.'%"';
    //    }
    //    else{
    //        
    //    }
    $q[]='invoice_date between '.$from.' and '.$to;
    $sales=$db->selectAll('sale_return','where '.implode(' and ',$q).' order by invoice_date asc','','array',$jArray);
    //$general->arrayIndexChange($sales);
    $tSubTotal=0;
    $tDiscount=0;
    $tTotal=0;
    if(!empty($sales)){
        $sIDs=[0=>0];
        $cIDs=[0=>0];
        $base_ids=[0=>0];
        foreach($sales as $s){
            $cIDs[$s['customer_id']]=$s['customer_id'];
            $sIDs[$s['id']]=$s['id'];
            $base_ids[$s['base_id']]=intval($s['base_id']);
        }
        $sale_return_process = $db->selectAll('sale_return_process_data','where sale_return_id in('.implode(',',$sIDs).')','id,sale_return_id');
        $general->arrayIndexChange($sale_return_process,'sale_return_id');
        $base = $db->selectAll('base','where id in('.implode(',',$base_ids).')','id,title');
        $general->arrayIndexChange($base,'id');
        //$sale_product = $db->selectAll('sale_products','where sale_id in('.implode(',',$sIDs).')');
        $sale_products=[];
        $product_ids=[];

        $customers=$db->selectAll('customer','where id in('.implode(',',$cIDs).')');
        $general->arrayIndexChange($customers,'id');
        $serial=1;
        foreach($sales as $s){
            $process = '';
            if(!isset($sale_return_process[$s['id']])){
                $process = '<button onclick="salse_return_process_init('.$s['id'].')" class="btn btn-success">Process</button>';    
            }
            $tSubTotal+=$s['sub_total'];
            $tDiscount+=$s['discount'];
            $tTotal+=$s['return_amount'];
            $rData[]=[
                's'=>$serial++,
                //'i'=>$s['invoice_no']??'',
                'c'=>$customers[$s['customer_id']]['name'].'('.$customers[$s['customer_id']]['code'].')',
                'd'=>$general->make_date($s['invoice_date']),
                'cl'=>$general->make_date($s['approved_date']),
                'mpo'=>$base[$s['base_id']]['title']??'',
                'st'=>$general->numberFormat($s['sub_total']),
                'di'=>$general->numberFormat($s['discount']),
                't'=>$general->numberFormat($s['return_amount']),
                'p'=>'<button onclick="salse_return_details_view('.$s['id'].')" class="btn btn-success">Details</button>',
                'a'=>$process.'<button onclick="are_you_sure(1,\'Are you sure you want to delete the sale return?\','.$s['id'].',sale_return_delete)" class="btn btn-danger sale_return_delete_'.$s['id'].'">Delete</button>',
                //<a href="'.URL.'/?print=sale&id='.$s['id'].'" target="_blank" class="btn btn-success">Print</a>',
                //'a'=>'<a href="'.URL.'/?mdl=sale-update&edit='.$s['id'].'" class="btn btn-info">Edit</a>'
                //<button onclick="are_you_sure(1,\'Are you sure you want to delete the sale?\','.$s['id'].',sale_delete)" class="btn btn-danger sale_delete_'.$s['id'].'">Delete</button>',
            ];
        }
    }

    $rData[]=[
        's'=>'',
        'd'=>['t'=>'Total','b'=>1],
        //'c'=>['t'=>''],
        'c'=>['t'=>''],
        'cl'=>['t'=>''],
        'mpo'=>['t'=>''],

        'st'=>['t'=>$general->numberFormat($tSubTotal),'b'=>1],
        'di'=>['t'=>$general->numberFormat($tDiscount),'b'=>1],
        't'=>['t'=>$general->numberFormat($tTotal),'b'=>1],
        'p'=>['t'=>''],
        'a'=>['t'=>''],

    ];
    $fileName='saleReport_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'saleReport',
        'title'     => 'Sale List',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"SL"         ,'key'=>'s','hw'=>5),
            //array('title'=>"Invoice no" ,'key'=>'i'),
            array('title'=>"Invoice Date",'key'=>'d'),
            array('title'=>"Approved date"   ,'key'=>'cl'),
            array('title'=>"Base"        ,'key'=>'mpo'),
            array('title'=>"Customer"   ,'key'=>'c'),
            array('title'=>"Subtotal"   ,'key'=>'st'     ,'al'=>'r'),
            array('title'=>"Discount"   ,'key'=>'di'     ,'al'=>'r'),
            array('title'=>"Return amount" ,'key'=>'t'     ,'al'=>'r'),
            array('title'=>"Details"      ,'key'=>'p'),
            array('title'=>"Process"      ,'key'=>'a'),
        ),
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;


