<?php
    $general->pageHeader($rModule['name'],[$pUrl=>$rModule['name'],1=>'New']);
    $draftID=0;
    $supInvNo='';
    $purType='';
    $discount='';
    $purDate=date('d-m-Y');
    $product=[];
    $cID=0;
    $users = $db->selectAll('users','where type='.USER_TYPE_MPO.' and isActive=1','id,name');
    $base = $db->selectAll('base','where status=1 order by code');



    $products = $db->selectAll('products','where type in('.PRODUCT_TYPE_GIFT_ITEM.','.PRODUCT_TYPE_FINISHED.') and isActive=1','id as pID, title,code');
    if(!empty($products)){
        foreach($products as $k=>$p){
            $products[$k]['title']=$p['code'].' '.$p['title'];
        }
    }


    $types=$smt->get_all_product_type([
        PRODUCT_TYPE_GIFT_ITEM,
        PRODUCT_TYPE_FINISHED,
    ]);
    $categoryData=$db->getCategoryData(); 
    $productData=$db->getProductData('and type in('.PRODUCT_TYPE_GIFT_ITEM.','.PRODUCT_TYPE_FINISHED.')',true);
?>
<script type="text/javascript">
    <?php echo 'var USE_PRODUCT_CATEGORY=0;';?>

    <?php echo 'var productData='.json_encode($productData).';';?>

    <?php echo 'var categoryData='.json_encode($categoryData).';';?>

    $(document).on('change keyup','#product-gift-qty',function(){
        gift_row_total();
    });
    $(document).on('change','#product_type',function(){
        $('#gift-distribute-product').html('');
        git_distribute_sub_total();
        type_wise_product_change(this.value)
        });
</script>
<div class="col-sm-12">
    <div class="white-box border-box">
        <div><?php show_msg();?></div>

        <div class="row">
            <div class="col-xs-6 col-md-6 col-sm-6">
            <?php $general->inputBoxSelect($base,'Base','base_id','id','title');?>
            <?php $general->inputBoxSelect($types,'Type','product_type','id','title');?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-4">
                <?php
                 $general->inputBoxText('gift-distribute-date','Date',$purDate,'','daterangepicker');
                 $general->inputBoxSelect($users,'User','user-id','id','name',$cID);
                 ?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-4">

            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php $general->inputBoxSelect($products,'Product','product_id','pID','title',script:'onchange="purchaseProductChange(this.value,true)"');?>
                <div class="form-group row">
                    <label for="qty" class="col-md-4 col-form-label">Quantity</label>
                    <div class="col-md-8">
                        <div class="input-group qty-input-group">
                            <input class="form-control amount_td" value="" placeholder="Quantity" id="product-gift-qty" type="text">
                            <div class="input-group-append">
                                <span class="input-group-text" id="qtyLabel">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-4">
                <?php $general->inputBoxText('salePrice','TP','','','amount_td','readonly');?>
                <?php $general->inputBoxText('total','Total','','','amount_td','readonly');?>
            </div>
        </div>
        <div class="row">
            <div class="clearfix visible-xs"></div>

            <div class="col-sm-12">
                <button onclick="gift_distribute_add_to_cart()" class="p-2 m-2 btn btn-info waves-effect waves-light pull-right m-t-10">Add to cart</button>
            </div>
        </div>
        <div class="row">
            <div style="display: none;">
                <table>
                    <tbody>
                        <tr id="goft-distribute-products-tr">
                            <td class="autoSerial"></td>
                            <td>
                                <input type="hidden" class="product_id" value="">
                                <span class="product-title"></span>
                            </td>

                            <td class="unit-title"></td>
                            <td class="qty amount_td"></td>
                            <td class="tp amount_td"></td>
                            <td class="total amount_td"></td>
                            <td class="amount_td"><button class="btn btn-danger remove">X</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-12">
                <table class="table table-border">
                    <thead>
                    <tr>
                        <th  style="width: 2%;">#</th>
                        <th>Product</th>
                        <th>Unit</th>
                        <th class="amount_td">Qty</th>
                        <th class="amount_td">TP</th>
                        <th class="amount_td">Total</th>
                        <th style="width: 3%;" class="amount_td">X</th>
                    </tr>
                    <thead>
                    <tbody id="gift-distribute-product">

                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"  class="amount_td"> </td>
                            <td class="amount_td"><b id="total_qty"></b></td>
                            <td></td>
                            <td class="amount_td"><b id="total_amount"></b></td>
                            <td  class="amount_td">

                                <button  onclick="gift_distribute()" class="mt-1 btn-lg btn-success pull-right gift_distribute">Distribute</button>

                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>
