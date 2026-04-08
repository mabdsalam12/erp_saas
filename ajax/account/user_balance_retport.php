<?php
    $users = $db->selectAll('users','where type='.USER_TYPE_MPO.' and isActive in(1,0)','name,id,base_id,ledger_id');
    $rData=[];
    $sr=1;
    $total_balance=0;
    $base = $db->selectAll('base');
    
    if(!empty($users)){
        $general->arrayIndexChange($base,'id');
        $ledger_ids=[];
        foreach($users as $u){
            $head=$acc->get_user_head($u);
            $ledger_ids[$u['id']]=$head;
        } 
        //$cash_head=$acc->getSystemHead(AH_CASH);
        //$q=['time<='.TIME,'user_id in('.implode(',',$ids).')',"ledger_id=".$cash_head];

        //$query="select (sum(debit)-sum(credit)) as balance, user_id from a_ledger_entry where ".implode(' and ',$q).' group by user_id';

        //$balance=$db->fetchQuery($query,'array',$jArray);
        $head_balance=$acc->headBalance($ledger_ids,TIME,0, ['groupByHID'=>1],$jArray);
        $general->arrayIndexChange($head_balance,'ledger_id');
        // $jArray[fl()] = $ledger_ids;
        // $jArray[fl()] = $head_balance;
        foreach($users as $u){
            $b = 0;
            $ledger_id=$ledger_ids[$u['id']];
            if(isset($head_balance[$ledger_id])){
                $b=floatval($head_balance[$ledger_id]['balance']);
                //$jArray[fl()][]=$b;
            }
            $total_balance+=$b;
            $base_title='';
            if(isset($base[$u['base_id']])){
                $base_title=$base[$u['base_id']]['title'];
            }
            $rData[]=[
                's'=>$sr++,
                'n'=>$u['name'],
                'a'=>$base_title,
                'b'=>$general->numberFormat($b)
            ];
        }


    }
    $rData[]=[
        's'=>['t'=>''],
        'n'=>['t'=>'Total','b'=>1],
        'a'=>'',
        'b'=>['t'=>$general->numberFormat($total_balance),'b'=>1],

    ];

    $fileName='user_balance'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'User balance',
        'title'     => 'User balance',
        'info'      => [],
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"SL"       ,'key'=>'s','hw'=>5),
            array('title'=>"Name"     ,'key'=>'n'),
            array('title'=>"Base"     ,'key'=>'a'),
            array('title'=>"Balance"  ,'key'=>'b'     ,'al'=>'r'),
        ),
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;

