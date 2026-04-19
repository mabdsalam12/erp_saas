<?php
    $general->pageHeader($rModule['name']);
    $base = $db->selectAll('base');
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
                        $general->inputBoxSelectForReport([
['id'=>'q','title'=>'Quantity'],
['id'=>'a','title'=>'Amount']
                        ],'Type','type','id','title','','','',false);
                    ?>


                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="item_wise_sale_report()">Search</button>
                        <script type="text/javascript">
                            function item_wise_sale_report(){
                                let base_id   = parse_int($('#base_id').val());
                                let type   = $('#type').val();
                                let dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage);
                                const sale_report_data={item_wise_sale_report:1,base_id:base_id,type:type,dRange:dRange};
                                localStorage.setItem('item_wise_sale_report_data', JSON.stringify(sale_report_data));
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:sale_report_data,
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
                                const report_data = JSON.parse(localStorage.getItem('item_wise_sale_report_data'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    if(report_data.base_id>0){
                                        $('#base_id').val(report_data.base_id);
                                    }
                                    
                                    select2Call();
                                }
                                item_wise_sale_report();
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
<?php
include_once ROOT_DIR.'/common/details_modal.php';