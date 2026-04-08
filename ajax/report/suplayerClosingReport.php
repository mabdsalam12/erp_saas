<?php

    $dRange = $_POST['dRange'];
    $general->getFromToFromStringt($dRange,$from,$to);
    $supID = intval($_POST['supID']);
    $scID = intval($_POST['scID']);
    $q=['sDate between '.$from.' and '.$to,'scID='.$scID];
    if($supID>0){
        $q[] = 'supID='.$supID;
    }
    $supplierSale = $db->selectAll('sale','where scID='.$scID.' and supID='.$supID.' and sDate between '.$from.' and '.$to,'sID,sDate,sTotal','array',$jArray);
    if(!empty($supplierSale)){
        foreach($supplierSale as $s){
            $sIDs[] = $s['sID'];
        }
        $returns=$db->selectAll($general->table(16),'where sID in('.implode(',',$sIDs).')');
    }
?>
