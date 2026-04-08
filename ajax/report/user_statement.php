<?php
    if(!isset($from)){
        $dRange=$_POST['dRange'];
        $general->getFromToFromString($dRange,$from,$to);
    }    
    $rData=[];
    $cash_head=$acc->getSystemHead(AH_CASH);
    $closingBalance=$acc->user_balance($userData['id'],$from-1);
    $reportInfo=[
        'My statement '.$userData['username'],
        $general->make_date($from).' to '.$general->make_date($to)
    ];
    $od=0;
    $oc=0;
    $balance=0;
    if($closingBalance<0){
        $oc=-$closingBalance;
    }
    else{
        $od=$closingBalance;
    }
    $balance+=$od;
    $balance-=$oc;
    $serial=1;
    $rData[]=[
        's'=>$serial++,
        'n'=>'',
        'm'=>'',
        'h'=>'',
        'd'=>'',
        'p'=>['t'=>'Opening','b'=>1],
        'u'=>'',
        'in'=>'',
        'out'=>'',
        'b'=>$general->numberFormat($balance),
    ];
    $tIn=0;
    $tOut=0;
    
    $statement=$acc->head_statement($cash_head,$from,$to,$userData['id'],$jArray);
    if(!empty($statement)){
        $users=$db->allUsers();
        foreach($statement as $s){
            $tIn+=$s['in'];
            $tOut+=$s['out'];

            $balance+=$s['in'];
            $balance-=$s['out'];
            $veIDs[$s['voucher_id']]=$s['voucher_id'];
            $rData[]=[
                's'=>$serial++,
                'h'=>$s['head_title'],
                'd'=>$general->make_date($s['time'],'time'),
                'p'=>$s['note'],
                'u'=>@$users[$s['createdBy']]['username'],
                'in'=>$general->numberFormat($s['in']),
                'out'=>$general->numberFormat($s['out']),
                'b'=>$general->numberFormat($balance),
            ];
        }
    }
    $rData[]=[
        's'=>'',
        'n'=>['t'=>'Total','b'=>1,'col'=>4],
        'd'=>['t'=>false],
        'p'=>['t'=>false],
        'u'=>['t'=>false],
        'in'=>['t'=>$general->numberFormat($tIn),'b'=>1],
        'out'=>['t'=>$general->numberFormat($tOut),'b'=>1],
        'b'=>['t'=>$general->numberFormat($balance),'b'=>1],
    ];
    $fileName='customerStat'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'mpo_statement'.date('d/m/Y'),
        'title'     => 'Mpo statement',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"              ,'key'=>'s'     ,'hw'=>5),
            array('title'=>"Head"           ,'key'=>'h'     ,'hw'=>15),
            array('title'=>"Date Time"      ,'key'=>'d'),
            array('title'=>"Particular"     ,'key'=>'p'),
            array('title'=>"User"           ,'key'=>'u'),
            array('title'=>"Pay"           ,'key'=>'in'    ,'al'=>'r'),
            array('title'=>"Receive"         ,'key'=>'out'     ,'al'=>'r'),
            array('title'=>"Balance"        ,'key'=>'b'    ,'al'=>'r'),

        ),
        'data'=>$rData
    );
    $jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    if(!isset($noNeedHtml)){
        $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
        $jArray['status']=1;   
        $general->jsonHeader($jArray);
    }
