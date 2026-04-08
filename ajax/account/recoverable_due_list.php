<?php
$dRange = $_POST['dRange'];
$reportInfo=["Date: $dRange"];
$employee_id = intval($_POST['employee_id']);
$customer_id = intval($_POST['customer_id']);
$base_id=intval($_POST['base_id']);
$zero_type = intval($_POST['zero_type']);
$report_type = intval($_POST['report_type']);
$general->getFromToFromString($dRange,$from,$to);
$q=[];
if($report_type!=2){
    $q=["createdOn between $from and $to"];
}
else{
    $q=["createdOn<$to"];
}
$employees = $db->selectAll('employees','','id,name');
$general->arrayIndexChange($employees);
$customers = $db->selectAll('customer','where isActive=1','id,name');
$general->arrayIndexChange($customers);
if($employee_id>0){
    $q[]="employee_id = $employee_id";
    $reportInfo[] = "Employee ".$employees[$employee_id]['name'];
}
if($customer_id>0){
    $q[]="customer_id = $customer_id";
    $reportInfo[] = "Customer ".$customers[$customer_id]['name'];
}
if($base_id>0){
    $base=$smt->base_info_by_id($base_id);
    $reportInfo[] = "Base  ".$base['title'];
    $customer_ids = $db->selectAll('customer','where base_id='.$base_id,'id');
    if(!empty($customer_ids)){
        $general->arrayIndexChange($customer_ids);
        $reference = array_keys($customer_ids);
        $q[]='customer_id in('.implode(',',$reference).')';
    }
    else{
        $q[]="id = -1";
    }
}
$types = [
    0 => 'Summary',
    1 => 'Details',
    2 => 'Employee Due',
];

$reportType = $types[$report_type] ?? 'Summary';
$reportInfo[] = "Zero type ".(($zero_type==0)?'With zero':'Without zero');
$reportInfo[] = "Report type {$reportType}";
$fileName='recoverable_due_list_'.TIME.rand(0,999).'.txt';
$rData=[];
$total_due=0;
$total_amount = 0;
$total_collect = 0;
$sr=1;
if($report_type==0 || $report_type==2){
    $recoverable_collection = $db->selectAll(
        'recoverable_collection',
        'where '.implode(' and ',$q).' group by employee_id',
        'sum(amount) as amount, sum(collect) as collect, (sum(amount)-sum(collect)) as due, employee_id',
        'array',
        $jArray
    );
    
    if(!empty($recoverable_collection)){
        foreach($recoverable_collection as $rc){
            if($zero_type && $rc['due']==0){ continue;}
            $total_amount += $rc['amount'];
            $total_collect += $rc['collect'];
            $total_due += $rc['due'];
            $rData[]=[
                's'=>$sr++,
                'e'=>$employees[$rc['employee_id']]['name']??'',
                'a'=>$general->numberFormat($rc['amount']),
                'c'=>$general->numberFormat($rc['collect']),
                'd'=>$general->numberFormat($rc['due']),
            ];
        }
    }
    $rData[]=[
        's'=>['t'=>''],
        'e'=>['t'=>'Total','b'],
        'a'=>['t'=>$general->numberFormat($total_amount),'b'],
        'c'=>['t'=>$general->numberFormat($total_collect),'b'],
        'd'=>['t'=>$general->numberFormat($total_due),'b'],
    ];
    $report_data=[
        'name'      => 'recoverable_due_list',
        'title'     => 'recoverable due list',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>[
            ['title'=>"SL"          ,'key'=>'s','hw'=>5],
            ['title'=>'Employee'      ,'key'=>'e'],
           
            // ['title'=>"collect"     ,'key'=>'c','al'=>'r'],
            // ['title'=>"Due"         ,'key'=>'d','al'=>'r'],

        ],
        'data'=>$rData
    ];
    if($report_type!=2){
        $report_data['head'][]= ['title'=>"Amount"      ,'key'=>'a','al'=>'r'];
        $report_data['head'][]= ['title'=>"collect"     ,'key'=>'c','al'=>'r'];
    }
    $report_data['head'][]= ['title'=>"Due"         ,'key'=>'d','al'=>'r'];
    
}
else{
    $recoverable_collection = $db->selectAll(
        'recoverable_collection',
        'where '.implode(' and ',$q).'  ORDER BY `id` DESC',
        'id, amount,  collect, customer_id,employee_id,createdOn'
    );
    if(!empty($recoverable_collection)){
        foreach($recoverable_collection as $rc){
            if($zero_type && $rc['due']==0){ continue;}
            $total_amount += $rc['amount'];
            $total_collect += $rc['collect'];
            $rData[]=[
                's'=>$sr++,
                'd'=>$general->make_date($rc['createdOn']),
                'e'=>$employees[$rc['employee_id']]['name']??'',
                'c'=>$customers[$rc['customer_id']]['name']??'',
                'a'=>$general->numberFormat($rc['amount']),
                'cl'=>$general->numberFormat($rc['collect']),
                'r'=>"<button class='btn btn-danger recoverableDelete_".$rc['id']."' onclick='recoverableDelete(".$rc['id'].")'>Delete</button>" ,
            ];
        }
    }
    $rData[]=[
        's'=>['t'=>''],
        'd'=>['t'=>'Total','b'],
        'e'=>['t'=>'','b'],
        'c'=>['t'=>'','b'],
        'a'=>['t'=>$general->numberFormat($total_amount),'b'],
        'cl'=>['t'=>$general->numberFormat($total_collect),'b'],
        'r'=>['t'=>'','b'],
    ];
    $report_data=[
        'name'      => 'recoverable_due_list',
        'title'     => 'recoverable due list',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>[
            ['title'=>"SL"          ,'key'=>'s','hw'=>5],
            ['title'=>'Date'        ,'key'=>'d'],
            ['title'=>'Employee'    ,'key'=>'e'],
            ['title'=>'Customer'    ,'key'=>'c'],
            ['title'=>"Amount"      ,'key'=>'a','al'=>'r'],
            ['title'=>"collect"     ,'key'=>'cl','al'=>'r'],
            ['title'=>"Delete"      ,'key'=>'r'],

        ],
        'data'=>$rData
    ];
}

$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$html     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml',$gAr);
$jArray['status']=1;
$jArray['html']=$html;