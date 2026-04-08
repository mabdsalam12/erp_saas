<?php
    $hID=intval($_POST['hID']);
    $hType=$_POST['hType'];
    $onlineStatus=$_POST['hStatus']==1?1:0;
    $h=$acc->headInfoByID($hID);
    if(empty($h)){$error=fl();setMessage(63,'Head');}

    if($hType=='i'){
        $columnID='for_income';
    }
    else{
        $columnID='for_expense';
    }

    if(!isset($error)){
        $data   =[ $columnID=>$onlineStatus ];
        $where  = [ 'id' => $hID];
        $update=$db->update('a_ledgers',$data,$where);
        if($update){
            $db->actionLogCreate('h'.$hID.'_SetForIncExp_'.$hType,$onlineStatus);
            $jArray['status']=1;
        }
    }
    $jArray['m']=show_msg('y');