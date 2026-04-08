<?php
$dRange=$_POST['dRange'];
$reportInfo=['Date :'.$dRange];
$general->getFromToFromString($dRange,$from,$to);
$supplier_id=intval($_POST['supplier_id']);
$type=intval($_POST['type']);


$q[]='date between '.$from.' and '.$to;
if($supplier_id>0){
    $q[]='supplier_id='.$supplier_id;
}
if($type>0){
    $q[]='type='.$type;
}
$purchases=$db->selectAll('purchase','where '.implode(' and ',$q).' order by date,mrr_code asc','','array',$jArray );
$types=$smt->get_all_product_type();

$rData=[];
$tSubTotal=0;
$tDiscount=0;
$tNetTotal=0;
$serial=1;
if(!empty($purchases)){
    $supIDs=[];
    foreach($purchases as $pur){
        $supIDs[$pur['supplier_id']]=$pur['supplier_id'];
    }
    $suppliers=$db->selectAll('suppliers','where id in('.implode(',',$supIDs).')');
    $general->arrayIndexChange($suppliers,'id');
    foreach($purchases as $pur){
        $tSubTotal+=$pur['sub_total'];
        $tDiscount+=$pur['discount'];
        $tNetTotal+=$pur['total'];
        $rData[]=[
            's' => $serial++,
            'su'=> $suppliers[$pur['supplier_id']]['name'],
            'i' => $pur['supplier_invoice_no'],
            'm' => $pur['mrr_code'],
            'd' => $general->make_date($pur['date']),
            'e' => $general->make_date($pur['createdOn']),
            't' => $types[$pur['type']]['title']??'',
            'st'=> $general->numberFormat($pur['sub_total']),
            'di'=> $general->numberFormat($pur['discount']),
            'n' => $general->numberFormat($pur['total']),
            'p'=>'<a href="'.URL.'/?print=purchase&purchase_id='.$pur['id'].'" target="_blank" class="btn btn-success">Print</a><button onclick="purchase_details_view('.$pur['id'].')" class="btn btn-success">Details</button>'
        ];
    }
}
$rData[]=[
    's'=>'',
    'su'=>['t'=>'Total','b'=>1],
    'i'=>['t'=>''],
    'm'=>['t'=>''],
    'd'=>['t'=>''],
    't'=>['t'=>''],
    'e'=>['t'=>''],
    'st'=>['t'=>$general->numberFormat($tSubTotal),'b'=>1],
    'di'=>['t'=>$general->numberFormat($tDiscount),'b'=>1],
    'n'=>['t'=>$general->numberFormat($tNetTotal),'b'=>1],
    //'a'=>['t'=>''],
];


$fileName='purRep_'.TIME.rand(0,999).'.txt';
$report_data=array(
    'name'      => 'purRep'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
    'title'     => 'Purchase Report',
    'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>array(
        array('title'=>"#"          ,'key'=>'s' ,'w'=>5),
        array('title'=>"Supplier"   ,'key'=>'su','w'=>10),
        array('title'=>"Sup Inv No" ,'key'=>'i','w'=>10),
        array('title'=>"MRR No"     ,'key'=>'m','w'=>10),
        array('title'=>"Date"       ,'key'=>'d' ,'hw'=>15),
        array('title'=>"Entry date" ,'key'=>'e' ,'hw'=>15),
        array('title'=>"type"       ,'key'=>'t' ,'hw'=>15),
        array('title'=>"Subtotal"   ,'key'=>'st','al'=>'r'),
        array('title'=>"Discount"   ,'key'=>'di','al'=>'r'),
        array('title'=>"Net Payable",'key'=>'n','al'=>'r'),
        array('title'=>"Print"       ,'key'=>'p'),
        //array('title'=>"Action"       ,'key'=>'a'),
       // array('title'=>"Edit"       ,'key'=>'e'),
    ),
    'data'=>$rData
);
$jArray[__LINE__]=$report_data;
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;   
?>