<script>

    function gift_distribute_add_to_cart(){
        let pID         = parse_float($('#product_id').val());
        let type = parse_int($('#product_type').val());
        errorSet=0;
        let qty         = new Big(parse_int($('#product-gift-qty').val()));
        let product_tp  = new Big(parse_float($('#salePrice').val()));
        if(pID<=0){swMessage('Please select a product');errorSet=1;}
        else if(qty.lt(0)){swMessage('Please enter a valid Quantity');errorSet=1;}
        if(errorSet==0){
            let p=productData[type][pID];
            // let p=productData[pID];
            let total=(product_tp.mul(qty));
            console.log({total,qty,product_tp});
            if(p.st<qty){swMessage('Product stock out!');errorSet=1;}
            else{
                let id='pd_'+pID+'_'+autoInc;autoInc++;
                $('#goft-distribute-products-tr .product_id').val(pID);

                $('#goft-distribute-products-tr .product-title').html(p.t);
                $('#goft-distribute-products-tr .unit-title').html(p.u);
                $('#goft-distribute-products-tr .qty').html(qty.toFixed(0));
                $('#goft-distribute-products-tr .tp').html(format_big_number(product_tp));
                $('#goft-distribute-products-tr .total').html(format_big_number(total));
                $('#goft-distribute-products-tr .remove').attr('onclick','remove_row_by_id(\''+id+'\');git_distribute_sub_total()')
                let goft_distribute_products_tr=$('#goft-distribute-products-tr').html();
                
                $('#gift-distribute-product').append('<tr id="'+id+'">'+goft_distribute_products_tr+'</tr>');
                $('#product_id').val('');
                $('#product-gift-qty').val('');
                select2Call();
                let tr_sl_start=1;$('#gift-distribute-product .autoSerial').each(function(){$(this).html(tr_sl_start);tr_sl_start++;});
                git_distribute_sub_total();
            }
        }
    }
    function git_distribute_sub_total(){
        let total_qty=0;
        let total_amount=0;
        $('#gift-distribute-product .product_id').each(function(a,b){
            let tID     = $(this).closest('tr').attr('id');
            let pID     = $('#'+tID+' .product_id').val();
            let total   = parse_float($('#'+tID+' .total').html());
            let qty     = parse_int($('#'+tID+' .qty').html());
            total_amount+=total;
            total_qty   +=qty;
            // console.log('total',total)
        });
        // console.log('total_amount',total_amount)
        $('#total_qty').html(total_qty);
        $('#total_amount').html(total_amount);
    }






    function gift_distribute(){
        buttonLoading('gift_distribute');
        let user_id = parse_int($('#user-id').val());
        let base_id = parse_int($('#base_id').val());
        let product_type = parse_int($('#product_type').val());
        let date = $('#gift-distribute-date').val();

        let products= {};
        let count=0;
        errorSet=0;
        if(user_id<1){errorSet=1;swMessage('Select a user')}
        if(base_id<1){errorSet=1;swMessage('Select a base')}
        if(errorSet==0){
            $('#gift-distribute-product .product_id').each(function(a,b){
                if(errorSet==0){
                    let tID=$(this).closest('tr').attr('id');
                    let product_id         = parse_int($('#'+tID+' .product_id').val());
                    let qty         = parse_float($('#'+tID+' .qty').html());
                    products[count]={
                        qty:qty,
                        product_id:product_id,
                    }
                    count++;
                }
            });
            if(count==0){errorSet=1;swMessage('Select a product');}
            else{

                let post_data={
                    gift_distribute:1,
                    base_id:base_id,
                    user_id:user_id,
                    date:date,
                    products:products,
                    product_type:product_type
                };
                console.log('post_data',post_data);
                $.ajax({
                    type:'post',
                    url:ajUrl,
                    data:post_data,
                    success:function(data){
                        if(typeof(data.status)  !== "undefined"){ 
                            if(data.status==1){
                                $('#user-id').val('');
                                $('#gift-distribute-product').html('');
                                select2Call();
                                git_distribute_sub_total();
                            }
                            swMessageFromJs(data.m);
                        }
                        else{
                            swMessage(AJAX_ERROR_MESSAGE); 
                        }
                        button_loading_destroy('gift_distribute','Distribute');  
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) { 
                        button_loading_destroy('gift_distribute','Distribute');   
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                });
            }
        }
        if(errorSet==1){
            button_loading_destroy('gift_distribute','Distribute');   
        }
    }

</script>
