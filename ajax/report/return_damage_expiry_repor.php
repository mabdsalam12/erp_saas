<?php
    $dRange = $_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $rData=[];
    $sr=1;
    
    $rData[]=[
        's'=>['t'=>''],
        'bn'=>['t'=>'Total','b'=>1],
        'sa'=>['t'=>''],
        'ga'=>['t'=>''],
        'h'=>['t'=>''],
        't'=>['t'=>''],
    ];


    $head=[
        ['title'=>'SL'                      ,'key'=>'s','hw'=>5],
        ['title'=>'Base No'                 ,'key'=>'bn','hw'=>5],
        ['title'=>'Sample Amount (TP Value)','key'=>'sa' ,'al'=>'r'],
        ['title'=>'Gift Amount'             ,'key'=>'ga' ,'al'=>'r'],
        ['title'=>'Homorium'                ,'key'=>'h' ,'al'=>'r'],
        ['title'=>'Total'                   ,'key'=>'t' ,'al'=>'r'],
    ];
    $fileName='return_damage_expiry_repor'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'return_damage_expiry_repor',
        'title'     => 'Return, Damage & Expiry Repor ',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;