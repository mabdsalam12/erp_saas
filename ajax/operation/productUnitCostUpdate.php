<?php
  $pID = intval($_POST['pID']);
  $unitCost = floatval($_POST['unit_cost']);
  $p = $db->getRowData('products','where id='.$pID);
  if(empty($p)){$error=fl(); setMessage(63,'products');}
  else{
      $data=['unit_cost'=>$unitCost];
      $where = ['id'=>$pID];
      $update = $db->update('products',$data,$where);
      if(!$update){$error=fl(); setMessage(66);}
      else{$jArray['status']=1;setMessage(2,'Unit cost update successfully for ',$p['title']);}
  }