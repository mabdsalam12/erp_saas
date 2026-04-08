<?php
    $sdID       = intval(@$_POST['draftID']);
    $cID        = intval($_POST['cID']);
    $products   = $_POST['products'];
    $saleDate   = strtotime($_POST['saleDate']);
    $discount   = floatval($_POST['discount']);
    if(empty($products)){$error=fl(); setMessage(1,'select a product');}
    else{
        
        $sData=['products'=>$products,'discount'=>$discount] ;
        
        $data=[
            'customer_id'=>$cID,
            'date'=>$saleDate,
            'data'=>json_encode($sData)
        ];
        if($sdID>0){
            $jArray['draft_id']=$sdID;
            $where = ['id'=>$sdID];
            $update = $db->update('sale_draft',$data,$where);
            if($update){$jArray['status']=1; setMessage(2,'Draf added');}
            else{$error=fl(); setMessage(66);}
        }
        else{
            $sdID = $db->insert('sale_draft',$data,true);
            $jArray['draft_id']=$sdID;
            if($sdID){$jArray['status']=1; setMessage(2,'Draf updated');}
            else{$error=fl(); setMessage(66);}
        }
    }
