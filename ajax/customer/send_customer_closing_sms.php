<?php
include_once ROOT_DIR.'/class/sms/Customer_closing_sms.php';
    $cls=new Customer_closing_sms($acc);

$id=intval($_POST['id']);
$customers=$general->getJsonFromString($general->content_show($_POST['customers']));

try{
    $details=$cls->details($id);
}
catch(Exception $e){
    setMessage(1,$e->getMessage());
    $error=fl();
}
$bases=$_POST['bases'];

$requested_bases=[];
if(!empty($bases)){
    foreach($bases as $b){
        $requested_bases[intval($b)]=intval($b);
    }
}
// $jArray[fl()]=$requested_bases;
// $error=fl();
if(!isset($error)){
    $sent_count=0;
    $customerData=[];
    foreach($details['details'] as $d){
        if($d['mobile']!='' && isset($customers[$d['id']])){
            $c=$customers[$d['id']];
            if(!empty($requested_bases) && !isset($requested_bases[$d['base_id']])){
                continue;
            }
            $name=$details['record']['name'];
            
            $smsText=$name;
            $smsText.="\nOpening Balance:$d[opening_balance]";
            $smsText.="\nSale:$d[sale]";
            $smsText.="\nCollection:$d[collection]";
            if($d['collection_discount']!=0){
                $smsText.="\nCollection Discount:$d[collection_discount]";
            }
            if($d['return']!=0){
                $smsText.="\nReturn:$d[return]";
            }
            $smsText.="\nClosing:$d[closing_balance]";
            $smsData = [
                'text' => $smsText,
            ];
            $data = [
                'data'      => json_encode($smsData),
                'mobile'    => $d['mobile'],
                'add_time'  => TIME,
            ];
            $jArray[fl()][]=$data;
            $db->insert('queue_sms', $data,false,'array',$jArray);
            $customerData[$d['id']]=[
                'id'    => $d['id'],
                'o'     => $d['opening_balance'],
                'c'     => $d['closing_balance'],
                's'     => $d['sale'],
                'cl'    => $d['collection'],
                'cld'   => $d['collection_discount'],
                'rt'    => $d['return'],
            ];
        }
    }
    $data=[
        'sms_send'=>1,
        'sms_send_time'=>TIME,
        'data'         =>json_encode($customerData),
    ];
    $where=['id'=>$id];
    $db->update('customer_closing_sms',$data,$where);
    $jArray['status']=1;
}