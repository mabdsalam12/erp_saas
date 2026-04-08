<?php
$source_ledgers=$acc->get_all_cash_accounts($jArray);
$base = $db->selectAll('base');
$base=[0=>['id'=>0,'title'=>'General'],...$base];
if(isset($_GET['edit'])){
    $edit = intval($_GET['edit']);
    $c = $db->get_rowData('contra_voucher','id',$edit);
    if(empty($i)){$general->redirect($pUrl,[37,$pageTitle]);}
    


    $data = ['javascript:void()'=>$rModule['title'],'1'=>'Edit'];
    $general->pageHeader('Edit '.$rModule['title'],$data);
?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                            <input type="hidden" id="id" name="id" value="<?=$edit?>">
                                <?php 
                                $general->inputBoxText('date','Date',$general->make_date($c['time']),'','daterangepicker','autocomplete="off"');
                                $general->inputBoxSelect($base,'Base','base_id','id','title',$c['base_id'],haveSelect:'n');
                                $general->inputBoxSelect($source_ledgers,'Debit ledger','debit','id','title',$c['debit']);
                                $general->inputBoxSelect($source_ledgers,'Credit Ledger','credit','id','title',$c['credit']);
                                $general->inputBoxText('amount','Amount',$general->numberFormatString($c['amount']));
                                $general->inputBoxText('reference','Reference',$c['reference']); 
                                $general->inputBoxText('transaction_charge','Transaction charge',$general->numberFormatString($c['transaction_charge'])); ?>

                                <div class="form-group row">
                                    <label for="note" class="col-md-4 col-form-label">Note</label>
                                    <div class="col-md-8">
                                        <textarea placeholder="Note" cols="" class="form-control" rows="" id="note"><?=$c['note']?></textarea>
                                        <div class="col-xs-6 col-sm-4 col-md-4"></div>
                                        <div class="col-xs-6 col-sm-4 col-md-4">
                                            <div class="form-group ">
                                                <div class="pull-right m-t-5">
                                                    <button  onclick="contra_voucher.edit();" class="btn btn-info waves-effect waves-light edit_button">Update</button>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
elseif(isset($_GET['add'])){
    $data = [$pUrl=>$rModule['title'],1=>'Add'];
    $general->pageHeader('Add '.$rModule['title'],$data);
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <?php 
                                    $general->inputBoxText('date','Date','','','daterangepicker','autocomplete="off"');
                                    $general->inputBoxSelect($base,'Base','base_id','id','title',haveSelect:'n');
                                    $general->inputBoxSelect($source_ledgers,'Debit ledger','debit','id','title');
                                    $general->inputBoxSelect($source_ledgers,'Credit Ledger','credit','id','title');
                                    $general->inputBoxText('amount','Amount'); 
                                    $general->inputBoxText('reference','Reference'); 
                                    $general->inputBoxText('transaction_charge','Transaction charge'); 
                                ?>
                                <div class="form-group row">
                                    <label for="note" class="col-md-4 col-form-label">Note</label>
                                    <div class="col-md-8">
                                        <textarea placeholder="Note" cols="" class="form-control" rows="" id="note"></textarea>
                                        <div class="col-xs-6 col-sm-4 col-md-4"></div>
                                        <div class="col-xs-6 col-sm-4 col-md-4">
                                            <div class="form-group ">
                                                <div class="pull-right m-t-5">
                                                    <button  onclick="contra_voucher.add();" class="btn btn-info waves-effect waves-light add_button">Save</button>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
    
}
else{
    $data = [$pUrl=>$rModule['title']];
    $general->pageHeader($rModule['title'],$data,$general->addBtnHtml($pUrl));
    
    ?>
    
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-3">
                        <h5 class="box-title">Date </h5>
                        <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Debit</h5>
                        <select id='s_debit' class="form-control select2">
                            <option value="">All ledger</option>
                            <?php
                            foreach($source_ledgers as $cn){
                            ?>
                                <option value="<?php echo $cn['id'];?>"><?php echo $cn['title'];?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Credit</h5>
                        <select id='s_credit' class="form-control select2">
                            <option value="">All ledger</option>
                            <?php
                            foreach($source_ledgers as $cn){
                            ?>
                                <option value="<?php echo $cn['id'];?>"><?php echo $cn['title'];?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <input type="submit" value="Search" class="btn btn-success" name="s" onclick="contra_voucher.list();">
                    </div>
                    <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                    <div class="col-sm-12 col-lg-12" id="trDetailsArea"></div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>
<script>
    
    $(document).ready(function(){
        const report_data = JSON.parse(localStorage.getItem('contra_list'));
        if(report_data){
            if(report_data.hasOwnProperty('dRange')){
                $('#dRange').val(report_data.dRange);
                if(report_data.debit>0){
                    $('#s_debit').val(report_data.debit);
                }
                
                if(report_data.credit>0){
                    $('#s_credit').val(report_data.credit);
                }
                select2Call();
            }
        }
        contra_voucher.list();
    });
    function contra_voucher_add(){
        const date              = $('#date').val();
        const debit             = $('#debit').val();
        const credit            = $('#credit').val();
        const amount            = $('#amount').val();
        const reference         = $('#reference').val();
        const transaction_charge= $('#transaction_charge').val();
        const note              = $('#note').val();

        const post_data = {
            contra_voucher_add  : 1,
            date                : date,
            debit               : debit,
            credit              : credit,
            amount              : amount,
            reference           : reference,
            transaction_charge  : transaction_charge,
            note: note
        };

        $.ajax({
            type:'post',
            url:ajUrl,
            data:post_data,
            success:function(data){
                if(typeof(data.status)  !== "undefined"){ 
                    if(data.status==1){
                        contra_list();
                        $('#debit').val('');
                        $('#credit').val('');
                        $('#amount').val('');
                        $('#reference').val('');
                        $('#transaction_charge').val('');
                        $('#note').val('');
                        select2Call();
                    }
                    swMessageFromJs(data.m);
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