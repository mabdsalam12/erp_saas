<?php 
$base_id = intval($_POST["base_id"]);
$type = intval($_POST["type"]);
$message = $_POST["message"];
$ids = $_POST["ids"];
$title = ($type === 1) ? 'Customer' : 'Doctor';
if(empty($message)){
    $error=fl();
    setMessage(1,'Invalid Message');
}
elseif($base_id<1){
    $error=fl();
    setMessage(1,'Select a base');
}
elseif(!in_array($type,[1,2])){
    $error=fl();
    setMessage(1,'Select a type');
}
elseif(empty($ids)){
    $error=fl();
    
    setMessage("Select at least one {$title}");
}
else{
    $table = ($type==1)?"customer":"doctor";
    $query = "where id in(".implode(",", $ids).") and base_id=$base_id";
    $doctor_or_customer = $db->selectAll($table,$query,'id,mobile','array',$jArray);
    $db->transactionStart();
    if(!empty($doctor_or_customer)){
        foreach($doctor_or_customer as $row){
            $to = $row['mobile'];
            $smsData = [
                'text' => $message,
                'ref' => $row['id'],
                'send_type' => $title,
            ];
            $data = [
                'data'      => json_encode($smsData),
                'mobile'    => $to,
                'add_time'  => TIME,
            ];
            $insert = $db->insert('queue_sms', $data,false,'array',$jArray);
            if(!$insert){
                $error=fl();
                setMessage(66);
            }
        }
    }
    $ac = false;
    if(!isset($error)){
        $ac = true;
        $jArray['status']=1;
        setMessage(29,'Message');
    }
    $db->transactionStop($ac);
}