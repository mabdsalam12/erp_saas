<?php
$dRange=$_POST['dRange'];
$reportInfo=['Date :'.$dRange];
$general->getFromToFromStringt($dRange,$from,$to);
$supID  = intval($_POST['supID']);
$type   = intval($_POST['type']);

$q[]='purrDate between '.$from.' and '.$to;
if($supID>0){
    $q[]='supID='.$supID;
}
if($type==PRODUCT_TYPE_DAMAGE||$type==PRODUCT_TYPE_GOOD){
    $q[]   ='purrType='.$type;
}
$returns=$db->selectAll('purchase_return','where '.implode(' and ',$q).' order by purrDate asc');
$tDue=0;
$rData=[];
$tSubTotal=0;
$tNetTotal=0;
$tDiscount=0;

$serial=1;
$notPaidDetails=[];
if(!empty($returns)){

    $supIDs=[];
    foreach($returns as $s){
        $supIDs[$s['supID']]=$s['supID'];
    }

    $suppliers=$db->selectAll($general->table(45),'where supID in('.implode(',',$supIDs).')');
    $general->arrayIndexChange($suppliers,'supID');

    foreach($returns as $r){
        $due=0;
        if($r['isPaid']==0){
            $due=$r['netTotal'];
        }
        $tDue       += $due;
        $tSubTotal  += $r['subTotal'];
        $tNetTotal  += $r['netTotal'];
        $tDiscount  += $r['discount'];
        $supName=$suppliers[$r['supID']]['supName'];
        $paid='Done';
        if($r['isPaid']==0){
            $paid='<a href="javascript:void()" class="btn btn-info" onclick="damageReturnPayInit('.$r['purrID'].')">Pay</a>';
            $notPaidDetails[$r['purrID']]=[
                'subTotal'  => (float)$r['subTotal'],
                'discount'  => (float)$r['discount'],
                'netTotal'  => (float)$r['netTotal'],
                'supName'   => $supName,
                'supInvNo'  => $r['supInvNo']
            ];
        }
        $rData[]=[
            's' => $serial++,
            'd' => $general->make_date($r['purrDate']),
            'su'=> $supName,
            'i' => $r['supInvNo'],
            'st'=> $general->numberFormat($r['subTotal'],0),
            'di'=> $general->numberFormat($r['discount'],0),
            't' => $general->numberFormat($r['netTotal'],0),
            'p' => $paid
        ];
    }
}
$jArray['notPaidDetails']=$notPaidDetails;
$rData[]=[
    's' =>'',
    'd' =>['t'=>'Total','b'=>1,'col'=>3],
    'su'=>['t'=>false],
    'i' =>['t'=>false],
    'st'=>['t'=>$general->numberFormat($tSubTotal,0),'b'=>1],
    'di'=>['t'=>$general->numberFormat($tDiscount,0),'b'=>1],
    't' =>['t'=>$general->numberFormat($tNetTotal,0),'b'=>1]
];

$headData=[
    array('title'=>"#"          ,'key'=>'s'     ,'hw'=>5),
    array('title'=>"Date"       ,'key'=>'d' ),
    array('title'=>"Supplier"   ,'key'=>'su'),
    ['title'=>"Invoice No"      ,'key'=>'i'],
    ['title'=>"Subtotal"        ,'key'=>'st'    ,'al'=>'r'],
    ['title'=>"Discount"        ,'key'=>'di'    ,'al'=>'r'],
    ['title'=>"Total"           ,'key'=>'t'     ,'al'=>'r'],
    ['title'=>"Paid"            ,'key'=>'p'     ,'al'=>'r'],
];
$fileName='productReturnReport_'.date('d_m_Y',$from).'_'.date('d_m_Y',$to).'.txt';
$report_data=array(
    'name'      => 'productReturnReport'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
    'title'     => 'Product return report',
    'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>$headData,
    'data'=>$rData
);
$jArray[fl()]=$report_data;
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;   
?>
