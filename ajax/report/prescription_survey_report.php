<?php
$dRange = $_POST['dRange'];
$reportInfo=['Date: '.$dRange];
$general->getFromToFromString($dRange,$from,$to);
$base_id_src = intval($_POST['base_id']);
$doctor_id = intval($_POST['doctor_id']);
$report_type = intval($_POST['report_type']);
if($report_type==2){              

    // Convert timestamps to DateTime objects
    $start = (new DateTime())->setTimestamp($from);
    $end = (new DateTime())->setTimestamp($to);

    // Add one day to the end date to include it in the range
    $end->modify('+1 day');

    // Loop through the range
    $interval = new DateInterval('P1D'); // 1 day interval
    $dateRange = new DatePeriod($start, $interval, $end);
    $dateArray=[];
    foreach ($dateRange as $date) {
        $dateArray[]= $date->format("d-m");
    } 
}

$q=["entry_time between $from and $to"]; 

if($doctor_id>0){
    $q[]="doctor_id=$doctor_id";
}

$visits = $db->selectAll('doctor_visit',"where ".implode(' and ',$q));
$report_data=[];
if(!empty($visits)){

    $doctor_ids=[];
    $doctor_data=[];
    foreach($visits as $v){
        $doctor_ids[$v['doctor_id']]=$v['doctor_id'];
        if($report_type==1){
            if(!isset($doctor_data[$v['doctor_id']])){
                $doctor_data[$v['doctor_id']]=[
                    'total_prescription'=>0,
                    'total_visit'=>0,
                ];
            }

            $doctor_data[$v['doctor_id']]['total_prescription']+=intval($v['prescription']);
            $doctor_data[$v['doctor_id']]['total_visit']+=1; 
        }
        else{
            if(!isset($doctor_data[$v['doctor_id']])){
                $doctor_data[$v['doctor_id']]=[];
            }
            if(!isset($doctor_data[$v['doctor_id']][date("d-m", $v['entry_time'])])){
                $doctor_data[$v['doctor_id']][date("d-m", $v['entry_time'])]=0;
            }
            $doctor_data[$v['doctor_id']][date("d-m", $v['entry_time'])]+= ($v['prescription']);
        }

    }

   // $jArray[fl()]=$doctor_data;

    if(!empty($doctor_ids)){
        $doctors = $db->selectAll('doctor','where id in('.implode(',',$doctor_ids).')','id,name,code,base_id,address');
        $general->arrayIndexChange($doctors);
        $base_ids=[];
        foreach($doctors as $d){
            $base_ids[$d['base_id']]=$d['base_id'];
            if($report_type==1){
                if(!isset($report_data[$d['base_id']])){
                    $report_data[$d['base_id']]=[];
                }
                $report_data[$d['base_id']][$d['id']]= $doctor_data[$d['id']]; 
            }else{
                if($base_id_src>0){
                    if($d['base_id']!=$base_id_src){
                        unset($doctor_data[$d['id']]);
                    }
                }
            }

        }

        $bases = $db->selectAll('base','where id in('.implode(',',$base_ids).')','id,title,code');
        $general->arrayIndexChange($bases);

    }
} 

$rData=[];
$total=1;  
$total_prescription=0;
$total_visit=0;
if($report_type==1){
    if(!empty($report_data)){  
        foreach($report_data as $base_id=>$doc){  

            if($base_id_src>0&&$base_id_src!=$base_id){
                continue;
            }

            $base=$bases[$base_id];
            $count=count($doc);
            $sr=0;
            foreach($doc as $doctor_id=>$dd){
                $data=[];
                $total_prescription+=$dd['total_prescription'];
                $total_visit+=$dd['total_visit'];
                if(!$sr){
                    $data['s']=['t'=>$total++,'row'=>$count,'al'=>'c'];
                    $data['b']=['t'=>$base['title'],'row'=>$count,'al'=>'c'];    
                    $data['d']=['t'=>$doctors[$doctor_id]['code'].' '.$doctors[$doctor_id]['name']];
                    $data['p']=['t'=>$dd['total_prescription']];
                    $data['v']=['t'=>$dd['total_visit'],'al'=>'r'];
                }
                else{
                    $data['s']=false;
                    $data['b']=false;   
                    $data['d']=['t'=>$doctors[$doctor_id]['code'].' '.$doctors[$doctor_id]['name']];
                    $data['p']=['t'=>$dd['total_prescription']];
                    $data['v']=['t'=>$dd['total_visit'],'al'=>'r'];
                }
                $sr=1;
                $rData[]=$data;
            }  
        }   
    }
    $rData[]=[
        's'=>['t'=>'Total','b'=>1],
        'b'=>['t'=>''],
        'd'=>['t'=>''],
        'p'=>['t'=>$total_prescription,'b'=>1],
        'v'=>['t'=>$total_visit,'b'=>1], 


    ]; 
}
else{
    if(!empty($doctor_data)){
        $sl=1;
        $jArray[fl()]=$dateArray;
        foreach($doctor_data as $doctor_id=>$dd){
            $data['s']=['t'=>$sl++,'al'=>'c'];
            $data['d']=['t'=>$doctors[$doctor_id]['name'].'-'.$doctors[$doctor_id]['address']];

            $a=1;
            $total=0;
            foreach($dateArray as $date){
                $prescription=0;
                if(isset($dd[$date])){
                    $prescription =$dd[$date];
                }
                $total+=$prescription;
                $data[$a]=['t'=>$prescription,'al'=>'r'];    

                $a++;
            }
            $data['t']=['t'=>$total,'al'=>'r'];
            $rData[]=$data;
        }
    }
}


if($report_type==2){
    // $rData=[];
    $fileName='prescription_survey_report_details_'.TIME.rand(0,999).'.txt';
    $head =[
        ['title'=>"SL"              ,'key'=>'s','hw'=>5], 
        ['title'=>"Doctor Name"     ,'key'=>'d'],
        ['title'=>"Total"           ,'key'=>'t','al'=>'r'], 
    ];
    $a=1;
    foreach($dateArray as $date){
        $head[]= ['title'=>$date     ,'key'=>$a];
        $a++;
    }
    $report_data=[
        'name'      => 'prescription_survey_report_details',
        'title'     => 'Prescription Survey Report (details)',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    ];
}
else{
    $fileName='prescription_survey_report_summarised_'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'prescription_survey_report_summarised',
        'title'     => 'Prescription Survey Report (Summarised)',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>[
            ['title'=>"SL"              ,'key'=>'s','hw'=>5],
            ['title'=>"Name of Base"    ,'key'=>'b','hw'=>15],
            ['title'=>"Doctor Name"     ,'key'=>'d'],
            ['title'=>"No. of Prescription",'key'=>'p','al'=>'r'],
            ['title'=>"No. of Visit"    ,'key'=>'v','al'=>'r'], 

        ],
        'data'=>$rData
    ]; 
}

$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;



?>
