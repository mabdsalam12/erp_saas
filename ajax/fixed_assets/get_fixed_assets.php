<?php
$id=intval($_POST['assets_id']);

$data=$db->get_rowData('fixed_assets','id',$id);
if(!empty($data)){   
    $type_title='';
    if($data['type_id']>0){
        $assets_type=$db->get_rowData('fixed_assets_type','id',$data['type_id']);
        if(!empty($assets_type)){
            $type_title =$assets_type['title'];
        }   
    }
    $jArray['status'] = 1;
    $jArray['assets'] =[
        'type'      =>$type_title,
        'product'   =>$data['product'],
        'date'      =>$general->make_date($data['time']),
        'depreciation'=>$general->numberFormat($data['depreciation']),
        'current_value'=>$general->numberFormat($data['current_value']),
    ];
}
else{
    $error=fl(); setMessage(36,'User'); 
}
?>
