<?php
$general->createLog('mpo_deposit_action',$_POST);
    $id = intval($_POST['id']);
    $type =intval($_POST['type']);
    $request = $db->get_rowData('deposit_request','id',$id);
    
    if(empty($request)){$error=fl();setMessage(63,'Deposit request');}
    elseif($type!=0&&$type!=1&&$type!=2){$error=fl(); setMessage(63,'Action');}
    elseif($request['status']!=1&&$request['status']!=2){$error=fl();setMessage(1,'Already processed.');}
    else{
        if($type==1){
            $amount = $request['amount'];
            $note = 'MPO balance deposit '.$request['note'];
            $user=$db->userInfoByID($request['user_id']);
            $db->transactionStart();
            $user_head=$acc->get_user_head($user);
            if($user_head==false){$error=fl();setMessage(66);}
            $debit_ledger=$request['bank_id'];
            if(!isset($error)){
                $balance = $acc->headBalance($user_head);
                $jArray[fl()]=$balance;

                $jArray[fl()]=1;
                $newVoucher=$acc->voucher_create(V_T_MPO_BALANCE_TRANSFER,$amount,$debit_ledger,$user_head,TIME,$note,$id,0,[],$jArray);
                if($newVoucher==false){$error=fl(); setMessage(66);}
            }
            if(!isset($error)){
                $data=['status'=>2,'action_by'=>USER_ID,'action_time'=>TIME];
                $where=['id'=>$request['id']];
                $update = $db->update('deposit_request',$data,$where,'array',$jArray);
                if(!$update){$error=fl();setMessage(66);}
            }
            
            $balance = $acc->headBalance($user_head,TIME+1);
            $jArray[fl()]=$balance;
            
            $customer_amount_receive=$db->selectAll('customer_amount_receive','where status=0 and mpo_id= '.$user['id'].' order by id desc','','array',$jArray);
            $jArray[fl()]=$customer_amount_receive;
            if(!empty($customer_amount_receive)){
                foreach($customer_amount_receive as $k=>$car){
                    if($balance<=0){
                        $jArray[fl()]=1;
                        break;
                    }
                    $check_balance=$balance-$car['amount'];
                    if($check_balance>=0){
                        $jArray[fl()][$car['id']]=$check_balance;
                        unset($customer_amount_receive[$k]);
                        $balance-=$car['amount'];
                    }
                    else{break;}
                }
                $jArray[fl()]=$customer_amount_receive;
                foreach($customer_amount_receive as $k=>$car){
                    $action =$acc->confirmCustomerDeposit($car['id'],$jArray);
                    $jArray[fl()]=$action;
                    if($action['status']==0){
                        $error=fl();
                        setMessage(1,$action['message']);
                        break;
                    }
                }
            }
            else{
                $jArray[fl()]=$customer_amount_receive;
            }
            $ac=false;
            
            if(!isset($error)){
                
                $variables=[
                    'amount'=>(float)$amount,
                    'user'=>$user['name']
                ];
                $smt->generate_sms('mpo_deposit_sms',$variables,$user['mobile'],$jArray);
                
            }
            if(!isset($error)){
                $ac=true;
                $jArray['status']=1;
                setMessage(2,'Request Accept');
            }
            $db->transactionStop($ac);
        }
        else if($type==2){
            $voucher = $acc->voucherDetails(V_T_MPO_BALANCE_TRANSFER, $id);
            if (empty($voucher)) {
                $error=fl();setMessage(1,'Voucher not found');
            }

            if(!isset($error)){
                //Delete voucher
                $voucher = $voucher[array_key_first($voucher)];
                $deleteVoucher = $acc->voucher_delete($voucher['id']);
                if ($deleteVoucher === false) {
                    $error=fl();setMessage(66);
                }
            }

            if(!isset($error)){
                $data=['status'=>0,'action_by'=>USER_ID,'action_time'=>TIME];
                $where=['id'=>$request['id']];
                $update = $db->update('deposit_request',$data,$where,'array',$jArray);
                if(!$update){$error=fl();setMessage(66);}
                else{
                    $jArray['status']=1;
                    setMessage(2,'Request Deleted');   
                }
            }
        }
        else{
            $data=['status'=>0,'action_by'=>USER_ID,'action_time'=>TIME];
            $where=['id'=>$request['id']];
            $update = $db->update('deposit_request',$data,$where,'array',$jArray);
            if(!$update){$error=fl();setMessage(66);}
            else{
                $jArray['status']=1;
                setMessage(2,'Request Cancel');   
            }
        }
    }
    
$general->createLog('mpo_deposit_action',$jArray);