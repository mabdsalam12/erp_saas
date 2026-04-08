<?php
    $dRange = $_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $base = $db->selectAll('base');
    $rData=[];
    $sr=1;
    if(!empty($base)){
        foreach($base as $b){
            $rData[]=[
                's'=>$sr++,
                'bn'=>$b['code'],
                'pr'=>'',
                'dr'=>'',
                'cd'=>'',
                'bd'=>'',
                'yd'=>'',
                'tcn'=>'',
            ];
        }
    }
    $rData[]=[
        's'=>['t'=>''],
        'bn'=>['t'=>'Total','b'=>1],
        'pr'=>['t'=>''],
        'dr'=>['t'=>''],
        'cd'=>['t'=>''],
        'bd'=>['t'=>''],
        'yd'=>['t'=>''],
        'tcn'=>['t'=>''],
    ];


    $head=[
        ['title'=>'SL'                      ,'key'=>'s','hw'=>5],
        ['title'=>'Base No'                 ,'key'=>'bn','hw'=>5],
        ['title'=>'Product Return'          ,'key'=>'pr' ,'al'=>'r'],
        ['title'=>'Damage Return'           ,'key'=>'dr' ,'al'=>'r'],
        ['title'=>'Collection Discount'     ,'key'=>'cd' ,'al'=>'r'],
        ['title'=>'Bad Debt'                ,'key'=>'bd' ,'al'=>'r'],
        ['title'=>'Yearly Discount'          ,'key'=>'yd' ,'al'=>'r'],
        ['title'=>'Total Credit note'       ,'key'=>'tcn' ,'al'=>'r'],
    ];
    $fileName='credit_note_report'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'credit_note_report',
        'title'     => 'Credit note report',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;