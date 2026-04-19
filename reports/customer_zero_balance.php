<?php
    $customer_data=$smt->get_base_wise_all_customer();
    $base_customers=$customer_data['base_customers'];
    $customers=$customer_data['customers'];
    $general->pageHeader($rModule['name']);
    $base = $db->selectAll('base','where status=1');
?>
<script type="text/javascript">
    <?php  echo 'var base_customers='.json_encode($base_customers).';'; ?>
    
    $(document).on('change','#base_id',function(){base_wise_customer(this.value)});

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
                        $general->inputBoxSelectForReport($customers,'Customer','customer_id','id','name');
                    ?>


                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="zero_balance_customer_list()">Search</button>
                        <script type="text/javascript">
                            function zero_balance_customer_list(){
                                let invoice_no   = $('#invoice_no').val();
                                let base_id   = parse_int($('#base_id').val());
                                let customer_id   = parse_int($('#customer_id').val());
                                let print_type   = parse_int($('#print_type').val());
                                let dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage);
                                const report_data={zero_balance_customer_list:1,base_id:base_id,customer_id:customer_id,dRange:dRange};
                                localStorage.setItem('zero_balance_customer_list', JSON.stringify(report_data));
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:report_data,
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
                                const report_data = JSON.parse(localStorage.getItem('zero_balance_customer_list'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    if(report_data.base_id>0){
                                        $('#base_id').val(report_data.base_id);
                                        base_wise_customer(report_data.base_id)
                                    }
                                    $('#customer_id').val(report_data.customer_id);
                                    select2Call();
                                }
                                zero_balance_customer_list();
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