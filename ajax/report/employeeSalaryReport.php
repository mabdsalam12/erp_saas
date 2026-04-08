<?php
$dRange = $_POST['dRange'];
$eID    = intval($_POST['eID']);
$type   = intval($_POST['type'])==1?1:0;
$reportInfo=['Date :'.$dRange];
$general->getFromToFromStringt($dRange,$from,$to);
$expTypes=[
    V_T_EMPLOYEE_PAY,
    V_T_EMPLOYEE_SALE_SALARY_PAY
];
$expences=$acc->voucherDetails($expTypes,'',$from,$to);
$jArray[__LINE__]=$expences;
$tSalary=0;
if($type==0){
    $empHeads=[];
    $total=0;
    foreach($expences as $ex){
        if(!isset($empHeads[$ex['debit']])){
            $empHeads[$ex['debit']]=0;
        }
        $empHeads[$ex['debit']]+=$ex['amount'];
        $total+=$ex['amount'];
    }
    if(!empty($empHeads)){
        $employees=$db->selectAll($general->table(74),'where hID in('.implode(',',array_keys($empHeads)).')','','array',$jArray);
        $general->arrayIndexChange($employees,'hID');
        $jArray[__LINE__]=$employees;
        $serial=1;
        $tSalary=0;
        foreach($empHeads as $k=>$eh){
            $e=$employees[$k];
            $tSalary+=@$e['eSalary'];
            $diff=@$e['eSalary']-$eh;
            $rData[]=[
                's'=>$serial++,
                'e'=>@$e['eName'],
                'sl'=>@$e['eSalary'],
                'a'=>$general->numberFormat($eh),
                'd'=>$general->numberFormat($diff),
                'st'=>'<a href="'.URL.'/?mdl=headStatement&hID='.$e['hID'].'&scId='.$e['scID'].'" class="btn btn-success" target="_blank">Statement</a>'
            ];
        }
    }
    $rData[]=[
        's' => '',
        'e' => ['t'=>'Total','b'=>1],
        'sl'=> ['t'=>$general->numberFormat($tSalary),'b'=>1],
        'a' => ['t'=>$general->numberFormat($total),'b'=>1],
        'd' => ['t'=>$general->numberFormat($tSalary-$total),'b'=>1],
        'st'=> ''
    ];

    $fileName='empSalaryReport_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'empSalaryReport',
        'title'     => 'Employee Salary Report',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"SN"             ,'key'=>'s'),
            array('title'=>"Employee"       ,'key'=>'e'),
            //array('title'=>"Designation"    ,'key'=>'d'    ,'hw'=>7,'al'=>'r'),
            array('title'=>"Salary"         ,'key'=>'sl'    ,'al'=>'r'),
            array('title'=>"Receive Amount" ,'key'=>'a'     ,'al'=>'r'),
            array('title'=>"Diff"           ,'key'=>'d'     ,'al'=>'r'),
            array('title'=>"Statment"       ,'key'=>'st'    ,'al'=>'r'),

        ),
        'data'=>$rData
    );
}
else{
    $empHeads=[];
    $total=0;
    $serial=1;
    $eIDs=[];
    if(!empty($expences)){
        foreach($expences as $ex){
            $eIDs[$ex['debit']]=$ex['debit'];
        }
        $employees=$db->selectAll($general->table(74),'where hID in('.implode(',',$eIDs).')','','array',$jArray);
        $general->arrayIndexChange($employees,'hID');
        foreach($expences as $ex){
            $e=$employees[$ex['debit']];
            if($eID>0){
                if($e['eID']!=$eID){continue;}
            }
            
            $rData[]=[
                's'=>$serial++,
                'd'=>$general->make_date($ex['veTime'],'time'),
                'e'=>@$e['eName'],
                'p'=>$ex['particular'],
                'a'=>$general->numberFormat($ex['amount']),
            ];
            $total+=$ex['amount'];
        }    
    }    
    $rData[]=[
        's' => '',
        'd' => ['t'=>'Total','b'=>1,'col'=>3],
        'e'=>['t'=>false],
        'p'=>['t'=>false],
        'a' => ['t'=>$general->numberFormat($total),'b'=>1]
    ];

    $fileName='empSalaryReport_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'empSalaryReport',
        'title'     => 'Employee Salary Report',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"SN"             ,'key'=>'s'),
            array('title'=>"Date"           ,'key'=>'d'),
            array('title'=>"Employee"       ,'key'=>'e'),
            array('title'=>"Particular"     ,'key'=>'p'),
            array('title'=>"Amount" ,'key'=>'a'     ,'al'=>'r')
        ),
        'data'=>$rData
    );
}
$jArray[__LINE__]=$report_data;
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;
?>