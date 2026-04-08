<?php
    $dRange = $_POST['dRange']  ;
    $base_id = intval($_POST['base_id']);
    $customer_id = intval($_POST['customer_id']);
    $reportInfo=['Date:  '.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $rData=[];
    $sr=1;
    $q=["entry_time between $from  and  $to"];
    if($customer_id>0){
        $q[]="customer_id= $customer_id";
    }  
    if($base_id>0){
        $customers = $smt->get_base_wise_all_customer(' and  base_id='.$base_id)['customers']??[];
        if(!empty($customers)){
            $q[]='customer_id in('.implode(',',array_keys($customers)).')';
        }
        else{
            $q[]='customer_id=0';
        }
    }   

    $visit_list =$db->selectAll('customer_visit','where '.implode(' and ',$q));
    if(!empty($visit_list)){
        $customer_ids=[];
        $user_ids=[];
        foreach($visit_list as $vl){
            $customer_ids[$vl['customer_id']]=$vl['customer_id'];
            $user_ids[$vl['createdBy']]=$vl['createdBy'];
        }
        $user= $db->allUsers('and id in('.implode(',',$user_ids).')');
        if($base_id==0){
            $customers = $smt->get_base_wise_all_customer(' and  id in('.implode(',',$customer_ids).')')['customers']??[];
        }
        $base = $db->selectAll('base');
        $general->arrayIndexChange($base);
        foreach($visit_list as $vl){
            $rData[]=[
                's'=>$sr++,
                'd'=>$general->make_date($vl['entry_time'],'time'),
                'ba'=>$base[$customers[$vl['customer_id']]['base_id']]['title']??'',
                'v'=>$customers[$vl['customer_id']]['name']??'',
                'r'=>$general->content_show($vl['note']),
                'c'=>$user[$vl['createdBy']]['name']??'',
                'de'=>'<button onclick="customer_visit_details_view('.$vl['id'].')" class="btn btn-success">Details</button>',
            ];
        }
    }
    $head=[
        ['title'=>'SL'          ,'key'=>'s','hw'=>5],
        ['title'=>'Date'        ,'key'=>'d'],
        ['title'=>'Base'        ,'key'=>'ba'],
        ['title'=>'Customer'    ,'key'=>'v'],
        ['title'=>'Note'        ,'key'=>'r' ],
        ['title'=>'Created by'  ,'key'=>'c' ],
        ['title'=>'Details'     ,'key'=>'de'],
    ];
    $fileName='customer_visit_list_'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'customer_visit_list',
        'title'     => 'Customer visit list',
        'info'      => $reportInfo,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
