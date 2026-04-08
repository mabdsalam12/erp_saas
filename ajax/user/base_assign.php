<?php
$uID =intval($_POST['user_id']);
$selected_base=$_POST['selected_base']??[];

$u = $db->userInfoByID($uID);

if(empty($u)){$error=fl(); setMessage(36,'User');}
else{
    $db->transactionStart();
    $assign_base= $db->selectAll('user_manager','where user_id='.$uID.' and isActive=1','id,assign_base_id,user_id',$general->showQuery());
    $new_assign=[]; 
    $remove_assign=[]; 
    if(empty($assign_base)){
        $new_assign= $selected_base;
    }
    elseif(empty($selected_base)){
        if(!empty($assign_base)){
            $remove_assign  = array_column($assign_base,'id');
        }
    }
    else{
        $jArray[fl()] = $selected_base;
        $assign_base_ids = array_column($assign_base, 'assign_base_id'); 
        $jArray[fl()] = $assign_base_ids;
        $assign_ids_map  = array_column($assign_base, 'id', 'assign_base_id'); // key = assign_base_id, value = id
        $jArray[fl()] = $assign_ids_map;

        // Find new and removed
        $new_assign    = array_diff($selected_base, $assign_base_ids); 
        $jArray[fl()] = $new_assign;
        // $remove_assign = array_intersect_key($assign_ids_map, array_diff($assign_base_ids, $selected_base));
        $remove_assign = array_intersect_key(
            $assign_ids_map,
            array_flip(array_diff($assign_base_ids, $selected_base))
        );
        $jArray[fl()] = $remove_assign;
        
    }




    if(!empty($new_assign)){  
        foreach($new_assign as $u){
            $data = [
                'user_id'       => $uID,
                'assign_base_id'=> $u,
                'assign_time'   => TIME,
                'assign_by'     => USER_ID,   
            ];

            $insert=$db->insert('user_manager',$data,true);

            if(!$insert){$error=fl(); setMessage(66);}
        }
    }
    if(!empty($remove_assign)){  
        foreach($remove_assign as $id){
            $data = [  
                'release_time'   => TIME,
                'release_by'     => USER_ID,   
                'isActive'       => 0,   
            ];
            $where=['id'=>$id];

            $update=$db->update('user_manager',$data,$where);

            if(!$update){$error=fl(); setMessage(66);}
        }
    }
    

    $ac=false;
    if(!isset($error)){
        $ac=true;
        $jArray['status'] = 1;
        setMessage(2,'Base successfully assigned.');
    }
    $db->transactionStop($ac);
}

