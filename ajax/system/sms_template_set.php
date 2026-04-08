<?php
  $smsdata=$_POST['smsdata'];
  
  $company_data = $db->get_company_data();
  
  $company_data['sms_event']=$smsdata;
  
  $data=['data'=>json_encode($company_data)];
  $where=['id'=>1];
  $update=$db->update('company',$data,$where);
  if($update){setMessage(30,'SMS Template');}
  else{$error=fl();setMessage(66);}
