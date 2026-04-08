<?php
	$dRange=$_POST['dRange'];
	$reportInfo=[];
	$reportInfo[]='Date : '.$dRange;
	$general->getFromToFromString($dRange,$from,$to);
	$jArray[fl()]=$from;
	$jArray[fl()]=$general->make_date($from,'time');
	$jArray[fl()]=$to;
	$jArray[fl()]=$general->make_date($to,'time');
	//$vType  = V_T_INCOME;
	$vType   = intval($_POST['type']);
	$hID    = intval(@$_POST['hID']);
	$uID    = intval(@$_POST['uID']);
	$caID   = intval(@$_POST['caID']);
	$isSummary= 0;
	$voucher_details = $acc->voucherDetails($vType,'',$from,$to);
	//print_r($voucher_details);
	$jArray[fl()]=$voucher_details;
	$rData=[];

	$ta=0;
	$total=1;
	
	if(!empty($voucher_details)){
		$general->getIDsFromArray($voucher_details,'debit,credit,createdBy',$ledger_ids,$ledger_ids,$user_ids);
		$allUsers = $db->allUsers(' and id in('.implode(',',$user_ids).')');
		$heads=$db->selectAll('a_ledgers','where id in('.implode(',',$ledger_ids).')','id,title,code,charts_accounts_id');
		$general->arrayIndexChange($heads);
		$base = $db->allBase_for_voucher();
		$jArray[fl()]=$heads;
		foreach($voucher_details as $tr){
			$debit=$tr['debit']; 
			$credit=$tr['credit'];
			if($hID>0){
				if($debit!=$hID && $credit!=$hID) continue;
			}

			if($caID>0){
				if($heads[$debit]['charts_accounts_id']!= $caID && $heads[$credit]['charts_accounts_id'] != $caID) continue;
			}
			$ta+=$tr['amount']; 
			$a=$tr['amount'];
			// $jArray[fl()][]=$debit;
			// $jArray[fl()][]=$heads[$debit]['title'];
			$data=[
				's' => $total++,
				'db'=> $heads[$debit]['code'].' '.$heads[$debit]['title'],//.' '.$tr['veID'],
				'cr'=> $heads[$credit]['code'].' '.$heads[$credit]['title'],//.' '.$tr['veID'],
				'b' => $base[$tr['base_id']]['title'],//.' '.$tr['veID'],
				'u' => @$allUsers[$tr['createdBy']]['username'],
				'dt'=> date('d-m-Y H:i',$tr['time']),
				't'=>$tr['time'],
				'p' => $tr['note'],
				'a' => $general->numberFormat(n: $a),
			];
			$rData[]=$data;
		}
		$jArray[fl()]=$rData;
	}

	$general->arraySortByColumn($rData,'t',SORT_DESC);
	$serial=1;
	foreach($rData as $k=>$v){
		$rData[$k]['s'] = $serial++;
	}
	$rData[]=[
		's' => '',
		'db'=> array('t'=>'Total','b'=>1,'col'=>6),
		'cr' => array('t'=>false),
		'b' => array('t'=>false),
		'u' => array('t'=>false),
		'dt'=> array('t'=>false),
		'p' => array('t'=>false),
		'a' => ['t'=>$general->numberFormat($ta),'b'=>1],
	];


	$fileName='income_expense_list_'.TIME.rand(0,999).'.txt';
	$headData=[
		array('title'=>"#"          ,'key'=>'s' ,'w'=>5 ,'hw'=>4),
		array('title'=>"Debit"      ,'key'=>'db' ),
		array('title'=>"Credit"     ,'key'=>'cr' ),
		array('title'=>"Base"     	,'key'=>'b' ),
		array('title'=>"User"       ,'key'=>'u' ),
		array('title'=>"Date time"  ,'key'=>'dt'),
		array('title'=>"Description",'key'=>'p' ),
		array('title'=>"Amount"     ,'key'=>'a' ,'al'=>'r')
	];


	$report_data=array(
		'name'      => 'Cash_Flow_Report'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
		'title'     => 'Cash Flow Report',
		'info'      => $reportInfo,
		'fileName'  => $fileName,
		'head'      => $headData,
		'data'      => $rData
	);

	$gAr['report_data']= $report_data;

	textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
	$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');

	$jArray['status']=1;


?>