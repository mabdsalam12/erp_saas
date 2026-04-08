<?php
$dRange = $_POST['dRange'];
$reportInfo=['Date: '.$dRange];
$type=intval($_POST['type']);
$only_cash_chart=intval($_POST['only_cash_chart']);
$without_zero_transaction=intval($_POST['without_zero_transaction']);
$without_zero=intval($_POST['without_zero']);
$general->getFromToFromString($dRange,$from,$to);
$master_accounts = $db->selectAll('a_master_account',"order by code",'id,title,code');
$general->arrayIndexChange($master_accounts);
$charts_accounts = $db->selectAll('a_charts_accounts',"order by code",'id,code,title,master_account_id');
$general->arrayIndexChange($charts_accounts);
$q=['isActive=1'];;
if($type>0){
    $reportInfo[]='Type: '.$acc->get_ledger_types()[$type]['title']??'';
    $q[]="type=$type";
}
$ledgers = $db->selectAll('a_ledgers',"where ".implode(' and ',$q).' order by code','id,code,title,charts_accounts_id');
$opCheck=strtotime('-1 second',$from);
$ledgers_data_query = "SELECT 
    ledger_id, 
    SUM(CASE WHEN time BETWEEN 0 AND $opCheck THEN debit ELSE 0 END) - 
    SUM(CASE WHEN time BETWEEN 0 AND $opCheck THEN credit ELSE 0 END) AS balance, 
    SUM(CASE WHEN time BETWEEN $from AND $to THEN debit ELSE 0 END) AS debit, 
    SUM(CASE WHEN time BETWEEN $from AND $to THEN credit ELSE 0 END) AS credit
FROM a_ledger_entry 
WHERE time BETWEEN 0 AND $to
GROUP BY ledger_id;";

