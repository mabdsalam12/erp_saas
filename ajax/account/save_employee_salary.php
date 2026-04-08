<?php
$year           = intval($_POST['salary_year']);
$month          = intval($_POST['salary_month']);

$department_id  = intval($_POST['department_id']);
$employeesData  = $_POST['employeesData'];

$firstDayTimestamp = strtotime("$year-$month-01");
$department=$db->get_rowData('employee_department','id',$department_id);

if(!empty($department)){
    $db->transactionStart();

    if(!empty($employeesData)){
        $salary_allowance_data=$db->selectAll("salary_allowance",'','id,ledger_id'); 
        $general->arrayIndexChange($salary_allowance_data,'id');
        foreach($employeesData as $e){
            $generate_date       = strtotime($e['generate_date']);
            $total=0;
            $already=$db->getRowData('employee_salary','where employee_id='.$e['id'].' and salary_date='.$firstDayTimestamp);

            if(empty($already)){
                $data=[
                    'employee_id'   => $e['id'],
                    'generate_date'      => $generate_date,
                    'salary_date'   => $firstDayTimestamp,
                    'allowance'     => 0,
                    'total'         => 0,
                ];

                $db->arrayUserInfoAdd($data);
                $salary_id=$db->insert('employee_salary',$data,true); 
            }
            else{
                $salary_id=$already['id'];
            }

            $other_allowances = [];
            if($salary_id!=false){
                $where=['salary_id'=>$salary_id];
                $delete=$db->delete('employee_salary_allowance',$where);
                $allowances_total=0;
                $salary_allowances = 0;
                if(isset($e['allowances'])&&!empty($e['allowances'])){
                    foreach($e['allowances'] as $a){
                        $salary = true;
                        $amount=floatval($a['amount']);
                        $allowances_total+=$amount;
                        if($amount>0){
                            $data=[
                                'salary_id'             => $salary_id,
                                'employee_id'           => $e['id'],
                                'salary_allowance_id'   => $a['id'],
                                'salary_period'         => $firstDayTimestamp,
                                'amount'                => $amount,
                            ];
                            $insert=$db->insert('employee_salary_allowance',$data);
                            if($insert==false){$error=fl();setMessage(66);}
                            if(isset($salary_allowance_data[$a['id']])){
                                $al = $salary_allowance_data[$a['id']];
                                if($al['ledger_id']>0){
                                    $salary = false;
                                    $other_allowances[$a['id']] = [
                                        'salary_allowance_id'=>$a['id'],
                                        'ledger_id'=>$al['ledger_id'],
                                        'amount'=>$amount,
                                    ];
                                }
                            }
                            if($salary){
                                $salary_allowances+=$amount;
                            }
                        }

                    } 
                }
                $total=floatval($e['salary'])+$salary_allowances;
                if(!isset($error)){
                    $data=[
                        'allowance' => $allowances_total,
                        'generate_date'  => $generate_date,
                        'total'     => $total,
                        'salary'   => floatval($e['salary']),
                    ];
                    $where=['id'=>$salary_id];
                    $update=$db->update('employee_salary',$data,$where);
                    if($update==false){$error=fl();setMessage(66);}
                }
                if(!isset($error)){
                    $employee=$smt->employeeInfoByID($e['id']);
                    $salary_head= $acc->getSystemHead(AH_EMP_SALARY,$jArray);
                    if($salary_head==false){$error=fl();setMessage(66);}
                    $employee_head= $acc->getEmployeeHead($employee,$jArray);
                    if($employee_head==false){$error=fl();setMessage(66);}
                    $voucher_refs = [];
                    if(!empty($salary_allowance_data)){
                        foreach($salary_allowance_data as $sa){
                            $voucher_refs[] = "{$salary_id}_{$sa['id']}"; // সব রেফারেন্স নিচ্ছি ভাউচার সার্চ করার জন্য
                        }
                    }
                }
                $have_other_allowances = empty($other_allowances); // সব ইম্পটি হলে সব ডিলেট করতে হবে
                if(!isset($error)){
                    $ledger_ids = array_column($other_allowances,'ledger_id');
                    $jArray[fl()] = $other_allowances;
                    $old_voucher=$acc->voucherDetails(V_T_EMPLOYEE_SALARY_ALLOWANCE,$voucher_refs);
                    $jArray[fl()] = $old_voucher;
                     $note = 'Salary allowance create '.$employee['name'].' '.$month.'-'.$year;
                     if(!empty($old_voucher)){
                        foreach($old_voucher as $v){
                            $salary_allowance_id = explode('_',$v['reference'])[1];
                             $jArray[fl()][] = $salary_allowance_id;
                            if(isset($other_allowances[$salary_allowance_id])){
                                $al = $other_allowances[$salary_allowance_id];
                                $voucher=$acc->voucherEdit(
                                    $v['id'],
                                    $al['amount'],
                                    $note,
                                    $al['ledger_id'],
                                    $employee_head,
                                    $generate_date,
                                    [],
                                    $jArray
                                );
                                $jArray[fl()][]=$al['amount'];
                                if($voucher==false){
                                    $error=fl();setMessage(66);
                                    break;
                                }
                                $jArray[fl()][] = $salary_allowance_id;
                                unset($other_allowances[$salary_allowance_id]); // এখানে unset করে দিলাম যেগুলো আপডেট হচ্ছে বাকি গুলা নতুন করে ক্রিয়েট করতে হবে 
                            }
                            else{
                                 $jArray[fl()][] = $salary_allowance_id;
                                $delete = $acc->voucher_delete($v['id']); //যেগুলো আগে ছিলো এখন নেই সেগুলো ডেলেট
                                if($delete==false){
                                    $error=fl();setMessage(66);
                                    break;
                                }
                                
                            }
                            
                        }
                    }
                    $jArray[fl()] = $other_allowances;
                    if(!empty($other_allowances)){ // নতুন গুলা আবার ভাউচার তৈরি করতেছি
                        foreach($other_allowances as $salary_allowance_id=>$al){
                            $voucher=$acc->voucher_create(V_T_EMPLOYEE_SALARY_ALLOWANCE,$al['amount'],$al['ledger_id'],$employee_head,$generate_date,$note,"{$salary_id}_{$salary_allowance_id}");
                            if($voucher==false){
                                $error=fl();setMessage(66);
                                break;
                            }
                        }
                    }

                }
                if($total > 0){

                    if(!isset($error)){
                        
                        $create_new=1;
                        if(!empty($already)){
                            $old_voucher=$acc->voucherDetails(V_T_EMPLOYEE_SALARY,$salary_id);
                            $o=current($old_voucher);
                            $jArray[fl()]=$o;
                            $jArray[fl()]=$employee_head;
                            if(!empty($old_voucher)){
                                // $dHead=$employee_head;
                                // $cHead=$cashHead; 
                                $update=$acc->voucherEdit($o['id'],$total,$o['note'],$salary_head,$employee_head,$generate_date,[],$jArray);
                                if($update==false){
                                    $error=fl();setMessage(66);
                                }
                                else{
                                    $create_new=0;
                                }
                            }
                        }
                        
                        if($create_new==1){
                            $voucher=$acc->voucher_create(V_T_EMPLOYEE_SALARY,$total,$salary_head,$employee_head,$generate_date,'Salary create '.$employee['name'].' '.$month.'-'.$year,$salary_id);
                            if($voucher==false){
                                $error=fl();setMessage(66);
                            }
                        }
                    }
                }
                if($total<=0){
                    $old_voucher=$acc->voucherDetails(V_T_EMPLOYEE_SALARY,$salary_id);
                    foreach($old_voucher as $o){
                        $acc->voucher_delete($o['id']);
                    }
                }
                if($have_other_allowances){
                    $old_voucher=$acc->voucherDetails(V_T_EMPLOYEE_SALARY_ALLOWANCE,$voucher_refs);
                    foreach($old_voucher as $o){
                        $acc->voucher_delete($o['id']);
                    }
                }
                if($total<=0 && $have_other_allowances){
                    $db->delete('employee_salary_allowance',['salary_id'=>$salary_id]);
                    $db->delete('employee_salary',['id'=>$salary_id]);
                }
            }
            else{
                $error=fl();setMessage(66);
            }
        
    }
    }
    else{
        $error=fl();setMessage(63,'Request');
    }

    if(!isset($error)){
        $ac=true;
        $jArray['status']=1;
        setMessage(30,'Employee Salary');
    }
    else{$ac=false;}
    $db->transactionStop($ac);
}