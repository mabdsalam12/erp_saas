<?php
$dRange=$_POST['dRange'];
$reportInfo=['Date :'.$dRange];
$general->getFromToFromString($dRange,$from,$to);
$base_id  = intval($_POST['base_id']);
$customer_id  = intval($_POST['customer_id']);
$q=[];
if($customer_id>0){
    $q[]='id='.$customer_id;
}
if($base_id>0){
    $q[]='base_id='.$base_id;
}
$sq='';
if(!empty($q)){
    $sq='where '.implode(' and ',$q);
}
$customers=$db->selectAll('customer',$sq);

$ledger_ids=[];
foreach($customers as $c){
    $ledger_id=$acc->getCustomerHead($c);
    if($ledger_id){
        $ledger_ids[$c['id']]=$ledger_id;
    }
}
$opening_balance=[];
$closing_balance=[];
if(!empty($ledger_ids)){
    $opening_balance=$acc->headBalance($ledger_ids,$from-1,0,['groupByHID'=>1]);
    $closing_balance=$acc->headBalance($ledger_ids,$to,0,['groupByHID'=>1]);
    $jArray[fl()]=$opening_balance;
    $jArray[fl()]=$closing_balance;
}
else{
    $jArray[fl()]=1;
}

if(!empty($opening_balance)){
    $general->arrayIndexChange($opening_balance,'ledger_id');
}
if(!empty($closing_balance)){
    $general->arrayIndexChange($closing_balance,'ledger_id');
}
$all_entry=$db->selectAll('a_ledger_entry','where ledger_id in('.implode(',',$ledger_ids).') and time between '.$from.' and '.$to);
$zero_date=[];
$head_wise_entry=[];
if(!empty($all_entry)){
    foreach($all_entry as $e){
        $ledger_id=$e['ledger_id'];
        if(!isset($head_wise_entry[$ledger_id])){
            $head_wise_entry[$ledger_id]=$opening_balance[$ledger_id]['balance']??0;;
        }
        $head_wise_entry[$ledger_id]+=$e['debit'];
        $head_wise_entry[$ledger_id]-=$e['credit'];
        // $jArray[fl()][]=[
        //     $opening_balance[$ledger_ids[$c['id']]]['balance'],
        //     $head_wise_entry
        //     ,$e];
        if($head_wise_entry[$ledger_id]<=0){
            $zero_date[$ledger_id]=$e['time'];
        }
    }
}
$jArray[fl()]=$head_wise_entry;


$rData=[];
$serial=1;
foreach($customers as $c){
    $base=$smt->base_info_by_id($c['base_id']);
    $ledger_id=$ledger_ids[$c['id']];
    $opening=$opening_balance[$ledger_id]['balance']??0;
    $closing=$closing_balance[$ledger_id]['balance']??0;
    $jArray[fl()][]=$opening;
    if(isset($zero_date[$ledger_id])){
        $z=$general->make_date($zero_date[$ledger_id]);
    }
    else{
        $z='';
    }
    $rData[]=[
    's'=>$serial++,
    'b'=>$base['title'],
    'n'=>$c['name'],
    'o'=>$general->numberFormat($opening,0),
    'z'=>$z,
    'c'=>$general->numberFormat($closing,0),
    ];
}


$fileName='zero_balance_customer_list_'.TIME.rand(0,999).'.txt';
$report_data=array(
    'name'      => 'saleReport',
    'title'     => 'Sale List',
    'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>array(
        array('title'=>"SL"                 ,'key'=>'s','hw'=>5),
        array('title'=>"Base"               ,'key'=>'b'),
        array('title'=>"Customer"           ,'key'=>'n'),
        array('title'=>"Opening balance"    ,'key'=>'o','al'=>'r'),
        array('title'=>"Zero balance date"  ,'key'=>'z','al'=>'r'),
        array('title'=>"Closing balance"    ,'key'=>'c','al'=>'r'),
    ),
    'data'=>$rData
);
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;