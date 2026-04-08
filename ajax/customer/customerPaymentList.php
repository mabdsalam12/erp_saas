<?php
    $dRange=$_POST['dRange'];
    $customer_id = intval($_POST['customer_id']);
    $base_id = intval($_POST['base_id']);
    $transaction_type = intval($_POST['transaction_type']);
    $reportInfo=[];
    $reportInfo[]='Date : '.$dRange;
    $general->getFromToFromString($dRange,$from,$to);
    $reference='';
    $recoverable_ref='';
    if($base_id>0){
        $reference=0;
        $customers = $db->selectAll('customer','where base_id='.$base_id,'id');
        $general->arrayIndexChange($customers);
        if(!empty($customers)){
            $reference = array_keys($customers);
        }
    }
    if($customer_id>0){
        $reference =[$customer_id];
    }
    $sq=['createdOn between '.$from.' and '.$to];
    if(!empty($reference)){
        $sq[]='customer_id in('.implode(',',$reference).')';
    }
    $recoverable=$db->selectAll('recoverable_collection','where '.implode(' and ',$sq),'id','array',$jArray);
    if(!empty($recoverable)){
        $jArray[fl()]=1;
        $general->arrayIndexChange($recoverable);
        $recoverable_ref = array_keys($recoverable);
    }
    else{
        $jArray[fl()]=1;
        $recoverable_ref=[];
    }

    $vType  = [V_T_CUSTOMER_YEARLY_DISCOUNT, V_T_PAY_TO_CUSTOMER,V_T_RECEIVE_FROM_CUSTOMER,V_T_RECOVERABLE_ENTRY,V_T_CUSTOMER_BAD_DEBT,V_T_CUSTOMER_COLLECTION_DISCOUNT];


    if($transaction_type>0){
        $vType = $transaction_type;
    }
    // if($vType==V_T_NEW_RECOVERABLE_ENTRY){
    //     $vType=[
    //         V_T_NEW_RECOVERABLE_ENTRY,V_T_RECOVERABLE_ENTRY
    //     ];
    // }
    $jArray[fl()]=$reference;
    $jArray[fl()]=$vType;

    $jArray[fl()]=$recoverable_ref;
    if($transaction_type!=V_T_NEW_RECOVERABLE_ENTRY){
        $tData1 = $acc->voucherDetails($vType,$reference,$from,$to,[],$jArray);
    }
    else{
        $tData1=[];
    }
    if($transaction_type==0||$transaction_type==V_T_NEW_RECOVERABLE_ENTRY){
        $tData2 = $acc->voucherDetails(V_T_NEW_RECOVERABLE_ENTRY,$recoverable_ref,$from,$to,[],$jArray);
    }
    else{
        $tData2=[];
    }
    $jArray[fl()] = $tData2;
    $jArray[fl()] = $tData1;
    
    $tData = array_merge($tData1, $tData2);
    $jArray[fl()] = $tData;

    $rData=[];
    $total_in=0;                         
    $total_out=0;
    $total=1;

    if(!empty($tData)){
        $ledger_ids=[];
        $user_ids=[];
        foreach($tData as $d){
            $ledger_ids[$d['debit']] = $d['debit'];
            $ledger_ids[$d['credit']] = $d['credit'];
            $user_ids[$d['createdBy']] = $d['createdBy'];
        }
        $heads = $db->selectAll('a_ledgers','where id in('.implode(',',$ledger_ids).')','id,title');
        $general->arrayIndexChange($heads);
        $users = $db->selectAll('users','where id in('.implode(',',$user_ids).')','id,name');
        $general->arrayIndexChange($users);
        foreach($tData as $tr){
            $in = 0;
            $out = 0;
            $delete_btn='<button onclick="are_you_sure(1,\'Are you sure you want to delete the transaction?\','.$tr['id'].',customer_transaction_remove)" class="btn btn-danger">Delete</button>';
            if($tr['type']==V_T_PAY_TO_CUSTOMER){
                $in = $tr['amount'];
                $ledger_id = $tr['debit'];
                $type='Pay to customer';
            }
            else if($tr['type']==V_T_RECEIVE_FROM_CUSTOMER){
                $out = $tr['amount'];
                $ledger_id = $tr['credit'];
                $type='Receive from customer';
            }
            else if($tr['type']==V_T_RECOVERABLE_ENTRY){
                $out = $tr['amount'];
                $ledger_id = $tr['credit'];
                $type='Recoverable collection';
            }
            else if($tr['type']==V_T_NEW_RECOVERABLE_ENTRY){
                $out = $tr['amount'];
                $ledger_id = $tr['credit'];
                $type='Recoverable collection';
            }
            else if($tr['type']==V_T_CUSTOMER_BAD_DEBT){
                $out = $tr['amount'];
                $ledger_id = $tr['credit'];
                $type='Bad-Debt';
            }else if($tr['type']==V_T_CUSTOMER_COLLECTION_DISCOUNT){
                $out = $tr['amount'];
                $ledger_id = $tr['credit'];
                $type='Collection discount';
            }else if($tr['type']==V_T_CUSTOMER_YEARLY_DISCOUNT){
                $out = $tr['amount'];
                $ledger_id = $tr['credit'];
                $type='Yearly discount';
            }
            else{
                $type='Old Recoverable collection';
                $delete_btn='';
            }
            $total_in+=$in;
            $total_out+=$out;
            $data=[
                's' => $total++,
                'h' => $heads[$ledger_id]['title'],//.' - '.$tr['id'],
                't' => $type,//.' '.$tr['id'],
                'u' => $users[$tr['createdBy']]['name']??'',
                'dt'=> date('d-m-Y H:i',$tr['time']),
                'p' => $tr['note'],
                'i' => $general->numberFormat($out),
                'o' => $general->numberFormat($in),
                'r' => $delete_btn
            ];
            $rData[]=$data;
        }
    }

    //$tData=$acc->cashFlowReport($from,$to,0,0,0,$vType,'',jArray: $jArray);

    $rData[]=[
        's' => '',
        'h'=> array('t'=>'Total','b'=>1,'col'=>5),
        'u' => array('t'=>false),
        't' => array('t'=>false),
        'dt'=> array('t'=>false),
        'p' => array('t'=>false),
        'o' => ['t'=>$general->numberFormat($total_in),'b'=>1],
        'i' => ['t'=>$general->numberFormat($total_out),'b'=>1],
    ];
    $fileName='customerPaymentList_'.TIME.rand(0,999).'.txt';
    $headData=[
        array('title'=>"#"              ,'key'=>'s' ,'w'=>5 ,'hw'=>4),
        array('title'=>"Customer"       ,'key'=>'h' ),
        array('title'=>"Typ"            ,'key'=>'t' ),
        array('title'=>"User"           ,'key'=>'u' ,'w'=>10,'hw'=>8),
        array('title'=>"Date time"      ,'key'=>'dt','w'=>20,'hw'=>12),
        array('title'=>"Description"    ,'key'=>'p' ,'w'=>20),
        array('title'=>"Receive"        ,'key'=>'i' ,'w'=>20,'hw'=>8    ,'al'=>'r'),
        array('title'=>"Pay"         	,'key'=>'o' ,'w'=>20,'hw'=>8    ,'al'=>'r'),
        array('title'=>"X"         	    ,'key'=>'r' ,'hw'=>8),
    ];
    $reportData=[
        'name'      => 'customer_payment_list_'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
        'title'     => 'customer payment list',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'      => $headData,
        'data'      => $rData
    ];
    $gAr['report_data']= $reportData;

    textFileWrite(json_encode($reportData),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');

    $jArray['status']=1;
