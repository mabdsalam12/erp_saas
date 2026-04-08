<?php
	
	$rData=[];
	$balance=0;
	$totalDr=0;
	$totalCr=0;
	$wob=0;

	$openingBalance=$acc->headBalance($hID,strtotime('-1 second',$from));
	$balance+=$openingBalance;
	$withoutOpaningBbalance=0;

	$rData[] = [
		's' => '',
		'u' => ['t' => 'Opening Balance', 'b' => 1, 'col' => 7],
		't' => ['t' => false],
		'cd' => ['t' => false],
		'dt' => ['t' => false],
		'p' => ['t' => false],
		'd' => ['t' => false],
		'c' => ['t' => false],
		'b' => ['t' => $general->numberFormat($openingBalance), 'b' => 1]
	];

	$dStatus=$db->permission(90);
	$canPrint=0;//$db->permission(100);
	
	$statement=$acc->ledger_statement($hID,$from,$to,$jArray);
	if(!empty($statement)){
        $jArray[fl()] = $statement;
		$uIDs=[];
		$veIDs=[];
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
				'b' => $general->numberFormat($balance),
			];
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
		'b' => ['t' => $general->numberFormat($balance), 'b' => 1],
		'r' => ''
	];


	$fileName='ledger_statement_'.TIME.rand(0,999).'.txt';
	$headData = [
		['title' => "#"							, 'key' => 's', 'w' => 5, 'hw' => 4],
		['title' => "User"						, 'key' => 'u', 'w' => 10, 'hw' => 8],
		['title' => "type"						, 'key' => 't', 'w' => 10, 'hw' => 8],
		['title' => "Code"						, 'key' => 'cd', 'w' => 10, 'hw' => 8],
		['title' => "Date time"					, 'key' => 'dt', 'w' => 20, 'hw' => 12],
		['title' => "Description"				, 'key' => 'p', 'w' => 20],
		['title' => "Debit"						, 'key' => 'd', 'w' => 20, 'hw' => 8, 'al' => 'r'],
		['title' => "Credit"					, 'key' => 'c', 'w' => 20, 'hw' => 8, 'al' => 'r'],
		['title' => "Balance"					, 'key' => 'b', 'w' => 20, 'hw' => 8, 'al' => 'r'],
		['title' => "View"						, 'key' => 'v', 'noForExcel' => 1],
	];

	$report_data = [
		'name'     => 'Ledger_Statement' . date('d_m_Y', $from) . '_' . date('d_m_Y', $to),
		'title'    => 'Ledger Statement',
		'info'     => $reportInfo,
		'fileName' => $fileName,
		'head'     => $headData,
		'data'     => $rData
	];

	$gAr['report_data']= $report_data;

	textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
	$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');

	$jArray['status']=1;
