<?php
$dRange         = $_POST['dRange']  ;
$reportInfo=["Date:  $dRange"];
$general->getFromToFromString($dRange,$from,$to);
$debit              = intval($_POST['debit']);
$credit             = intval($_POST['credit']);
$q=["time between $from and $to"];
if($debit>0){
    $q[]="debit=$debit";
}
if($credit>0){
    $q[]="credit=$credit";
}
$contra=$db->selectAll('contra_voucher','where '.implode(' and ',$q));
$rData=[];
$total_amount=0;
$total_charge=0;

if(!empty($contra)){
    $serial=1;
    $base = $db->allBase_for_voucher();
    foreach($contra as $c){
        $debit_data=$acc->headInfoByID($c['debit']);
        $credit_data=$acc->headInfoByID($c['credit']);
        $u=$db->userInfoByID($c['createdBy']);

        $total_amount+=$c['amount'];
        $total_charge+=$c['transaction_charge'];
        $data=[
            's' => $serial++,
            'd' => $general->make_date($c['time'],'time'),
            'db'=> $debit_data['title'],
            'cr'=> $credit_data['title'],
            'b' => $base[$c['base_id']]['title']??'',
            'n' => $c['note'],
            'c' => $u['username'],
            'a' => $general->numberFormat($c['amount']),
            't' => $general->numberFormat($c['transaction_charge']),
            'e'=>'<a href="'.URL.'?mdl=contra-voucher&edit='.$c['id'].'" class="btn btn-info">Edit</a>',
            'de'=>'<button onclick="contra_voucher.delete('.$c['id'].')" class="btn btn-danger delete_'.$c['id'].'">Delete</button>',
            'table_tr_id'=>'contra_voucher_'.$c['id']
        ];
        $rData[]=$data;
    }
}
$rData[]=[
    's' => '',
    'd' => ['t'=>'Total','b'=>1,'col'=>6],
    'db'=> ['t'=>false],
    'cr'=> ['t'=>false],
    'b' => ['t'=>false],
    'n' => ['t'=>false],
    'c' => ['t'=>false],
    'a' => ['t'=>$general->numberFormat($total_amount),'b'=>1],
    't' => ['t'=>$general->numberFormat($total_charge),'b'=>1],
    'e' => ['t'=>''],
    'de' => ['t'=>''],
];

$head=[
    ['title'=>'SL'          ,'key'=>'s','hw'=>5],
    ['title'=>'Date'        ,'key'=>'d'],
    ['title'=>'Debit'       ,'key'=>'db'],
    ['title'=>'Credit'      ,'key'=>'cr'],
    ['title'=>'Base'        ,'key'=>'b' ],
    ['title'=>'Note'        ,'key'=>'n' ],
    ['title'=>'Created by'  ,'key'=>'c' ],
    ['title'=>'Amount'      ,'key'=>'a','al'=>'r'],
    ['title'=>'Charge'      ,'key'=>'t','al'=>'r'],
    ['title'=>'Edit'        ,'key'=>'e'],
    ['title'=>'Delete'      ,'key'=>'de'],
];
$fileName='customer_visit_list_'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'  => 'customer_visit_list',
    'title' => 'Customer visit list',
    'info'  => $reportInfo,
    'head'  =>$head,
    'data'  =>$rData
];
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;

