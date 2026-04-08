<?php

$doctors=$db->selectAll('doctor','','id,name,base_id');
$base_doctors=[];
if(!empty($doctors)){
    foreach($doctors as $d){
        $base_doctors[$d['base_id']][]=$d;
    }
}
$base = $db->selectAll('base');

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
                    $general->inputBoxSelectForReport($base,'Base','base_id','id','title');
                    $general->inputBoxSelectForReport($doctors, 'Doctor','doctor_id','id','name');
                    $general->inputBoxSelectForReport($report_type,'Type','report_type','id','title','','','',false);
                    ?>


                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="prescription_survey_report()">Search</button>
                        <script type="text/javascript">
                            let base_doctors=<?=json_encode($base_doctors);?>;
                            $(document).on('change','#base_id',function(){base_wise_doctor(this.value,'All');});
                            function prescription_survey_report(){
                                let dRange  = $('#dRange').val();
                                let base_id = $('#base_id').val();
                                let doctor_id = $('#doctor_id').val();
                                let report_type = $('#report_type').val();
                                $('#reportArea').html(loadingImage);
                                const prescription_survey_data={prescription_survey_report:1,dRange:dRange,base_id:base_id,doctor_id:doctor_id,report_type:report_type};
                                localStorage.setItem('prescription_survey_data', JSON.stringify(prescription_survey_data));
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:prescription_survey_data,
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
                                    $('#doctor_id').val(report_data.base_id);
                                    select2Call();
                                }
                                prescription_survey_report();
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