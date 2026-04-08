<?php 
$statement=$acc->headStatement(0,$hID,$from,$to,$jArray);
$balance = 0;
$tIn = 0;
$tOut = 0;
$report_data =[];
if(!empty($statement)){
    foreach($statement as $row){
        if(!isset($report_data[$row["ledger_id"]])){
            $report_data[$row["ledger_id"]] = [
                "title"=> $row['head_title'],
                "in"=> 0,
                "out"=> 0,
            ];
        }
        $tIn += $row['in'];
        $tOut += $row['out'];
        $report_data[$row["ledger_id"]]["in"]+=$row['in'];
        $report_data[$row["ledger_id"]]["out"]+=$row['out'];
    }
    $openingBalance=$acc->headBalance(array_keys($report_data),$from,0,['groupByHID'=>1],$jArray);
    $general->arrayIndexChange($openingBalance,'ledger_id');
}
$rData=[];
$s=1;
if(!empty($report_data)){
    foreach($report_data as $ledger_id=>$row){
        $opening = $openingBalance[$ledger_id]['balance']??0;
        
        $balance+=$opening;
        $balance+=$row['in'];
        $balance-=$row['out'];
        $rData[]=[
            's'=>$s++,
            't'=>$row['title'],
            'op'=> $general->numberFormat($opening),
            'in'=> $general->numberFormat($row['in']),
            'out'=> $general->numberFormat($row['out']),
            'b'=> $general->numberFormat($balance),
        ];
    }
}
$rData[]=[
    's'=>'',
    't'=>['t'=>'Total','b'=>1],
    'op'=>['t'=>''],
    'in'=>['t'=>$general->numberFormat($tIn)],
    'out'=>['t'=>$general->numberFormat($tOut)],
    'b'=>['t'=>$general->numberFormat($balance)],
];
$fileName='purRep_'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'LedgerStatement'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
    'title'     => 'Ledger Statement Summarize',
    //'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>[
        ['title'=>"#"          ,'key'=>'s','w'=>5],
        ['title'=>"Ledger"          ,'key'=>'t'],
        ['title'=>"Opining"          ,'key'=>'op'],
        ['title'=>"In"          ,'key'=>'in','al'=>'r'],
        ['title'=>"Out"          ,'key'=>'out','al'=>'r'],
        ['title'=>"Balance"          ,'key'=>'b','al'=>'r'],
        
    ],
    'data'=>$rData
];
//$jArray[__LINE__]=$report_data;
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1; 