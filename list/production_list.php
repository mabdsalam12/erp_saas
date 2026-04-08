<?php
    $general->pageHeader($rModule['title']);
    //$types=$general->get_all_product_type();
    $products = $db->selectAll('products','where isActive=1 order by title','id,title');
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    <div class="col-md-2">
                        <h5 class="box-title">Date</h5>
                        <input type="text" id="dRange" class="daterangepickerMulti form-control" value="<?php echo date('d-m-Y').' to '.date('d-m-Y');?>">
                    </div>
                        <input type="hidden" id="type" value="<?=$type?>">
                        <?php
                        $general->inputBoxSelectForReport($products,'Target product','product_id','id','title');
                        ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button id="purchase" onclick="production_list()" class="btn btn-success" >Search</button>
                        <script type="text/javascript">
                            $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('production_list_data'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    $('#product_id').val(report_data.product_id);
                                    select2Call();
                                }
                                production_list();
                            })
                            function production_list(){
                                let type   = parse_int($('#type').val());
                                let product_id   = parse_int($('#product_id').val());
                                let dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage); 
                                let request={production_list:1,dRange:dRange,type:type,product_id:product_id};
                                localStorage.setItem('production_list_data',JSON.stringify(request));
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:request,
                                    success:function(data){
                                        $('#reportArea').html(''); 
                                        if(typeof(data.status)  !== "undefined"){ 
                                            if(data.status==1){
                                                $('#reportArea').html(data.html);
                                            }
                                            swMessageFromJs(data.m);
                                        }   
                                        else{
                                            swMessage(AJAX_ERROR_MESSAGE);    
                                        }
                                    },
                                    error:function(data){
                                        $('#reportArea').html(''); 
                                        swMessage(AJAX_ERROR_MESSAGE);  
                                    }
                                });
                            }
                            function production_details_view(id){
                                $('#details-body').html(loadingImage);
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{production_details_view:1,id:id},
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
                            function production_delete(id){
                                if(confirm('Are you sure to remove this production?')){
                                    $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{production_delete:1,id:id},
                                    success:function(data){
                                        if(typeof(data.status)!=='undefined'){
                                            if(data.status==1){
                                                production_list();
                                                $('.close').click();
                                            }
                                            swMessageFromJs(data.m);
                                        }
                                        else{
                                            swMessage(AJAX_ERROR_MESSAGE); 
                                        }
                                    },
                                    error:function(){
                                        swMessage(AJAX_ERROR_MESSAGE); 
                                    }
                                });
                                
                                }
                            }
                        </script>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea">
                </div>
            </div>
        </div>
    </div>
</div>
<div style="display: none;">
    <button data-toggle="modal" data-target="#details-modal" id="details-modal-btn"></button>
</div>

<div class="modal fade" id="details-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Production Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="details-body">

            </div>
        </div>
    </div>
</div>