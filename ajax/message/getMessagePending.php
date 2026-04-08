<?php 
$pending = $db->selectAll('queue_sms',"where send_time=0",'COUNT(*) as pending','array',$jArray)[0]['pending']??0;
$jArray[fl()] = $pending;
$jArray['pending_message'] = $pending;
$jArray['status'] = 1;