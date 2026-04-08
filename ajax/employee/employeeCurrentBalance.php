<?php
$employee_id = $_POST['employee_id'];
$employee = $smt->employeeInfoByID($employee_id);
$employee_balance=0;
if(!empty($employee)){
    $ledger_id = $acc->getEmployeeHead($employee);
    $employee_balance = $acc->headBalance($ledger_id);
}
$jArray['status']=1;
$jArray['balance']=$employee_balance;
