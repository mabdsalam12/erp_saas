<?php
$bill_date  = strtotime($_POST['bill_date']);
$product_id = intval($_POST['product_id']);
$qty        = floatval($_POST['qty']);
$note       = $_POST['note'];
$stock_in_out_type=$_POST['stock_in_out_type']=='stock_entry'?'stock_entry':'reject_entry';

if($bill_date<strtotime('-2 years')){$error=fl();setMessage(63,'date');}
elseif($bill_date>strtotime('2 years')){$error=fl();setMessage(63,'date');}
elseif($qty<=0){$error=fl();setMessage(63,'quantity');}
elseif($note==''){$error=fl();setMessage(36,'note');}
if(!isset($error)){
    $p=$smt->productInfoByID($product_id);
    if(empty($p)){$error=fl();setMessage(63,'product');}
}

if(!isset($error)){
    if($bill_date==TODAY_TIME){
        $bill_date=TIME;
    }
    $total=$p['unit_cost']*$qty;
    $data=[
        'product_id'=> $product_id,
        'quantity'  => $qty,
        'date'      => $bill_date,
        'unit_cost' => $p['unit_cost'],
        'total'     => $total,
        'note'      => $note,
    ];
    $db->arrayUserInfoAdd($data);

    $db->transactionStart();
    if($stock_in_out_type=='stock_entry'){
        $reject_id=$db->insert('products_stock_in',$data,true);
        $stock_log_type=ST_CH_STOCK_ENTRY;
        $voucher_type=V_T_PRODUCT_STOCK_ENTRY;
        $message='Stock entry successfully';
        $voucher_particular='Stock Entry';
    }
    else{
        $reject_id=$db->insert('reject_products',$data,true);
        $stock_log_type=ST_CH_REJECT;
        $voucher_type=V_T_PRODUCT_REJECT;
        $message='Reject entry successfully';
        $voucher_particular='Reject Entry';
    }
    
    if($reject_id){

        $code=$db->setAutoCode($stock_in_out_type,$reject_id);


        $reject_head=$acc->getSystemHead(AH_PRODUCT_REJECT);
        if($reject_head==false){$error=fl();setMessage(66);}
        $process_head=$acc->getSystemHead(AH_PRODUCT_REJECT_PROCESS);
        if($process_head==false){$error=fl();setMessage(66);}

        if(!isset($error)){
            $log=$smt->productStockChangeLog($product_id,$qty,$stock_log_type,$reject_id,$bill_date);
            if($log==false){$error=fl();setMessage(66);}
            $update=$smt->update_product_closing_stock($product_id,$jArray);
            if($update==false){$error=fl();setMessage(66);}
            // $nextQty-=$qty;
            // $where=['id'=>$product_id];
            // $update=$db->update('products',['stock'=>$nextQty],$where);
            // if($update==false){$error=fl();setMessage(66);}            
        }
        if(!isset($error)){
            $voucher=$acc->voucher_create($voucher_type,$total,$reject_head,$process_head,$bill_date,"$voucher_particular $code",$reject_id,0,[],$jArray);
            if($voucher==false){$error=fl();setMessage(66);}
        }
    }
    else{
        $error=fl();setMessage(66);
    }

    if(!isset($error)){
        $ac=true;
        $jArray['status']=1;
        setMessage(2,$message);
    }
    else{
        $ac=false;
    }
    $db->transactionStop($ac);
}
