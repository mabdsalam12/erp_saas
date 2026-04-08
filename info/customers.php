<?php
    $aStatus      = true;
    $eStatus      = true;

    $tpID           = 'id';
    $base = $db->selectAll('base');
    $general->arrayIndexChange($base,'id');
    $bazar = $db->selectAll('bazar','','id,title,base_id');
    $customer_category = $db->selectAll('customer_category','where isActive=1','id,title');
    $general->arrayIndexChange($customer_category);
    $baze_wise_bazars=[];
    if(!empty($bazar)){
        foreach($bazar as $b){
            $baze_wise_bazars[$b['base_id']][$b['id']]=$b;
        }
    }
    if(isset($_GET['add'])){
        if(!$aStatus){$general->redirect($pUrl,146,'add Customer');}
        $data = array($pUrl=>$rModule['title'],1=>'Add');
        $general->pageHeader('Add '.$rModule['title'],$data);
        $credit_limit = $db->get_company_settings('customer_default_credit_limit');
        if(isset($_POST['add'])){
            $general->createLog('customer_add',$_POST);
            $cName                  = $_POST["cName"];
            $opBalance              = intval($_POST["opBalance"]);
            $blType                 = intval($_POST["blType"]);
            $base_id                = intval($_POST["base_id"]);
            $customer_category_id   = intval($_POST["customer_category_id"]);
            //$cDOB                 = strtotime($_POST["cDOB"]);
            $cMobile                = $_POST["cMobile"];
            $district               = $_POST["district"];
            $police_station         = $_POST["police_station"];
            $cAddress               = $_POST["cAddress"];
            $cEmail                 = '';
            $bazar                  = $_POST["bazar"];
            $bazar_id               = intval($_POST["bazar_id"]);
            $owner_name             = $_POST["owner_name"];
            $credit_limit           = intval($_POST["credit_limit"]);
            $due_day                = intval($_POST["due_day"]);



            if(empty($cName)){setMessage(36,'Name');$error=fl();}
            elseif($base_id<1){setMessage(36,'MPO');$error=fl();}
            elseif(!isset($base[$base_id])){setMessage(63,'MPO');$error=fl();}
            elseif($customer_category_id!=0&&!isset($customer_category[$customer_category_id])){setMessage(36,'Category');$error=fl();}
            elseif($opBalance<0){$error=fl();setMessage(63,'Opening Balance');}
            elseif($bazar_id>0 && !isset($baze_wise_bazars[$base_id][$bazar_id])){$error=fl();setMessage(63,'bazar');}
            elseif($opBalance>0&&$blType!=DEBIT&&$blType!=CREDIT){$error=fl();setMessage(63,'Opening Balance Type');}




            if(!isset($error)){
                $base = $base[$base_id]; 
                $base_data = $general->getJsonFromString($base['data']);

                $customer_data = [
                    'credit_limit'  =>$credit_limit,
                    'district'      =>$district,
                    'police_station'=>$police_station,
                ];

                $total_customer =  intval(@$base_data['total_customer']);
                $total_customer++;
                $prefix = $base['code'].'-';
                $code=$prefix.str_pad($total_customer,4,0,STR_PAD_LEFT);
                //if(intval($cDOB)==0){$cDOB=0;}
                $data = [
                    //'mIDNo'             => $mIDNo,
                    'name'          =>$cName,
                    'base_id'       =>$base_id,
                    'customer_category_id'       =>$customer_category_id,
                    'mobile'        =>$cMobile,
                    'address'       =>$cAddress,
                    'email'         =>$cEmail,
                    'due_day'       =>$due_day,
                    'bazar'         =>$bazar,
                    'bazar_id'      =>$bazar_id,
                    'owner_name'    =>$owner_name,
                    'data'          =>json_encode($customer_data),
                    'code'          =>$code
                ];
                $db->transactionStart();
                $db->arrayUserInfoAdd($data);
                $cID=$db->insert('customer',$data,true);
                if($cID!=false){
                    $base_data['total_customer']=$total_customer;
                    $data=['data'=>json_encode($base_data)];
                    $where=['id'=>$base_id];
                    $update=$db->update('base',$data,$where);
                    if(!$update){$error=fl(); setMessage(66);}
                    $db->actionLogCreate('customer_id'.$cID.'_newCustomerAdd',$data);
                    $c=$smt->customerInfoByID($cID);
                    $customerHead=$acc->getCustomerHead($c);
                    if($customerHead==false){$error=fl();setMessage(66);}
                    if($opBalance>0){
                        $capitalHead=$acc->getSystemHead(AH_CAPITAL);
                        if($capitalHead==false){$error=fl();setMessage(66);}
                        if(!isset($error)){
                            $opening=$acc->opening_voucher_create(
                                OPENING_VOUCHER_TYPE_CUSTOMER,$cID,
                                $customerHead,
                                $opBalance,$blType);
                            if($opening!==false){
                            }else{$error=fl();setMessage(66);}
                        }
                    }
                }         
                else{
                    $error=fl();setMessage(66);
                }
                $ac=false;
                if(!isset($error)){
                    $ac=true;
                }
                $db->transactionStop($ac);
                if(!isset($error)){
                    $general->redirect($pUrl,29,'Customer');
                }
            }
        }
    ?>
    <script type="text/javascript">
        <?php  echo 'var bazar_wise_bazars='.json_encode($baze_wise_bazars).';'; ?>
        $(document).on('change','#base_id',function(){base_wise_bazar(this.value)});
    </script>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxSelect($base,'Base','base_id','id','title',@$_POST['base_id']);?>
                                    <?php $general->inputBoxSelect($customer_category,'Category','customer_category_id','id','title',@$_POST['customer_category_id']);?>
                                    <?php $general->inputBoxText('cName','Name',@$_POST['cName'],'y');?>
                                    <?php $general->inputBoxText('cMobile','Mobile',@$_POST['cMobile']);?>
                                    <?php $general->inputBoxText('owner_name','Owner Name',@$_POST['owner_name']);?>
                                    <?php $general->inputBoxText('district','District',@$_POST['district']);?>
                                    <?php $general->inputBoxText('police_station','Police station',@$_POST['police_station']);?>
                                    <?php $general->inputBoxTextArea('cAddress','Address',@$_POST['cAddress']);?>
                                    <div class="form-group row">
                                        <label for="qty" class="col-md-4 col-form-label">Opening Balance</label>
                                        <div class="col-md-8">
                                            <div class="input-group qty-input-group">
                                                <input class="form-control amount_td" value="<?php echo @$_POST['opBalance'];?>" placeholder="Balance" type="text" name="opBalance">
                                                <div class="input-group-append">
                                                    <select name="blType">
                                                        <option value="">Select Type</option>
                                                        <option value="<?php echo DEBIT;?>" <?php echo $general->selected(DEBIT,@$_POST['blType']);?>>কাস্টমার দিবে</option>
                                                        <option value="<?php echo CREDIT;?>" <?php echo $general->selected(CREDIT,@$_POST['blType']);?>>কাস্টমার পাবে</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $general->inputBoxText('credit_limit','Credit limit',$credit_limit);?>
                                    <?php $general->inputBoxText('due_day','Due day',@$_POST['due_day']);?>
                                    <?php $general->inputBoxText('bazar','Bazar',@$_POST['bazar']);?>
                                    <?php $general->inputBoxSelect([],'Bazar','bazar_id','id','title');?>

                                </div>

                            </div>
                            <div class="row">
                                <?php echo $general->addBtn();?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

    }   
    elseif(isset($_GET['editOpening'])){
        if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
        $cID= intval($_GET['editOpening']);
        $c = $db->get_rowData('customer',$tpID,$cID);
        if(empty($c)){$general->redirect($pUrl,37,$rModule['title']);}

        $general->arrayContentShow($c);
        $data = array($pUrl=>$rModule['title'],'javascript:void()'=>$c['name'],'1'=>'Edit');
        $general->pageHeader($c['name'],$data);

        $openingVoucher=$acc->voucherDetails(V_T_OPENING,OPENING_VOUCHER_TYPE_CUSTOMER.'_'.$cID);
        $cOpeningB=0;
        $cOpeningD=0;
        $openingType=0;
        $customerHead=$acc->getCustomerHead($c);
        //$ehID=$acc->getEmployeeHead($e);
        //$general->printArray($openingVoucher);
        if(!empty($openingVoucher)){
            $o=current($openingVoucher);
            $cOpeningB=(float)$o['amount'];
            if($o['debit']==$customerHead){
                $openingType=DEBIT;
            }
            else{
                $openingType=CREDIT;
            }
        }
        $capitalHead=$acc->getSystemHead(AH_CAPITAL);

        if(isset($_POST['edit'])){
            $openingBalance=floatval($_POST['opBalance']);
            $blType=intval($_POST['blType']);
            $db->transactionStart();

            if($openingBalance>0){
                if($blType!=DEBIT&&$blType!=CREDIT){$error=fl();setMessage(63,'Opening Balance Type');}
                if(!empty($openingVoucher)){
                    if($blType==DEBIT){
                        $dHead=$customerHead;
                        $cHead=$capitalHead;
                    }
                    else{
                        $dHead=$capitalHead;
                        $cHead=$customerHead;
                    }
                    $update=$acc->voucherEdit($o['id'],$openingBalance,$o['note'],$dHead,$cHead);
                    if($update==false){$error=fl();setMessage(66);}
                }
                else{
                    if(!isset($error)){
                        $opening=$acc->opening_voucher_create(OPENING_VOUCHER_TYPE_CUSTOMER,$cID,$customerHead,$openingBalance,$blType);
                        if($opening==false){$error=fl();setMessage(66);}
                    }
                    else{
                        echo $error;
                    }
                }
            }
            else{
                if(!empty($openingVoucher)){
                    foreach($openingVoucher as $o){
                        $delete=$db->delete('a_ledger_entry',['voucher_id'=>$o['id']],'d');
                        if($delete==false){$error=fl();setMessage(66);}
                        $delete=$db->delete('a_voucher_entry',['id'=>$o['id']],'d');
                        if($delete==false){$error=fl();setMessage(66);}
                    }
                }
            }
            if(!isset($error)){
                $ac=true;
            }
            else{
                $ac=false;
            }
            $db->transactionStop($ac);
            if(!isset($error)){
                //$general->redirect($pUrl,29,'Customer Opening');
            }
            else{
                setErrorMessage($error);
            }
        }
    ?>
    <div class="row">
        <div class="col-sm-12">
            <?php show_msg();?>
        </div>
        <div class="col-sm-12">
            <div class="white-box border-box">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-xs-6 col-sm-5">
                            <div class="form-group row">
                                <label for="qty" class="col-md-4 col-form-label">Opening Balance</label>
                                <div class="col-md-8">
                                    <div class="input-group qty-input-group">
                                        <input class="form-control amount_td" value="<?php echo $cOpeningB;?>" placeholder="Balance" type="text" name="opBalance">
                                        <div class="input-group-append">
                                            <select name="blType">
                                                <option value="">Select Type</option>
                                                <option value="<?php echo DEBIT;?>" <?php echo $general->selected(DEBIT,$openingType);?>>কাস্টমার দিবে</option>
                                                <option value="<?php echo CREDIT;?>" <?php echo $general->selected(CREDIT,$openingType);?>> কাস্টমার পাবে</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--<div class="col-xs-6 col-sm-4">
                        <?php $general->inputBoxText('openingDue','Opening Due',$cOpeningD,'','amount_td');?>
                        </div> -->
                        <div class="col-xs-6 col-sm-3">

                            <?php $general->editBtn();?>
                        </div>
                    </div>
                </form> 
            </div>
        </div>

    </div>
    <?php
    } 
    else if(isset($_GET['archive'])){
        $base = $db->selectAll('base');
        $data = array($pUrl=>$rModule['title'],'1'=>'Archive');
        $general->pageHeader('Archive '.$rModule['title'],$data);
        $bazar = $db->selectAll('bazar','','id,title,base_id');
        $bazar_wise_bazars=[];
        if(!empty($bazar)){
            foreach($bazar as $b){
                $bazar_wise_bazars[$b['base_id']][$b['id']]=$b;
            }
        }
    ?>
    <script type="text/javascript">
        <?php  echo 'var bazar_wise_bazars='.json_encode($bazar_wise_bazars).';'; ?>
        $(document).on('change','#base_id',function(){base_wise_bazar(this.value,'All')});
    </script>
    <div class="col-sm-12">
        <div class="white-box border-box">
            <?php
                show_msg();


            ?>
            <div class="row">
                <div class="col-sm-12 col-lg-12 padding-left-0">
                    <?php $general->inputBoxSelectForReport($base,'Base','base_id','id','title'); ?>
                    <?php $general->inputBoxSelectForReport($bazar,'Bazar','bazar_id','id','title'); ?>
                    <div class="col-md-2">
                        <h5 class="box-title">ID</h5>  
                        <input type="text" class="form-control" id="customer_id" value="<?php echo @$_GET['customer_id']?>" onkeypress="return search_key_press(event)">  
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Name</h5>  
                        <input type="text" class="form-control" id="cName" value="<?php echo @$_GET['mName']?>" >  
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Mobile</h5>
                        <input type="text" class="form-control" id="cMobile" value="<?php echo @$_GET['eMobile']?>" >
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <input type="submit" value="Search" class="btn btn-success" onclick="customer_list(1)" name="s">

                    </div>

                </div>

                <div class="col-sm-12 col-lg-12" id="reportArea">
                </div>

            </div>
        </div>
    </div>
    <script type="">
        $(document).ready(function(){
            customer_list(1);
        });
    </script>
    <?php

    }
    elseif(isset($_GET['edit'])){
        $edit = intval($_GET['edit']);
        $c = $smt->customerInfoByID($edit);
        if(empty($c)){$general->redirect($pUrl,37,$rModule['title']);}
        $customer_data = $general->getJsonFromString($c['data']);
        $statuss=[
            ['id'=>1,'title'=>'Active'],
            ['id'=>0,'title'=>'In Active'],
            ['id'=>3,'title'=>'Archive']
        ];
        $yesNo=[
            ['id'=>"1",'title'=>'Yes'],
            ['id'=>"0",'title'=>'No']
        ];
        if(isset($_POST['edit'])){

            if(!$aStatus){$general->redirect($pUrl,146,'add Customer');}
            $data = array($pUrl=>$rModule['title'],1=>'Add');
            $general->pageHeader('Add '.$rModule['title'],$data);
            if(isset($_POST['edit'])){
                $customer_category_id   = intval($_POST["customer_category_id"]);
                $cName                  = $_POST["cName"];

                //$cDOB                   = strtotime($_POST["cDOB"]);

                $cMobile                = $_POST["cMobile"];
                $district               = $_POST["district"];
                $police_station         = $_POST["police_station"];
                $cAddress               = $_POST["cAddress"];
                $bazar                  = $_POST["bazar"];
                $bazar_id              = intval($_POST["bazar_id"]);
                $owner_name             = $_POST["owner_name"];
                $credit_limit           = intval($_POST["credit_limit"]);
                $due_day                = intval($_POST["due_day"]);
                $status                = intval($_POST["status"]);
                $getClosingSMS         = intval($_POST["getClosingSMS"])==1?1:0;



                if(empty($cName)){setMessage(36,'Name');$error=fl();}
                elseif($customer_category_id!=0&&!isset($customer_category[$customer_category_id])){setMessage(36,'Category');$error=fl();}
                elseif($bazar_id>0 && !isset($baze_wise_bazars[$c['base_id']][$bazar_id])){$error=fl();setMessage(63,'bazar');}
                if(!isset($error)){
                    $customer_data['credit_limit'] = $credit_limit;
                    $customer_data['district'] = $district;
                    $customer_data['police_station'] = $police_station;
                    $customer_data['getClosingSMS'] = $getClosingSMS;
                    //if(intval($cDOB)==0){$cDOB=0;}
                    $data = array(
                        'name'        =>$cName,
                        'customer_category_id'        =>$customer_category_id,
                        'mobile'      =>$cMobile,
                        'address'     =>$cAddress,
                        'bazar'       =>$bazar,
                        'bazar_id'      =>$bazar_id,
                        'due_day'     =>$due_day,
                        'owner_name'  =>$owner_name,
                        'data'        =>json_encode($customer_data),
                        'isActive'    =>$status
                    );
                    $db->arrayUserInfoEdit($data);
                    $db->transactionStart();
                    $where=array($tpID=>$edit);
                    $update=$db->update('customer',$data,$where);
                    if($update!=false){
                        if($c['ledger_id']>0){
                            $data=[
                                'title'=>$cName
                            ];
                            $where=['id'=>$c['ledger_id']];
                            $update=$db->update('a_ledgers',$data,$where);
                            if(!$update){$error=fl();setMessage(66);}
                        }


                    }
                    else{
                        $error=fl();setMessage(66);
                    }
                    $ac=false;
                    if(!isset($error))   {
                        $ac=true;
                    }
                    $db->transactionStop($ac);
                    if(!isset($error)){
                        // $general->redirect($pUrl,30,'Customer');
                    }
                }
            }
        }
        $getClosingSMS=$customer_data['getClosingSMS']??"1";
        $data = [$pUrl=>$rModule['title'],1=>'Edit'];
        $general->pageHeader('Edit '.$rModule['title'],$data);
        $bazars= $bazar_wise_bazars[$c['bazar_id']]??[];
    ?>
    <script type="text/javascript">
        <?php  echo 'var bazar_wise_bazars='.json_encode($baze_wise_bazars).';'; ?>
        $(document).ready(function(){
            base_wise_bazar(<?=$c['base_id']?>,'Select bazar',<?=$c['bazar_id']?>);
        })
    </script>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    
                                    <?php $general->inputBoxText('cName','Name',$c['name'],'y');?>
                                    <?php $general->inputBoxText('cMobile','Mobile',$c['mobile']);?>
                                    <?php $general->inputBoxSelect($customer_category,'Category','customer_category_id','id','title',$c['customer_category_id']);?>
                                    <?php $general->inputBoxText('owner_name','Owner Name',$c['owner_name']);?>
                                    <?php $general->inputBoxText('district','District',@$customer_data['district']);?>
                                    <?php $general->inputBoxText('police_station','Police station',@$customer_data['police_station']);?>
                                    <?php $general->inputBoxTextArea('cAddress','Address',$c['address']);?>
                                    <a href="<?php echo $pUrl;?>&editOpening=<?php echo $edit;?>" style="color: green;">Edit Opening </a>
                                    <?php $general->inputBoxText('credit_limit','Credit limit',@$customer_data['credit_limit']);?>
                                    <?php $general->inputBoxText('due_day','Due day',$c['due_day']);?>
                                    <?php $general->inputBoxSelect($bazars,'Bazar','bazar_id','id','title',$c['bazar_id']);  ?>
                                    <?php $general->inputBoxText('bazar','Bazar',$c['bazar']);?>
                                    
                                    <?php $general->inputBoxSelect($yesNo,'Get Closing SMS','getClosingSMS',
                                    'id','title',$getClosingSMS,haveSelect:'n');  ?>
                                    <?php $general->inputBoxSelect($statuss,'Status','status','id','title',$c['isActive'],haveSelect:'n');  ?>
                                </div>

                            </div>
                            <div class="row">
                                <?php echo $general->editBtn();?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    else{
        $base = $db->selectAll('base');
        $data = array($pUrl=>$rModule['title']);
        $archived = '<a href="' . $pUrl . '&archive" class="btn btn-info">Archive</a>';
        $general->pageHeader($rModule['title'],$data,$general->addBtnHtml($pUrl).$archived);
        $bazar = $db->selectAll('bazar','','id,title,base_id');
        $bazar_wise_bazars=[];
        if(!empty($bazar)){
            foreach($bazar as $b){
                $bazar_wise_bazars[$b['base_id']][$b['id']]=$b;
            }
        }
    ?>
    <script type="text/javascript">
        <?php  echo 'var bazar_wise_bazars='.json_encode($bazar_wise_bazars).';'; ?>
        $(document).on('change','#base_id',function(){base_wise_bazar(this.value,'All')});
    </script>
    <div class="col-sm-12">
        <div class="white-box border-box">
            <?php
                show_msg();


            ?>
            <div class="row">
                <div class="col-sm-12 col-lg-12 padding-left-0">
                    <?php $general->inputBoxSelectForReport($base,'Base','base_id','id','title'); ?>
                    <?php $general->inputBoxSelectForReport($customer_category,'Category','customer_category_id','id','title'); ?>
                    <?php $general->inputBoxSelectForReport($bazar,'Bazar','bazar_id','id','title'); ?>
                    <div class="col-md-2">
                        <h5 class="box-title">ID</h5>  
                        <input type="text" class="form-control" id="customer_id" value="<?php echo @$_GET['customer_id']?>" onkeypress="return search_key_press(event)">  
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Name</h5>  
                        <input type="text" class="form-control" id="cName" value="<?php echo @$_GET['mName']?>" >  
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Mobile</h5>
                        <input type="text" class="form-control" id="cMobile" value="<?php echo @$_GET['eMobile']?>" >
                    </div>

                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <input type="submit" value="Search" class="btn btn-success" onclick="customer_list()" name="s">
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;"></div>

            </div>
        </div>
    </div>
    <script type="">
        $(document).ready(function(){
            const report_data = JSON.parse(localStorage.getItem('customerList'));
            if(report_data){
                if(report_data.customer_category_id!=''){
                    $('#customer_category_id').val(report_data.customer_category_id);
                }
                if(report_data.customer_id!=''){
                    $('#customer_id').val(report_data.customer_id);
                }
                if(report_data.cName!=''){
                    $('#cName').val(report_data.cName);
                }
                if(report_data.cMobile!=''){
                    $('#cMobile').val(report_data.cMobile);
                }
                if(report_data.base_id>0){
                    $('#base_id').val(report_data.base_id);
                }
                select2Call();
            }
            customer_list();
        });
    </script>
    <?php
        $general->onclickChangeJavaScript('customer',$tpID);  
    }
?>
<script>
    function customer_list(status=0){
        let customer_id=$('#customer_id').val();
        let customer_category_id=$('#customer_category_id').val();
        let cName=$('#cName').val();
        let cMobile=$('#cMobile').val();
        let base_id=$('#base_id').val();
        let bazar_id=$('#bazar_id').val();
        let request={
            customerList:1,
            customer_id:customer_id,
            cName:cName,
            cMobile:cMobile,
            base_id:base_id,
            bazar_id:bazar_id,
            customer_category_id:customer_category_id,
            status:status
        };
        ajax_report_request(request,'reportArea');
    }

    function search_key_press(evt){
        var charCode=(evt.which)?evt.which:event.keyCode;
        var stt=0;
        if(charCode==13){
            customerList()
        }
        return true;
    }
</script>