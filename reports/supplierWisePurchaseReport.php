<?php
    $suppliers=$db->selectAll('suppliers','where isActive=1 order by supName asc','supID,supName');
    $users=$db->allUsers(' and isActive=1');
    $general->pageHeader($rModule['title']);
    $categoryData=$db->getCategoryData();
    $productData=$db->getProductData();
    $reportType=[
    ['id'=>0,'title'=>'Summary'],
    ['id'=>1,'title'=>'Product wise'],
    ['id'=>2,'title'=>'Details'],
    
    ];
?>
<script type="text/javascript">
    var NEED_ALL_FOR_FIRST_SELECT=1;
    <?php echo 'var productData='.json_encode($productData).';';?>
    <?php echo 'var categoryData='.json_encode($categoryData).';';?>
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
                    <?php $general->inputBoxSelectForReport($reportType,'Report type','rType','id','title',needFirstOption:false) ?>
                    <?php $general->inputBoxSelectForReport($suppliers,'Supplier','supID','supID','supName') ?>
                    <?php $general->inputBoxSelectForReport($categoryData,'Category','category','id','title');?>
                    <?php $general->inputBoxSelectForReport([],'Sub Category','subCategory','id','title'); ?>
                    <?php $general->inputBoxSelectForReport([],'Product','pID','pID','title',script:'onchange="saleProductChange(this.value)"');?>
                    <?php $general->inputBoxSelectForReport($users,'User','uID','uID','uFullName') ?>


                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <a href="javascript:void()" class="btn btn-success" onclick="supplierWisePurchaseReport()">Search</a>
                        <script type="text/javascript">
                            function supplierWisePurchaseReport(){
                                let dRange       = $('#dRange').val();
                                let rType        = parse_int($('#rType').val());
                                let supID        = parse_int($('#supID').val());
                                let category     = parse_int($('#category').val());
                                let subCategory  = parse_int($('#subCategory').val());
                                let pID          = parse_int($('#pID').val());
                                let uID          = parse_int($('#uID').val());
                                $('#reportArea').html(loadingImage);
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{supplierWisePurchaseReport:1,dRange:dRange,rType:rType,supID:supID,category:category,subCategory:subCategory,pID:pID,uID:uID},
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
                                    error: function(XMLHttpRequest, textStatus, errorThrown) { 
                                        $('#reportArea').html('');
                                        swMessage(AJAX_ERROR_MESSAGE); 
                                    }
                                });
                            }
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