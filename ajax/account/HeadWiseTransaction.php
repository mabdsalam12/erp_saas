<?php
$dRange=date('d-m-Y').' to '.date('d-m-Y');
if(isset($_POST['dRange'])){
	if($_POST['dRange']!=''){
		$dRange=$_POST['dRange'];
	}
}
$reportInfo=array();
$reportInfo[]='Date : '.$dRange;
$dRange = explode(' to ',$dRange);
$from   = strtotime($dRange[0]);
$from   = $tkt->departureDate($from);
$to     = strtotime(trim($dRange[1])) ;
$to     = $tkt->departureDate($to,true);

$cnID   = intval($_POST['cnID']);
$hID    = intval($_POST['hID']);
$uID    = intval($_POST['uID']);
$h=$acc->headInfoByID($hID);

$reportInfo[]='Head : '.$h['hTitle'];
$q=array();
if(!empty($h)){
	$ledgers=$db->selectAll($general->table(93),'where hID='.$hID,'lID','array',$jArray);
	if(!empty($ledgers)){
		$general->arrayIndexChange($ledgers,'lID');
		$q[]='lID in('.implode(',',array_keys($ledgers)).')';
	}else{$q[]='lID=0';}
}
$q[]='createdOn between '.$from.' and '.$to;
if($cnID>0){
	$cn=$tkt->counterInfoByID($cnID);
	if(!empty($cn)){
		if($cn['bID']==$bID){
			$reportInfo[]='Counter : '.$cn['cnTitle'];
			$q[]='cnID='.$cnID;
		}else{$reportInfo[]='Counter : All';}
	}else{$reportInfo[]='Counter : All';}
}else{$reportInfo[]='Counter : All';}
if($uID>0){
	$u=$tkt->userInfoByID($uID);
	if(!empty($u)){
		if($u['bID']==$bID){
			$q[]='createdBy='.$uID;
		}else{$reportInfo[]='User : '.$u['username'];}
	}else{$reportInfo[]='User : All';}
}else{$reportInfo[]='User : All';}
$sq='where '.implode(' and ',$q);
$transactions=$db->selectAll('a_ledger_entry',$sq,'','array',$jArray);
$jArray[__LINE__]=$transactions;
$rData=array();
$balance=0;
$tIn=0;
$tOut=0;
if(!empty($transactions)){
	$veIDs=array();
	$lIDs=array();
	$hIDs=array();
	foreach($transactions as $tr){
		$veIDs[$tr['veID']]=$tr['veID'];
		$lIDs[$tr['lID']]=$tr['lID'];
		
	}
	$vouchers=$db->selectAll($general->table(96),'where veID in('.implode(',',$veIDs).')');
	$general->arrayIndexChange($vouchers,'veID');
	$otLedgers=$db->selectAll($general->table(97),'where veID in('.implode(',',$veIDs).') and lID not in('.implode(',',$lIDs).')','veID,lID');
	$general->arrayIndexChange($otLedgers,'veID');
	//$jArray[__LINE__]=$otLedgers;
	//$lIDs=array();
	foreach($otLedgers as $le){
		$lIDs[$le['lID']]=$le['lID'];
	}
	if(!empty($lIDs)){
		$ledgers=$db->selectAll($general->table(93),'where lID in('.implode(',',$lIDs).')','lID,hID');
		//$general->arrayIndexChange($ledgers,'hID');

		foreach($ledgers as $l){
			$hIDs[$l['hID']]=$l['hID'];
		}
		$heads=$db->selectAll($general->table(100),'where hID in('.implode(',',$hIDs).')','hID,hTitle');
	}
	//$heads=$db->selectAll($general->table(100),'where hID in('.implode(',',array_keys($ledgers)).')','hID,hTitle');
	$general->arrayIndexChange($ledgers,'lID');
	$general->arrayIndexChange($heads,'hID');

	$serial=1;
	$allCounters=$tkt->allCounters($bID);
	foreach($transactions as $tr){
		$tIn+=$tr['leAmountDr'];
		$tOut+=$tr['leAmountCr'];

		$balance+=$tr['leAmountDr'];
		$balance-=$tr['leAmountCr'];
		//$a=@$otLedgers[$tr['veID']];
		$rData[]=array(
			's' => $serial++,
			'h' => @$heads[$ledgers[$tr['lID']]['hID']]['hTitle'],
			'cn'=> $allCounters[$tr['cnID']]['cnTitle'],
			'p' => $general->content_show($tr['leParticulars']),//.' '.$tr['veID'],
			'd' => $general->make_date($tr['createdOn']),
			't' => date('H:i',$tr['createdOn']),
			'i' => $general->numberFormat($tr['leAmountDr']),
			'o' => $general->numberFormat($tr['leAmountCr']),
			'b' => $general->numberFormat($balance),
		);
	}
}
$rData[]=array(
	's'=>'',
	'h'=> array('t'=>'Total','bold'=>1,'col'=>5),
	'cn'=>array('t'=>false),
	'p'=>array('t'=>false),
	'd'=>array('t'=>false),
	't'=>array('t'=>false),
	'i'=>array('t'=>$general->numberFormat($tIn),'b'=>1),
	'o'=>array('t'=>$general->numberFormat($tOut),'b'=>1),
	'b'=>array('t'=>$general->numberFormat($balance),'b'=>1),
);
$fileName='cndtwisesalesreport_'.TIME.rand(0,999).'.txt';
$report_data=array(
	'name'=>'Head_Wise_Transaction'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
	'title'=>'Head Wise Transaction',
	'info'=>$reportInfo,
	'fileName'=>$fileName,
	'head'=>array(
		array('title'=>"#"          ,'key'=>'s' ,'w'=>5,'hw'=>4),
		array('title'=>"Head"       ,'key'=>'h' ,'w'=>20,'hw'=>12),
		array('title'=>"Counter"    ,'key'=>'cn','w'=>20,'hw'=>12),
		array('title'=>"Description" ,'key'=>'p'),
		array('title'=>"Date"       ,'key'=>'d' ,'w'=>10    ,'hw'=>8),
		array('title'=>"Time"       ,'key'=>'t' ,'w'=>10    ,'hw'=>5),
		array('title'=>"In"         ,'key'=>'i' ,'w'=>10    ,'hw'=>10   ,'al'=>'r'),
		array('title'=>"Out"        ,'key'=>'o' ,'w'=>10    ,'hw'=>10   ,'al'=>'r'),
		array('title'=>"Balance"    ,'key'=>'b' ,'w'=>10    ,'hw'=>10   ,'al'=>'r'),
	),
	'data'=>$rData
);
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_tkt/'.$fileName);
//$jArray[__LINE__]=$rData; 
$gAr['report_data']= $report_data;
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');//$general->fileToVariable(__DIR__.'/cn_n_dt_wise_sales_report.phtml');
$jArray['status']   = 1;

$jArray['m']=show_msg('y');
$general->jsonHeader($jArray);
?>