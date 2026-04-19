<?php
    $general->pageHeader($rModule['name'],[$pUrl=>$rModule['name'],1=>'New']);
    $productData=$db->getProductData('',true);
    $types=$smt->get_all_product_type();
?>
<div class="row">
    <div class="col-lg-12">
        <div class="white-box border-box">
            <div class="row">
                <div class="col-lg-12"><?php show_msg();?></div>
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <form method="post" action="">
                        <div class="col-xs-6 col-sm-4 col-md-4">
                            <?php
                            $general->inputBoxText('bill-date','Bill Date',date('d-m-Y'),'','daterangepicker');
                            $general->inputBoxSelect($types,'Type','product_type','id','title');
                            $general->inputBoxSelect([],'Product','product_id','id','title',script:'onchange="purchaseProductChange(this.value)"');
                            ?>
                            <div class="form-group row mb-1">
                                <label for="qty" class="col-md-4 col-form-label">Quantity</label>
                                <div class="col-md-8">
                                    <div class="input-group qty-input-group">
                                        <input class="form-control amount_td" value="" placeholder="Quantity" id="qty" type="text">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="qtyLabel">--</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                                $general->inputBoxText('note','Note');
                            ?>
                            <div class="form-group m-b-0">
                                <div class="pull-right">
                                    <a href="javascript:void()" class="btn btn-lg btn-info waves-effect waves-light" onclick="product_reject_entry()">Add</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    <?php echo 'var productData='.json_encode($productData).';';?>
    $(document).on('change','#product_type',function(){
        type_wise_product_change(this.value)
    });
    function product_reject_entry(){
        let bill_date   = $('#bill-date').val();
        let product_id  = parse_int($('#product_id').val());
        let qty         = parse_float($('#qty').val());
        let note        = $('#note').val();
        let error_set   = 0;
        if(product_id<=0){error_set=1;swMessage('Please select product');}
        else if(qty<=0){error_set=1;swMessage('Please enter quantity');}
        else if(note==''){error_set=1;swMessage('Please enter note');}

        if(error_set==0){
            let postData={
                product_reject_entry:1,
                stock_in_out_type:stock_in_out_type,
                bill_date:bill_date,
                product_id:product_id,
                qty:qty,
                note:note
            }
            $.ajax({  
                type:'post',
                url:ajUrl,
                data:postData,
                success:function(data){
                    if(typeof(data.status)!=="undefined"){
                        if(data.status==1){
                            $('#product_type').val('');
                            $('#product_id').val('');
                            $('#qty').val('');
                            $('#note').val('');
                            select2Call();
                        }
                        swMessageFromJs(data.m);
                    }
                    else{
                        swMessage(AJAX_ERROR_MESSAGE);  
                    }
                },
                error:function(data){
                    swMessage(AJAX_ERROR_MESSAGE);  
                }
            });
        }
    }
</script>