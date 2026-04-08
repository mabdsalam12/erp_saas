<?php

    $general->pageHeader($rModule['title']);
    $base = $db->selectAll('base');

?>

<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">   
                
                    <?php
                        $general->inputBoxTextForReport('dRange','Date',className:'daterangepickerMulti form-control');
                        $general->inputBoxSelectForReport($base,'Base','base_id','id','title');
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="doctors_wise_contribution()">Search</button>
                        <script type="text/javascript">
                            function doctors_wise_contribution(){
                                let base_id   = parse_int($('#base_id').val());
                                let dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage);
                                let request={doctors_wise_contribution:1,dRange:dRange,base_id:base_id}
                                localStorage.setItem('doctors_wise_contribution',JSON.stringify(request));
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:request,
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
                                const report_data = JSON.parse(localStorage.getItem('pharmacy_wise_doctors_contribution'));
                                if(report_data){
                                    if(report_data.hasOwnProperty('dRange')){
                                        $('#dRange').val(report_data.dRange);
                                        if(report_data.base_id>0){
                                            $('#base_id').val(report_data.base_id);
                                        }
                                        select2Call();
                                    }
                                }
                                doctors_wise_contribution();
                            });
                        </script>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;">
                </div>
            </div>
        </div>
    </div>
</div>
