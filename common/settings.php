<?php
$data = array($pUrl=>$rModule['name']);
$general->pageHeader($rModule['name'],$data);
$company_data = $db->get_company_data();
$checkbox_settings = [
    'use_product_production'=>['title'=>'Use product production', 'value'=>0],
    'use_product_category'=>['title'=>'Use product Category', 'value'=>0],
    'minus_stock_order_from_app'=>['title'=>'Minus stock order from app', 'value'=>0],
    'receive_from_customer_in_app'=>['title'=>'Receive from customer in app', 'value'=>0],
    'manage_order_number_and_date'=>['title'=>'Manage order number and date', 'value'=>0],
]; 
$input_settings = [
    'customer_default_credit_limit'=>['title'=>'Customer default credit limit', 'value'=>0],
    'customer_default_due_date'=>['title'=>'Customer default due date', 'value'=>0],
];

$sms_settings = [
    'invoice_create'=>['title'=>'Send sms in invoice create', 'value'=>0],
    'money_receive_form_customer'=>['title'=>'Send sms in money receive form customer', 'value'=>0],
    'mpo_deposit_sms'=>['title'=>'Send sms in mpo deposit', 'value'=>0],
];

if(isset($_POST['save'])){
    foreach($checkbox_settings as $k=>$s){
        $company_data[$k]=isset($_POST[$k])?1:0;
    }
    foreach($sms_settings as $k=>$s){
        $company_data['sms_settings'][$k]=isset($_POST[$k])?1:0;
    }
    foreach($input_settings as $k=>$s){
        $company_data[$k]=isset($_POST[$k])?$_POST[$k]:$s['value'];
    }
    $data=['data'=>json_encode($company_data)];
    $where=['id'=>1];
    $update=$db->update('company',$data,$where);
    if($update){setMessage(30,'sittings'); $general->redirect($pUrl);}
    else{$error=fl();setMessage(66);}
}

show_msg();
?>
<div class="white-box border-box">
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-6">
                <form action="" method="POST">
                    <div class="col-md-12">
                        <?php
                        foreach($checkbox_settings as $k=>$s){
                            $ssl=0;
                            if(isset($company_data[$k])){
                                $ssl=$company_data[$k];
                            }
                            ?>
                            <div class="form-check">
                                <input class=" form-control form-check-input" type="checkbox" <?php if($ssl==1){echo "checked";} ?> value="1" id="<?=$k?>" name="<?=$k?>">
                                <label class="form-check-label"  for="<?=$k?>">
                                    <?=$s['title']?>
                                </label>
                            </div>
                            <?php 
                        }
                        ?>
                    </div>
                    <div class="col-md-12">
                        <?php
                        foreach($input_settings as $k=>$s){
                            $value='';
                            if(isset($company_data[$k])){
                                $value=$company_data[$k];
                            }
                            $general->inputBoxText($k,$s['title'],$value);
                        }
                        ?>
                    </div>
                    <div class="col-md-12">
                        <?php
                        foreach($sms_settings as $k=>$s){
                            $ssl=0;
                            if(isset($company_data['sms_settings'][$k])){
                                $ssl=$company_data['sms_settings'][$k];
                            }
                            ?>
                            <div class="form-check">
                                <input class=" form-control form-check-input" type="checkbox" <?php if($ssl==1){echo "checked";} ?> value="1" id="<?=$k?>" name="<?=$k?>">
                                <label class="form-check-label"  for="<?=$k?>">
                                    <?=$s['title']?>
                                </label>
                            </div>
                            <?php 
                        }
                        ?>
                    </div>
                    <div class="col-md-2">
                        <input type="submit" name="save" value="Save" class="btn btn-success"/>
                    </div>
                </form>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

</div>
