<?php
$can_see_unit_cost=$db->permission(104);
$q=['isActive in(1,0)'];
$reportInfo=['Date :'.date('d-M-Y')];
$type = intval($_POST['type']);
$types=$smt->get_all_product_type();
if($type>=0){
    $q[]='type='.$type;
    $reportInfo[]='Type: '.$types[$type]['title']??'';
}

if(!empty($types)){
    $q[]='type in('.implode(',',array_keys($types)).')';
}
else{
    $q[]='id=-0';
}

$products=$db->selectAll('products','where '.implode(' and ',$q).' order by title asc');
$units=$db->selectAll('unit');
$general->arrayIndexChange($units,'id');

$tStockAmount=0;
$tStockAmount_tp=0;
if(!empty($products)){
    $serial=1;
    foreach($products as $p){
        $stockAmount_tp    = 0;
        if($p['stock']>0){
            $stockAmount_tp=round($p['stock']*$p['sale_price'],2);
        }   
        $tStockAmount_tp   += $stockAmount_tp;
        $stockAmount    = 0;
        if($p['stock']>0){
            $stockAmount=round($p['stock']*$p['unit_cost'],2);
        }   
        $tStockAmount   += $stockAmount;
        
        
        $rData[]=[
            's'     => $serial++,
            'c'     => $p['code'],
            't'     => $p['title'],
            'ty'     => $types[$p['type']]['title'],
            'un'    => $units[$p['unit_id']]['title'],
            'bp'    => $p['unit_cost'],
            'sp'    => $p['sale_price'],
            'st'    => $general->numberFormat($p['stock'],0),
            'sa'    => $general->numberFormat($stockAmount),
            'sat'    => $general->numberFormat($stockAmount_tp),
        ];
    }
}
$rData[]=[
    's'=>'',
    'c'=>['t'=>'Total','b'=>1,'col'=>7],
    't'=>['t'=>false],
    'ty'=>['t'=>false],
    'un'=>['t'=>false],
    'bp'=>['t'=>false],
    'sp'=>['t'=>false], 
    'tc'=>['t'=>false], 
    'st'=>['t'=>false],
    'stD'=>['t'=>false],
    'sa'=>['t'=>$general->numberFormat($tStockAmount),'b'=>1],
    'sat'=>['t'=>$general->numberFormat($tStockAmount_tp),'b'=>1],
];  
//$can_see_unit_cost
$head_columns=[
    array('title'=>"SN"             ,'key'=>'s'),
    array('title'=>"Code"          ,'key'=>'c'),
    array('title'=>"Title"          ,'key'=>'t'),
    array('title'=>"Type"          ,'key'=>'ty'),
    array('title'=>"Unit"           ,'key'=>'un'    ,'hw'=>7,'al'=>'r'),
    array('title'=>"Sale Price"     ,'key'=>'sp'    ,'hw'=>10,'al'=>'r'),
    // array('title'=>"Stock"          ,'key'=>'st'    ,'hw'=>9,'al'=>'r'),
    // array('title'=>"Stock Amount (UC)"   ,'key'=>'sa'    ,'al'=>'r'),
    // array('title'=>"Stock Amount (TP)"   ,'key'=>'sat'    ,'al'=>'r'),
];
if($can_see_unit_cost){
    $head_columns[]=array('title'=>"Unit cost"      ,'key'=>'bp'    ,'hw'=>10,'al'=>'r');
}
//$head_columns[]=array('title'=>"Sale Price"     ,'key'=>'sp'    ,'hw'=>10,'al'=>'r');
$head_columns[]=array('title'=>"Stock"          ,'key'=>'st'    ,'hw'=>9,'al'=>'r');
$head_columns[]=array('title'=>"Stock Amount (TP)"   ,'key'=>'sat'    ,'al'=>'r');
if($can_see_unit_cost){
    $head_columns[]=array('title'=>"Stock Amount (UC)"   ,'key'=>'sa'    ,'al'=>'r');
}
$fileName='prdliRep_'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'purRep',
    'title'     => 'Products List Report',
    'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>$head_columns,
    'data'=>$rData
];

$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;