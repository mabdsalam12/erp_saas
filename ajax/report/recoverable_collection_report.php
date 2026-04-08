<?php

    $dRange=date('d-m-Y').' to '.date('d-m-Y');
    $base_id=intval($_POST['base_id']);
    if(isset($_POST['dRange'])){
        if($_POST['dRange']!=''){
            $dRange=$_POST['dRange'];
        }
    }
    $reportInfo=[];
    $reportInfo[]='Date : '.$dRange;
    $general->getFromToFromString($dRange,$from,$to);
    // $vType  = [V_T_RECOVERABLE_COLLECTION];
    // $tData=$acc->cashFlowReport($from,$to,0,0,$vType,'',$jArray);
    $vType[] = V_T_NEW_RECOVERABLE_COLLECTION;
    $vType[] = V_T_RECOVERABLE_COLLECTION;
    $extra_data=[];
    $extra_data['base_id']=$base_id;
    
    $vouchers = $acc->voucherDetails($vType,'',$from,$to,$extra_data);
    $jArray[fl()] = $vouchers;
    $rData=[];
    $total_in=0;
    if(!empty($vouchers)){
        $ledger_ids=[];
        $user_ids=[];
        foreach($vouchers as $v){
            $ledger_ids[$v['debit']]=$v['debit'];
            $user_ids[$v['createdBy']]=$v['createdBy'];
        }
        $users=$db->selectAll('users','where id in('.implode(',',$user_ids).')','id,name');
        $ledgers=$db->selectAll('a_ledgers','where id in('.implode(',',$ledger_ids).')','id,title');
        $general->arrayIndexChange($users);
        $general->arrayIndexChange($ledgers);
        $serial=1;
        foreach($vouchers as $v){
            $total_in+=$v['amount'];
            $base_title='';
            if($v['base_id']>0){
                $base_title=$smt->base_info_by_id($v['base_id'])['title'];
            }
            $rData[]=[
                's' => $serial++,
                'u' => $users[$v['createdBy']]['name'],
                'b' => $base_title,
                'd' => $ledgers[$v['debit']]['title'],
                'dt'=> date('d-m-Y h:i a',$v['time']),
                'p' => $v['note'],
                'i' => ['t'=>$general->numberFormat($v['amount'])],
            ];
        }
    }
    $rData[]=[
        's' => '',
        'u' => array('t'=>'Total','b'=>1,'col'=>5),
        'dt'=> array('t'=>false),
        'p' => array('t'=>false),
        'b' => array('t'=>false),
        'd' => array('t'=>false),
        'i' => ['t'=>$general->numberFormat($total_in),'b'=>1],
    ];
    $fileName='recoverable_collection_report_'.TIME.rand(0,999).'.txt';
    $headData=[
        ['title'=>"#"              ,'key'=>'s' ,'w'=>5 ,'hw'=>4],
        ['title'=>"User"           ,'key'=>'u' ,'w'=>10,'hw'=>8],
        ['title'=>"Base"           ,'key'=>'b' ,'w'=>10,'hw'=>8],
        ['title'=>"Debit"          ,'key'=>'d' ,'w'=>10,'hw'=>8],
        ['title'=>"Date time"      ,'key'=>'dt','w'=>20,'hw'=>12],
        ['title'=>"Description"    ,'key'=>'p' ,'w'=>20],
        ['title'=>"Receive"        ,'key'=>'i' ,'w'=>20,'hw'=>8    ,'al'=>'r'],
    ];
    $reportData=[
        'name'      => 'recoverable_collection_report_'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
        'title'     => 'recoverable collection report',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'      => $headData,
        'data'      => $rData
    ];
    $gAr['report_data']= $reportData;

    textFileWrite(json_encode($reportData),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');

    $jArray['status']=1;


?>  