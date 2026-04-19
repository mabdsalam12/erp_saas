<?php
    $general->pageHeader($rModule['name']);
    $types=$smt->get_all_product_type();
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    <?php
                        show_msg();
                        $general->inputBoxSelectForReport($types,'Type','type','id','title',all_value:-1); 
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="productList()">Search</button>

                    </div>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;">
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){productList();});
    function productList(){
        $('#reportArea').html(loadingImage);
        let type = $("#type").val();
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{productsReport:1,type:type},
            success:function(data){
                $('#reportArea').html('');
                if(typeof(data.status)!=='undefiend'){
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
</script>