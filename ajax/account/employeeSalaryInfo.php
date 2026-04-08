<?php
    $eID  = intval($_POST['eID']);
    $month= intval($_POST['month']);
    $year = intval($_POST['year']);
    $e    = $tkt->employeeInfoByID($eID);
    if(empty($e)){$error=fl();setMessage(63,'Employee');}
    elseif($e['bID']!=$bID){          $error = __LINE__;setMessage(63,'Employee');}
    elseif($month<1||$month>12){      $error = __LINE__;setMessage(63,'Month');}
    elseif($year<2018||$year>2028){   $error = __LINE__;setMessage(63,'Year');}
    else{
        
        $op=$acc->employeeSalaryInfo($eID,$year,$month);
        $payable=$op['salary']-$op['esDeduction'];
        $due    =$payable-$op['pay'];
        $ed=$db->get_rowData($general->table(72),'edID',$e['edID']);
        $es=$db->get_rowData($general->table(75),'esID',$e['esID']);
        $cn=$tkt->counterInfoByID($e['cnID']);
        $info=array(
            'eName'         => $e['eName'],
            'eSalary'       => $op['salary'],
            'eName'         => $e['eName'],
            'payable'       => $payable,
            'edTitle'       => $ed['edTitle'],
            'esTitle'       => $es['esTitle'],
            'esDeduction'   => $op['esDeduction'],
            'paid'          => $op['pay'],
            'counter'       => $cn['cnTitle'],
            'due'           => $due
        );
        $jArray['info']=$info;
        $jArray['status']=1;
    }
    $jArray['m']=show_msg('y');
    $general->jsonHeader($jArray);
?>
