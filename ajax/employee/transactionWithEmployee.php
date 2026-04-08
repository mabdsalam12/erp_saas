<?php
    $date= strtotime($_POST["date"]);
    $id     = intval($_POST["id"]);
    $trType = intval($_POST["trType"]);
    $amount = floatval($_POST["trAmount"]);
    $bank_id = floatval($_POST["bank_id"]);
    $note   = $_POST["trNote"];
    $employee=$smt->employeeInfoByID($id);
    if(!empty($employee)){
        $cash_accounts=$acc->get_all_cash_accounts();
        if($date<strtotime('-10 year')){$error=fl();setMessage(63,'Date');}
        elseif($trType!=1&&$trType!=2){$error=fl();setMessage(63,'Transaction type');}
        elseif(!isset($cash_accounts[$bank_id])){$error=fl(); setMessage(63,'Bank');}
        elseif($date==TODAY_TIME){$date=TIME;}
        if(!isset($error)){
            $db->transactionStart();
            $chID=$acc->getEmployeeHead($employee,$jArray);
            if($chID==false){$error=fl();setMessage(66);}
            //$cashHead=$acc->getSystemHead(AH_CASH);
            //if($cashHead==false){$error=fl();setMessage(66);}
            if(!isset($error)){
                if($trType==2){
                    $newVoucher=$acc->newVoucher(0,V_T_EMPLOYEE_PAY,$amount,$chID,$bank_id,$date,$note,$id);
                }
                else{
                    $newVoucher=$acc->newVoucher(0,V_T_RECEIVE_FROM_EMPLOYEE,$amount,$bank_id,$chID,$date,$note,$id);
                }
                if($newVoucher==false){$error=fl();setMessage(66);}
            }
            if(!isset($error)){
                $ac=true;
                $jArray['status']=1;
                setMessage(2,'Employee transaction added successfully');
            }
            else{
                $ac=false;
            }
            $db->transactionStop($ac);


        }
    }
    