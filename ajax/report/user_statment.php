<?php
    $user_id = intval($_POST['user_id']);
    $user = $db->userInfoByID($user_id);
    if(empty($user)){$error=fl();setMessage(63);}
    elseif($user['type']!=USER_TYPE_MPO){$error=fl(); setMessage(63);}
    else{
        $dRange=$_POST['dRange'];
        $general->getFromToFromString($dRange,$from,$to);

        $rData=[];
        
        $user_ledger=$acc->get_user_head($user,$jArray);
        $opening_balance = $acc->headBalance($user_ledger,$from-1);

        $reportInfo=[
            'User '.$user['username'],
            $general->make_date($from).' to '.$general->make_date($to)
        ];
        $od=0;
        $oc=0;
        $balance=0;
        if($opening_balance<0){
            $oc=-$opening_balance;
        }
        else{
            $od=$opening_balance;
        }
        $balance+=$od;
        $balance-=$oc;
        $serial=1;
        $rData[]=[
            's'=>$serial++,
            'n'=>['t'=>''],
            'm'=>['t'=>''],
            'h'=>['t'=>''],
            'd'=>['t'=>''],
            'p'=>['t'=>'Opening','b'=>1],
            'u'=>['t'=>''],
            'in'=>['t'=>''],
            'out'=>['t'=>''],
            'b'=>$general->numberFormat($balance),
        ];
        $tIn=0;
        $tOut=0;

        $statement=$acc->head_statement($user_ledger,$from,$to,0,$jArray);
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
            'n'=>['t'=>'Total','b'=>1],
            'd'=>['t'=>''],
            'p'=>['t'=>''],
            'u'=>['t'=>''],
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
        // $jArray[__LINE__]=$report_data;
        $gAr['report_data']= $report_data;
        textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);

        $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
        $jArray['status']=1;   

}