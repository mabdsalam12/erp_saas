<?php
    $agID = intval($_POST['change_premessions']);
    $perID = intval($_POST['per']);
    $st = intval($_POST['st']);
    $b=$db->adminGroupInfoByID($agID);
    $type=$_POST['type'];
    if(!empty($b)){
        if($type=='p'){
            $db->permissionSetForPermission($agID,$perID,$st);
        }
        else if($type=='m'){
            $db->permissionSetForModule($agID,$perID,$st);
        }
    }
