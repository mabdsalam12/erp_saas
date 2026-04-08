<?php
$list=$db->selectAll('customer_closing_sms','order by id desc');
$rData=[];
if(!empty($list)){
    foreach($list as $l){
        $rData[]=[
            's' => $l['id'],
            'n' => $l['name'],
            'st' => date('d-m-Y',$l['from']),
            'e' => date('d-m-Y',$l['to']),
            'sm' => ($l['sms_send']==1)?'<span class="label label-success">Sent</span>':'<span class="label label-danger">Not sent</span>',
            'dt' => '<a href="'.URL.'?mdl=customer_closing_sms&details='.$l['id'].'" class="btn btn-info">Details</a>'
        ];
    }
}

$fileName='customer_closing_sms_list'.TIME.rand(0,999).'.txt';
$report_data = [
    'name'     => 'customer_closing_sms_list',
    'title'    => 'Customer Closing SMS List',
    'fileName' => $fileName,
    'head'     => [
        ['title' => "#",       'key' => 's',  'hw' => 5],
        ['title' => "Name",    'key' => 'n'],
        ['title' => "Start date",  'key' => 'st'],
        ['title' => "End date", 'key' => 'e'],
        ['title' => "SMS status",'key' => 'sm'],
        ['title' => "Details"  ,'key' => 'dt']
    ],
    'data'     => $rData
];
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;
