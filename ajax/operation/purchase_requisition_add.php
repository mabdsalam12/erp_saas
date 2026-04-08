<?php
    $date = strtotime($_POST['date']);
    $products = $_POST['products'];
    $note = $_POST['note'];
    if(empty($products)){$error=fl(); setMessage(36,'Product');}
    elseif($date<strtotime('-2 years')){$error=fl();setMessage(63,'Date');}
    else{
        $db->transactionStart();
        $data=[
            'date'=>$date,
            'note'=>$note,
        ];
        $db->arrayUserInfoAdd($data);
        $id = $db->insert('purchase_requisition',$data,true,'array',$jArray);
        if(!$id){$error=fl(); setMessage(66);}
        else{

            //$code=$db->setAutoCode('purchase_requisition',$id);
            //if(!$code){$error=fl(); setMessage(66);}

            $productData=$db->getProductData();
            foreach($products as $product_id=>$pd){
                $pd['quantity'] = intval($pd['quantity']);
                if(!isset($productData[$product_id])){$error=fl(); setMessage(63,'Product');break;}
                if($pd['quantity']<1){$error=fl();setMessage(63,'Quantity'); break;}
                $data=[
                    'purchase_requisition_id'   => $id,
                    'product_id'                => $product_id,
                    'quantity'                  => $pd['quantity'],
                ];
                $insert = $db->insert('purchase_requisition_details',$data,'','array',$jArray);
                if(!$insert){$error=fl(); setMessage(66);break;}
            }
        }
        if(!isset($error)){
            $ac=true;
            $jArray['status']=1;
            setMessage(29,'Purchase requisition');
        }
        else{$ac=false;}
        $db->transactionStop($ac);
    }