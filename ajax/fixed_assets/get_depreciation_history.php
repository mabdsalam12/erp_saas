<?php
$id=intval($_POST['assets_id']);
$data=$db->get_rowData('fixed_assets','id',$id);
if(!empty($data)){   
    $depreciations=$db->selectAll('fixed_assets_depreciation','where fixed_assets_id='.$id);

    if(!empty($depreciations)){
        $data=[];
        foreach($depreciations as $d){
            $data[]=[
                'amount'=>$general->numberFormat($d['amount']),
                'date'=>$general->make_date($d['time']),
                'note'=>$d['note'],
            ]; 
        }


        $jArray['status'] = 1;
        $jArray['depreciations'] =$data;
    }
    else{
        $error=fl(); setMessage(2,'No depreciation records available at the moment.');  
    }

}
else{
    $error=fl(); setMessage(36,'User'); 
}

?>
