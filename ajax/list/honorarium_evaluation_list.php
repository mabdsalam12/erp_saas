<?php
$reportInfo=[];
    $base_id = intval($_POST['base_id']);
    $doctors_id = intval($_POST['doctors_id']);
    $customer_id = intval($_POST['customer_id']);
    $src_month = intval($_POST['src_month']);
    $src_year = intval($_POST['src_year']);

    $base = $db->selectAll('base','','id,title');
    $general->arrayIndexChange($base);
    $doctors = $db->selectAll('doctor','','id,base_id,name');
    $general->arrayIndexChange($doctors);
    $customers = $db->selectAll('customer','where isActive=1','id,name,code,due_day,base_id');
    $general->arrayIndexChange($customers);
    if(!empty($customers)){
        foreach($customers as $k=>$p){
            $p['name']=$p['code'].' '.$p['name'];
            $customers[$k]['name']=$p['name'];
        }
    }


    $q=[];
    if($base_id>0){
        $q[]='base_id='.$base_id;
        $reportInfo[]='Base: '.$base[$base_id]['title'];
    }
    if($doctors_id>0){
        $q[]='doctor_id='.$doctors_id;
        $reportInfo[]='Doctors: '.$doctors[$doctors_id]['name'];
    }
    if($customer_id>0){
        $q[]='customer_id='.$customer_id;
        $reportInfo[]='Customer: '.$customers[$customer_id]['name'];
    }
    if($src_month>0&& $src_year>0){
       $src_date= mktime(0, 0, 0, $src_month, 1, $src_year);
       $q[]=$src_date.' BETWEEN start_date AND end_date';
       $reportInfo[]='Month: '.date('F Y', $src_date);
    }
    
    $sq='';
    if(!empty($q)){
        $sq = 'where '.implode(' and ',$q);
    }
    $honorarium=$db->selectAll('honorarium_evaluation',$sq,'','array',$jArray);
    $jArray[fl()] =  $honorarium;
    $rData=[];
    $total=1;
    if(!empty($honorarium)){
        foreach($honorarium as $u){
            $rData[]=[
                's'=>$total++,
                'base'=>$base[$u['base_id']]['title']??'',
                'doctor'=>$doctors[$u['doctor_id']]['name']??'',
                'customer'=>$customers[$u['customer_id']]['name']??'',
                'contribute'=>$u['contribute'],
                'start_date'=> date('F Y', $u['start_date']),
                'end_date'=>date('F Y', $u['end_date']),
                'edit'=>'<a href="'.URL.'?mdl=honorarium-evaluation&edit='.$u['id'].'" class="btn btn-info">Edit</a>',
            ];
        }
    }
    
    $fileName='honorarium_evaluation_list_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'honorarium_evaluation_list',
        'title'     => 'Honorarium Evaluation List',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"SL"         ,'key'=>'s','hw'=>5),
            array('title'=>"Base"       ,'key'=>'base'),
            array('title'=>"Doctor"     ,'key'=>'doctor'),
            array('title'=>"Customer"   ,'key'=>'customer'),
            array('title'=>"Contribute" ,'key'=>'contribute'),
            array('title'=>"Start Month" ,'key'=>'start_date'),
            array('title'=>"End Month"   ,'key'=>'end_date'),
            array('title'=>"Edit"       ,'key'=>'edit'),
            
        ),
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;

