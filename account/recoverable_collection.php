<?php
$pageTitle      = $rModule['name'];
$base = $db->selectAll('base','','id,title');

$general->arrayIndexChange($base);
$source_ledgers=$acc->get_all_cash_accounts($jArray);
if(isset($_GET['add'])){
    $data = [$pUrl=>$pageTitle,'1'=>'Add'];
    $general->pageHeader('Add '.$pageTitle,$data);
    
    $customer_data=$smt->get_base_wise_all_customer();
    $customers=$customer_data['customers'];
    $base_customers=$customer_data['base_customers'];
    $employees = $db->selectAll('employees','where isActive=1','id,name');
    $general->arrayIndexChange($employees);
    if(isset($_POST['add'])){
        $date = strtotime($_POST['date']);
        $amount = floatval($_POST['amount']);
        $debit_ledger = floatval($_POST['debit']);
        // $base_id = intval($_POST['base_id']);
        // $customer_id = intval($_POST['customer_id']);
        $employee_id = intval($_POST['employee_id']);
        $note = $_POST['note'];
        if($date<strtotime('-10 year')){$error=fl();setMessage(63,'Date');}
        // else if($base_id<1){$error=fl(); setMessage(36,'Base');}
        elseif(!array_key_exists($debit_ledger,$source_ledgers)){setMessage(36,'debit ledger');$error=fl();}
        // elseif($customer_id<1){$error=fl(); setMessage(36,'Customer');}
        elseif($employee_id<1){$error=fl(); setMessage(36,'Employee');}
        // elseif(!isset($customers[$customer_id])){$error=fl(); setMessage(63,'Customer');}
        elseif(!isset($employees[$employee_id])){$error=fl(); setMessage(63,'Customer');}
        elseif($amount<=0){$error=fl(); setMessage(63,'Amount');}
        else{
            $recoverable_head=$acc->getSystemHead(AH_RECOVERABLE_COLLECTION);   
            if($recoverable_head==false){$error=fl();setMessage(66);}
        }
        if($date==TODAY_TIME){$date=TIME;}
        if(!isset($error)){
            $db->transactionStart();
            $data=[
                // 'customer_id'=>$customer_id,
                'customer_id'=>0,
                'employee_id'=>$employee_id,
                'collect'=>$amount,
                'createdBy'=>USER_ID,
                'createdOn'=>$date,
            ];
            $ref = $db->insert('recoverable_collection',$data,true);
            if(!$ref){
                $error=fl();setMessage(66);
            }
            $note = "Recoverable collection $note";
            $newVoucher=$acc->voucher_create(V_T_NEW_RECOVERABLE_COLLECTION,$amount,$debit_ledger,$recoverable_head,$date,$note,$ref,0,['base_id'=>$base_id]);
            if($newVoucher==false){
                $error=fl(); setMessage(66);
            }
            $ac=false;
            if(!isset($error)){
                $ac=true;                     
            }
            $db->transactionStop($ac);
            if(!isset($error)){
                $general->redirect($pUrl,29,$pageTitle);
            }
        }
    }
    
    ?>
    <script>
        <?php  echo 'const base_customers='.json_encode($base_customers).';'; ?>
        $(document).on('change','#base_id',function(){base_wise_customer(this.value,'','','Select Customer')});
    </script>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">

                                <?php
                                $general->inputBoxText('date','Date','','','daterangepicker','autocomplete="off"');
                                // $general->inputBoxSelect($base,'Base','base_id','id','title',@$_POST['base_id'],'y','','','Select base');
                                $general->inputBoxSelect($source_ledgers,'Debit ledger','debit','id','title','','y','','','Select ledger');
                                // $general->inputBoxSelect([],'Customer','customer_id','id','name','','y','','','Select Customer');
                                $general->inputBoxSelect($employees,'Employee','employee_id','id','name');
                                $general->inputBoxText('amount','Amount',@$_POST['amount']);
                                $general->inputBoxTextArea('note','Note',@$_POST['note']);
                                ?>
                                <div class="form-group m-b-0">
                                    <div class="pull-right">
                                        <input type="submit" name="add" value="Add" class="btn btn-lg btn-info waves-effect waves-light">
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
    $data = array($pUrl=>$pageTitle);
    $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));

    ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">

                    <div class="col-md-3">
                        <h5 class="box-title">Date </h5>
                        <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>
                    <?php
                    $general->inputBoxSelectForReport($base,'Base','base_id','id','title');    
                    ?>

                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <input type="submit" value="Search"class="btn btn-success" name="s" onclick="recoverable_collection_report();">
                    </div>
                    <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                    <div class="col-sm-12 col-lg-12" id="reportArea"></div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function recoverable_collection_report(){
            let dRange   = $('#dRange').val();
            let base_id = $('#base_id').val();
            $('#reportArea').html(loadingImage);
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{recoverable_collection_report:1,dRange:dRange,base_id:base_id},
                success:function(data){
                    if(typeof(data.status)!="undefined"){
                        if(data.status==1){
                            $('#reportArea').html(data.html);
                        }
                        else{
                            $('#reportArea').html('');
                        }
                        swMessageFromJs(data.m);
                    }
                    else{
                        $('#reportArea').html('');
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                },
                error:function(){
                    $('#reportArea').html('');
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            });
        }
        $(document).ready(function(){
            recoverable_collection_report();
        });
    </script>
    <?php
}