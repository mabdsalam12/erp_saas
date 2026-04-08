<?php
    $customers = $db->selectAll('customer','where isActive=1','id,name,code,due_day,base_id');
    $general->arrayIndexChange($customers,'id');
    $base_customers = [];
    if(!empty($customers)){
        foreach($customers as $k=>$p){
            $customers[$k]['name']=$p['code'].' '.$p['name'];
        }

        foreach($customers as $c){
            $base_customers[$c['base_id']][]=$c;
        }
    }
    $general->pageHeader($rModule['title']);
    $base = $db->selectAll('base','where status=1');
?>
<script type="text/javascript">
    <?php  echo 'var base_customers='.json_encode($base_customers).';'; ?>
    function mpo_wise_customer(base_id){
        $('#customer_id').html('<option value="">All</option>');
        if(base_id>0){
            if(typeof(base_customers[base_id])!='undefined'){
                $.each(base_customers[base_id],function(a,b){
                    let sel = '';

                    $('#customer_id').append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
                });

            }
        }
        else{
            $.each(base_customers,function(mpo_id,b){
                $.each(base_customers[mpo_id],function(a,b){
                    let sel = '';

                    $('#customer_id').append('<option '+sel+' value="'+b.id+'">'+b.name+'</option>');
                }); 
            }); 
        }
        select2Call();

    }
    $(document).on('change','#base_id',function(){mpo_wise_customer(this.value)});

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
                        <button class="btn btn-success" onclick="sale_retun_list()">Search</button>
                        <script type="text/javascript">
                            function sale_retun_list(){
                                let invoice_no   = $('#invoice_no').val();
                                let base_id   = parse_int($('#base_id').val());
                                let customer_id   = parse_int($('#customer_id').val());
                                let dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage);
                                const sale_report_data={sale_retun_list:1,invoice_no:invoice_no,base_id:base_id,customer_id:customer_id,dRange:dRange};
                                //localStorage.setItem('saleReport_data', JSON.stringify(sale_report_data));
                                console.log('dd')
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
                            function sale_return_delete(id){
                                if(id>0){
                                    buttonLoading('sale_return_delete_'+id);
                                    $.ajax({
                                        type:'post',
                                        url:ajUrl,
                                        data:{sale_return_delete:1,id:id},
                                        success:function(data){
                                            if(typeof(data.status)!=='undefined'){
                                                if(data.status==1){
                                                        sale_retun_list();
                                                }
                                                swMessageFromJs(data.m);
                                            }
                                            else{
                                                swMessage(AJAX_ERROR_MESSAGE); 
                                            }
                                            button_loading_destroy('sale_return_delete_'+id,'Delete');
                                        },
                                        error:function(){   
                                            button_loading_destroy('sale_return_delete_'+id,'Delete');
                                            swMessage(AJAX_ERROR_MESSAGE); 
                                        }
                                    });
                                }
                                else{
                                    button_loading_destroy('sale_return_delete_'+id,'Delete');
                                    swMessage(AJAX_ERROR_MESSAGE); 
                                }
                            }
                            function salse_return_process_init(id){
                                $('#modal_title').html('Sale return process');
                                $('#details-body-init').html(loadingImage);
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{salse_return_process_init:1,id:id},
                                    success:function(data){
                                        $('#details-body-init').html('');
                                        if(typeof(data.status)!=='undefined'){
                                            if(data.status==1){
                                                $('#details-body-init').html(data.html);
                                            }

                                            swMessageFromJs(data.m);
                                        }
                                        else{
                                            swMessage(AJAX_ERROR_MESSAGE); 
                                        }
                                    },
                                    error:function(){
                                        $('#details-body-init').html('');
                                        swMessage(AJAX_ERROR_MESSAGE); 
                                    }
                                });
                                $('#details-modal-btn-init').click();
                            }
                            function sale_return_process(){

                                buttonLoading('sale_return_process');
                                let error=0;
                                let id = parse_int($('#sale_return_id').val());
                                let process_date = $('#process_date').val();
                                if(process_date==''){
                                    error=1; swMessage('Please select process date');
                                }
                                else if(id<1){error=1; swMessage('Invalid sale return');}
                                else{
                                    let sale_return_process_data = {};
                                    $('#details_products .product_id').each(function(a,b){
                                        let tID=$(this).closest('tr').attr('id');
                                        let prodct_title = $('#'+tID+' .product_title').html();
                                        let product_id = parse_int($('#'+tID+' .product_id').val());
                                        let quantity = parse_int($('#'+tID+' .quantity').html());
                                        let good = parse_int($('#'+tID+' .good').html());
                                        let damage = parse_int($('#'+tID+' .damage').html());
                                        let expiry = parse_int($('#'+tID+' .expiry').html());
                                        console.log(product_id,quantity,good,damage,expiry);
                                        if((good+damage+expiry)!=quantity){
                                            error=1; 
                                            swMessage('Invalid quantity for '+prodct_title);
                                            return false;
                                        }
                                        sale_return_process_data[product_id]={
                                            product_id:product_id,
                                            good:good,
                                            damage:damage,
                                            expiry:expiry,
                                            prodct_title:prodct_title,
                                        }
                                    });
                                    if(error==0){
                                        $.ajax({
                                            type:'post',
                                            url:ajUrl,
                                            data:{sale_return_process:1,id:id,process_date:process_date,sale_return_process_data:sale_return_process_data},
                                            success:function(data){
                                                button_loading_destroy('sale_return_process','Process'); 
                                                if(typeof(data.status)!=='undefined'){
                                                    if(data.status==1){
                                                        $('#details-body-init').html(data.html);
                                                        $('#sale_return_process_modul_close_btn').click();
                                                        sale_retun_list();
                                                    }

                                                    swMessageFromJs(data.m);
                                                }
                                                else{
                                                    swMessage(AJAX_ERROR_MESSAGE); 
                                                }
                                            },
                                            error:function(){
                                                button_loading_destroy('sale_return_process','Process'); 
                                                swMessage(AJAX_ERROR_MESSAGE); 
                                            }
                                        });
                                    }

                                }
                                if(error==1){
                                    button_loading_destroy('sale_return_process','Process'); 
                                }
                            }
                            function salse_return_details_view(id){
                                $('#details-modal-title').html('Sale return detail');
                                $('#details-body').html(loadingImage);
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{salse_return_details_view:1,id:id},
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
                                //const report_data = JSON.parse(localStorage.getItem('saleReport_data'));
                                //                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                //                                    $('#dRange').val(report_data.dRange);
                                //                                    $('#invoice_no').val(report_data.invoice_no);
                                //                                    $('#customer_id').val(report_data.customer_id);
                                //                                    $('#base_id').val(report_data.base_id);
                                //                                    select2Call();
                                //                                }
                                sale_retun_list();
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
<div style="display: none;">
    <button data-toggle="modal" data-target="#details-modal-init" id="details-modal-btn-init"></button>
</div>

<div class="modal fade" id="details-modal-init" tabindex="-1" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Sale return process</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="details-body-init">

            </div>
            <div class="modal-footer">
                <button type="button" onclick="sale_return_process()" class="btn btn-primary sale_return_process">Process</button>
                <button type="button" class="btn btn-secondary" id="sale_return_process_modul_close_btn" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php
    include_once ROOT_DIR.'/common/details_modal.php';
