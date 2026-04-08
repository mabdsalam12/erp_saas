<?php
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
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="sales_discount_avg_value_sp()">Search</button>
                        <script type="text/javascript">
                            function sales_discount_avg_value_sp(){
                                ajax_report_request({sales_discount_avg_value_sp:1});
                            }
                            $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('sales_discount_avg_value_sp'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                }
                                sales_discount_avg_value_sp();
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