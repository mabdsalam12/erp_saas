<?php
	//$cID=intval($_POST['cID']);
	$persons=$db->selectAll('person','where isActive in(0,1) order by name asc','ledger_id,id,name');
	$general->arrayIndexChange($person,'id');
	$dRange=date('d-m-Y').' to '.date('d-m-Y');
	if(isset($_POST['dRange'])){
		if($_POST['dRange']!=''){
			$dRange=$_POST['dRange'];
		}
	}
	$reportInfo=array();
	$reportInfo[]='Date : '.$dRange;
	$general->getFromToFromString($dRange,$from,$to);
	$vType  = [ V_T_PAY_TO_PERSON,V_T_RECEIVE_FROM_PERSON];
	
	
	$rData=[];
    $totalCr=0;                         
	$totalDr=0; 
	$total=1;
	$voucher_details = $acc->voucherDetails($vType,from:$from,to:$to);
	if(!empty($voucher_details)){
		$general->getIDsFromArray($voucher_details,'debit,credit,createdBy',$ledger_ids,$ledger_ids,$user_ids);
		$allUsers=$db->selectAll('users','where id in('.implode(',',$user_ids).')');
		$ledgers=$db->selectAll('a_ledgers','where id in('.implode(',',$ledger_ids).')','id,title');
		$general->arrayIndexChange($ledgers);
		$jArray[fl()]=$ledgers;
		foreach($voucher_details as $v){
			$in=0;
			$out=0;
			$ledger_id=0;
			if($v['type']==V_T_PAY_TO_PERSON){
				$out = $v['amount'];
				$ledger_id = $v['debit'];
			}
			elseif($v['type']==V_T_RECEIVE_FROM_PERSON){
				$in = $v['amount'];
				$ledger_id = $v['credit'];
			}
			$totalCr+=$in;
			$totalDr+=$out;
			$data=array(
				's' => $total++,
				'h' => $ledgers[$ledger_id]['title']??'',//.' '.$tr['veID'],
				'u' => @$allUsers[$v['createdBy']]['username']??'',
				'dt'=> $general->make_date($v['time'],'time'),
				'd' => $v['note'],
                'r' => $general->numberFormat($out),	
				'p' => $general->numberFormat($in),
			);
			$rData[]=$data;
		}
	}
	//$jArray[__LINE__]=$tData;
	$rData[]=array(
		's' => '',
		'h'=> array('t'=>'Total','b'=>1,'col'=>4),
		'u' => array('t'=>false),
		'dt'=> array('t'=>false),
		'd' => array('t'=>false),
        'r' => ['t'=>$general->numberFormat($totalDr),'b'=>1],
		'p' => ['t'=>$general->numberFormat($totalCr),'b'=>1],
	);
	$fileName='personPaymentList_'.TIME.rand(0,999).'.txt';
	$headData=array(
		array('title'=>"#"              ,'key'=>'s' ,'w'=>5 ,'hw'=>4),
		array('title'=>"Person"         ,'key'=>'h' ),
		array('title'=>"User"           ,'key'=>'u' ,'w'=>10,'hw'=>8),
		array('title'=>"Date time"      ,'key'=>'dt','w'=>20,'hw'=>12),
		array('title'=>"Description"    ,'key'=>'d' ,'w'=>20),
        array('title'=>"Receive"        ,'key'=>'r' ,'w'=>20,'hw'=>8    ,'al'=>'r'),
		array('title'=>"Pay"            ,'key'=>'p' ,'w'=>20,'hw'=>8    ,'al'=>'r'),
	);
	$report_data=array(
		'name'      => 'customer_payment_list_'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
		'title'     => 'customer payment list',
		'info'      => $reportInfo,
		'fileName'  => $fileName,
		'head'      => $headData,
		'data'      => $rData
	);
	$gAr['report_data']= $report_data;

	textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
	$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
	$jArray['status']=1;