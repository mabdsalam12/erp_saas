<?php
if(!isset($toll_sale_type)){
    $toll_sale_type=PRODUCT_TYPE_FINISHED;
}
    $customer_data=$smt->get_base_wise_all_customer();
    $base_customers=$customer_data['base_customers'];
    $customers=$customer_data['customers'];

    $print_type=[
        ['id'=>0,'title'=>'With TP'],
        ['id'=>1,'title'=>'Without TP']
    ];
    
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
                    <div class="col-md-2">
                        <h5 class="box-title">Invoice No</h5>
                        <input type="text" id="invoice_no" class="form-control" value="">
                    </div> 
                    <?php
                        $general->inputBoxSelectForReport($base,'Base','base_id','id','title');
                        $general->inputBoxSelectForReport($customers,'Customer','customer_id','id','name');
                        $general->inputBoxSelectForReport($print_type,'Print type','print_type','id','title','','','',false);
                    ?>
                <input type="hidden" id="toll_sale_type" value="<?=$toll_sale_type?>">

                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="sale_list()">Search</button>
                        <script type="text/javascript">
                            function sale_list(){
                                let invoice_no   = $('#invoice_no').val();
                                let base_id   = parse_int($('#base_id').val());
                                let customer_id   = parse_int($('#customer_id').val());
                                let print_type   = parse_int($('#print_type').val());
                                let toll_sale_type   = parse_int($('#toll_sale_type').val());
                                let dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage);
                                const sale_report_data={sale_list:1,invoice_no:invoice_no,base_id:base_id,customer_id:customer_id,dRange:dRange,print_type:print_type,toll_sale_type:toll_sale_type};
                                localStorage.setItem('sale_list_data', JSON.stringify(sale_report_data));
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
                            function sale_delete(id){
                                if(id>0){
                                    buttonLoading('sale_delete_'+id);
                                    $.ajax({
                                        type:'post',
                                        url:ajUrl,
                                        data:{sale_delete:1,id:id},
                                        success:function(data){
                                            console.log('swMessageFromJs call')
                                            swMessageFromJs(data.m);
                                            if(typeof(data.status)!=='undefined'){
                                                if(data.status==1){
                                                //    saleReport();
                                                }
                                            }
                                            else{
                                                swMessage(AJAX_ERROR_MESSAGE); 
                                            }
                                            button_loading_destroy('sale_delete_'+id,'Delete');
                                        },
                                        error:function(){   
                                            button_loading_destroy('sale_delete_'+id,'Delete');
                                            swMessage(AJAX_ERROR_MESSAGE); 
                                        }
                                    });
                                }
                                else{
                                    button_loading_destroy('sale_delete_'+id,'Delete');
                                    swMessage(AJAX_ERROR_MESSAGE); 
                                }
                            }
                            function salse_details_view(id){
                                $('#details-body').html(loadingImage);
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{salse_details_view:1,id:id},
                                    success:function(data){
                                        $('#details-body').html('');
                                        if(typeof(data.status)!=='undefined'){
                                            if(data.status==1){
                                                $('#details-body').html(data.html);
                                            }
                                            swMessageFromJs(data.m);
                                        }
                                        else{
                                            swMessage(AJAX_ERROR_MESSAGE); 
                                        }
                                    },
                                    error:function(){
                                        $('#details-body').html('');
                                        swMessage(AJAX_ERROR_MESSAGE); 
                                    }
                                });
                                $('#details-modal-btn').click();
                            }
                            $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('sale_list_data'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    $('#invoice_no').val(report_data.invoice_no);
                                    if(report_data.base_id>0){
                                        $('#base_id').val(report_data.base_id);
                                        base_wise_customer(report_data.base_id)
                                    }
                                    $('#customer_id').val(report_data.customer_id);
                                    $('#print_type').val(report_data.print_type);
                                    select2Call();
                                }
                                sale_list();
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