<?php 

$dRange = $_POST['dRange'];
$reportInfo=['Date: '.$dRange];
$general->getFromToFromString($dRange,$from,$to);
$base_id = intval($_POST['base_id']);
//$firstOfMonth = strtotime(date("Y-m-01 00:00:00", $from));
//$lastOfMonth = strtotime(date("Y-m-t 23:59:59", $from));


$q=["date between $from and $to"];   
if($base_id>0){
    $q[]="base_id=$base_id";
}
$sales = $db->selectAll('sale',"where ".implode(' and ',$q),'id,customer_id,total');
//$honorarium_evaluation = $db->selectAll('honorarium_evaluation','where '.$from.' BETWEEN start_date AND end_date');
$honorarium_evaluation = $db->selectAll('honorarium_evaluation');
$rData=[];
$total=1;         
$jArray[fl()]=$sales;
if(!empty($sales)){
    $customer_wise_sale=[];
    $customer_ids=[];
    $doctor_ids=[];
    foreach($sales as $s){
        if(!isset($customer_wise_sale[$s['customer_id']])){
            $customer_wise_sale[$s['customer_id']]=0;
        }
        $customer_wise_sale[$s['customer_id']]+=$s['total'];
        $customer_ids[$s['customer_id']]=$s['customer_id'];
    }
    $doctor_wise_honorarium_evaluation=[];
    foreach($honorarium_evaluation as $he){
        $doctor_ids[$he['doctor_id']]=$he['doctor_id'];
        $doctor_wise_honorarium_evaluation[$he['customer_id']][$he['doctor_id']][]=$he;
    }
    $report_data=[];
    foreach($customer_wise_sale as $customer_id=>$c){
        $doctors = $doctor_wise_honorarium_evaluation[$customer_id]??[];
        if(!$doctors){$jArray[fl()][]=1; continue;}
        $data=[];
        foreach($doctors as $doctor_id=>$h){
            $data[$doctor_id]=[
                'cont'=>0,
                'contTK'=>0,
            ];
            foreach($h as $d){
                if($d['start_date']<=$from&&$d['end_date']>=$from){ 
                    $data[$doctor_id]=[
                        'cont'=>$d['contribute'],
                        'contTK'=>$general->numberFormat(($c*$d['contribute'])/100),
                    ]; 
                }

            }
        }
        $report_data[$customer_id]=[
            'amount'=>$general->numberFormat($c),
            'doctor'=>$data,
        ];

    }
    if($report_data){
        $customers = $db->selectAll('customer','where id in('.implode(',',$customer_ids).')','id,name,code');
        $general->arrayIndexChange($customers);
        $doctors = $db->selectAll('doctor','where id in('.implode(',',$doctor_ids).')','id,name,code');
        $general->arrayIndexChange($doctors);
        foreach($report_data as $customer_id=>$rd){
            $doctors_data = $rd['doctor']??[];
            if(!$doctors_data){$jArray[fl()][]=1; continue;}
            $count = count($doctors_data);
            $sr=0;
            $c=$customers[$customer_id];
            foreach($doctors_data as $doctor_id=>$dd){
                //print_r($dd);
                $d=$doctors[$doctor_id];
                $data=[];
                if(!$sr){
                    $data['s']=['t'=>$total++,'row'=>$count,'al'=>'c'];
                    $data['c']=['t'=>$c['code'],'row'=>$count,'al'=>'c'];
                    $data['n']=['t'=>$c['name'],'row'=>$count];
                    $data['cl']=['t'=>$rd['amount'],'row'=>$count,'al'=>'r'];
                    $data['d']=['t'=>$d['code'].' '.$d['name']];
                    $data['cont']=['t'=>$dd['cont']];
                    $data['contTk']=['t'=>$dd['contTK'],'al'=>'r'];
                }
                else{
                    $data['s']=false;
                    $data['c']=false;
                    $data['n']=false;
                    $data['cl']=false;
                    $data['d']=['t'=>$d['code'].' '.$d['name']];
                    $data['cont']=['t'=>$dd['cont']];
                    $data['contTk']=['t'=>$dd['contTK'],'al'=>'r'];
                }
                $sr=1;
                $rData[]=$data;
            }

        }
    }

}
$jArray[fl()]=$rData;
$fileName='pharmacy_wise_doctors_contribution_'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'pharmacy_wise_doctors_contribution',
    'title'     => 'Pharmacy wise doctors contribution',
    'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>[
        ['title'=>"SL"              ,'key'=>'s','hw'=>5],
        ['title'=>"Code"            ,'key'=>'c','hw'=>10],
        ['title'=>"Pharmacy"        ,'key'=>'n'],
        ['title'=>"Collection"      ,'key'=>'cl','al'=>'r'],
        ['title'=>"Doctor"          ,'key'=>'d'],
        ['title'=>"Cont %"          ,'key'=>'cont'],
        ['title'=>"Cont TK"         ,'key'=>'contTk','al'=>'r'],

    ],
    'data'=>$rData
];
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;