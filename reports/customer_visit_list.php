<?php
    $customer_data=$smt->get_base_wise_all_customer();
    $customers=$customer_data['customers'];
    $base = $db->selectAll('base');
    $general->pageHeader($rModule['title']);
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
                   
                    <?php
                        $general->inputBoxSelectForReport($base,'Base','base_id','id','title');
                        $general->inputBoxSelectForReport($customers,'Customer','customer_id','id','name');
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="customer_visit_list()">Search</button>
                        <script type="text/javascript">
                            let base_customers= <?=json_encode($customer_data['base_customers'])?> ;
                            $(document).on('change','#base_id',function(){base_wise_customer(this.value)});
                            $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('customer_visit_list'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    $('#customer_id').val(report_data.customer_id);
                                    $('#base_id').val(report_data.base_id);
                                    select2Call();
                                }
                                customer_visit_list();
                            });
                        </script>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;"></div>
            </div>
        </div>
    </div>
</div>
<?php
include_once ROOT_DIR.'/common/details_modal.php';