<?php

    $customer_data=$smt->get_base_wise_all_customer();
    $base_customers=$customer_data['base_customers'];
    $customer=$customer_data['customers'];

    
    $base = $db->selectAll('base','where status=1');
    $employees = $db->selectAll('employees','where isActive=1','id,name');
    $data = [$pUrl=>$rModule['name']];
    $general->pageHeader($rModule['name'],$data);
    $transaction_type=[
        [
            'id'=>V_T_RECEIVE_FROM_CUSTOMER,
            'title'=>'Receive from customer'
        ],
        [
            'id'=>V_T_CUSTOMER_COLLECTION_DISCOUNT,
            'title'=>'Collection discount'
        ],
        [
            'id'=>V_T_CUSTOMER_YEARLY_DISCOUNT,
            'title'=>'Yearly discount'
        ],
        [
            'id'=>V_T_RECOVERABLE_ENTRY,
            'title'=>'Old Recoverable collection'
        ],
        [
            'id'=>V_T_NEW_RECOVERABLE_ENTRY,
            'title'=>'Recoverable collection'
        ],
        [
            'id'=>V_T_CUSTOMER_BAD_DEBT,
            'title'=>'Bad-Debt'
        ],
        [
            'id'=>V_T_PAY_TO_CUSTOMER,
            'title'=>'Pay to customer'
        ]
        ];
?>
<script>
    <?php  echo 'const base_customers='.json_encode($base_customers).';'; ?>
    $(document).on('change','#base_id',function(){base_wise_customer(this.value,'','','Select Customer')});
    $(document).on('change','#search_base_id',function(){base_wise_customer(this.value,'search_customer_id')});
    
