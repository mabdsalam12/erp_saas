<?php
$heads_data=$db->selectAll('a_ledgers','where isActive=1 and '.$columnID.'=1');
$chart_of_accounts=$db->selectAll('a_charts_accounts','where isActive=1 order by code','id,title,code');

$heads=[];
foreach($heads_data as $h){
    $h['title']=$h['code'].' '.$h['title'];
    $heads[$h['id']]=$h;
}

if($chart_of_accounts){
    foreach($chart_of_accounts as $k=>$cot){
        $chart_of_accounts[$k]['title']=$cot['code'].' '.$cot['title'];
    }
}

$source_ledgers=$acc->get_all_cash_accounts($jArray);
$base = $db->allBase_for_voucher();
if($vType==V_T_INCOME){
$debit_ledgers=$source_ledgers;
$credit_ledgers=$heads;
}
else{
    $debit_ledgers=$heads;
    $credit_ledgers=$source_ledgers;
}
if(isset($_GET['edit'])){
    $edit = intval($_GET['edit']);
    $i = $db->get_rowData($general->table($tPTbl),$tpID,$edit);
    if(empty($i)){$general->redirect($pUrl,array(37,$pageTitle));}
    
    $general->arrayContentShow($i);
    if(isset($_POST['edit'])){
        $hID= $_POST["hID"];
        $amount= $_POST["trAmount"];
        $note= $_POST["trNote"]; 
        if(empty($hID)){SetMessage(36,'Head');$error=1;}
        if(empty($amount)){SetMessage(36,'Amount');$error=1;}
        if(!isset($error)){ 
            $db->transactionStart();
            $data = array(
                'hID'       => $hID,
                'trAmount'  => $amount,
                'trNote'    => $note
            );
            $db->arrayUserInfoEdit($data);
            $where = array($tpID=>$edit);
            $update = $db->update($general->table($tPTbl),$data,$where);  
            if($update){
                $logDescription2=$logDescription.' a='.$amount.' |h='.$hID.' |n='.$note;
                $log=$db->actionLogCreate('tr'.$trID.'_transactionEdit',$logDescription2);
                if($log==false){
                    $error=fl();setMessage(66);
                }
            }
            else{$error=fl();SetMessage(66);}
            if(!isset($error)){
                $ac=true;
            }
            else{
                $ac=false;
            }
            $db->transactionStop($ac);
            if(!isset($error)){
                $general->redirect($pUrl.'&'.$type,30,$pageTitle);
            }
        }
    }

    $data = array($pUrl=>$pageTitle,'javascript:void()'=>$pageTitle,'1'=>'Edit');
    $general->pageHeader('Edit '.$rModule['title'].' - '.$pageTitle,$data);
?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                <?php $general->inputBoxText('bID', $db->l('brand') ,$b['bTitle'],'','','readonly');?>  
                            </div> 
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                <?php
                                $db->dropdownInput(43,'hID','hID','hTitle',$i['hID'],'','y','','Select '.$pageTitle.' Head','and hType='.$headType);
                                ?>
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-4"> 
                                <?php $general->inputBoxText('trAmount','Amount',$i['trAmount'],'y'); ?>
                            </div>
                            <div class="clearfix visible-xs"></div>
                            <div class="col-xs-6 col-sm-12 col-md-12 padding-right-0">
                                <div class="form-group m-b-0">
                                    <div class="pull-right m-t-5">
                                        <input type="submit" name="edit" value="Update" class="btn btn-info waves-effect waves-light">
                                    </div>
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
    if(isset($_POST['add'])){
        $trDate = strtotime($_POST["trDate"]);
        $base_id    = intval($_POST["base_id"]);
        $debit    = intval($_POST["debit"]);
        $credit    = intval($_POST["credit"]);
        $amount = floatval($_POST["amount"]);
        $note   = $_POST["note"];
        if(!array_key_exists($debit,$debit_ledgers)){setMessage(36,$page_title.' Ledger');$error=fl();}
        elseif(!array_key_exists($credit,$credit_ledgers)){setMessage(36,$page_title.' Ledger');$error=fl();}
        elseif(!isset($base[$base_id])){setMessage(36,$page_title.' Ledger');$error=fl();}
        elseif($trDate<strtotime('-2 year')){$error=fl();setMessage(63,'Date');$error=fl();}
        elseif($amount<=0){setMessage(36,'Amount');$error=fl(); echo 'what';}
        if($trDate==TODAY_TIME){$trDate=TIME;}
        if(!isset($error)){
            $db->transactionStart();
            $extraData=['base_id'=>$base_id];
            $veID=$acc->voucher_create($vType,$amount,$debit,$credit,$trDate,$note,extraData:$extraData);
            if($veID==false){$error=fl();setMessage(66);}
            $ac = !isset($error);
            
            $db->transactionStop($ac);
            if($ac){
                setMessage(29,$page_title);
                $general->redirect($pUrl.'&date='.$_POST["trDate"]);
            }
        }
    }
    $data = [$pUrl=>$page_title];
    $general->pageHeader($page_title,$data);
    $date='';
    if(isset($_GET['date'])){
        $date=$_GET['date'];
    }
    if(isset($_POST['trDate'])){
        $date=$_POST['trDate'];
    }
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <h4><?= $page_title;?> Add</h4>
                        
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <?php 
                                        $general->inputBoxText('trDate','Date',$date,'','daterangepicker','autocomplete="off"');
                                        $general->inputBoxSelect($base,'Base','base_id','id','title','',haveSelect:'n');
                                        $general->inputBoxSelect($debit_ledgers,'Debit ledger','debit','id','title','','y','','','Select ledger');
                                        $general->inputBoxSelect($credit_ledgers,'Credit Ledger','credit','id','title','','y','','','Select ledger');
                                    ?>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <?php $general->inputBoxText('amount','Amount','','y'); ?>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12"> 
                                    <div class="form-group row">
                                        <label for="note" class="col-md-4 col-form-label">Note</label>
                                        <div class="col-md-8">
                                            <textarea placeholder="Note" cols="" class="form-control" rows="" id="note"></textarea>
                                            <div class="col-xs-6 col-sm-4 col-md-4"></div>
                                            <div class="col-xs-6 col-sm-4 col-md-4">
                                                <div class="form-group ">
                                                    <div class="pull-right m-t-5">
                                                        <button class="btn btn-info waves-effect waves-light income_and_expense_add" onclick="income_and_expense_add(<?=$vType?>)">Save</button>
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                    </div>
                </div>
                <div class="row"><div class="col-sm-12 col-lg-12" id="trDetailsAreaf"></div></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-3">
                        <h5 class="box-title">Date </h5>
                        <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>
                    <?php
                        $general->inputBoxSelectForReport($chart_of_accounts,'Chart of Account','charts_accounts_id','id','title','select2 form-control');
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Head</h5>
                        <select id='shID' class="form-control select2">
                            <option value="">All</option>
                            <?php
                            foreach($heads as $cn){
                            ?>
                                <option value="<?= $cn['id'];?>"><?= $cn['title'];?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">User</h5>
                        <select id='uID' class="form-control select2">
                            <option value="">All</option>
                            <?php
                            $heads=$db->allUsers('order by username asc');
                            foreach($heads as $cn){
                            ?>
                                <option value="<?= $cn['id'];?>" <?= $general->selected($cn['id'],USER_ID);?>><?= $cn['username'];?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <input type="submit" value="Search" class="btn btn-success" name="s" onclick="income_expense_list('<?= $vType;?>');">
                    </div>
                    <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                    <div class="col-sm-12 col-lg-12" id="reportArea"></div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>
<script>
    $( document ).ready(function() {
        income_expense_list('<?= $vType;?>');
    });
</script>
