<?php 

$statement=$acc->headStatement(0,$hID,$from,$to,$jArray);
$jArray[fl()]=$statement;
$rData=[];
$sr=1;
$balance = 0;
$tIn = 0;
$tOut = 0;
if(!empty($statement)){
    $users = $db->allUsers();
    foreach($statement as $s){
        $tIn+=$s['in'];
        $tOut+=$s['out'];
        $balance+=$s['in'];
        $balance-=$s['out'];
        $rData[]=[
            's'=>$sr++,
            'd'=>$general->make_date($s['time'],'time'),
            'r'=>'',
            'n'=>$s['note'],
            'cb'=>$users[$s['createdBy']]['name']??'',
            'de'=>$general->numberFormat($s['in']),
            'cr'=>$general->numberFormat($s['out']),
            'b'=>$general->numberFormat($balance),
        ];
    }
}
$rData[]=[
    's'=>['t'=>''],
    'd'=>['t'=>'Total','b'=>1],
    'r'=>['t'=>''],
    'n'=>['t'=>''],
    'cb'=>['t'=>''],
    'de'=>['t'=>$general->numberFormat($tIn)],
    'cr'=>['t'=>$general->numberFormat($tOut)],
    'b'=>['t'=>$general->numberFormat($balance)],
];

$fileName='ledgerStatement'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'LedgerStatement'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
    'title'     => 'Ledger Statement',
    //'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>[
        ['title'=>"S/N"          ,'key'=>'s'],
        ['title'=>"Date Time"  ,'key'=>'d'],
        ['title'=>"Ref No:" ,'key'=>'r'],
        ['title'=>"Particular"       ,'key'=>'n'],
        ['title'=>"Create by"  ,'key'=>'cb'],
        ['title'=>"Debit"         ,'key'=>'de'    ,'al'=>'r'],
        ['title'=>"Credit"        ,'key'=>'cr'    ,'al'=>'r'],
        ['title'=>"Balance"    ,'key'=>'b'    ,'al'=>'r'],
    ],
    'data'=>$rData
];
//$jArray[__LINE__]=$report_data;
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;   

$jArray['m']=show_msg('y');
$general->jsonHeader($jArray);