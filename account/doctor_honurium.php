<?php

$source_ledgers=$acc->get_all_cash_accounts($jArray);
$base = $db->allBase_for_voucher();
$doctors=$db->selectAll('doctor','order by name asc','id,name,base_id');

if(isset($_GET['edit'])){
    $general->redirect($pUrl,array(37,$pageTitle));
    
}
else{
    $data = [$pUrl=>$rModule['title']];
    $general->pageHeader($rModule['title'],$data);
    $date='';
    if(isset($_GET['date'])){
        $date=$_GET['date'];
    }
    ?>
    <script>
        const doctors=<?= json_encode($doctors);?>;
        $(document).on('change','#base_id',function(){
            let baseID=$(this).val();
            let doctorSelect=$('#doctor_id');
            doctorSelect.html('');
            let filteredDoctors=doctors.filter(doc=>doc.base_id==baseID || baseID==0);
            doctorSelect.append(new Option('Select doctor',''));
            filteredDoctors.forEach(doc=>{
                doctorSelect.append(new Option(doc.name,doc.id));
            });
        });
        $(document).on('change','#search_base_id',function(){
            let baseID=$(this).val();
            let doctorSelect=$('#search_doctor_id');
            doctorSelect.html('');
            let filteredDoctors=doctors.filter(doc=>doc.base_id==baseID || baseID==0);
            doctorSelect.append(new Option('All doctor',''));
            filteredDoctors.forEach(doc=>{
                doctorSelect.append(new Option(doc.name,doc.id));
            });
        });
        
        function doctor_honurium_add(type){
            buttonLoading('doctor_honurium_add');
            let date = $('#trDate').val();
            let doctor_id = parse_int($('#doctor_id').val());
            let credit = parse_int($('#credit').val());
            let amount = parse_float($('#amount').val());
            let note = $('#note').val();
            let error = 0;
            if(doctor_id<1){swMessage('Please select doctor.');error=1;}
            else if(credit<1){swMessage('Please select credit account.');error=1;}
            else if(amount<=0){swMessage('Please enter amount.');error=1;}
            if(!error){
                $.ajax({
                    type:'post',
                    url:ajUrl,
                    data:{
                        doctor_honurium_add:1,
                        date:date,
                        doctor_id:doctor_id,
                        credit:credit,
                        amount:amount,
                        note:note,
                        type:type
                    },
                    success:function(data){
                        button_loading_destroy('income_and_expense_add','Add');
                        if(typeof data.status !== 'undefined'){
                            if(data.status==1){
                                $('#base_id').val(0);
                                $('#doctor_id').val('');
                                $('#credit').val('');
                                $('#amount').val('');
                                $('#note').val('');
                                select2Call();
                                // income_expense_list(type);
                            }
                            swMessageFromJs(data.m);
                        }
                        else{
                            swMessage(AJAX_ERROR_MESSAGE);
                        }
                    },
                    error:function(data){
                        button_loading_destroy('income_and_expense_add','Add');
                        swMessage(AJAX_ERROR_MESSAGE);
                    }
                });
            }
            else{
                button_loading_destroy('doctor_honurium_add','Add');
            }
        }
    </script>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <h4><?= $rModule['title'];?> Add</h4>
                        
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <?php 
                                        $general->inputBoxText('trDate','Date',$date,'','daterangepicker','autocomplete="off"');
                                        $general->inputBoxSelect($base,'Base','base_id','id','title','',haveSelect:'select base');
                                        $general->inputBoxSelect([],'Doctor','doctor_id','id','name');
                                        $general->inputBoxSelect($source_ledgers,'Credit Ledger','credit','id','title','','y','','','Select ledger');
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
                                                        <button class="btn btn-info waves-effect waves-light doctor_honurium_add" onclick="doctor_honurium_add()">Save</button>
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
                        $general->inputBoxSelectForReport($base,'Base','search_base_id','id','title','select2 form-control');
                        $general->inputBoxSelectForReport([],'Doctor','search_doctor_id','id','name','select2 form-control');
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <input type="submit" value="Search" class="btn btn-success" name="s" onclick="doctor_honurium_list();">
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
        doctor_honurium_list();
    });
    function doctor_honurium_list(){
        let dateRange = $('#dRange').val();
        let base_id = parse_int($('#search_base_id').val());
        let doctor_id = parse_int($('#search_doctor_id').val());
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{
                doctor_honurium_list:1,
                date_range:dateRange,
                base_id:base_id,
                doctor_id:doctor_id
            },
            success:function(data){
                if(typeof data.status !== 'undefined'){
                    if(data.status==1){
                        $('#reportArea').html(data.html);
                    }
                    else{
                        swMessageFromJs(data.m);
                    }
                }
                else{
                    swMessage(AJAX_ERROR_MESSAGE);
                }
            },
            error:function(data){
                swMessage(AJAX_ERROR_MESSAGE);
            }
        });
    }
</script>
