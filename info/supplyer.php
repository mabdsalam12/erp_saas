<?php
    $eStatus=true;
    $types=$smt->get_all_product_type();
    if(isset($_GET['add'])){
        $data = array($pUrl=>$rModule['title'].' Add',1=>'Add New '.$rModule['title']);
        $general->pageHeader('Add '.$rModule['title'],$data);
        if(isset($_POST['add'])){
            $code    = $_POST["code"];
            $name    = $_POST["name"];
            $contact_person    = $_POST["contact_person"];
            $mobile    = $_POST["mobile"];
            $address    = $_POST["address"];
            $product_type    = $_POST["product_type"]??'';
            $openingBalance=floatval($_POST['openingBalance']);
            $balanceType=DEBIT;
            if($openingBalance>0){
                $balanceType=$_POST['balanceType'];
                if($balanceType!=DEBIT&&$balanceType!=CREDIT){
                    $error=fl();setMessage(63,'Opening balance type');
                }
            }
            if(empty($name)){setMessage(36,'Name');$error=fl();}
            else if(empty($code)){setMessage(36,'Code');$error=fl();}
            else if($product_type==''){setMessage(36,'Category');$error=fl();}
                else if(strlen($mobile)>15){setMessage(63,'Mobile');$error=fl();}

                    if(!isset($error)){
                $data = array(
                    'name'   => $name,
                    'code'=> $code,
                    'product_type'   => intval($product_type),
                    'contact_person'   => $contact_person,
                    'mobile'   => $mobile,
                    'address'   => $address,
                );
                $db->arrayUserInfoAdd($data);
                $db->transactionStart();
                $id=$db->insert('suppliers',$data,true);
                if($id!=false){
                    $sup=$smt->supplierInfoByID($id);
                    if($sup){
                        $supHead=$acc->getSupplierHead($sup);
                        if($supHead){
                            $opening=$acc->opening_voucher_create(OPENING_VOUCHER_TYPE_SUPPLIER,$id,$supHead,$openingBalance,$balanceType);
                            if($opening==false){$error=fl();setMessage(66);}
                        }else{$error=fl();setMessage(66);}
                    }else{$error=fl();setMessage(66);}
                }
                else{
                    $error=fl();setMessage(66);
                }

                if(!isset($error)){
                    $ac=true;
                }
                else{
                    $ac=false;
                }
                $db->transactionStop($ac);
                if(!isset($error)){
                    $general->redirect($pUrl,29,'Supplier');
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
                                    <?php 
                                        $general->inputBoxText('code','Code',@$_POST['code'],'y');
                                        $general->inputBoxText('name','Name',@$_POST['name'],'y');
                                        $general->inputBoxSelect($types,'Category','product_type','id','title',@$_POST['product_type']); 
                                        $general->inputBoxText('contact_person','Contact Person',@$_POST['contact_person']);
                                        $general->inputBoxText('mobile','Mobile',@$_POST['mobile']);
                                        $general->inputBoxTextArea('address','Address',@$_POST['address']);
                                    ?>
                                </div>
                                <div class="col-xs-6 col-sm-4">
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" for="openingBalance" >Opening Balance</label>
                                        <div class="col-md-8 no-gutters">
                                            <div class="col-md-7">
                                                <input type="text" class="form-control" name="openingBalance" id="openingBalance">
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-control" name="balanceType">
                                                    <option value="">Select Type</option>
                                                    <option value="<?php echo DEBIT;?>">পাব <?php echo $acc->columnNameById(DEBIT);?></option>
                                                    <option value="<?php echo CREDIT;?>">পাবে <?php echo $acc->columnNameById(CREDIT);?></option>

                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-6 col-sm-4">
                                    <div class="row"><?php $general->addBtn();?></div>
                                </div>
                                <div class="col-xs-6 col-sm-4">
                                </div>  
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
        $sup= $smt->supplierInfoByID($id);
        if(empty($sup)){$general->redirect($pUrl,37,$rModule['title']);}
        $data = array($pUrl=>$rModule['title'],'javascript:void()'=>$sup['name'],'1'=>'Opening Balance Edit');
        $general->pageHeader('Opening Balance Edit '.$sup['name'],$data);
        $openingVoucher=$acc->voucherDetails(V_T_OPENING,OPENING_VOUCHER_TYPE_SUPPLIER.'_'.$id);
        $cOpeningB=0;
        $supHead=$acc->getSupplierHead($sup);
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
                        $opening=$acc->opening_voucher_create(OPENING_VOUCHER_TYPE_SUPPLIER,$id,$supHead,$openingBalance,$balanceType);
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
    elseif(isset($_GET['archive'])){
        $data = array($pUrl=>$rModule['title'],'1'=>'Archive');
        $general->pageHeader('Archive '.$rModule['title'],$data);

    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <?php
                    show_msg();
                    $q=array();
                    $q[]='isActive=3';
                    $sq='where '.implode(' and ',$q);
                    $suppliers=$db->selectAll('suppliers',$sq);
                ?>
                <script type="text/javascript">
                    <?php
                        $cData=[];
                        $s=1;
                        foreach($suppliers as $k=>$sup){
                            $balance=0;
                            $cData[]=[
                                $sup['name'],
                                $types[$sup['product_type']]['title'],
                                $sup['contact_person'],
                                $sup['mobile'],
                                $sup['id'],


                            ];
                            unset($suppliers[$k]);
                        }
                        //$general->printArray($cData);
                        echo 'var dataSet='.json_encode($cData).';';
                    ?>
                    var editColumnID=4;

                    var daTableOption;
                    function supDataTable(){
                        if(daTableOption!=undefined){
                            daTableOption.destroy();
                        }
                        daTableOption=$('#supDataTable').DataTable( {
                            data: dataSet,
                            "lengthMenu": [[50,100,500,1000],[50,100,500,1000],[50,100,500,1000]],
                            columns: [
                                {title: "Name"},
                                {title: "Category"},
                                {title: "Contact Person"},
                                {title: "Mobile"},
                                {title: "Edit"}

                            ],
                            "createdRow": function ( row, data, index ) {
                                $('td', row).eq(editColumnID).html('<a href="<?php echo $pUrl;?>&edit='+data[editColumnID]+'" class="btn btn-info">Edit</a>');
                            }
                        });
                        t(daTableOption)
                    }
                    $(document).ready(function() {
                        supDataTable()
                    });
                </script>
                <table id="supDataTable" class="display"></table>
            </div>
        </div>
    </div>
    <?php 
    }
    elseif(isset($_GET['edit'])){
        if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
        $id= intval($_GET['edit']);
        $sup = $smt->supplierInfoByID($id);
        if(empty($sup)){$general->redirect($pUrl,37,$rModule['title']);}
        $deletePremision=false;
        if($deletePremision){
            if(isset($_GET['delete'])){
                $data = array(
                    'isActive'=> 2
                );
                $db->arrayUserInfoEdit($data);
                $where=array('id'=>$edit);
                $update=$db->update('suppliers',$data,$where);
                if($update){
                    $general->redirect($pUrl,14,'Employee');
                }
            }
        }
        $statuss=[
            ['i'=>1,'t'=>'Active'],
            ['i'=>0,'t'=>'In Active'],
            ['i'=>3,'t'=>'Archive']
        ];
        $supData = $general->getJsonFromString($sup['data']);

        if(isset($_POST['edit'])){
            $name        = $_POST["name"];
            $code        = $_POST["code"];
            $contact_person    = $_POST["contact_person"];
            $mobile    = $_POST["mobile"];
            $address    = $_POST["address"];
            $product_type    = $_POST["product_type"]??'';
            $status = intval($_POST['st']);

            if(empty($name)){setMessage(36,'Name');$error=fl();}
            else if($product_type==''){setMessage(36,'Category');$error=fl();}
                else if(strlen($mobile)>15){setMessage(63,'Mobile');$error=fl();}
                    if(!isset($error)){
                $data = [
                    'name' => $name,
                    'code'=> $code,
                    'product_type'   => intval($product_type),
                    'contact_person'   => $contact_person,
                    'mobile'   => $mobile,
                    'address'   => $address,
                    'isActive'=>$status
                ];
                $db->transactionStart();
                $db->arrayUserInfoEdit($data);
                $update=$db->update('suppliers',$data,['id'=>$id]);
                if($update!=false){
                    if($sup['ledger_id']>0){
                        $data=[
                            'title'=>$code.' '.$name
                        ];
                        $where=['id'=>$sup['ledger_id']];
                        $update=$db->update('a_ledgers',$data,$where);
                        if(!$update){$error=fl();setMessage(66);}
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
                    $general->redirect($pUrl,30,'Supplier');
                }

            }
        }
        $data = array($pUrl=>$rModule['title'],'javascript:void()'=>$sup['name'],'1'=>'Edit');
        $general->pageHeader('Edit '.$rModule['title'],$data);
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
                                    <?php 
                                        $general->inputBoxText('code','Code',$sup['code'],'y');
                                        $general->inputBoxText('name','Name',$sup['name'],'y');
                                        $general->inputBoxSelect($types,'Category','product_type','id','title',$sup['product_type']); 
                                        $general->inputBoxText('contact_person','Contact Person',$sup['contact_person']);
                                        $general->inputBoxText('mobile','Mobile',$sup['mobile']);
                                        $general->inputBoxTextArea('address','Address',$sup['address']);
                                    ?>
                                    <a href="<?php echo $pUrl;?>&editOpening=<?php echo $id;?>" style="color: green;">Edit Opening </a>

                                </div>

                                <div class="col-xs-6 col-sm-4">
                                    <?php


                                    ?>
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label" for="st">Status</label>
                                        <div class="col-md-8">
                                            <select name="st" id="st" class="form-control"  >
                                                <?php
                                                    foreach($statuss as $i){
                                                    ?>
                                                    <option value="<?=$i['i']?>" <?=$general->selected($i['i'],$sup['isActive'])?>><?=$i['t']?></option>
                                                    <?php
                                                    }

                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->saveBtn();?>
                                </div>

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
        $data = array($pUrl=>$rModule['title']);
        $archived = '<a href="' . $pUrl . '&archive" class="btn btn-info">Archive</a>';
        $general->pageHeader($rModule['title'],$data,$general->addBtnHtml($pUrl).$archived);

    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="white-box">
                    <div class="row">

                        <div class="col-sm-12 col-lg-12">
                            <?php
                                show_msg();
                            ?>
                        </div>
                        <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;">
                        </div>
                    </div>
                </div>
                <?php
                    show_msg();
                    
                ?>
                <script type="text/javascript">
                    

                    $(document).ready(function() {
                        supplier_list()
                    });
                </script>
            </div>
        </div>
    </div>
    <?php
    }
?>