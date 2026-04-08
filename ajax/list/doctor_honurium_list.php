<?php
$date_range = $_POST['date_range'];
$general->getFromToFromString($date_range, $fromDate, $toDate);
$base_id = intval($_POST['base_id']);
$doctor_id = intval($_POST['doctor_id']);
$q=[];
$q[]='date between '.$fromDate.' and '.$toDate;

if($base_id>0){
    $q[]='base_id='.$base_id;
}
if($doctor_id>0){
    $q[]='doctor_id='.$doctor_id;
}
$rData=[];
$total=0;
$honorariums = $db->selectAll('doctor_honurium','where '.implode(' and ',$q));
if(!empty($honorariums)){
    $doctor_ids=[];
    foreach($honorariums as $h){
        $doctor_ids[$h['doctor_id']]=$h['doctor_id'];
    }
    $doctors=$db->selectAllByID('doctor','id',$doctor_ids);
    $serial=0;
    foreach($honorariums as $h){
        $total+=$h['amount'];
        $rData[]=[
            's'=>$serial++,
            'dc'=>$doctors[$h['doctor_id']]['name'],
            'd'=>$general->make_date($h['date']),
            'n'=>$h['note'],
            'a'=>$general->numberFormat($h['amount'])
        ];
    }
}


    $rData[]=[
            's' => '',
            'dc' => ['t'=>'Total','b'=>1],
            'd' => '',
            'n' => '',
            'a' => ['t'=>$general->numberFormat($total),'b'=>1],
        ];

    $fileName='doctor_honurium'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'doctor_honurium',
        'title'     => 'Doctor Honorarium List',
        'fileName'  => $fileName,
        'head'=>[
            ['title'=>"#"              ,'key'=>'s','hw'=>5],
            ['title'=>"Doctor"         ,'key'=>'dc'],
            // ['title'=>"Payment method" ,'key'=>'m'],
            ['title'=>"Date"           ,'key'=>'d'],
            ['title'=>"note"           ,'key'=>'n'],
            ['title'=>"Amount"         ,'key'=>'a','al'=>'r'],

        ],
        'data'=>$rData
    ];
    //$jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;


