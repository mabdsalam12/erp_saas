<?php
$bID    = intval($_POST['bID']);
$sbID    = intval($_POST['sbID']);
$b=$tkt->brandInfoByID($bID);
if(!empty($b)){
	$pcStatus= $db->permission(PER_CHANGE_PER_STATUS,$bID);
	if($pcStatus==true){
		$agID=intval($_POST['copyPermissionFrom']);
		$source=intval($_POST['source']);
		$ag=$db->adminGroupInfoByID($agID);
		if(!empty($ag)){
			$ag=$db->adminGroupInfoByID($source);
			if(!empty($ag)){
				$jArray['status']=1;
				$where = array('agID'=>$agID,'bID'=>$bID);
				$db->delete($general->table(38),$where);
				$ops=$db->selectAll($general->table(38),'where agID='.$source.' and bID='.$sbID);
				if(!empty($ops)){
					foreach($ops as $op){
						$data=array(
							'agID'  => $agID,
							'perID' => $op['perID'],
							'bID'   => $bID
						);
						$insert=$db->insert($general->table(38),$data,'','array',$jArray);
					}
				}
				$where = array('agID'=>$agID,'bID'=>$bID);
				$db->delete($general->table(41),$where);
				$ops=$db->selectAll($general->table(41),'where agID='.$source.' and bID='.$sbID);
				if(!empty($ops)){
					foreach($ops as $op){
						$data=array(
							'agID'  => $agID,
							'cmID'  => $op['cmID'],
							'bID'   => $bID
						);
						$insert=$db->insert($general->table(41),$data,'','array',$jArray);
					}
				}
			}
		}

	}
}
$general->jsonHeader($jArray);
?>
