<?php
    $dRange=$_POST['dRange'];
    $status=$_POST['status'];
    
    $reportInfo=[];
    $reportInfo[]='Date : '.$dRange;
    $general->getFromToFromString($dRange,$from,$to);
    
    $q=[];
    if($status=='p'){
        $q[]='status=0';
    }
    else{
        $q[]="time between $from and $to";
    }
    $collection=$db->selectAll('customer_amount_receive','where '.implode(' and ',$q));
    $rData=[];
    $serial=1;
    $total_amount=0;
    if(!empty($collection)){
        $customer_ids=[];
        
        $mpo_ids=[];
        foreach($collection as $c){
            $mpo_ids[$c['mpo_id']]=$c['mpo_id'];
            $mpo_ids[$c['confirm_by']]=$c['confirm_by'];
            $customer_ids[$c['customer_id']]=$c['customer_id'];
        }
        $customers=$db->selectAllByID('customer','id',$customer_ids);
        $users=$db->selectAllByID('users','id',$mpo_ids);
        foreach($collection as $cl){
            $c              = $customers[$cl['customer_id']];
            $u              = $users[$cl['mpo_id']];
            $b              = $smt->base_info_by_id($c['base_id']);
            $total_amount  += $cl['amount'];
            $action='';
            $confirm_by='';
            $confirm_time='';
            if($cl['status']==0){
                $action='<button onclick="are_you_sure(1,\'Are you sure you want to delete the transaction?\','.$cl['id'].',customer_collection_action)" class="btn btn-danger">Confirm</button>';
            }
            else{
                $confirm_by=$users[$cl['confirm_by']]['name'];
                $confirm_time=$general->make_date($cl['confirm_time'],'time');
            }
            $rData[]=[
                's' => $serial++,
                'c' => $c['name'],
                'b' => $b['title'],
                'u' => $u['name'],
                'a' => $cl['amount'],
                't' => $general->make_date($cl['time'],'time'),
                'cn' => $action,
                'cb'=>$confirm_by,
                'ct'=>$confirm_time
            ];
        }
    }
    
    
    
    $rData[]=[
        's' => '',
        'c'=> array('t'=>'Total','b'=>1,'col'=>3),
        'u' => array('t'=>false),
        'b' => array('t'=>false),
        'a' => ['t'=>$general->numberFormat($total_amount),'b'=>1],
        'r'=>''
    ];
    $fileName='customerPaymentList_'.TIME.rand(0,999).'.txt';
    $headData=array(
        array('title'=>"#"          ,'key'=>'s' ,'hw'=>4),
        array('title'=>"Customer"   ,'key'=>'c'),
        array('title'=>"Base"       ,'key'=>'b'),
        array('title'=>"User"       ,'key'=>'u' ,'hw'=>8),
        array('title'=>"Date time"  ,'key'=>'t','hw'=>12),
        array('title'=>"Amount"     ,'key'=>'a' ,'hw'=>8    ,'al'=>'r'),
        array('title'=>"Confirm"    ,'key'=>'cn' ,'hw'=>8),
        array('title'=>"Confirm by" ,'key'=>'cb' ,'hw'=>8),
        array('title'=>"Confirm time",'key'=>'ct' ,'hw'=>8),
    );
    $reportData=array(
        'name'      => 'customer_payment_list_'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
        'title'     => 'customer payment list',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'      => $headData,
        'data'      => $rData
    );
    $gAr['report_data']= $reportData;

    textFileWrite(json_encode($reportData),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');

    $jArray['status']=1;
