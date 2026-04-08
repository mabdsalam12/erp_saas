<?php
  $cID=intval($_POST['cID']);
  $c=$smt->customerInfoByID($cID);

    if(!empty($c)){
        $jArray['balance']=$general->numberFormat($acc->headBalance($c['hID']),0);
        $jArray['status']=1;
    }