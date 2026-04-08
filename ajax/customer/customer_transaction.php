<?php
	$general->createLog('customer_transaction',$_POST);
	$trDate 	= strtotime($_POST["trDate"]);
	$customer_id= intval($_POST["customer_id"]);
	$trType		= intval($_POST["trType"]);
	$employee_id= intval($_POST["employee_id"]);
	$invoice_id	= intval($_POST["invoice_id"]);
	$amount 	= floatval($_POST["trAmount"]);
	$note   	= $_POST["trNote"];
	$c=$smt->customerInfoByID($customer_id);
	if(!empty($c)){
		if($trDate<strtotime('-10 year')){$error=fl();setMessage(63,'Date');}
		elseif(
			$trType!=V_T_RECEIVE_FROM_CUSTOMER
			&&$trType!=V_T_CUSTOMER_COLLECTION_DISCOUNT
            &&$trType!=V_T_NEW_RECOVERABLE_ENTRY
            &&$trType!=V_T_CUSTOMER_BAD_DEBT
			&&$trType!=V_T_CUSTOMER_YEARLY_DISCOUNT
			&&$trType!=V_T_PAY_TO_CUSTOMER
			){$error=fl();setMessage(63,'Transaction type');}
		elseif($trType==V_T_NEW_RECOVERABLE_ENTRY && empty($db->getRowData('employees',"where id=$employee_id and isActive=1"))){
			$error=fl();
			setMessage(63,'employee');
		}
		if($trDate==TODAY_TIME){$trDate=TIME;}
		if($invoice_id>0){
			$s = $smt->saleInfoByID($invoice_id);
		}

		if(!isset($error)){
			$db->transactionStart();
			$customer_head=$acc->getCustomerHead($c);
			if($customer_head==false){$error=fl();setMessage(66);}
			$cashHead=$acc->getSystemHead(AH_CASH);
			if($cashHead==false){$error=fl();setMessage(66);}
			$discount_head=$acc->getSystemHead(AH_CUSTOMER_COLLECTION_DISCOUNT);
			if($discount_head==false){$error=fl();setMessage(66);}
			$yearly_head=$acc->getSystemHead(AH_CUSTOMER_YEARLY_DISCOUNT);
			if($yearly_head==false){$error=fl();setMessage(66);}
            if($trType==V_T_NEW_RECOVERABLE_ENTRY){
				$recoverable_head=$acc->getSystemHead(AH_RECOVERABLE_COLLECTION); 
            }
            else if($trType==V_T_CUSTOMER_BAD_DEBT){
				$bad_debit_head=$acc->getSystemHead(AH_CUSTOMER_BAD_DEBT); 
            }
			if(!isset($error)){
				if($invoice_id>0){
					if(!empty($s)&&$s['customer_id']==$c['id']){
						$sale_data=$general->getJsonFromString($s['data']);
						$already_paid=0;
						if(isset($sale_data['paid'])){
							$already_paid=$sale_data['paid'];
						}
						$already_paid+=$amount;
						if($s['total']<$already_paid){
							$already_paid=$s['total'];
						}
						$sale_data['paid']=$already_paid;
						$data=[
							'data'=>json_encode($sale_data)
						];
						$note.=' '.$s['invoice_no'];
						$update=$db->update('sale',$data,['id'=>$invoice_id],'array',$jArray);
						if(!$update){setMessage(66);$error=fl();}
					}
				}

				if($trType==V_T_PAY_TO_CUSTOMER){
					$voucher_type=$trType;
					$newVoucher=$acc->voucher_create($trType,$amount,$customer_head,$cashHead,$trDate,$note,$customer_id);
				}
				else
			 	{
					$ref = $customer_id;
					$voucher_type=$trType;
					if($trType==V_T_CUSTOMER_COLLECTION_DISCOUNT){
						$head=$discount_head;
					}
					else if($trType==V_T_CUSTOMER_YEARLY_DISCOUNT){
						$head=$yearly_head;
					}
					else if($trType==V_T_NEW_RECOVERABLE_ENTRY){
						$head=$recoverable_head;
						$data=[
							'customer_id'	=> $customer_id,
							'employee_id'	=> $employee_id,
							'amount'		=> $amount,
							'createdBy'		=> USER_ID,
							'createdOn'		=> TIME,
						];
						$ref = $db->insert('recoverable_collection',$data,true);
						if(!$ref){
							$error=fl();setMessage(66);
						}
					}
					else if($trType==V_T_CUSTOMER_BAD_DEBT){
						$head=$bad_debit_head;
					}
					else{
						$head=$cashHead;
						$voucher_type=V_T_RECEIVE_FROM_CUSTOMER;
					}
					$newVoucher=$acc->voucher_create($voucher_type,$amount,$head,$customer_head,$trDate,$note,$ref);
					if($newVoucher==false){$error=fl();setMessage(66);}
				}
			}
			
            
			if(!isset($error)){
				$jArray[fl()]=$trType;
				if($trType==V_T_RECEIVE_FROM_CUSTOMER){
					$due=$acc->headBalance($customer_head,strtotime('+1 minute'));
					$variables=[
						'amount'	=> $amount,
						'due'		=> $due
					];
					$jArray[fl()]=$variables;
					$sms=$smt->generate_sms('money_receive_form_customer',$variables,$c['mobile'],$jArray);
					$jArray[fl()]=$sms;

				}
			}
			$ac=false;
			if(!isset($error)){
				$ac=true;
				$jArray['status']=1;
				setMessage(2,'Customer transaction added successfully');
			}
			$db->transactionStop($ac);
		}
	}
	$general->createLog('customer_transaction',$jArray);
