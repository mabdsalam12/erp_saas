<?php 
$dRange = $_POST['dRange']  ;
$base_id = intval($_POST['base_id']);
$doctor_id = intval($_POST['doctor_id']);
$type = intval($_POST['type']);
$reportInfo=['Date:  '.$dRange];
$general->getFromToFromString($dRange,$from,$to);
$rData=[];
$sr=1;
$total_finished_value=0;
$total_gift_value=0;
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
    $general->getIDsFromArray($visit_list,'id,doctor_id',$ids,$doctor_ids);
    $doctors = $db->selectAll('doctor','where id in('.implode(',',$doctor_ids).')','id,name');
    $general->arrayIndexChange($doctors);
    $finished_product = $db->selectAll('doctor_visit_finished_product','where doctor_visit_id in('.implode(',',$ids).')');
    $finished_product_data=[];
    if(!empty($finished_product)){
        foreach($finished_product as $fp){
            $finished_product_data[$fp['doctor_visit_id']][]=$fp;
        }
    }
    $product_ids=[];
    $general->getIDsFromArray($finished_product,'product_id',$product_ids);
    $gift_product = $db->selectAll('doctor_visit_gift_product','where doctor_visit_id in('.implode(',',$ids).')');
    $gift_product_data=[];
    if(!empty($gift_product)){
        foreach($gift_product as $fp){
            $gift_product_data[$fp['doctor_visit_id']][]=$fp;
        }
    }
    $general->getIDsFromArray($gift_product,'product_id',$product_ids);
    $product_details=[];
    if(!empty($product_ids)){
        $product_details = $db->getProductData(' and id in('.implode(',',$product_ids).')');
    }
    $summary_data=[];
    foreach($visit_list as $dv){
        $name = $doctors[$dv['doctor_id']]['name'] ??'';
        $finished = $finished_product_data[$dv['id']]??[];
        $finished_product=[];
        $finished_value=0;
        if(!empty($finished)){
            foreach($finished as $f){
                $p = $product_details[$f['product_id']];
                $finished_product[]=$p['t'];
                $finished_value+=$p['s']*$f['quaintity'];
                $total_finished_value+=$p['s']*$f['quaintity'];
            }
        }
        $gift = $gift_product_data[$dv['id']]??[];
        $gift_product=[];
        $gift_value=0;
        if(!empty($gift)){
            foreach($gift as $f){
                $p = $product_details[$f['product_id']];
                $gift_product[]=$p['t'];
                $gift_value+=$p['s']*$f['quaintity'];
                $total_gift_value+=$p['s']*$f['quaintity'];
            }
        }
        if($type){
            $rData[]=[
                's'=>$sr++,
                'co'=>'',
                'd'=>$name,
                'h'=>'',
                'f'=>implode(', ',$finished_product),
                'fv'=>$general->numberFormat($finished_value,0),
                'g'=>implode(', ',$gift_product),
                'gv'=>$general->numberFormat($gift_value,0),
            ];
        }
        else{
            $doctor_id = $dv['doctor_id'];
            if(!isset($summary_data[$doctor_id])){
                $summary_data[$doctor_id]=[
                    's'=>$sr++,
                    'co'=>'',
                    'd'=>$name,
                    'h'=>'',
                    'f'=>[],
                    'fv'=>0,
                    'g'=>[],
                    'gv'=>0,
                ];
            }
            $summary_data[$doctor_id]['fv']+=$finished_value;
            $summary_data[$doctor_id]['gv']+=$gift_value;
            $summary_data[$doctor_id]['f']=array_unique(array_merge($summary_data[$doctor_id]['f'], $finished_product));;
            $summary_data[$doctor_id]['g']=array_unique(array_merge($summary_data[$doctor_id]['g'], $gift_product));;
        }
    }
    if(!$type){
        if(!empty($summary_data)){
            foreach($summary_data as $d){
                $rData[]=[
                    's'=>$d['s'],
                    'co'=>$d['co'],
                    'd'=>$d['d'],
                    'h'=>$d['h'],
                    'f'=>implode(', ',$d['f']),
                    'fv'=>$general->numberFormat($d['fv'],0),
                    'g'=>implode(', ',$d['g']),
                    'gv'=>$general->numberFormat($d['gv'],0),
                ];
            }
        }
    }

}
$rData[]=[
    's'=>['t'=>''],
    'co'=>['t'=>'Total','b'=>1],
    'd'=>['t'=>''],
    'h'=>['t'=>''],
    'f'=>['t'=>''],
    'fv'=>['t'=>$general->numberFormat($total_finished_value,0),'b'=>1],
    'g'=>['t'=>''],
    'gv'=>['t'=>$general->numberFormat($total_gift_value,0),'b'=>1],
];
$head=[
    ['title'=>'SL'                  ,'key'=>'s','hw'=>5],
    ['title'=>'Code'                ,'key'=>'co'],
    ['title'=>'Doctor name'         ,'key'=>'d'],
    ['title'=>'Honorium'            ,'key'=>'h'],
    ['title'=>'Name of sample'      ,'key'=>'f'],
    ['title'=>'Sample value (TP)'   ,'key'=>'fv' ],
    ['title'=>'Name of gift'        ,'key'=>'g' ],
    ['title'=>'Gift value'          ,'key'=>'gv'],
];
$fileName='doctor_visit_report_'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'doctor_visit_report',
    'fileName'      => $fileName,
    'title'     => 'doctor visit report',
    'info'      => $reportInfo,
    'head'=>$head,
    'data'=>$rData
];
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;