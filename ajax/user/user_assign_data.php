<?php
$uID =intval($_POST['user_id']);

$u = $db->userInfoByID($uID);

if(empty($u)){$error=fl(); setMessage(36,'User');}
elseif($u['type']!=USER_TYPE_MANAGER&&$u['type']!=USER_TYPE_RSM){
    $error=fl(); setMessage(36,'User Type');
}
else{
    $q=['status=1'];  
    

    $sq='where '.implode(' and ',$q);

    $base   = $db->selectAll('base',$sq,'id,title,code',$general->showQuery());
    $assign_base= $db->selectAll('user_manager','where user_id='.$uID.' and isActive=1','assign_base_id,user_id','array',$jArray);
    $all_assign=[];
    if(!empty($assign_base)){
        $all_assign = array_column($assign_base,'assign_base_id');
    }

    
    $gAr['base']   =$base;
    $gAr['all_assign']   =$all_assign;
    $jArray['html'] = $general->fileToVariable(__DIR__.'/user_assign_data.phtml');
    $jArray['status'] = 1;
}

