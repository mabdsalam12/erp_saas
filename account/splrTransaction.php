<?php
$general->pageHeader($rModule['name']);
$suppliers = $db->selectAll('suppliers', ' order by name asc');
$cash_accounts=$acc->get_all_cash_accounts();
?>
<div class="white-box border-box">
    <div id="message_show_box"><?php show_msg(); ?></div>
    <div class="row">
        <div class="col-xs-6 col-sm-4">
        <?php $general->inputBoxText('date', 'Date',date('d-m-Y'), '', 'daterangepicker_e', ''); ?>
            <div class="form-group row">
                <label for="supID" class="col-md-4 col-form-label">Supplyer</label>
                <div class="col-md-8">
                    <select id="supID" class="form-control select2" onchange="getSupplierDue()">
                        <option value="">Select Supplier</option>
                        <?php
                        foreach ($suppliers as $fs) {
                        ?>
                            <option value="<?php echo $fs['id']; ?>"><?php echo $fs['name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="type" class="col-md-4 col-form-label">Transaction Type</label>
                <div class="col-md-8">
                    <select id="type" class="form-control select2">
                        <option value="">Select Type</option>
                        <option value="<?php echo CREDIT; ?>">Pay</option>
                        <option value="<?php echo DEBIT; ?>">Receive</option>
                    </select>
                </div>
            </div>
            <?php $general->inputBoxSelect($cash_accounts,'Bank / Cash','bank_id','id','title'); ?>
            <?php $general->inputBoxText('sPayable', 'Balance', '', '', '', 'disabled'); ?>
            <?php $general->inputBoxText('pay', 'Pay amount', '', '', 'amount_td'); ?>
        </div>
        <div class="col-xs-6 col-sm-4">
            <div class="form-group row">
                <label for="note" class="col-md-4 col-form-label">Note</label>
                <div class="col-md-8">
                    <textarea class="form-control" id="note" placeholder="Write some note about this payment"></textarea>
                </div>
            </div>
            <?php echo INFO_SUPPLIER_BALANCE; ?>
            <div class="form-group row">
                <div class="col-md-12">
                    <button onclick="supplier_transaction_add()" class="btn btn-danger waves-effect waves-light pull-right supplier_transaction_add">Pay</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    
function supplier_transaction_add(){
    buttonLoading('supplier_transaction_add')
    let date    = $('#date').val();
    var type    = parse_int($('#type').val());
    var supID   = parse_int($('#supID').val());
    var pay     = parse_float($('#pay').val());
    var note    = $('#note').val();
    let bank_id =parse_int($('#bank_id').val()) ;
    errorSet=0;
    if(supID<0){
        errorSet=1;
        swMessage('Invalid Supplier');
    }
    else if(pay<1){
        errorSet=1;
        swMessage('Invalid Transaction Amount');
    }
    else if(type<=0){
        errorSet=1;
        swMessage('Invalid Transaction');
    }
    else if(bank_id<1){
        errorSet=1;
        swMessage('Please select Bank.');
    } 
    else if(note==''){
        errorSet=1;
        swMessage('নোট দিতে হবে');
    }
    if(errorSet==0){
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{supNewTransaction:1,supID:supID,pay:pay,note:note,type:type,date:date,bank_id:bank_id},
            success:function(response){
                button_loading_destroy('supplier_transaction_add','Pay')
                if (response && typeof response.status !== "undefined") {
                    if(response.status==1){

                        $('#supID').val('');
                        $('#sPayable').val('');
                        $('#pay').val('');
                        $('#note').val('');

                        select2Call();
                        
                    }
                    swMessageFromJs(response.m);
                }
                else{
                    button_loading_destroy('supplier_transaction_add','Pay');
                }
            },
            error:function(error){
                swMessage(AJAX_ERROR_MESSAGE || "Unexpected error occurred.");
                button_loading_destroy('supplier_transaction_add','Pay');
            }
        });
 
    }
    else{
        button_loading_destroy('supplier_transaction_add','Pay');  
    }
}
</script>