$main_data=[];
$rData=[];
$total_balance=0;
$total_debit=0;
$total_credit=0;
$total_net_balance=0;
$total_closing_balance=0;
foreach($master_accounts as $ma){
    $main_data[$ma['id']]=[];
}
foreach($charts_accounts as $ma){
    $main_data[$ma['master_account_id']][$ma['id']]=[];
}
$jArray[fl()]=$$ledgers_data_query;
$company_data = $db->get_company_data();
$cash_and_cash_equivalent=$company_data['chart_of_account']['cash-and-cash-equivalent'];
$cash_master_account_id = 0;
if(!empty($ledgers)){
    $all_total=[];
    $ledgers_data = $db->fetchQuery($ledgers_data_query,'array',$jArray);
    $general->arrayIndexChange($ledgers_data,'ledger_id');
    foreach($ledgers as $l){
        $charts_account = $charts_accounts[$l['charts_accounts_id']];
        if($only_cash_chart==1 && $l['charts_accounts_id']!=$cash_and_cash_equivalent){
            $cash_master_account_id=$charts_account['master_account_id'];
            continue;
        }
        if(!isset($main_data[$charts_account['master_account_id']])){
            $main_data[$charts_account['master_account_id']]=[];
        }
        if(!isset($main_data[$charts_account['master_account_id']][$l['charts_accounts_id']])){
            $main_data[$charts_account['master_account_id']][$l['charts_accounts_id']]=[];
        }
        if(!isset($all_total[$charts_account['master_account_id']])){
            $all_total[$charts_account['master_account_id']]=[
                'opening'=>0,
                'debit'=>0,
                'credit'=>0,
                'chart_accounts'=>[]
            ];
        }
        if(!isset($all_total[$charts_account['master_account_id']]['chart_accounts'][$l['charts_accounts_id']])){
            $all_total[$charts_account['master_account_id']]['chart_accounts'][$l['charts_accounts_id']]=[
                'opening'=>0,
                'debit'=>0,
                'credit'=>0,
            ];
        }
        $data = $ledgers_data[$l['id']]??['balance'=>0,'debit'=>0,'credit'=>0];
        $all_total[$charts_account['master_account_id']]['opening']+=$data['balance'];
        $all_total[$charts_account['master_account_id']]['debit']+=$data['debit'];
        $all_total[$charts_account['master_account_id']]['credit']+=$data['credit'];

        $all_total[$charts_account['master_account_id']]['chart_accounts'][$l['charts_accounts_id']]['opening']+=$data['balance'];
        $all_total[$charts_account['master_account_id']]['chart_accounts'][$l['charts_accounts_id']]['debit']+=$data['debit'];
        $all_total[$charts_account['master_account_id']]['chart_accounts'][$l['charts_accounts_id']]['credit']+=$data['credit'];

        $main_data[$charts_account['master_account_id']][$l['charts_accounts_id']][]=$l;
    }
    
    // $jArray[fl()]=$main_data;
    foreach($main_data as $master_account_id=>$master_data){
        if($only_cash_chart==1 && $master_account_id!=$cash_master_account_id){
            continue;
        }
        $master_account = $master_accounts[$master_account_id];
        $master_total=$all_total[$master_account_id]??['opening'=>0,'debit'=>0,'credit'=>0];
        $net_balance = $master_total['debit']-$master_total['credit'];
        $closing_balance = $master_total['opening']+$net_balance;
        $rData[]=[
            's'=>['t'=>$charts_account['code'].' '.$master_account['title'],'col'=>2,'b'=>1],
            't'=>false,
            'op'=>['t'=>$general->numberFormat($master_total['opening']),'b'=>1],
            'dr'=>['t'=>$general->numberFormat($master_total['debit']),'b'=>1],
            'cr'=>['t'=>$general->numberFormat($master_total['credit']),'b'=>1],
            'nt'=>['t'=>$general->numberFormat($net_balance),'b'=>1],
            'cl'=>['t'=>$general->numberFormat($closing_balance),'b'=>1],
        ];
        // $rData[]=[
        //     's'=>['t'=>'a','col'=>7,'b'=>1],
        //     't'=>false,
        //     'op'=>false,
        //     'dr'=>false,
        //     'cr'=>false,
        //     'nt'=>false,
        //     'cl'=>false,
        // ];
        foreach($master_data as $charts_accounts_id=>$charts_account_data){
            if($only_cash_chart==1 && $charts_accounts_id!=$cash_and_cash_equivalent){
                
                continue;
            }
            
            $charts_account = $charts_accounts[$charts_accounts_id];
            $charts_account_total = $master_total['chart_accounts'][$charts_accounts_id]??['opening'=>0,'debit'=>0,'credit'=>0];
            $net_balance = $charts_account_total['debit']-$charts_account_total['credit'];
            $closing_balance = $charts_account_total['opening']+$net_balance;
            $rData[]=[
                's'=>['t'=>$charts_account['code'].' '.$charts_account['title'],'col'=>2,'b'=>1],
                't'=>false,
                'op'=>['t'=>$general->numberFormat($charts_account_total['opening']),'b'=>1],
                'dr'=>['t'=>$general->numberFormat($charts_account_total['debit']),'b'=>1],
                'cr'=>['t'=>$general->numberFormat($charts_account_total['credit']),'b'=>1],
                'nt'=>['t'=>$general->numberFormat($net_balance),'b'=>1],
                'cl'=>['t'=>$general->numberFormat($closing_balance),'b'=>1],
            ];
            // $rData[]=[
            //     's'=>['t'=>' ','col'=>7,'b'=>1],
            //     't'=>false,
            //     'op'=>false,
            //     'dr'=>false,
            //     'cr'=>false,
            //     'nt'=>false,
            //     'cl'=>false,
            // ];
            $sr=1;
            
            foreach($charts_account_data as $l){
                $ledger_id = $l['id'];
                $data = $ledgers_data[$ledger_id]??['balance'=>0,'debit'=>0,'credit'=>0];
                
                $net_balance = $data['debit']-$data['credit'];
                $closing_balance = $data['balance']+$net_balance;
                
                if($charts_accounts_id!=10){
                    if($without_zero==1){
                        if($data['balance']==0 && $data['debit']==0 && $data['credit']==0){
                            continue;
                        }
                    }
                    if($without_zero_transaction==1){
                        if($data['debit']==0 && $data['credit']==0){
                            continue;
                        }
                    }
                    $pre_data=[
                        's'=>$sr++,
                        't'=>$l['code'].' '.$l['title'],
                        'op'=>$general->numberFormat($data['balance']),
                        'dr'=>$general->numberFormat($data['debit']),
                        'cr'=>$general->numberFormat($data['credit']),
                        'nt'=>$general->numberFormat($net_balance),
                        'cl'=>$general->numberFormat($closing_balance),
                    ];
                    $rData[]=$pre_data;
                }
                $total_balance+=$data['balance'];
                $total_debit+=$data['debit'];
                $total_credit+=$data['credit'];
                $total_net_balance+=$net_balance;
                $total_closing_balance+=$closing_balance;
            }
            

        }
    }
    

}
$rData[]=[
    's'=>['t'=>''],
    't'=>['t'=>''],
    'op'=>['t'=>$general->numberFormat($total_balance),'b'=>1],
    'dr'=>['t'=>$general->numberFormat($total_debit),'b'=>1],
    'cr'=>['t'=>$general->numberFormat($total_credit),'b'=>1],
    'nt'=>['t'=>$general->numberFormat($total_net_balance),'b'=>1],
    'cl'=>['t'=>$general->numberFormat($total_closing_balance),'b'=>1],
];
$head=[
    ['title'=>'#','key'=>'s','w'=>5],
    ['title'=>'Ledger','key'=>'t'],
    ['title'=>'Opening balance','key'=>'op','al'=>'r'],
    ['title'=>'Total debits','key'=>'dr','al'=>'r'],
    ['title'=>'Total credit','key'=>'cr','al'=>'r'],
    ['title'=>'Net movement','key'=>'nt','al'=>'r'],
    ['title'=>'Closing balance','key'=>'cl','al'=>'r'],
];
$fileName='general-ledger-summary'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'general-ledger-summary',
    'title'     => 'General ledger summary',
    'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'      =>$head,
    'data'      =>$rData
];
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;

