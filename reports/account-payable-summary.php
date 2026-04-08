<?php
    $general->pageHeader($rModule['title']);
    $suppliers = $db->selectAll('suppliers','where isActive in(1,0)','name,id');
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">   
                    <div class="col-md-2">
                        <h5 class="box-title">Date</h5>
                        <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                    </div>   
                    <?php $general->inputBoxSelectForReport($suppliers,'Supplier','supplier_id','id','name'); ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="account_payable_summary()" id="account_payable_summary">Search</button>
                    </div>
                </div>   

                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea">
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        const report_data = JSON.parse(localStorage.getItem('account_payable_summary'));
        if(report_data&&report_data.hasOwnProperty('dRange')){
            $('#dRange').val(report_data.dRange);
            $('#supplier_id').val(report_data.supplier_id);
            select2Call();
        }
        account_payable_summary();
    });
</script>
