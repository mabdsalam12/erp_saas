<?php

    $bazar=$db->selectAll('bazar','','id,title,base_id');
    $base = $db->selectAll('base');
    $bazar_wise_bazars=[];
    if(!empty($bazar)){
        foreach($bazar as $d){
            $bazar_wise_bazars[$d['base_id']][]=$d;
        }
    }

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
                        $general->inputBoxSelectForReport($bazar, 'bazar','bazar_id','id','title');
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="bazar_visit_list()">Search</button>
                        <script type="text/javascript">
                            let bazar_wise_bazars=<?=json_encode($bazar_wise_bazars);?>;
                            $(document).on('change','#base_id',function(){base_wise_bazar(this.value);});
                            function bazar_visit_list(){
                                let dRange  = $('#dRange').val();
                                let bazar_id = $('#bazar_id').val();
                                let base_id = $('#base_id').val();
                                $('#reportbazar').html(loadingImage);
                                const bazar_visit_data={bazar_visit_list:1,dRange:dRange,base_id:base_id,bazar_id:bazar_id};
                                localStorage.setItem('bazar_visit_data', JSON.stringify(bazar_visit_data));
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:bazar_visit_data,
                                    success:function(data){
                                        $('#reportbazar').html('');
                                        if(typeof(data.status)!=='undefined'){
                                            if(data.status==1){
                                                $('#reportbazar').html(data.html);
                                            }
                                            swMessageFromJs(data.m);
                                        }
                                        else{
                                            swMessage(AJAX_ERROR_MESSAGE); 
                                        }
                                    },
                                    error:function(){
                                        $('#reportbazar').html('');
                                        swMessage(AJAX_ERROR_MESSAGE); 
                                    }
                                });
                            }
                            
                            $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('bazar_visit_data'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    $('#bazar_id').val(report_data.base_id);
                                    select2Call();
                                }
                                bazar_visit_list();
                            });
                        </script>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportbazar" style="overflow: auto;"></div>
            </div>
        </div>
    </div>
</div>
<?php
include_once ROOT_DIR.'/common/details_modal.php';