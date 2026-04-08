<?php
    $dRange = $_POST['dRange']  ;
    $base_id = intval($_POST['base_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $reportInfo=['Date:  '.$dRange];
    $general->getFromToFromString($dRange,$from,$to);
    $rData=[];
    $sr=1;
    $q=["entry_time between $from  and  $to"];
    if($doctor_id>0){
        $q[]="doctor_id= $doctor_id";
    }
    if($base_id>0){
        $doctors = $db->selectAll('doctor','where base_id='.$base_id,'id,code,name,base_id');
        $general->arrayIndexChange($doctors);
        if(!empty($doctors)){
            $q[]='doctor_id in('.implode(',',array_keys($doctors)).')';
        }
        else{
            $q[]='doctor_id=0';
        }
    } 

    $visit_list =$db->selectAll('doctor_visit','where '.implode(' and ',$q));
    if(!empty($visit_list)){
        $doctor_ids=[];
        $user_ids=[];
        foreach($visit_list as $vl){
            $doctor_ids[$vl['doctor_id']]=$vl['doctor_id'];
            $user_ids[$vl['createdBy']]=$vl['createdBy'];
        }
        if($base_id==0){
            $doctors = $db->selectAll('doctor','where id in ('.implode(',',$doctor_ids).')','id,code,name,base_id');
        }
        $general->arrayIndexChange($doctors);
        $user= $db->allUsers('and id in('.implode(',',$user_ids).')');
        $base = $db->selectAll('base');
        $general->arrayIndexChange($base);
        foreach($visit_list as $vl){
            $rData[]=[
                's'=>$sr++,
                'd'=>$general->make_date($vl['entry_time'],'time'),
                'ba'=>$base[$doctors[$vl['doctor_id']]['base_id']]['title']??'',
                'v'=>$doctors[$vl['doctor_id']]['name'],
                'p'=>$vl['prescription'],
                'r'=>$general->content_show($vl['note']),
                'c'=>$user[$vl['createdBy']]['name']??'',
                'de'=>'<button onclick="doctor_visit_details_view('.$vl['id'].')" class="btn btn-success">Details</button>',
            ];
        }
    }
    $head=[
        ['title'=>'SL'          ,'key'=>'s','hw'=>5],
        ['title'=>'Date'        ,'key'=>'d'],
        ['title'=>'Base'        ,'key'=>'ba'],
        ['title'=>'Doctor'      ,'key'=>'v'],
        ['title'=>'Prescription','key'=>'p'],
        ['title'=>'Note'        ,'key'=>'r' ],
        ['title'=>'Created by'  ,'key'=>'c' ],
        ['title'=>'Details'     ,'key'=>'de'],
    ];
    $fileName='doctor_visit_list_'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'doctor_visit_list',
        'title'     => 'doctor visit list',
        'info'      => $reportInfo,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
