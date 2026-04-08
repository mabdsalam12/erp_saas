<?php
    $dRange = $_POST['dRange']  ;
    $customer_id = intval($_POST['customer_id']);
    $reportInfo=['Date:  '.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $rData=[];
    $sr=1;
    $q=["entry_time between $from  and  $to"];
    if($customer_id>0){
        $q[]="customer_id= $customer_id";
    }

    $visit_list =$db->selectAll('customer_visit','where '.implode(' and ',$q),'',' ');
    if(!empty($visit_list)){
        $customer_ids=[];
        $user_ids=[];
        foreach($visit_list as $vl){
            $customer_ids[$vl['customer_id']]=$vl['customer_id'];
            $user_ids[$vl['createdBy']]=$vl['createdBy'];
        }
        $user= $db->allUsers('and id in('.implode(',',$user_ids).')');

        $customers = $smt->get_base_wise_all_customer(' and  id in('.implode(',',$customer_ids).')')['customers']??[];
        foreach($visit_list as $vl){
            $rData[]=[
                's'=>$sr++,
                'd'=>$general->make_date($vl['entry_time'],'time'),
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
