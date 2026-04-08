<?php

    $general->pageHeader($rModule['title']);
    $products=$db->getProductData('and isActive in(0,1)');
    
    $general->arraySortByColumn($products,'t');
    $types=$smt->get_all_product_type();
    $types[]=[
        'id'=>-1,
        'title'=>'All'
    ];
    $general->arraySortByColumn($types,'id');
    //$general->printArray($products);
    //$products = $db->selectAll('products','where isActive=1 order by title','id,title');
    $zero_select=[
        [
            'id'=>1,
            'title'=>'Without zero'
        ],
        [
            'id'=>2,
            'title'=>'With zero'
        ]
    ];
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
                        $general->inputBoxSelectForReport($products,'Product','product_id','id','t');
                        $general->inputBoxSelectForReport($types,'Type','type','id','title','','','',false);
                        $general->inputBoxSelectForReport($zero_select,'Column','zero_column','id','title','','','',false);
                        $general->inputBoxSelectForReport($zero_select,'Row','zero_row','id','title','','','',false);
                    ?>



                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="product_report()">Search</button>
                        <script type="text/javascript">
                            function product_report(){
                                let product_id   = parse_int($('#product_id').val());
                                let type   = parse_int($('#type').val());
                                let zero_column   = parse_int($('#zero_column').val());
                                let zero_row   = parse_int($('#zero_row').val());
                                let dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage);
                                let request={product_report:1,product_id:product_id,zero_row:zero_row,zero_column:zero_column,type:type,dRange:dRange}
                                localStorage.setItem('product_report_data',JSON.stringify(request));
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
                                const report_data = JSON.parse(localStorage.getItem('product_report_data'));
                                if(report_data){
                                    if(report_data.hasOwnProperty('dRange')){
                                        $('#dRange').val(report_data.dRange);
                                        if(report_data.product_id>0){
                                            $('#product_id').val(report_data.product_id);
                                        }
                                        
                                        if(report_data.type!=''){
                                            $('#type').val(report_data.type);
                                        }
                                        select2Call();
                                    }
                                }


                                product_report();
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
