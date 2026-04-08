<?php
$supplier_id = intval($_POST['supplier_id']);
$dRange = $_POST['dRange'];
$reportInfo = ['Date: '.$dRange];
$general->getFromToFromString($dRange,$from,$to);
$q=['isActive in(1,0)'];
if($supplier_id>0){
    $q[] = 'id='.$supplier_id;
}
$rData=[];
$sr=1;
$suppliers = $db->selectAll('suppliers','where '.implode(' and ',$q),'code,name,id,ledger_id');
$total_opening = 0;
$total_debit = 0;
$total_credit = 0;
$total_due = 0;
if(!empty($suppliers)){
    $general->getIDsFromArray($suppliers,'ledger_id',$ledger_ids);
    $opening_balance = $db->selectAll(
        'a_ledger_entry',
        'where ledger_id in('.implode(',',$ledger_ids).') and time<'.$from.' GROUP by ledger_id',
        'sum(debit)-SUM(credit) as b,ledger_id',
        'array',
        $jArray
    );
    $general->arrayIndexChange($opening_balance,'ledger_id');
    $debit_credit = $db->selectAll(
        'a_ledger_entry',
        'where ledger_id in('.implode(',',$ledger_ids).') and time between '.$from.' and '.$to.' GROUP by ledger_id',
        'sum(debit) as debit, sum(credit) as credit, ledger_id',
        'array',
        $jArray
    );
    $general->arrayIndexChange($debit_credit,'ledger_id');
    foreach($suppliers as $s){
        
        $total_opening += floatval($opening_balance[$s['ledger_id']]['b']??0);
        $total_debit += floatval($debit_credit[$s['ledger_id']]['debit']??0);
        $total_credit += floatval($debit_credit[$s['ledger_id']]['credit']??0);
        $total_due += floatval($opening_balance[$s['ledger_id']]['b']??0)+($debit_credit[$s['ledger_id']]['debit']??0)-($debit_credit[$s['ledger_id']]['credit']??0);
        
        $rData[]=[
            //'s'=>$sr++,
            'n'=>$s['code'].' '.$s['name'],
            'o'=>$general->numberFormat($opening_balance[$s['ledger_id']]['b']??0),
            'd'=>$general->numberFormat($debit_credit[$s['ledger_id']]['debit']??0),
            'c'=>$general->numberFormat($debit_credit[$s['ledger_id']]['credit']??0),
            'b'=>$general->numberFormat(($opening_balance[$s['ledger_id']]['b']??0)+($debit_credit[$s['ledger_id']]['debit']??0)-($debit_credit[$s['ledger_id']]['credit']??0)),
        ];
    }
}
$general->arraySortByColumn($rData,'n');
    foreach($rData as $k=>$v){
        $rData[$k]['s']=$k+1;
    }
$rData[]=[
    's'=>'',
    'n'=>['t'=>'Total','b'=>1],
    'o'=>['t'=>$general->numberFormat($total_opening),'b'=>1],
    'd'=>['t'=>$general->numberFormat($total_debit),'b'=>1],
    'c'=>['t'=>$general->numberFormat($total_credit),'b'=>1],
    'b'=>['t'=>$general->numberFormat($total_due),'b'=>1],
];
$head=[
    ['title'=>"#"                  ,'key'=>'s','hw'=>5],
    ['title'=>"Vendor Name"        ,'key'=>'n'],
    ['title'=>"Opening"            ,'key'=>'o','al'=>'r'],
    ['title'=>"Payment"           ,'key'=>'d','al'=>'r'],
    ['title'=>"Purchase"          ,'key'=>'c','al'=>'r'],
    ['title'=>"Due Balance"        ,'key'=>'b','al'=>'r'],
];
$fileName='account_payable_summary_'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'account_payable_summary',
    'title'     => 'account payable summary',
    'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>$head,
    'data'=>$rData
];
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;