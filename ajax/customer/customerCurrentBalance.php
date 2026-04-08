<?php
	$cID = intval($_POST['cID']);
	if(0<$cID){
		$c=$smt->customerInfoByID($cID);
		$due_info=$acc->customer_due_details($cID);
		$jArray['status']=1;
		$jArray['balance']=intval(-$due_info['customer_balance']);
		$jArray['cMobile']=$c['mobile'];
		$jArray['cAddress']=$c['address'];
		$jArray['due_invoice']=[];
		$jArray[fl()]=$due_info;
		$jArray[fl()]=$c;
		if(!empty($due_info['due_data'])){
			foreach($due_info['due_data'] as $d){
				$jArray['due_invoice'][$d['id']]=[
					'id'		=> $d['id'],
					'invoice_no'=> $d['invoice_no'],
					'due'		=> (float)$d['due']
				];
			}
		}
		$customer_data=$general->getJsonFromString($c['data']);
		$credit_limit=0;
		$can_invoice=1;
		if(isset($customer_data['credit_limit'])){
			$credit_limit=$customer_data['credit_limit'];
		}
		if(($credit_limit!=0&&$jArray['balance']>=$credit_limit)&&$jArray['balance']!==0){
			$can_invoice=0;
			$jArray[fl()]=1;
		}
		if($due_info['due_date']>0&&$due_info['due_date']<TODAY_TIME){
			$can_invoice=0;
			$jArray[fl()]=1;
		}
		// $can_invoice=0;
		$jArray['can_invoice']=$can_invoice;
	}
