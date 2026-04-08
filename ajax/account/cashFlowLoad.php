<?php
	$dRange=date('d-m-Y').' to '.date('d-m-Y');
	if(isset($_POST['dRange'])){
		if($_POST['dRange']!=''){
			$dRange=$_POST['dRange'];
		}
	}
	$reportInfo=array();
	$reportInfo[]='Date : '.$dRange;
	$general->getFromToFromString($dRange,$from,$to);
	$jArray[__LINE__]=$from;
	$jArray[__LINE__]=$general->make_date($from,'time');
	$jArray[__LINE__]=$to;
	$jArray[__LINE__]=$general->make_date($to,'time');
	$vType  = intval(@$_POST['vType']);
	$cnID   = intval(@$_POST['cnID']);
	$hID    = intval(@$_POST['hID']);
	$uID    = intval(@$_POST['uID']);
	$scID  = 0;//intval(@$_POST['scID']);
	$isSummary= 0;
	// $tData=$acc->cashFlowReport($from,$to,$hID,$uID,$vType,'',$jArray);
	// $jArray[__LINE__]=$tData;
	$rData=array();
	$balance=0;
	$totalDr=0;
	$totalCr=0;
	$wob=0;

	$cashHead=$acc->getSystemHead(AH_CASH);
	$openingBalance=$acc->headBalance($cashHead,strtotime('-1 second',$from));
	$balance+=$openingBalance;
	$withoutOpaningBbalance=0;

	$rData[] = [
		's' => '',
		'u' => ['t' => 'Opening Balance', 'b' => 1, 'col' => 8],
		't' => ['t' => false],
		'cd' => ['t' => false],
		'dt' => ['t' => false],
		'p' => ['t' => false],
		'd' => ['t' => false],
		'c' => ['t' => false],
		'wb' => ['t' => false],
		'b' => ['t' => $general->numberFormat($openingBalance), 'b' => 1],
		'r' => '',
		'pr' => ''
	];

	$dStatus=$db->permission(90);
	$canPrint=0;//$db->permission(100);
	$cash_ledger=$acc->getSystemHead(AH_CASH);
	$statement=$acc->ledger_statement($cash_ledger,$from,$to,$jArray);
	if(!empty($statement)){
		$uIDs=array();
		
		$veIDs=array();
		foreach($statement as $tr){
			$uIDs[$tr['createdBy']]=$tr['createdBy'];
			$veIDs[$tr['voucher_id']]=$tr['voucher_id'];
		}
		$allUsers=$db->selectAll('users','where id in('.implode(',',$uIDs).')');
		$general->arrayIndexChange($allUsers,'id');

		$general->arrayContentShow($statement);
		$total=1;
		$canAppChe = true;

		foreach($statement as $tr){
			$totalDr += $tr['debit'];
			$totalCr += $tr['credit'];
			$balance += $tr['debit'];
			$balance -= $tr['credit'];
			$bl = $tr['debit'];
			$bl -= $tr['credit'];
			$withoutOpaningBbalance += $bl;

			$data = [
				's' => $total++,
				'h' => $tr['head_title'],
				'u' => @$allUsers[$tr['createdBy']]['username'],
				'cd' => $tr['code'],
				'dt' => date('d-m-Y H:i', $tr['time']),
				't' => $tr['type_title'],
				'p' => $tr['note'],
				'd' => $general->numberFormat($tr['debit']),
				'c' => $general->numberFormat($tr['credit']),
				'v' => '<button class="btn btn-info voucher_details_load" data-id="' . $tr['voucher_id'] . '">View</button>',
				'wb' => $general->numberFormat($withoutOpaningBbalance),
				'b' => $general->numberFormat($balance),
			];


			if($dStatus){
				if(in_array($tr['type'],REMOVE_ABLE_VOUCHER_TYPE)){
					$data['r']='<button  onclick="voucherRemove('.$tr['voucher_id'].')">X</button>';
				}
				else{
					$data['r']='';//$tr['vType'];
				}
			}
			if($canPrint){
				$data['pr']='<a href="?print=voucher&voucher_id='.$tr['voucher_id'].'" target="blank">Print</a>';
			}
			$rData[]=$data;

		}
	}
	$rData[] = [
		's' => '',
		'u' => ['t' => 'Total', 'b' => 1, 'col' => 4],
		'dt' => ['t' => false],
		'p' => ['t' => false],
		't' => ['t' => false],
		'd' => ['t' => $general->numberFormat($totalDr), 'b' => 1],
		'c' => ['t' => $general->numberFormat($totalCr), 'b' => 1],
		'wb' => ['t' => $general->numberFormat($withoutOpaningBbalance), 'b' => 1],
		'b' => ['t' => $general->numberFormat($balance), 'b' => 1],
		'r' => ''
	];


	$fileName='cashflowreport_'.TIME.rand(0,999).'.txt';
	$headData = [
		['title' => "#"							, 'key' => 's', 'w' => 5, 'hw' => 4],
		['title' => "User"						, 'key' => 'u', 'w' => 10, 'hw' => 8],
		['title' => "type"						, 'key' => 't', 'w' => 10, 'hw' => 8],
		['title' => "Code"						, 'key' => 'cd', 'w' => 10, 'hw' => 8],
		['title' => "Date time"					, 'key' => 'dt', 'w' => 20, 'hw' => 12],
		['title' => "Description"				, 'key' => 'p', 'w' => 20],
		['title' => "Debit"						, 'key' => 'd', 'w' => 20, 'hw' => 8, 'al' => 'r'],
		['title' => "Credit"					, 'key' => 'c', 'w' => 20, 'hw' => 8, 'al' => 'r'],
		['title' => "Balance without opening "	, 'key' => 'wb', 'w' => 20, 'hw' => 8, 'al' => 'r'],
		['title' => "Balance"					, 'key' => 'b', 'w' => 20, 'hw' => 8, 'al' => 'r'],
		['title' => "View"						, 'key' => 'v', 'noForExcel' => 1],
	];
	if ($canPrint) {
		$headData[] = ['title' => "Print", 'key' => 'pr', 'w' => 20, 'noForExcel' => 1];
	}
	if ($dStatus) {
		$headData[] = ['title' => "X", 'key' => 'r', 'hw' => 1, 'noForExcel' => 1];
	}

	$report_data = [
		'name'     => 'Cash_Flow_Report' . date('d_m_Y', $from) . '_' . date('d_m_Y', $to),
		'title'    => 'Cash Flow Report',
		'info'     => $reportInfo,
		'fileName' => $fileName,
		'head'     => $headData,
		'data'     => $rData
	];

	$gAr['report_data']= $report_data;

	textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
	$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');

	$jArray['status']=1;