</script>
<div class="row">
    <div class="col-lg-12">
        <div class="white-box border-box">
            <div class="row">
                <div class="col-lg-12"><?php show_msg();?></div>
                <div class="col-xs-6 col-sm-6 col-md-6">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <?php $general->inputBoxText('trDate','Date','','','daterangepicker','autocomplete="off"'); ?>
                            <?php
                                $general->inputBoxSelect($base,'Base','base_id','id','title');
                                $general->inputBoxSelect([],'Customer','customer_id','id','name','','y','','','Select Customer');
                                $general->inputBoxText('cMobile','Mobile');
                                $general->inputBoxText('cAddress','Address');
                                $general->inputBoxSelect([],'Invoice','invoice_id','id','invoice_no');
                                $general->inputBoxSelect($transaction_type,'Transaction type','trType','id','title','','','','onclick="newBalance()"');
                                
                            ?>
                            <div id="employees" style="display: none;">
                                <?php 
                                $general->inputBoxSelect($employees,'Employee','employee_id','id','name');
                                ?>
                            </div>
                            <div id="customerStBtn"></div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            মাইনাস মানে কাস্টমার পাবে
                            <?php $general->inputBoxText('cBlance','Balance','','','','disabled') ?>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <?php $general->inputBoxText('trAmount','Amount','','','','autocomplete="off" onkeyup="newBalance()"'); ?>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <?php $general->inputBoxText('nBalance','New Balance','','','','disabled') ?>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12"> 
                            <div class="form-group row">
                                <label for="trNote" class="col-md-4 col-form-label">Note</label>
                                <div class="col-md-8">
                                    <textarea placeholder="Note" cols="" class="form-control" rows="" id="trNote" name="trNote"><?php echo htmlspecialchars(@$_POST['trNote'].' ') ?></textarea>
                                    <div class="col-xs-6 col-sm-4 col-md-4"></div>
                                    <div class="col-xs-6 col-sm-4 col-md-4">
                                        <div class="form-group ">
                                            <div class="pull-right m-t-5">
                                                <button onclick="customer_transaction()" id="payCustomerAdd" value="Save" class="btn btn-info waves-effect waves-ligh customer_transactiont">Save</button>
                                            </div>
                                        </div>
                                    </div> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        function newBalance(){
                            let cBalance=parse_int($('#cBlance').val());
                            let trAmount=parse_int($('#trAmount').val());
                            let trType      = parse_int($('#trType').val());
                            let nBalance=0;
                            $('#employees').hide();
                            if(trType==<?=V_T_NEW_RECOVERABLE_ENTRY?>){
                                $('#employees').show();
                            }
                            if(
                                trType==<?=V_T_RECEIVE_FROM_CUSTOMER?>
                                ||trType==<?=V_T_CUSTOMER_COLLECTION_DISCOUNT?>
                                ||trType==<?=V_T_CUSTOMER_YEARLY_DISCOUNT?>
                                ||trType==<?=V_T_NEW_RECOVERABLE_ENTRY?>
                                ||trType==<?=V_T_CUSTOMER_BAD_DEBT?>){
                                nBalance=cBalance-trAmount;
                                $('#forPay').hide();
                            }
                            else if(trType==<?=V_T_PAY_TO_CUSTOMER?>){
                                nBalance=cBalance+trAmount;
                                $('#forPay').show(); 
                            }
                            else{
                                nBalance='';
                            }
                            $('#nBalance').val(nBalance);
                        }
                        function customerStBtn(){
                            let cID = parse_int($('#customer_id').val());
                            if(cID>0){
                                $('#customerStBtn').html('<a href="?mdl=customerStatment&cID='+cID+'" target="_blank" class="btn btn-info">Statment</a>');
                            }
                            else{
                                $('#customerStBtn').html('');
                            }
                        }
                        $('#customer_id').on('change', function(){
                            customerCurrentBalance();
                            customerStBtn();
                        });
                        function customerCurrentBalance(){
                            let cID = parse_int($('#customer_id').val());
                            if(0<cID){
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{customerCurrentBalance:1,cID:cID},
                                    success:function(data){
                                        if(data.status==1){
                                            let mbalance=data.balance;
                                            $('#cBlance').val(mbalance);
                                            $('#cMobile').val(data.cMobile);
                                            $('#cAddress').val(data.cAddress);
                                            $('#invoice_id').html('');
                                            $('#invoice_id').append('<option value="">Select invoice</option>');
                                            $.each(data.due_invoice,function(a,b){
$('#invoice_id').append('<option value="'+b.id+'">'+b.invoice_no+' #'+b.due+'</option>');
                                            });
                                            select2Call();
                                            newBalance();
                                        }
                                        //swMessageFromJs(data.m);
                                    }
                                }); 
                            }
                        }
                        function customer_transaction(){
                            let cID         = parse_int($('#customer_id').val());
                            let trDate      = $('#trDate').val();
                            let trNote      = $('#trNote').val();
                            let trAmount    = parse_float($('#trAmount').val());
                            let onlineCharge= parse_float($('#onlineCharge').val());
                            let trType      = parse_int($('#trType').val());
                            let invoice_id  = parse_int($('#invoice_id').val());
                            let employee_id  = parse_int($('#employee_id').val());
                            errorSet=0;
                            if(1>cID){
                                errorSet=1;   
                            }
                            else if(0==trType){
                                errorSet=1;
                                swMessage('Please select transaction type.');
                            } 
                            else if(trType==<?=V_T_NEW_RECOVERABLE_ENTRY?> && employee_id==0){
                                errorSet=1;
                                swMessage('Employee field is required.');
                            }
                            else if(0==trAmount){
                                errorSet=1;
                                swMessage('Amount field is required.');
                            } 
                            else if(''==trNote){
                                errorSet=1;
                                swMessage('Note field is required.');
                            } 
                            if(errorSet==0){
                                buttonLoading('customer_transaction');
                                //$('#reportArea').html(loadingImage);
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{
                                        customer_transaction:1,
                                        customer_id:cID,
                                        trDate:trDate,
                                        trNote:trNote,
                                        trType:trType,
                                        employee_id:employee_id,
                                        trAmount:trAmount,
                                        onlineCharge:onlineCharge,
                                        invoice_id:invoice_id
                                    },
                                    success:function(data){
                                        button_loading_destroy('customer_transaction','Save');
                                        //$('#reportArea').html('');
                                        if(typeof(data.status)!=='undefined'){
                                            if(data.status==1){
                                                //console.log('it work')

                                                $('#base_id').val('');
                                                $('#customer_id').html('');
                                                $('#trType').val('');
                                                $('#trNote').val('');
                                                $('#cBlance').val('');
                                                $('#trAmount').val('');
                                                $('#nBalance').val('');
                                                $('#onlineCharge').val('');
                                                $('#customerStBtn').html('');
                                                customerPaymentList();
                                                select2Call();
                                            }
                                            swMessageFromJs(data.m);
                                        }
                                        else{
                                            swMessage(AJAX_ERROR_MESSAGE); 
                                        }
                                    },
                                    error:function(data){
                                        button_loading_destroy('customer_transaction','Save');
                                        swMessage(AJAX_ERROR_MESSAGE); 
                                    }
                                });
                            }
                        }  
                        function customer_transaction_remove(voucher_id){
                            $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{customer_transaction_remove:1,voucher_id:voucher_id},
                                    success:function(data){
                                        if(data.status==1){
                                            customerPaymentList();
                                        }
                                        swMessageFromJs(data.m);
                                    }
                                }); 
                            
                        }
                    </script>
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
                    $base = $db->selectAll('base','','id,title');
                ?>
                <?php $general->inputBoxSelectForReport($base,'Base','search_base_id','id','title') ?>
                <?php $general->inputBoxSelectForReport($customer,'Customer','search_customer_id','id','name') ?>
                <?php $general->inputBoxSelectForReport($transaction_type,'Transaction type','transaction_type','id','title'); ?>

                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <button class="btn btn-success" name="s" onclick="customerPaymentList();">Search</button>
                </div>
                <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                <div class="col-sm-12 col-lg-12" id="trDetailsArea"></div>
            </div>
        </div>
    </div>
</div>
<script>
    $( document ).ready(function() {
        customerPaymentList();
    });
    function customerPaymentList(){
        //let cID=$('#cIDL').val();
        let dRange=$('#dRange').val();
        let customer_id =  $('#search_customer_id').val();
        let base_id =  $('#search_base_id').val();
        let transaction_type =  $('#transaction_type').val();
        
        $('#trDetailsArea').html(loadingImage);

        $.ajax({
            type:'post',
            url:ajUrl,
            data:{customerPaymentList:1,dRange:dRange,customer_id:customer_id,base_id:base_id,transaction_type:transaction_type},
            success:function(data){
                $('#trDetailsArea').html('');
                //$('#reportArea').html('');
                if(typeof(data.status)!=='undefined'){
                    if(data.status==1){
                        $('#trDetailsArea').html(data.html);
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            },
            error:function(data){
                $('#trDetailsArea').html('');
                swMessage(AJAX_ERROR_MESSAGE); 
            }
        });

    }   

</script>