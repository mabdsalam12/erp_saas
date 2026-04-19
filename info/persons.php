<?php
    $aStatus      = true;
    $eStatus      = true;

    $districts=$db->getAllDistricts();
    $general->arrayIndexChange($sections,'scID');

    if(isset($_GET['add'])){
        if(!$aStatus){$general->redirect($pUrl,146,'add employee');}
        $data = array($pUrl=>$rModule['name'],1=>'Add');
        $general->pageHeader('Add '.$rModule['name'],$data);
        if(isset($_POST['add'])){
            $name       = $_POST["name"];
            $opBalance  = floatval($_POST["opBalance"]);
            $blType     = intval($_POST["blType"]);
            $mobile     = $_POST["mobile"];

            if(empty($name)){setMessage(36,'Name');$error=fl();}
            elseif($opBalance<0){$error=fl();setMessage(63,'Opening Balance');}
            elseif($opBalance>0){
                if($blType!=DEBIT&&$blType!=CREDIT){$error=fl();setMessage(63,'Opening Balance Type');}
            }

            if(!isset($error)){
                $data = [
                    'name'  => $name,
                    'mobile'=> $mobile,
                ];
                $db->transactionStart();
                $db->arrayUserInfoAdd($data);
                $personID=$db->insert('person',$data,true);
                if($personID!=false){
                    $person=$smt->personInfoByID($personID);
                    $personHead=$acc->getPersonHead($person);
                    if($personHead==false){$error=fl();setMessage(66);}
                    if($opBalance>0){
                        $capitalHead=$acc->getSystemHead(AH_CAPITAL);
                        if($capitalHead==false){$error=fl();setMessage(66);}
                        if(!isset($error)){
                            $opening=$acc->opening_voucher_create(OPENING_VOUCHER_TYPE_PERSON,$personID,$personHead,$opBalance,$blType);
                            if($opening==false){$error=fl();setMessage(66);}
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
                    $general->redirect($pUrl,29,$rModule['name']);
                }
            }
        }
    ?>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('name','Name',@$_POST['name'],'y');?>
                                    <?php $general->inputBoxText('mobile','Mobile',@$_POST['mobile']);?>
                                    <div class="form-group row">
                                        <label for="qty" class="col-md-4 col-form-label">Opening Balance</label>
                                        <div class="col-md-8">
                                            <div class="input-group qty-input-group">
                                                <input class="form-control amount_td" value="<?php echo @$_POST['opBalance'];?>" placeholder="Balance" type="text" name="opBalance">
                                                <div class="input-group-append">
                                                    <select name="blType">
                                                        <option value="">Select Type</option>
                                                        <option value="<?php echo DEBIT;?>" <?php echo $general->selected(DEBIT,@$_POST['blType']);?>>সে আমাকে দিবে</option>
                                                        <option value="<?php echo CREDIT;?>" <?php echo $general->selected(CREDIT,@$_POST['blType']);?>>সে পাবে</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

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
        $id= intval($_GET['editOpening']);
        $sup= $smt->personInfoByID($id);
        if(empty($sup)){$general->redirect($pUrl,37,$rModule['name']);}
        $data = array($pUrl=>$rModule['name'],'javascript:void()'=>$sup['name'],'1'=>'Opening Balance Edit');
        $general->pageHeader('Opening Balance Edit '.$sup['name'],$data);
        $openingVoucher=$acc->voucherDetails(V_T_OPENING,OPENING_VOUCHER_TYPE_PERSON.'_'.$id);
        $cOpeningB=0;
        $supHead=$acc->getPersonHead($sup);
        if($supHead==false){$error=fl();setMessage(66);}
        $opening_balance_type=0;
        if(!empty($openingVoucher)){
            $o=current($openingVoucher);
            // $general->printArray($o);
            $cOpeningB=(float)$o['amount'];
            if($cOpeningB==0){
                $acc->voucher_delete($o['id']);
                $openingVoucher=[];
            }
            else{
                if($o['debit']==$supHead){
                    $opening_balance_type=DEBIT;
                }
                elseif($o['credit']==$supHead){
                    $opening_balance_type=CREDIT;
                }
            }
        }
        if(isset($_POST['edit'])){
            $openingBalance=floatval($_POST['openingBalance']);
            $db->transactionStart();
            $balanceType=DEBIT;
            if($openingBalance>0){
                $balanceType=$_POST['balanceType'];
                if($balanceType!=DEBIT&&$balanceType!=CREDIT){
                    $error=fl();setMessage(63,'Opening balance type');
                }
            }
            if(!isset($error)){
                if($openingBalance>0){
                    if(!empty($openingVoucher)){
                        $opening_head=$acc->getSystemHead(AH_CAPITAL);
                        if($opening_head==false){$error=fl();setMessage(66);}
                        if($balanceType==DEBIT){
                            $debit_head=$supHead;
                            $credit_head=$opening_head;
                        }
                        else{
                            $debit_head=$opening_head;
                            $credit_head=$supHead;
                        }
                        $update=$acc->voucherEdit($o['id'],$openingBalance,$o['note'],$debit_head,$credit_head);
                        if($update==false){$error=fl();setMessage(66);}
                    }
                    else{
                        $opening=$acc->opening_voucher_create(OPENING_VOUCHER_TYPE_PERSON,$id,$supHead,$openingBalance,$balanceType);
                        if($opening==false){$error=fl();setMessage(66);}
                    }
                }
                else{
                    if(!empty($openingVoucher)){
                        $update=$acc->voucher_delete($o['id']);
                        if($update==false){$error=fl();setMessage(66);}
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
                $general->redirect($pUrl,29,'Supplier Opening');
            }
            else{
                setErrorMessage($error);
            }
        }
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            show_msg();
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-sm-5">
                            <?php $general->inputBoxText('openingBalance','Opening Balance',$cOpeningB,'','amount_td');?>
                        </div>
                        <div class="col-xs-6 col-sm-4">
                            
                            <div class="col-md-5">
                                <select class="form-control" name="balanceType">
                                    <option value="">Select Type</option>
                                    <option value="<?php echo DEBIT;?>" <?=$general->selected(DEBIT,$opening_balance_type)?>>পাব <?php echo $acc->columnNameById(DEBIT);?></option>
                                    <option value="<?php echo CREDIT;?>" <?=$general->selected(CREDIT,$opening_balance_type)?>>পাবে <?php echo $acc->columnNameById(CREDIT);?></option>

                                </select>
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-4">
                            <?php $general->editBtn();?>
                        </div>
                    </div>
                </form> 
            </div>
        </div>

    </div>
    <?php
    }
    elseif(isset($_GET['edit'])){
        // if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
        $edit = intval($_GET['edit']);
        $c = $db->get_rowData('person','id',$edit);
        if(empty($c)){$general->redirect($pUrl,37,$rModule['name']);}

        if(isset($_POST['edit'])){
            if(isset($_POST['edit'])){
                $name       = $_POST["name"];
                $mobile     = $_POST["mobile"];

                if(empty($name)){setMessage(36,'Name');$error=fl();}
                if(!isset($error)){
                    $data = [
                        'name'  => $name,
                        'mobile'=> $mobile,
                    ];
                    $db->arrayUserInfoEdit($data);
                    $where=array('id'=>$edit);
                    $update=$db->update('person',$data,$where);
                    if($update!=false){
                        $general->redirect($pUrl,30,'person');
                    }
                    else{
                        $error=fl();setMessage(66);
                    }   
                }
            }
        }
        $data = array($pUrl=>$rModule['name'],1=>'Edit');
        $general->pageHeader('Edit '.$rModule['name'],$data);
    ?>

    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('name','Name',$c['name'],'y');?>
                                    <?php $general->inputBoxText('mobile','Mobile',$c['mobile']);?>
                                    <a href="<?php echo $pUrl;?>&editOpening=<?php echo $edit;?>" style="color: green;">Edit Opening </a>
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

        $data = array($pUrl=>$rModule['name']);
        $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));
    ?>
    <div class="row">
    <div class="col-sm-12">
        <div class="white-box border-box">
            <?php
                show_msg();


            ?>
            <div class="row">
                <div class="col-sm-12 col-lg-12 padding-left-0">



                    <div class="col-md-2">
                        <h5 class="box-title">Name</h5>  
                        <input type="text" class="form-control" id="name" value="">  
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Mobile</h5>
                        <input type="text" class="form-control" id="mobile" value="">

                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Balance Type</h5>
                        <select class="form-control" id="balanceType">
                            <option value="0">with zero balance</option>
                            <option value="1">without zero balance</option>
                        </select>

                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <a href="javascript:void()" class="btn btn-success" onclick="personList()">Search</a>

                    </div>
                </div>

                <div class="col-sm-12 col-lg-12" id="reportArea">
                </div>

            </div>
        </div>
    </div>
    <?php
    }
?>
<script>
    $(document).ready(function(){
        personList();
    });
    function personList(){
        var name=$('#name').val();
        var mobile=$('#mobile').val();
        var balanceType=$('#balanceType').val();
        $('#reportArea').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{personList:1,name:name,mobile:mobile,balanceType:balanceType},
            success:function(data){
                if(data.status==1){
                    $('#reportArea').html(data.html);
                }
                swMessageFromJs(data.m);
            }

        });
    }
</script>