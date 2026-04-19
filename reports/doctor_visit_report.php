<?php

    $customer_data=$smt->get_base_wise_all_customer();
    $customers=$customer_data['customers'];
    $base = $db->selectAll('base');

    $general->pageHeader($rModule['name']);
    $doctors = $db->selectAll('doctor','','id,base_id,name');
$base_doctors=[];
if(!empty($doctors)){
    foreach($doctors as $d){
        $base_doctors[$d['base_id']][]=$d;
    }
}
    
    
?>
<script>
    <?php echo 'const base_doctors='.json_encode($base_doctors).';';?>
</script>
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
                        $general->inputBoxSelectForReport($doctors,'Doctor','doctor_id','id','name');
                        $general->inputBoxSelectForReport([['i'=>0,'t'=>'Summary'],['i'=>1,'t'=>'Details']],'Report Type','type','i','t',needFirstOption:false);
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="doctor_visit_report()">Search</button>
                        <script type="text/javascript">
                            let base_customers= <?=json_encode($customer_data['base_customers'])?> ;
                            $(document).on('change','#base_id',function(){base_wise_doctor(this.value,'All')});
                            $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('doctor_visit_report'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    $('#doctor_id').val(report_data.doctor_id);
                                    $('#base_id').val(report_data.base_id);
                                    $('#type').val(report_data.type);
                                    select2Call();
                                }
                                doctor_visit_report();
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

