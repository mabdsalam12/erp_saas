<?php
$reportInfo=["Date: $dRange"];
$customer=$smt->customerInfoByID($customer_id);
$general->getFromToFromString($dRange,$from,$to);
$rData=[];
if($base_id>0){
    $base=$smt->base_info_by_id($base_id);
    if($base){
        $reportInfo['k']="Base: $base[title]";
    }
}

if(!empty($customer)){
    $base = $db->baseInfoByID($customer['base_id']);
    $reportInfo[]='Customer: '.$customer['code'].' '.$customer['name'].' '.$base['title'];
}

if($type=='s'){
    $customer_data=[];
    $ledger_ids=[];
    if(!empty($customer)){
        $ledger_id=$acc->getCustomerHead($customer);
        $ledger_ids[$ledger_id]=$ledger_id;
        $customer_data[$ledger_id]=$customer;
    }
    else{
        $q=[];
        $q[]='isActive in(0,1)';
        if($base_id>0){
            $q[]="base_id=$base_id";
            $base=$smt->base_info_by_id($base_id);
            if($base){
                $reportInfo['k']="Base: $base[title]";
            }
        }
        if($customer_category_id>0){
            $category = $db->get_rowData('customer_category','id',$customer_category_id);
            $q[]="customer_category_id=$customer_category_id";
            $reportInfo[]="Customer Category: $category[title]";
        }
        if($bazar_id>0){
            $bazar = $db->get_rowData('bazar','id',$bazar_id);
            $q[]="bazar_id=$bazar_id";
            $reportInfo[]="Bazar: $bazar[title]";
        }
        $customers=$db->selectAll('customer','where '.implode(' and ',$q).' order by code asc','id,name,code,ledger_id');
        if(!empty($customers)){
            
            foreach($customers as $c){
                $ledger_id=$acc->getCustomerHead($c);
                $ledger_ids[$ledger_id]=$ledger_id;
                $customer_data[$ledger_id]=$c;
            }
        }
    }
    $transactions=$acc->head_vouchers($ledger_ids,$from,$to,0,$jArray);
    $jArray[fl()]=$transactions;
    $transaction_by_head_type=[];

    if(!empty($transactions)){
        foreach($transactions as $ledger_id=>$tr){
            foreach($tr as $s){
                $target_head=0;
                if(isset($customer_data[$s['debit_head']])){
                    $target_head=$s['debit_head'];
                }
                elseif(isset($customer_data[$s['credit_head']])){
                    $target_head=$s['credit_head'];
                }
                else{
                    $jArray[fl()][]=$s;
                }


                if(!isset($transaction_by_head_type[$target_head])){
                    $transaction_by_head_type[$target_head]=[];
                }
                if(!isset($transaction_by_head_type[$target_head][$s['type']])){
                    $transaction_by_head_type[$target_head][$s['type']]=0;
                }
                $transaction_by_head_type[$target_head][$s['type']]+=$s['amount'];
            }
        }
    }
    $jArray[fl()]=$customer_data;

    $r_data=[];
    $serial=1;
    $total=[
        'sale'		=> 0,
        'opening'		=> 0,
        'collection'=> 0,
        'collection_discount'=> 0,
        'return'=> 0,
        'yearly'=> 0,
        'recoverable'=> 0,
        'bad_debt'=> 0,
        'closing'=> 0,
    ];
    $ledgers_balance=[];
    if($ledger_ids){
        $opening_from = $from-1;
        $ledgers_data_query = "SELECT 
        ledger_id, 
        SUM(CASE WHEN time BETWEEN 0 AND $opening_from THEN debit ELSE 0 END) - SUM(CASE WHEN time BETWEEN 0 AND $opening_from THEN credit ELSE 0 END) AS opening, 
        SUM(CASE WHEN time BETWEEN 0 AND $to THEN debit ELSE 0 END) - SUM(CASE WHEN time BETWEEN 0 AND $to THEN credit ELSE 0 END) AS closing
        FROM a_ledger_entry 
        WHERE time BETWEEN 0 AND $to and ledger_id in(".implode(',',$ledger_ids).")
        GROUP BY ledger_id;";
        $ledgers_balance = $db->fetchQuery($ledgers_data_query,'array',$jArray);
        
        $general->arrayIndexChange($ledgers_balance,'ledger_id');
    }
    $collection_zero=true;
    $return_zero=true;
    $collection_discount_zero=true;
    $recoverable_zero=true;
    $bad_debt_zero=true;
    $yearly_zero=true;
    foreach($customer_data as $c){
        $jArray[fl()]=1;
        //if(!isset($transaction_by_head_type[$c['ledger_id']]))continue;
        //$opening=$acc->headBalance($c['ledger_id'],$from-1);
        //$closing=$acc->headBalance($c['ledger_id'],$to,0,[],$jArray);
        $opening = $ledgers_balance[$c['ledger_id']]['opening']??0;
        $closing = $ledgers_balance[$c['ledger_id']]['closing']??0;
        $jArray[fl()]=1;
        $sale=0;
        $collection=0;
        $collection_discount=0;
        $return=0;
        $recoverable=0;
        $bad_debt=0;
        $yearly=0;
        if(isset($transaction_by_head_type[$c['ledger_id']][V_T_SALE_CASH_CUSTOMER])){
            $sale=$transaction_by_head_type[$c['ledger_id']][V_T_SALE_CASH_CUSTOMER];
        }
        if(isset($transaction_by_head_type[$c['ledger_id']][V_T_RECEIVE_FROM_CUSTOMER])){
            $collection=$transaction_by_head_type[$c['ledger_id']][V_T_RECEIVE_FROM_CUSTOMER];
        }
        if(isset($transaction_by_head_type[$c['ledger_id']][V_T_CUSTOMER_YEARLY_DISCOUNT])){
            $yearly=$transaction_by_head_type[$c['ledger_id']][V_T_CUSTOMER_YEARLY_DISCOUNT];
        }
        if(isset($transaction_by_head_type[$c['ledger_id']][V_T_RECOVERABLE_ENTRY])){
            $recoverable=$transaction_by_head_type[$c['ledger_id']][V_T_RECOVERABLE_ENTRY];
        }
        if(isset($transaction_by_head_type[$c['ledger_id']][V_T_NEW_RECOVERABLE_ENTRY])){
            $recoverable=$transaction_by_head_type[$c['ledger_id']][V_T_NEW_RECOVERABLE_ENTRY];
        }
        if(isset($transaction_by_head_type[$c['ledger_id']][V_T_CUSTOMER_BAD_DEBT])){
            $bad_debt=$transaction_by_head_type[$c['ledger_id']][V_T_CUSTOMER_BAD_DEBT];
        }
        if(isset($transaction_by_head_type[$c['ledger_id']][V_T_CUSTOMER_COLLECTION_DISCOUNT])){
            $collection_discount=$transaction_by_head_type[$c['ledger_id']][V_T_CUSTOMER_COLLECTION_DISCOUNT];
        }
        if(isset($transaction_by_head_type[$c['ledger_id']][V_T_SALE_RETURN])){
            $return=$transaction_by_head_type[$c['ledger_id']][V_T_SALE_RETURN];
        }
        if($type_zero==2){
            if($closing==0&&$opening==0){
                continue;
            }
        }
        if($type_zero==0&&$sale==0&&$collection==0&&$return==0&&$collection_discount==0&&$recoverable==0&&$bad_debt==0&&$yearly==0){
            continue;
        }
        if($collection>0){
            $collection_zero=false;
        }
        if($return>0){
            $return_zero=false;
        }
        if($collection_discount>0){
            $collection_discount_zero=false;
        }
        if($recoverable>0){
            $recoverable_zero=false;
        }
        if($bad_debt>0){
            $bad_debt_zero=false;
        }
        
        if($yearly>0){
            $yearly_zero=false;
        }
        
        $total['opening']+=$opening;
        $total['sale']+=$sale;
        $total['collection']+=$collection;
        $total['return']+=$return;
        $total['yearly']+=$yearly;
        $total['recoverable']+=$recoverable;
        $total['bad_debt']+=$bad_debt;
        $total['closing']+=$closing;
        $total['collection_discount']+=$collection_discount;
        $data=[
            's'=>$serial++,
            'c'=> $c['name'],
            'cd'=>$c['code'],
            'sl'=>$general->numberFormat($sale),
            'o'=>$general->numberFormat($opening),
            'co'=>$general->numberFormat($collection),
            'cld'=>$general->numberFormat($collection_discount),
            'rc'=>$general->numberFormat($recoverable),
            'r'=>$general->numberFormat($return),
            'b'=>$general->numberFormat($bad_debt),
            'y'=>$general->numberFormat($yearly),
            'cl'=>$general->numberFormat($closing),
        ];
        $r_data[]=$data;
    }
    $r_data[]=[
            's'	=>'',
            'c'	=>['t'=>'Total','col'=>2,'b'=>1],
            'cd'=>['t'=>false,'col'=>2,'b'=>1],
            'o'	=>['t'=>$general->numberFormat($total['opening']),'b'=>1],
            'sl'=>['t'=>$general->numberFormat($total['sale']),'b'=>1],
            'co'=>['t'=>$general->numberFormat($total['collection']),'b'=>1],
            'cld'=>['t'=>$general->numberFormat($total['collection_discount']),'b'=>1],
            'r'	=>['t'=>$general->numberFormat($total['return']),'b'=>1],
            'rc'	=>['t'=>$general->numberFormat($total['recoverable']),'b'=>1],
            'b'	=>['t'=>$general->numberFormat($total['bad_debt']),'b'=>1],
            'y'	=>['t'=>$general->numberFormat($total['yearly']),'b'=>1],
            'cl'=>['t'=>$general->numberFormat($total['closing']),'b'=>1],
    ];
    $head=[
        ['title'=>'#'				,'key'=>'s'],
        ['title'=>'Customer'		,'key'=>'c'],
        ['title'=>'Code'			,'key'=>'cd'],
        ['title'=>'Opening'			,'key'=>'o' 	,'al'=>'r'],
        ['title'=>'Sale'			,'key'=>'sl' 	,'al'=>'r']
    ];
    if($collection_zero==false){
        $head[]=['title'=>'Collection'		,'key'=>'co' 	,'al'=>'r'];
    }
    if($collection_discount_zero==false){
        $head[]=['title'=>'Collection discount'		,'key'=>'cld' 	,'al'=>'r'];
    }
    if($return_zero==false){
        $head[]=['title'=>'Return'			,'key'=>'r' 	,'al'=>'r'];
    }
    if($recoverable_zero==false){
        $head[]=['title'=>'Recoverable'		,'key'=>'rc' 	,'al'=>'r'];
    }
    if($bad_debt_zero==false){
        $head[]=['title'=>'Bad debt'		,'key'=>'b' 	,'al'=>'r'];
    }
    if($yearly_zero==false){
        $head[]=['title'=>'Yearly discount'	,'key'=>'y' 	,'al'=>'r'];
    }
    $head[]=['title'=>'Closing'			,'key'=>'cl' 	,'al'=>'r'];

    $fileName='customerStatement'.TIME.rand(0,999).'.txt';

    $report_data=[
        'name'      => 'customerStatement-'.date('d-m-Y'),
        'title'     => 'customer statement',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'		=> $head,
        'data'		=> $r_data
    ];
    if(isset($from_app)){
        unset($report_data['fileName']);
    }
    $jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;   
    }
