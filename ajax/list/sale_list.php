<?php
    $dRange=$_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $invoice_no = $_POST['invoice_no'];
    $base_id  = intval($_POST['base_id']);
    $customer_id  = intval($_POST['customer_id']);
    $print_type  = intval($_POST['print_type'])==1?1:0;
    $toll_sale_type  = intval($_POST['toll_sale_type']);
    if($toll_sale_type!=PRODUCT_TYPE_OFFER&&$toll_sale_type!=PRODUCT_TYPE_MANUFACTURING){
        $toll_sale_type=PRODUCT_TYPE_FINISHED;
    }
    $company_data = $db->get_company_data();
    $toll_product_base=$company_data['toll_product_base'];
    if($toll_sale_type==PRODUCT_TYPE_MANUFACTURING){
        $q[]='base_id='.$toll_product_base;
    }
    elseif($toll_sale_type==PRODUCT_TYPE_OFFER){
        $q[]='product_type='.$toll_sale_type;
    }
    else{
        $q[]='base_id!='.$toll_product_base;
    }
    
    if(!empty($invoice_no)){
        $q[] = 'invoice_no like "%'.$invoice_no.'%"';
    }
    else{
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
        $q[]='date between '.$from.' and '.$to;
    }

    $sales=$db->selectAll('sale','where '.implode(' and ',$q).' order by date desc','','array',$jArray);
    $general->arrayIndexChange($sales,'id');
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
        $base = $db->selectAll('base','where id in('.implode(',',$base_ids).')','id,title');
        $general->arrayIndexChange($base,'id');
        //$sale_product = $db->selectAll('sale_products','where sale_id in('.implode(',',$sIDs).')');
        $sale_products=[];
        $product_ids=[];

        $customers=$db->selectAll('customer','where id in('.implode(',',$cIDs).')');
        $general->arrayIndexChange($customers,'id');
        $serial=1;
        $print_without_tp='';
        if($print_type==1){
            $print_without_tp='&without_tp=1';
        }
        foreach($sales as $s){
            $u=$db->userInfoByID($s['createdBy']);
            $tSubTotal+=$s['sub_total'];
            $tDiscount+=$s['discount'];
            $tTotal+=$s['total'];
            $order_date ='';
            if($s['order_date']>0){
                $order_date = $general->make_date($s['order_date']);
            }
            $rData[]=[
                's'=>$serial++,
                'i'=>$s['invoice_no']??'',
                'c'=>$customers[$s['customer_id']]['name'].'('.$customers[$s['customer_id']]['code'].')',
                'd'=>$general->make_date($s['date']),
                'u'=>$u['username'],
                'cl'=>$general->make_date($s['collection_date']),
                'mpo'=>$base[$s['base_id']]['title']??'',
                'odn'=>$s['order_no'],
                'odd'=>$order_date,
                'st'=>$general->numberFormat($s['sub_total']),
                'di'=>$general->numberFormat($s['discount']),
                't'=>$general->numberFormat($s['total']),
                'p'=>'
                <button onclick="salse_details_view('.$s['id'].')" class="btn btn-success">Details</button>
                <a href="'.URL.'/?print=sale&id='.$s['id'].$print_without_tp.'" target="_blank" class="btn btn-success">Print</a>
                
                <a href="'.URL.'/?print=sale&id='.$s['id'].'&challan=1" target="_blank" class="btn btn-success">Challan</a>',
                'a'=>
                '<a href="'.URL.'/?mdl=sale-update&edit='.$s['id'].'" class="btn btn-info">Edit</a>
                <button onclick="are_you_sure(1,\'Are you sure you want to delete the sale?\','.$s['id'].',sale_delete)" class="btn btn-danger sale_delete_'.$s['id'].'">Delete</button>',
            ];
        }
    }
    
    $rData[]=[
        's'=>'',
        'i'=>['t'=>'Total','b'=>1],
        'c'=>['t'=>''],
        'd'=>['t'=>''],
        'cl'=>['t'=>''],
        'u'=>['t'=> ''],
        'mpo'=>['t'=>''],
        'odn'=>['t'=>''],
        'odd'=>['t'=>''],
        'p'=>['t'=>''],
        'st'=>['t'=>$general->numberFormat($tSubTotal),'b'=>1],
        'di'=>['t'=>$general->numberFormat($tDiscount),'b'=>1],
        't'=>['t'=>$general->numberFormat($tTotal),'b'=>1]
    ];
    $fileName='saleReport_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'saleReport',
        'title'     => 'Sale List',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"SL"         ,'key'=>'s','hw'=>5),
            array('title'=>"Invoice no" ,'key'=>'i'),
            array('title'=>"Invoice Date",'key'=>'d'),
            array('title'=>"Due date"   ,'key'=>'cl'),
            array('title'=>"Base"        ,'key'=>'mpo'),
            array('title'=>"Order No"        ,'key'=>'odn'),
            array('title'=>"Order Date"        ,'key'=>'odd'),
            array('title'=> 'Created by','key'=> 'u'),
            array('title'=>"Customer"   ,'key'=>'c'),
            array('title'=>"Subtotal"   ,'key'=>'st'     ,'al'=>'r'),
            array('title'=>"Discount"   ,'key'=>'di'     ,'al'=>'r'),
            array('title'=>"Net amount" ,'key'=>'t'     ,'al'=>'r'),
            array('title'=>"Details"      ,'key'=>'p','no_for_excel'=>1),
            array('title'=>"Action"      ,'key'=>'a','no_for_excel'=> 11),
        ),
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;