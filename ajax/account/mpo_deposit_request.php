<?php
    $reportInfo=[];
    $dRange = $_POST['dRange'];
    $reportInfo[]='Date: '.$dRange;
    $user_id = intval($_POST['user_id']);
    $status = intval($_POST['status']);
    $base_id = intval($_POST['base_id']);
    $general->getFromToFromString($dRange,$from,$to);
    $base = $db->selectAll('base');
    $general->arrayIndexChange($base);

    $q=['time between '.$from.' and '.$to];
    if($user_id>0){
        $q[]='user_id='.$user_id;
    }
    if($base_id>0){
        $q[]='user_id in (select id from users where base_id='.$base_id.')';
    }
    if($status>-1){
        $q[]='status='.$status;
    }
    $request = $db->selectAll('deposit_request','where '.implode(' and ',$q));
    $rData=[];
    $sr=1;
    $total=0;
    if(!empty($request)){
        $user_ids=[];
        $bank_ids=[];
        foreach($request as $r){
            $user_ids[$r['user_id']] = $r['user_id'];
            $user_ids[$r['action_by']] = $r['action_by'];
            $bank_ids[$r['bank_id']] = $r['bank_id'];
        }
        $banks = $db->selectAll('bank','where id in('.implode(',',$bank_ids).')','id,name');
        $users = $db->selectAll('users','where id in('.implode(',',$user_ids).')','id,name,base_id');
        $general->arrayIndexChange($users);
        $general->arrayIndexChange($banks);
        $cash_accounts=$acc->get_all_cash_accounts();
        foreach($request as $r){
            $action = '';
            $cancel_btn = '';
            if($r['status']==1){
                $action = '<button class="btn btn-info action_btn_1_'.$r['id'].'"  onclick="are_you_sure(1,\'Are you sure you want to Accept the transaction?\',{id:'.$r['id'].',type:1},mpo_deposit_action);">Accept</button>
                <button class="btn btn-danger action_btn_0_'.$r['id'].'"  onclick="are_you_sure(1,\'Are you sure you want to Cancel the transaction?\',{id:'.$r['id'].',type:0},mpo_deposit_action);">Cancel</button>';
            }
            else if($r['status']==2){
                $action='Confirmed <button class="btn btn-danger action_btn_0_'.$r['id'].'"  onclick="are_you_sure(1,\'Are you sure you want to delete the transaction?\',{id:'.$r['id'].',type:2},mpo_deposit_action);">Return to user</button>';
            }
            else if($r['status']==0){
                $action='Canceled';
            }
            $total+=$r['amount'];
            $rData[]=[
                's'=>$sr++,
                'i'=>$r['id'],
                'date' =>$general->make_date($r['time'],'st'),
                'bank' =>$cash_accounts[$r['bank_id']]['title']??'',
                'base' =>$base[$users[$r['user_id']]['base_id']]['title']??'',
                'request_by' =>$users[$r['user_id']]['name']??'',
                'note' =>$r['note'],
                'amount' =>$general->numberFormat($r['amount']),
                'action_time' =>($r['action_time']!=0)?$general->make_date($r['action_time'],'st'):'',
                'action_by' =>$users[$r['action_by']]['name']??'',
                'action' =>$action,
            ];
        }
    }
    $rData[]=[
        's'=>'',
        'date'=>['t'=>'Total','b'=>1],
        'i'=>'',
        'bank'=>['t'=>''],
        'base'=>['t'=>''],
        'request_by'=>['t'=>''],
        'note'=>['t'=>''],
        'amount'=>['t'=>$general->numberFormat($total),'b'=>1],
        'action_time'=>['t'=>''],
        'action_by'=>['t'=>''],
        'action'=>['t'=>''],
    ];
    $fileName='MPO_Deposit_Request'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'MPO_Deposit_Request',
        'title'     => 'MPO Deposit Request',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"SL"             ,'key'=>'s','hw'=>5),
            array('title'=>"ID"             ,'key'=>'i','hw'=>6),
            array('title'=>"Date"           ,'key'=>'date'),
            array('title'=>"Bank"           ,'key'=>'bank'),
            array('title'=>"Base"           ,'key'=>'base'),
            array('title'=>"Request by"     ,'key'=>'request_by'),
            array('title'=>"Note"           ,'key'=>'note'),
            array('title'=>"Amount"         ,'key'=>'amount'     ,'al'=>'r'),
            array('title'=>"Action date"    ,'key'=>'action_time'),
            array('title'=>"Action_by"      ,'key'=>'action_by'),
            array('title'=>"Action"         ,'key'=>'action'),
            //array('title'=>"Product"    ,'key'=>'pr'),

        ),
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