else{


if(!empty($customer)){
    $jArray['cAddress']=$customer['address'];
    $jArray['cMobile']=$customer['mobile'];
    $hID=$acc->getCustomerHead($customer);
    //$head=$db->selectAll($general->table(97),'WHERE hID='.$hID);


    if($hID>0){
        $closingBalance=$acc->headBalance($hID,$from);
    }
    else{
        $closingBalance=0;
    }
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
        'i'=>'',
        'o'=>'',
        'b'=>$general->numberFormat($balance),
    ];
    $tIn=0;
    $tOut=0;
    
    $statement=$acc->head_statement($hID,$from,$to,0,$jArray);
    $jArray[fl()]=$statement;
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
                //'n'=>$customer['name'],
                //'m'=>$customer['mobile'],
                //'h'=>$s['hTitle'],//.' '.$s['veID'],
                'h'=>$s['head_title'].' '.$s['voucher_id'],
                'd'=>$general->make_date($s['time'],'time'),
                'p'=>$s['note'],
                'u'=>@$users[$s['createdBy']]['username'],
                'i'=>$general->numberFormat($s['in']),
                'o'=>$general->numberFormat($s['out']),
                'b'=>$general->numberFormat($balance),
            ];
        }
    }
    $rData[]=[
        's'=>'',
        //'n'=>['t'=>'Total','b'=>1],
        //'m'=>['t'=>''],
        'h'=>['t'=>'Total','b'=>1],
        'd'=>['t'=>''],
        'p'=>['t'=>''],
        'u'=>['t'=>''],
        'i'=>['t'=>$general->numberFormat($tIn),'b'=>1],
        'o'=>['t'=>$general->numberFormat($tOut),'b'=>1],
        'b'=>['t'=>$general->numberFormat($balance),'b'=>1],
    ];

    
    $fileName='customerStat'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'customerStatement-'.date('d-m-Y'),
        'title'     => 'customer Statement',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"              ,'key'=>'s'     ,'hw'=>5),
            //array('title'=>"Customer Name"    ,'key'=>'n'),
            //array('title'=>"Mobile"         ,'key'=>'m'),
            array('title'=>"Head"           ,'key'=>'h'     ,'hw'=>15),
            array('title'=>"Date Time"      ,'key'=>'d'),
            array('title'=>"Particular"     ,'key'=>'p'),
            array('title'=>"User"           ,'key'=>'u'),
            array('title'=>"Debit"           ,'key'=>'i'    ,'al'=>'r'),
            array('title'=>"Credit"         ,'key'=>'o'     ,'al'=>'r'),
            array('title'=>"Balance"        ,'key'=>'b'    ,'al'=>'r'),

        ),
        'data'=>$rData
    ];
    if(isset($from_app)){
        unset($report_data['fileName']);
    }
    $jArray[fl()]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;   
    $general->jsonHeader($jArray);
}
}