<?php

$departments=$db->selectAll('employee_department','where isActive=1');
$general->arrayIndexChange($departments, 'id');

$general->pageHeader($rModule['title']);
$report_type=[
    [
        'id'=>1,
        'title'=>'Summarized'
    ],
    [
        'id'=>2,
        'title'=>'Details'
    ]
];

?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">   

                    <?php
                    $general->inputBoxTextForReport('dRange','Date',className:'daterangepickerMulti form-control');
                    $general->inputBoxSelectForReport($departments,'Department','department_id','id','title');
                    $general->inputBoxSelectForReport($report_type,'Type','report_type','id','title','','','',false);
                    ?>


                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="salary_payment_report()">Search</button>
                        <script type="text/javascript">
                            $(document).on('change','#base_id',function(){base_wise_doctor(this.value,'All');});
                            function salary_payment_report(){
                                let dRange  = $('#dRange').val();
                                let department_id = $('#department_id').val();
                                let report_type = $('#report_type').val();
                                $('#reportArea').html(loadingImage);
                                const salary_payment_data={salary_payment_report:1,dRange:dRange,department_id:department_id,report_type:report_type};
                                localStorage.setItem('salary_payment_data', JSON.stringify(salary_payment_data));
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:salary_payment_data,
                                    success:function(data){
                                        $('#reportArea').html('');
                                        if(typeof(data.status)!=='undefined'){
                                            if(data.status==1){
                                                $('#reportArea').html(data.html);
                                            }
                                            swMessageFromJs(data.m);
                                        }
                                        else{
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
                                const report_data = JSON.parse(localStorage.getItem('prescription_survey_data'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    $('#department_id').val(report_data.base_id);
                                    select2Call();
                                }
                                salary_payment_report();
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