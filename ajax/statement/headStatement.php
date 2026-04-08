<?php
	$reportInfo=[];
	$dRange=$_POST['dRange'];
	$reportInfo[]="Date: $dRange";
	$general->getFromToFromString($dRange,$from,$to);
	$hID    = intval($_POST['ledger_id']);
	$type = intval($_POST['type']);
	if($type==1){
		include_once __DIR__.'/ledgerStatement.php';
	}
	else if($type== 2){
		include_once __DIR__.'/summarizeLedgerStatement.php';
	}
	else if($type== 3){
		include_once __DIR__.'/ledger_statement.php';
	}
	else{

	
		$rData=[];
		if($hID>0){
			// $closingBalance=
			$acc->headBalance($hID,strtotime('-1 second',$from));
			$mm=strtotime('-1 second',$from);
			$closingBalance=$acc->headBalance($hID,$mm,0,[],$jArray);
			$jArray[fl()]=$general->make_date($mm,'time');
		}
		else{
			$closingBalance=0;
		}
		$od=0;
		$oc=0;
		$balance=0;
		if($closingBalance<0){
			$oc=-$closingBalance;
		}
		else{
			$od=$closingBalance;
		}
		$balance+=$od;
		$balance-=$oc;
		$serial=1;
		$rData[]=[
			's'=>$serial++,
			'h'=>'',
			'v'=>'',
			'd'=>'',
			'p'=>['t'=>'Opening','b'=>1],
			'u'=>'',
			'i'=>'',
			'o'=>'',
			'b'=>$general->numberFormat($balance),
		];

		$jArray[__LINE__]=$general->make_date($from,'time');
		$jArray[__LINE__]=$general->make_date($to,'time');
		$statement=$acc->headStatement(0,$hID,$from,$to,$jArray);
		$jArray[fl()]=$statement;
		$tIn=0;
		$tOut=0;
		$veIDs=[];
		if(!empty($statement)){
			$users=$db->allUsers();
			foreach($statement as $s){
				$tIn+=$s['in'];
				$tOut+=$s['out'];

				$balance+=$s['in'];
				$balance-=$s['out'];
				$veIDs[$s['voucher_id']]=$s['voucher_id'];
				$rData[]=[
					's'=>$serial++,
					//'h'=>$s['hTitle'],//.' '.$s['veID'],
					'h'=>$s['head_title'],
					'v'=>$s['code'],
					'd'=>$general->make_date($s['time'],'time'),
					'p'=>$s['note'],
					'u'=>@$users[$s['user_id']]['username'],
					'c'=>@$users[$s['createdBy']]['username'],
					'i'=>$general->numberFormat($s['in']),
					'o'=>$general->numberFormat($s['out']),
					'b'=>$general->numberFormat($balance),
				];
			}
		}
//$jArray[__LINE__]=$veIDs;
//$jArray[__LINE__]=implode(',',$veIDs);
		$rData[]=[
			's'=>'',
			'h'=>['t'=>'Total v','b'=>1,'col'=>5],
			'v'=>['t'=>false],
			'd'=>['t'=>false],
			'p'=>['t'=>false],
			'c'=>['t'=>false],
			'i'=>['t'=>$general->numberFormat($tIn),'b'=>1],
			'o'=>['t'=>$general->numberFormat($tOut),'b'=>1],
			'b'=>['t'=>$general->numberFormat($balance),'b'=>1],
		];

		$fileName='purRep_'.TIME.rand(0,999).'.txt';
		$report_data=array(
			'name'      => 'LedgerStatement'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
			'title'     => 'Ledger Statement',
			//'info'      => $reportInfo,
			'fileName'  => $fileName,
			'head'=>array(
				array('title'=>"#"          ,'key'=>'s'),
				array('title'=>"Ledger"       ,'key'=>'h'     ,'hw'=>15),
				['title'=>"Voucher code"         ,'key'=>'v'],
				array('title'=>"Date Time"  ,'key'=>'d'),
				array('title'=>"Particular" ,'key'=>'p'),
				//array('title'=>"User"       ,'key'=>'u'),
				array('title'=>"Create by"  ,'key'=>'c'),
				array('title'=>"In"         ,'key'=>'i'    ,'al'=>'r'),
				array('title'=>"Out"        ,'key'=>'o'     ,'al'=>'r'),
				array('title'=>"Balance"    ,'key'=>'b'    ,'al'=>'r'),
			),
			'data'=>$rData
		);
		//$jArray[__LINE__]=$report_data;
		$gAr['report_data']= $report_data;
		textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
		$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
		$jArray['status']=1;   
	
	$jArray['m']=show_msg('y');
	$general->jsonHeader($jArray);
	}