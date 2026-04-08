<?php 
$base_id = intval($_POST['base_id']);
$user_id = intval($_POST['user_id']);
$q=[];
if($base_id>0){
    $q[]="base_id=$base_id";
}
if($user_id>0){
    $q[]="user_id=$user_id";
}
$sq='';
if(!empty($q)){
    $sq='where '.implode(' and ',$q);
}
$monthly_target = $db->selectAll('monthly_target',$sq);
$general->getIDsFromArray($monthly_target,'user_id,base_id',$user_ids,$base_ids);
$rData=[];
if(!empty($monthly_target)){
    $sr=1;
    $users = $db->allUsers(' and id in('.implode(',',$user_ids).')');
    $base = $db->allBase('where id in('.implode(',',$base_ids).')');
    $months = $general->allMonth();
    $general->arrayIndexChange($months);
    foreach($monthly_target as $mt){
        $rData[]=[
            's'=>$sr++,
            'b'=>$base[$mt['base_id']]['title']??"",
            'u'=>$users[$mt['user_id']]['name']??"",
            'y'=>date('Y',$mt['date']),
            'm'=>$months[intval(date('m',$mt['date']))]['name'],
            'st'=>$mt['sale_target'],
            'ct'=>$mt['collection_target'],
            'e' => '<a href="'.URL.'?mdl=monthly-target&edit='.$mt['id'].'" class="btn btn-info">Edit</a>',
        ];
    }
}
$fileName='monthly_target'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'monthly_target',
    'title'     => 'Monthly target',
    'fileName'  => $fileName,
    'head'=>[
        ['title'=>"#"                   ,'key'=>'s','hw'=>5],
        ['title'=>"Base"                ,'key'=>'b'],
        ['title'=>"User"                ,'key'=>'u'],
        ['title'=>"Year"                ,'key'=>'y'],
        ['title'=>"Month"               ,'key'=>'m'],
        ['title'=>"Sale target"         ,'key'=>'st','al'=>'r'],
        ['title'=>"Collection target"   ,'key'=>'ct','al'=>'r'],
        ['title'=>"Edit"                ,'key'=>'e'],

    ],
    'data'=>$rData
];
//$jArray[__LINE__]=$report_data;
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;
