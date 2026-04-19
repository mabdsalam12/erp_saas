<?php
    $persons=$db->selectAll('person','where isActive=1 order by name asc','id,name');
    $general->arrayIndexChange($persons,'id');
    $data = array($pUrl=>$rModule['name']);
    $general->pageHeader($rModule['name'],$data);
    $cash_accounts=$acc->get_all_cash_accounts();
    
?>
<div class="row">
    <div class="col-lg-12">
        <div class="white-box border-box">
            <div class="row">
                <div class="col-lg-12"><?php show_msg();?></div>
                <div class="col-xs-6 col-sm-6 col-md-6">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <?php $general->inputBoxText('date','Date','','','daterangepicker','autocomplete="off"'); ?>
                            <?php
                                $general->inputBoxSelect($persons,'Person','id','id','name','','y','','','Select Person');
                                $general->inputBoxText('mobile','mobile',inputScript:'disabled');
                                $general->inputBoxSelect([
                                    [
                                        'id'=>'1',
                                        'title'=>'Receive from person'
                                    ],
                                    [
                                        'id'=>'2',
                                        'title'=>'Pay to person'
                                    ],
                                    ],'Transaction type','trType','id','title','','','','onclick="newBalance()"');
                            ?>
                            <div id="customerStBtn"></div>
                            <?php 
                            $general->inputBoxSelect($cash_accounts,'Bank / Cash','bank_id','id','title');
                            ?>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                        মাইনাস মানে পার্সন পাবে
                            <?php $general->inputBoxText('currentBalance','Balance','','','','disabled') ?>
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
                                    <textarea placeholder="Note" cols="" class="form-control" rows="" id="trNote" name="trNote"><?php echo isset($_POST['trNote'])?htmlspecialchars(@$_POST['trNote']):''?></textarea>
                                    <div class="col-xs-6 col-sm-4 col-md-4"></div>
                                    <div class="col-xs-6 col-sm-4 col-md-4">
                                        <div class="form-group ">
                                            <div class="pull-right m-t-5">
                                                <button  onclick="transactionWithPerson()" id="personTransactionBtn" class="btn btn-info waves-effect waves-light transactionWithPerson">Save</button>
                                            </div>
                                        </div>
                                    </div> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--</form>-->
                    <script>
                        function newBalance(){
                            var cBalance=parse_int($('#currentBalance').val());
                            var trAmount=parse_int($('#trAmount').val());
                            var trType      = parse_int($('#trType').val());
                            var nBalance=0;
                            if(trType==1){
                                nBalance=cBalance-trAmount;
                                $('#forPay').hide();
                            }
                            else if(trType==2){
                                nBalance=cBalance+trAmount;
                                $('#forPay').show(); 
                            }
                            else{
                                nBalance='';
                            }
                            $('#nBalance').val(nBalance);
                        }
                        function customerStBtn(){
                            var id = parse_int($('#id').val());
                            if(id>0){
                                $('#customerStBtn').html('<a href="?mdl=personsStatment&person_id='+id+'" target="_blank" class="btn btn-info">Statment</a>');
                            }
                            else{
                                $('#customerStBtn').html('');
                            }
                        }
                        $('#id').on('change', function(){
                            personCurrentBalance();
                            customerStBtn();
                        }); 
                           
                        function personCurrentBalance(){
                            var id= parse_int($('#id').val());
                            if(0<id){

                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{personCurrentBalance:1,id:id},
                                    success:function(data){
                                        if(data.status==1){
                                            $('#currentBalance').val(data.balance);
                                            $('#mobile').val(data.mobile);
                                            newBalance();
                                        }
                                        //swMessageFromJs(data.m);
                                    }
                                }); 
                            }
                        }
                        function transactionWithPerson(){
                            buttonLoading('transactionWithPerson');
                            var id         = parse_int($('#id').val());
                            var date      = $('#date').val();
                            var trNote      = $('#trNote').val();
                            var trAmount    = parse_float($('#trAmount').val());
                            var trType      = parse_int($('#trType').val());
                            let bank_id =parse_int($('#bank_id').val()) ;
                            errorSet=0;
                            if(1>id){
                                errorSet=1;   
                            }
                            else if(0==trType){
                                errorSet=1;
                                swMessage('Please select transaction type.');
                            } 
                            else if(0==trAmount){
                                errorSet=1;
                                swMessage('Amount field is required.');
                            } 
                            
                            else if(bank_id<1){
                                errorSet=1;
                                swMessage('Please select Bank.');
                            } 
                            else if(''==trNote){
                                errorSet=1;
                                swMessage('Note field is required.');
                            } 
                            if(errorSet==0){
                                $('#personTransactionBtn').hide();
                                //$('#reportArea').html(loadingImage);
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{transactionWithPerson:1,id:id,date:date,trNote:trNote,trType:trType,trAmount:trAmount,bank_id:bank_id},
                                    success:function(response){
                                        button_loading_destroy('transactionWithPerson','Save')
                                        //$('#reportArea').html('');
                                        if (response && typeof response.status !== "undefined") {
                                            if(response.status==1){
                                                //$('#id').val('');
                                                $('#trNote').val('');
                                                $('#trAmount').val('');
                                                $('#nBalance').val('');
                                                $('#currentBalance').val('');
                                                $('#customerStBtn').html('');
                                                //$('#trType').val('');
                                                
                                                personPaymentList();
                                                select2Call();
                                            }
                                            $('#personTransactionBtn').show();
                                            swMessageFromJs(response.m);
                                        }
                                        else{
                                            swMessage(AJAX_ERROR_MESSAGE || "Unexpected error occurred.");
                                        }
                                    },
                                    error:function(error){
                                        button_loading_destroy('transactionWithPerson','Save')
                                        swMessage(AJAX_ERROR_MESSAGE || "Unexpected error occurred.");
                                    }
                                });
                            }
                            else{
                                button_loading_destroy('transactionWithPerson','Save')
                            }
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

                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <input type="submit" value="Search"class="btn btn-success" name="s" onclick="personPaymentList();">
                </div>
                <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                <div class="col-sm-12 col-lg-12" id="trDetailsArea"></div>
            </div>
        </div>
    </div>
</div>
<script>
    $( document ).ready(function() {
        personPaymentList();
    });
    function personPaymentList(){
        //var cID=$('#cIDL').val();
        var dRange=$('#dRange').val();
        $('#trDetailsArea').html(loadingImage);
        $.post(ajUrl,{personPaymentList:1,dRange:dRange},function(data){
            if(data.status==1){
                $('#trDetailsArea').html(data.html);
            }
            else{
                $('#trDetailsArea').html('');
                swMessageFromJs(data.m);
            }
        });
    }   

</script>