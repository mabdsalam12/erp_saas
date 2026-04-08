<?php
$assets_id    = intval($_POST['assets_id']);
$date         = strtotime($_POST["dep_date"]);
$dep_amount   = floatval($_POST["dep_amount"]);
$dep_notes   = @$_POST["dep_notes"];
$assets=$db->get_rowData('fixed_assets','id',$assets_id);
if($dep_amount<=0){$error=fl();setMessage(63,'Request amount');}
if(empty($assets)){$error=fl();setMessage(63,'Request');}
if(!isset($error)){
    if (date('Y-m-d', $date) === date('Y-m-d')) {
        $date = time(); // update to current timestamp
    }
    $data =[
        'fixed_assets_id'=>$assets_id,
        'amount'         =>$dep_amount,
        'note'           =>$dep_notes,
        'time'           =>$date,
    ];

    $db->arrayUserInfoAdd($data);
    $db->transactionStart();
    $depreciation_id=$db->insert('fixed_assets_depreciation',$data,true);
    if($depreciation_id!=false){
        $vType=V_T_FIXED_ASSETS_DEPRECIATION;
        $fixedAssetsDepreciationHead=$acc->getSystemHead(AH_FIXED_ASSETS_DEPRECIATION);
        if($fixedAssetsDepreciationHead==false){$error=fl();setMessage(66);}


        $fixedAssetsHead=$acc->getSystemHead(AH_FIXED_ASSETS);
        if($fixedAssetsHead==false){$error=fl();setMessage(66);}

        $debit=$fixedAssetsDepreciationHead;
        $credit=$fixedAssetsHead;
        $note='Fixed Assets Depreciation '.$dep_notes;
        if(!isset($error)){
            $voucher=$acc->voucher_create($vType,$dep_amount,$debit,$credit,$date,$note,$depreciation_id);
            if($voucher==false){$error=fl();setMessage(66);}
        }
        if(!isset($error)){
            $where=['id'=>$assets_id];
            $total_depreciation=floatval($assets['depreciation'])+$dep_amount;
            $total_current_value=floatval($assets['current_value'])-$dep_amount;
            $data=[
                'depreciation'  =>$total_depreciation,
                'current_value' =>$total_current_value,
            ];
            $db->arrayUserInfoEdit($data);

            $update=$db->update('fixed_assets',$data,$where);

            if(!$update){
                $error=fl();setMessage(66);
            } 
        }  
    }
    else{
        $error=fl();setMessage(66);
    }
}

if(!isset($error)){
    $ac=true;
    $jArray['status']=1;
    setMessage(30,'Depreciation');
}
else{$ac=false;}
$db->transactionStop($ac);