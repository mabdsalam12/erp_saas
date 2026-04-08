<?php
    $supID=intval($_POST['supID']);

    $sup=$smt->supplierInfoByID($supID);
    if(!empty($sup)){
        $products=$db->selectAll($general->table(104),'where supID='.$supID);
        $jArray['status']=1;
        $pData=[];
        $units=$smt->getAllUnit();
        foreach($products as $p){
            $pData[]=[
                'pID'=>$p['pID'],
                'title'=>$p['title'].' '.@$units[$p['unID']]['unTitle']
            ];
        }
        $jArray['products']=$pData;
    }
?>
