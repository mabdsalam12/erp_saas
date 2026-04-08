<?php
	$tbl    = $_GET['stch'];
    $table =  $tbl;
    if($table==false){
        $table = $tbl;
    }
	$op_id  = intval($_GET['ch_id']);
	$action = intval($_GET['action']);
	$name   = $_GET['name'];
	if($name!='set_roles'){
		$data   = array('isActive'=>$action);
		$where  = array($name =>$op_id);
		$db->update($table,$data,$where,'a');
	}
?>
