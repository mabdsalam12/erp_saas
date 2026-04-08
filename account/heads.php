<?php
    $aStatus      = $db->permission(23);
    $eStatus      = true;
    $chartAcc=$db->selectAll('a_charts_accounts','order by title asc');
    $general->arrayIndexChange($chartAcc,'id');
    $master_account=$db->selectAll('a_master_account');
    $general->arrayIndexChange($master_account,'id');

    if(isset($_GET['add'])){    
        if(isset($_POST['add'])){
            $title              = $_POST["title"];
            $code  = $_POST["code"];
            $opBalance  = floatval($_POST["opBalance"]);
            $blType     = intval($_POST["blType"]);
            $charts_accounts_id = intval($_POST["charts_accounts_id"]);
            if(empty($title)){setMessage(36,'Title');$error=fl();}
            else if(empty($code)){setMessage(36,'Code');$error=fl();}
            else if(!array_key_exists($charts_accounts_id,$chartAcc)){$error=fl();setMessage(63,'Chart of accounts');}
            elseif($opBalance<0){$error=fl();setMessage(63,'Opening Balance');}
            elseif($opBalance>0){
                if($blType!=DEBIT&&$blType!=CREDIT){$error=fl();setMessage(63,'Opening Balance Type');}
            }
            if(!isset($error)){
                $data = [
                    'title'             => $title,
                    'type'              => H_TYPE_CUSTOM,
                    'code'              => $code,
                    'charts_accounts_id'=> $charts_accounts_id
                ];
                $db->arrayUserInfoAdd($data);
                $ledger_id = $db->insert('a_ledgers',$data,true);
                if($ledger_id){
                    if($opBalance>0){
                        $capitalHead=$acc->getSystemHead(AH_CAPITAL);
                        if($capitalHead==false){$error=fl();setMessage(66);}
                        if(!isset($error)){
                            $opening=$acc->opening_voucher_create(OPENING_VOUCHER_TYPE_LEDGER,$ledger_id,$ledger_id,$opBalance,$blType);
                            if($opening==false){$error=fl();setMessage(66);}
                        }
                    }

                    $general->redirect($pUrl,29,$rModule['title']);
                }
                else{
                    $error=fl();setMessage(66);
                }
            }
        }
        $data = [$pUrl=>$rModule['title'],1=>'Add'];
        $general->pageHeader($rModule['title'],$data);
    ?>
    <div class="row">
        <div class="col-lg-12"><?php show_msg();?></div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('code','Code',@$_POST['code'],'y');?>
                                    <?php
                                        $general->inputBoxText('title','Title',@$_POST['title'],'y');  
                                    ?>
                                    <?php $general->inputBoxSelect($chartAcc,'Chart of Accounts','charts_accounts_id','id','title',@$_POST['charts_accounts_id'],'y');?>
                                    
                                    <div class="form-group row">
                                        <label for="qty" class="col-md-4 col-form-label">Opening Balance</label>
                                        <div class="col-md-8">
                                            <div class="input-group qty-input-group">
                                                <input class="form-control amount_td" value="<?php echo @$_POST['opBalance'];?>" placeholder="Balance" type="text" name="opBalance">
                                                <div class="input-group-append">
                                                    <select name="blType">
                                                        <option value="">Select Type</option>
                                                        <option value="<?php echo DEBIT;?>" <?php echo $general->selected(DEBIT,@$_POST['blType']);?>>Debit</option>
                                                        <option value="<?php echo CREDIT;?>" <?php echo $general->selected(CREDIT,@$_POST['blType']);?>>Credit</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php echo $general->addBtn();?>
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
    elseif(isset($_GET['edit'])){
        if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
        $id           = intval($_GET['edit']);
        $h              = $acc->headInfoByID($id);
        $for_voucher_entry=0;
        if(GROUP_ID==SUPERADMIN_USER&&$h['system_head']){
            $company_data = $db->get_company_data();
            $for_voucher_entry_ledgers = $company_data['for_voucher_entry_ledgers']??[];
            $for_voucher_entry = isset($for_voucher_entry_ledgers[$id])?1:0;
        }

        if(empty($h)){$general->redirect($pUrl,37,$rModule['title']);}

        $general->arrayContentShow($h);
        if(isset($_POST['edit'])){
            $title              = $_POST["title"];
            $code  = $_POST["code"];
            $charts_accounts_id = intval($_POST["charts_accounts_id"]);
            if(GROUP_ID==SUPERADMIN_USER&&$h['system_head']){
                $for_voucher_entry = isset($_POST['for_voucher_entry'])?1:0;
            }   
            if(empty($title)&&$h['system_head']==0){setMessage(36,'Title');$error=1;}
            else if(empty($code)){setMessage(36,'Code');$error=fl();}
            else if(!array_key_exists($charts_accounts_id,$chartAcc)){$error=fl();setMessage(63,'Chart of accounts');}

            if(!isset($error)){
                $data = [
                    'code'              => $code,
                    'charts_accounts_id'=> $charts_accounts_id
                ];
                if($h['system_head']==0){
                    $data['title']=$title;
                }
                $db->arrayUserInfoEdit($data);
                $where = ['id'=>$id];
                $update = $db->update('a_ledgers',$data,$where);  
                if($update){
                    if(GROUP_ID==SUPERADMIN_USER&&$for_voucher_entry){
                        $for_voucher_entry_ledgers[$id]=$id;
                        $company_data['for_voucher_entry_ledgers']=$for_voucher_entry_ledgers;
                        $update = $db->company_data_update($company_data);
                        if(!$update){$error=fl(); setMessage(66);}
                    }
                    
                }
                else{$error=fl();SetMessage(66);}
            }
            if(!isset($error)){
                $general->redirect($pUrl,30,$rModule['title']);
            }
        }

        $data = [$pUrl=>$rModule['title'],'javascript:void()'=>$h['title'],'1'=>'Edit'];
        $general->pageHeader('Edit '.$rModule['title'],$data);
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('code','Code',$h['code'],'y');?>
                                    <?php
                                        $disabled='';
                                        if($h['system_head']==1){
                                            $disabled='disabled';
                                        }
                                        $general->inputBoxText('title','Title',$h['title'],'y','',$disabled);
                                    ?>
                                    <?php $general->inputBoxSelect($chartAcc,'Chart of Accounts','charts_accounts_id','id','title',$h['charts_accounts_id'],'y');?>
                                    <?php
                                        if(GROUP_ID==SUPERADMIN_USER&&$h['system_head']){
                                        ?>
                                        <div class="form-group row">
                                            <label for="for_voucher_entry" class="col-md-4 col-form-label ">For voucher entry</label>
                                            <div class="col-md-8">
                                                <input class="form-check-input" <?=$general->checked($for_voucher_entry)?> value="1"  id="for_voucher_entry" type="checkbox" name="for_voucher_entry" >
                                            </div>
                                        </div>
                                        <?php
                                        }
                                    ?>
<a href="<?php echo $pUrl;?>&editOpening=<?php echo $id;?>" style="color: green;">Edit Opening </a>
                                    <?php echo $general->editBtn();?>
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
        $h              = $acc->headInfoByID($id);
        if(empty($h)){$general->redirect($pUrl,37,$rModule['title']);}

        
        $data = [$pUrl=>$rModule['title'],'javascript:void()'=>$h['title'],'1'=>'Edit'];
        $general->pageHeader($h['title'],$data);

        $openingVoucher=$acc->voucherDetails(V_T_OPENING,OPENING_VOUCHER_TYPE_LEDGER.'_'.$id);
        $cOpeningB=0;
        $cOpeningD=0;
        $openingType=0;
        $general->printArray($openingVoucher);
        if(!empty($openingVoucher)){
            $o=current($openingVoucher);
            $cOpeningB=(float)$o['amount'];
            if($o['debit']==$id){
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
                        $dHead=$id;
                        $cHead=$capitalHead;
                    }
                    else{
                        $dHead=$capitalHead;
                        $cHead=$id;
                    }
                    $update=$acc->voucherEdit($o['id'],$openingBalance,$o['note'],$dHead,$cHead);
                    if($update==false){$error=fl();setMessage(66);}
                }
                else{
                    if(!isset($error)){
                        $opening=$acc->opening_voucher_create(OPENING_VOUCHER_TYPE_LEDGER,$id,$id,$openingBalance,$blType);
                        if($opening==false){$error=fl();setMessage(66);}
                    }
                }
            }
            else{
                if(!empty($openingVoucher)){
                    foreach($openingVoucher as $o){
                        $delete=$db->delete('a_ledger_entry',['voucher_id'=>$o['id']]);
                        if($delete==false){$error=fl();setMessage(66);}
                        $delete=$db->delete('a_voucher_entry',['id'=>$o['id']]);
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
                $general->redirect($pUrl,29,'Ledger opening');
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
                                                <option value="<?php echo DEBIT;?>" <?php echo $general->selected(DEBIT,$openingType);?>>Debit</option>
                                                <option value="<?php echo CREDIT;?>" <?php echo $general->selected(CREDIT,$openingType);?>> Credit</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    else{

        $general->pageHeader($rModule['title'],'',$general->addBtnHtml($pUrl));

    ?>
    <div class="row">
        <div class="col-sm-12"><?php show_msg();?></div>
    </div>
    <div class="row">
        <?php
            $ledger_search_title=$_SESSION['ledger_search_title']??'';
            $ledger_search_type=$_SESSION['ledger_search_type']??0;
            $charts_accounts_id=$_SESSION['charts_accounts_id']??0;
            $q=[];

            if(isset($_GET['title'])){
                $title=$_GET['title']; 
                if(!empty($title)){
                    $ledger_search_title=$title;
                }
                else{
                    $ledger_search_title='';
                }
            }
            if(isset($_GET['type'])){
                $type=intval($_GET['type']);
                $ledger_search_type=$type;

            }
            if(isset($_GET['charts_accounts_id'])){
                $charts_accounts_id=intval($_GET['charts_accounts_id']);
            }
            if($ledger_search_title!=''){
                $q[]="title like '%$ledger_search_title%'";
            }
            if($ledger_search_type>0){
                $q[]="type=$ledger_search_type";
            }
            if($charts_accounts_id>0){
                $q[]="charts_accounts_id=$charts_accounts_id";
            }
            $_SESSION['ledger_search_title']=$ledger_search_title;
            $_SESSION['ledger_search_type']=$ledger_search_type;
            $_SESSION['charts_accounts_id']=$charts_accounts_id;
            if(count($q)>0){
                $query="where ".implode(' and ',$q); 
            }
            else{
                $query="";
            }
            $heads=$db->selectAll('a_ledgers', $query.' order by code asc','',$general->showQuery()); 
            $ledger_ids=[];
            foreach($heads as $h){
                $ledger_ids[]=$h['id'];
            }
            if(!empty($ledger_ids)){
                $all_balance=$acc->headBalance($ledger_ids,0,0,['groupByHID'=>1]);
            }
            if(!empty($all_balance)){
                $general->arrayIndexChange($all_balance,'ledger_id');
            }
        ?>
        <div class="col-sm-12">
            <div class="white-box">
                <div class="col-sm-12 col-lg-12 padding-left-0">
                    <form action="" method="get">
                        <input type="hidden" name="<?php echo MODULE_URL;?>" value="<?php echo $rModule['slug'];?>">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="title" value="<?php echo $ledger_search_title?>" placeholder='Title'>  
                        </div>
                        <div class="col-md-2">
                            <select name="type">
                                <option value="0">All type</option>
                                <option value="<?=H_TYPE_CUSTOM?>" <?=$general->selected(H_TYPE_CUSTOM,$ledger_search_type)?>>Custom</option>
                                <option value="<?=H_TYPE_SUPPLIER?>" <?=$general->selected(H_TYPE_SUPPLIER,$ledger_search_type)?>>Supplier</option>
                                <option value="<?=H_TYPE_CUSTOMER?>" <?=$general->selected(H_TYPE_CUSTOMER,$ledger_search_type)?>>Customer</option>
                                <option value="<?=H_TYPE_AUTO_HEAD?>" <?=$general->selected(H_TYPE_AUTO_HEAD,$ledger_search_type)?>>System head</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <?php
                            $general->inputBoxSelect($chartAcc,'Chart of Accounts','charts_accounts_id','id','title',@$_GET['charts_accounts_id']);
                            ?>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" value="Search" class="btn btn-success" name="s">
                        </div>
                    </form>
                </div> 
                <table class="table table-striped table-bordered table-hover only_show">
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Type</th>
                            <th>Main account</th>
                            <th>Chart of accounts</th>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Balance</th>
                            <th>Income</th>
                            <th>Expense</th>
                            <?php
                                if($eStatus){
                                ?>
                                <th>Edit</th>
                                <?php
                                }
                            ?>
                        </tr>
                    </thead> 
                    <tbody>
                        <?php
                            $total=1;
                            $total_balance=0;
                            foreach($heads as $h){
                                $c=$chartAcc[$h['charts_accounts_id']];
                                $m=$master_account[$c['master_account_id']];
                                $balance=0;
                                if(isset($all_balance[$h['id']])){
                                    $balance=$all_balance[$h['id']]['balance'];
                                }
                                $total_balance+=$balance;
                            ?>
                            <tr>
                                <td><?=$total++?></td>
                                <td><?=$acc->get_head_type($h['type'])?></td>
                                <td><?=$m['code']?> <?=$m['title']?></td>
                                <td><?=$c['code']?> <?=$c['title']?></td>
                                <td><?=$h['code']?></td>
                                <td><?=$h['title']?></td>
                                <td class="amount_td"><?=$general->numberFormat($balance)?></td>

                                <td>
                                    <?php
                                        if($h['system_head']==0){
                                        ?>
                                        <div class="checkbox checkbox-info checkbox-circle">
                                            <input type="checkbox"
                                                class="checkbox-circle" <?php echo $general->checked($h['for_income']);?>
                                                id="dBrokenOpeni_<?php echo $h['id'];?>"
                                                value="1"
                                                onchange="headForIncExpSet('<?php echo $h['id'];?>','i',this.checked)"
                                                >
                                            <label for="dBrokenOpen_<?php echo $h['id'];?>"></label>
                                        </div>
                                        <?php
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if($h['system_head']==0){
                                        ?>
                                        <div class="checkbox checkbox-info checkbox-circle">
                                            <input type="checkbox"
                                                class="checkbox-circle" <?php echo $general->checked($h['for_expense']);?>
                                                id="dBrokenOpene_<?php echo $h['id'];?>"
                                                value="1"
                                                onchange="headForIncExpSet('<?php echo $h['id'];?>','e',this.checked)"
                                                >
                                            <label for="dBrokenOpen_<?php echo $h['id'];?>"></label>
                                        </div>
                                        <?php
                                        }
                                    ?>
                                </td>
                                <?php
                                    if($eStatus){
                                    ?>
                                    <td>
                                        <a href="<?=$pUrl?>&edit=<?=$h['id']?>" class="btn btn-info">Edit</a>
                                    </td>
                                    <?php
                                    }
                                ?>
                            </tr>
                            <?php
                            }
                        ?>
                        <tr>
                            <td colspan="6" class="text-right">Total</td>
                            <td class="amount_td"><b><?=$general->numberFormat($total_balance)?></b></td>
                            <td></td>
                            <td></td>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}