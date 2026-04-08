<?php
if(!isset($dRange)){
    $dRange = $_POST['dRange'];
    $base_id = intval($_POST['base_id']);
    $customer_id = intval($_POST['customer_id']);
    $paid_type = intval($_POST['paid_type']);
    $pay_type = intval($_POST['pay_type']);
}

$general->getFromToFromString($dRange,$from,$to);
$q=["date between $from and $to"];
$reportInfo=["Date: $dRange"];
if($paid_type==2){
    $reportInfo[]="Paid/Unpaid: Paid";
}
elseif($paid_type==3){
    $reportInfo[]="Paid/Unpaid: Unpaid";
}
if($base_id){
    $reportInfo[]="Base: ".$db->getRowData('base','where id='.$base_id)['title']??'';
    $q[] = "base_id = $base_id";
}
if($customer_id){
    $reportInfo[]="Customer: ".$db->getRowData('customer','where id='.$customer_id)['name']??'';
    $q[] = "customer_id = $customer_id";
}
if($pay_type==PAY_TYPE_CASH||$pay_type==PAY_TYPE_CREDIT||$pay_type==PAY_TYPE_CASH_ON_DELIVERY){
    $reportInfo[]="Pay Type: ".($pay_type==PAY_TYPE_CASH?'Cash':($pay_type==PAY_TYPE_CREDIT?'Credit':'Cash On Delivery'));
    $q[] = "pay_type = $pay_type";
}
$sales = $db->selectAll('sale','where '.implode(' and ',$q),'id,customer_id,date,total,invoice_no,note,pay_type','array',$jArray);
$rData=[];
$sr=1;
$total_amount = 0;
$total_paid = 0;
$total_due = 0;
if(!empty($sales)){
    $customer_ids=[];
    $general->getIDsFromArray($sales,'customer_id',$customer_ids);
    $customers = $db->selectAll('customer','where id in ('.implode(',',$customer_ids).')','id,name,code');
    $general->arrayIndexChange($customers);
    $customer_due_info=[];
    foreach($sales as $s){
        $aged = $general->get_time_difference_in_days($s['date'],TIME);
        
        
        
        if(!isset($customer_due_info[$s['customer_id']])){
            $customer_due_info[$s['customer_id']]=$acc->customer_due_details($s['customer_id']);
        }
        $due_info=$customer_due_info[$s['customer_id']];
        $due_data = $due_info['due_data']??[];
        $general->arrayIndexChange($due_data,'invoice_no');
        $due = $due_data[$s['invoice_no']]['due']??0;
        $paid = $s['total']-$due;
        $status = $due==0?'Paid in full':$aged.' days';
        if($paid_type==2&&$due>0){
            continue;
        }
        elseif($paid_type==3&&$due==0){
            continue;
        }
        $customer = $customers[$s['customer_id']];

        $total_amount+=$s['total'];
        $total_paid+=$paid;
        $total_due+=$due;
        $pt=$s['pay_type']==PAY_TYPE_CASH?'Cash':($s['pay_type']==PAY_TYPE_CREDIT?'Credit':'Cash On Delivery');
        $rData[]=[
            's'=>$sr++,
            'c'=>$customer['code'].' '.$customer['name'],
            'i'=>$s['invoice_no'],
            'pt'=>$pt,
            'n'=>$s['note'],
            'd'=>$general->make_date($s['date']),
            'a'=>$general->numberFormat($s['total']),
            'pa'=>$general->numberFormat($paid),
            'da'=>$general->numberFormat($due),
            'st'=>$status,
        ];

    }
    $jArray[fl()]=$customer_due_info;
}
$rData[]=[
    's'=>['t'=>''],
    'c'=>['t'=>'Total'],
    'i'=>['t'=>''],
    'pt'=>['t'=>''],
    'd'=>['t'=>''],
    'n'=>['t'=>''],
    'a'=>['t'=>$general->numberFormat($total_amount)],
    'pa'=>['t'=>$general->numberFormat($total_paid)],
    'da'=>['t'=>$general->numberFormat($total_due)],
    'st'=>['t'=>''],
];
$head=[
    ['title'=>'SL'                  ,'key'=>'s','hw'=>5],
    ['title'=>'Customer'            ,'key'=>'c'],
    ['title'=>'Invoice No'          ,'key'=>'i'],
    ['title'=>'Invoice Date'        ,'key'=>'d'],
    ['title'=>'Invoice Amount'      ,'key'=>'a' ,'al'=>'r'],
    ['title'=> 'Pay type'           ,'key'=>'pt'],
    ['title'=>'Note'                ,'key'=>'n'],
    ['title'=>'Paid Amount'         ,'key'=>'pa' ,'al'=>'r'],
    ['title'=>'Due Amount'          ,'key'=>'da' ,'al'=>'r'],
    ['title'=>'Aged/Paid in full'   ,'key'=>'st'],
];
$fileName='paid_and_unpaid_invoice_report_'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'paid_and_unpaid_invoice_report',
    'title'     => 'Paid & Unpaid Invoice Report',
    'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>$head,
    'data'=>$rData
];
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);

$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');

$jArray['status']=1;