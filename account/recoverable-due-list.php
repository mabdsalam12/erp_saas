<?php



    $general->pageHeader($rModule['name']);
    $employees = $db->selectAll('employees','where isActive=1','id,name');
    $base = $db->selectAll('base','where status=1');
    $customer_data=$smt->get_base_wise_all_customer();
    $base_customers=$customer_data['base_customers'];
    $customers=$customer_data['customers'];
?>
<script>
    <?php  echo 'const base_customers='.json_encode($base_customers).';'; ?>
    $(document).on('change','#base_id',function(){base_wise_customer(this.value)});
    
</script>
<div class="row">
    <div class="col-sm-12" id="message_show_box"></div>
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <?php 
                $general->inputBoxTextForReport('dRange','Date',className:'daterangepickerMulti form-control');
                
                $general->inputBoxSelectForReport($base,'Base','base_id','id','title');
                $general->inputBoxSelectForReport($customers,'Customer','customer_id','id','name');
                $general->inputBoxSelectForReport($employees,'Employee','employee_id','id','name');
                $zero_type = [
                    ['i'=>0, 't'=>'With zero'],
                    ['i'=>1, 't'=>'Without zero']
                ];
                $general->inputBoxSelectForReport($zero_type,'Zero type','zero_type','i','t',needFirstOption:false);
                $report_type = [
                    ['i'=>0, 't'=>'Summary'],
                    ['i'=>1, 't'=>'Details'],
                    ['i'=>2, 't'=>'Employee Due'],
                ];
                $general->inputBoxSelectForReport($report_type,'Report type','report_type','i','t',needFirstOption:false);
                ?>
              
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <input type="submit" value="Search"class="btn btn-success" name="s" onclick="recoverable_due_list();">
                </div>
                <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                <div class="col-sm-12 col-lg-12" id="reportArea"></div>
            </div>
        </div>
    </div>
</div>

