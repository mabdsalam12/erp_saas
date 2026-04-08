<?php
    $hID=$_POST['hID'];
    $h=$acc->headInfoByID($hID);
    if(!empty($h)){
        if($h['bID']==$bID){
            $jArray['balance']=$acc->headBalance($hID);
            $jArray['status']=1;
            /*$ledgers=$db->selectAll($general->table(93),'where hID='.$hID,'lID','array',$jArray);
            if(!empty($ledgers)){
                $q=array();
                $general->arrayIndexChange($ledgers,'lID');
                $q[]='lID in('.implode(',',array_keys($ledgers)).')';
                $sq='where '.implode(' and ',$q);
                $query="select (sum(leAmountDr)-sum(leAmountCr)) as balance from ".$general->table(97)." ".$sq;
                $balance=$db->fetchQuery($query,'array',$jArray);
                $jArray['balance']=$general->numberFormat($balance[0]['balance']);
                $jArray['status']=1;

            }*/
        }
    }
    $jArray['m']=show_msg('y');
    $general->jsonHeader($jArray);
?>
