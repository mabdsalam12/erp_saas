<?php
$from   = intval($_POST['from']);
$to     = intval($_POST['to']);
$b      = $tkt->brandInfoByID($from);
$b2     = $tkt->brandInfoByID($to);
if(!empty($b)&&!empty($b2)){
    $jArray['status']=1;
    $where = array('bID'=>$to);
    $db->delete($general->table(124),$where);
    $ops=$db->selectAll($general->table(124),'where bID='.$from);
    if(!empty($ops)){
        foreach($ops as $op){
            $data=array(
                'perID' => $op['perID'],
                'bID'   => $to
            );    
            $insert=$db->insert($general->table(124),$data);
        }
    }
    $db->delete($general->table(123),$where);
    $ops=$db->selectAll($general->table(123),'where bID='.$from);
    if(!empty($ops)){
        foreach($ops as $op){
            $data=array(
                'cmID'  => $op['cmID'],
                'bID'   => $to
            );    
            $insert=$db->insert($general->table(123),$data);
        }
    }
}
$general->jsonHeader($jArray);
?>
