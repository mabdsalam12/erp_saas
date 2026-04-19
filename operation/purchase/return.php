<?php
    $general->pageHeader($rModule['name'],[$pUrl=>$rModule['name'],1=>'New']);

    $date=date('d-m-Y');


    $product_type = $smt->get_all_product_type();
    $productData=$db->getProductData('',true);

    $suppliers=$db->selectAll('suppliers','where isActive=1 order by name asc','id,name,product_type');
    $supplyer_data = [];
    if(!empty($suppliers)){
        foreach($suppliers as $s){
            $supplyer_data[$s['product_type']][] = $s;
        }

    }
?>
<script type="text/javascript">
    <?php echo 'var productData='.json_encode($productData).';';?>
    <?php echo 'var supplyer_data='.json_encode($supplyer_data).';';?>

    $(document).on('change','#prodcut_type',function(){type_wise_supplyer_and_product(this.value)});


    function purchase_product_change(pID){

        let product_type = parse_int($('prodcut_type').val());
        if(typeof(productData[product_type][pID])!=="undefined"){
            let p=productData[product_type][pID];
            $('#product_price').val(p.s);
            $('#VAT').val(0);
            $('#product-tp').val(p.s);
            $('#qtyLabel').html(p.u);
        }
        else{
            $('#product_price').val('');
            $('#qtyLabel').html('');  
        }
        return_row_Total()
    }
    function return_row_Total(){
        let product_price = parse_float($('#product_price').val());
        let qty = parse_int($('#product_qty').val());
        let total =  product_price*qty;
        if(total==0){
            total='';
        }
        $('#total').val(total);

    }
    function purchase_return_product_add_to_cart(){
        let product_id         = parse_float($('#product_id').val());
        let prodcut_type         = parse_float($('#product_type').val());
        error=0;
        let salePrice   = parse_float($('#product_price').val());
        let qty         = parse_int($('#product_qty').val());

        if(prodcut_type<0){swMessage('Please select a product type');error=1;}
        else if(product_id<=0){swMessage('Please select a product');error=1;}
            else if(qty<=0){swMessage('Please enter a valid Quantity');error=1;}
                else if(salePrice<=0){swMessage('Please enter a valid sale price');error=1;}
        if(error==0){
            let p=productData[prodcut_type][product_id];


            let total=(salePrice*qty).toFixed(2);
            let id='pd_'+product_id+'_'+autoInc;autoInc++;
            $('#return-products-tr .pID').val(product_id);

            $('#return-products-tr .prodcut_title').html(p.t);
            $('#return-products-tr .unTitle').html(p.u);
            $('#return-products-tr .salePrice ').html(salePrice.toFixed(2));
            $('#return-products-tr .qty').html(qty);
            $('#return-products-tr .total').html(total);
            $('#return-products-tr .remove').attr('onclick','remove_row_by_id(\''+id+'\');product_return_subtotal()')
            var purchaseProductsTr=$('#return-products-tr').html();
            //        t('purchaseProductsTr')
            //        t(purchaseProductsTr)
            $('#return-product').append('<tr id="'+id+'">'+purchaseProductsTr+'</tr>');
            $('#product_id').val('');
            $('#product_price').val('');
            $('#total').val('');
            $('#product_qty').val('');
            select2Call();
            let tr_sl_start=1;$('#return-product .autoSerial').each(function(){$(this).html(tr_sl_start);tr_sl_start++;});
            product_return_subtotal();

        }
    }
    $(document).on('keyup change','#product_qty,#product_price',function(){return_row_Total();});  
    function return_entry(){
        buttonLoading('return_entry');
        $('#return-print-btn').html('');
        let prodcut_type = parse_int($('#prodcut_type').val());
        let supplyer_id = parse_int($('#supplier_id').val());

        let date = $('#date').val();
        let subTotal=parse_float($('#subTotal').html());
        let discount=parse_float($('#return-discount').val());
        let netPayable=parse_float($('#netPayable').html());
        let note = $('#note').val();



        let products= {};
        let count=0;
        errorSet=0;

        if(prodcut_type<0){swMessage('Please select a product type.');errorSet=1;}
        else if(supplyer_id<1){swMessage('Please select a supplier.');errorSet=1;}
            else if(discount<0){swMessage('Invalid discount.');errorSet=1;}
                else if(subTotal<0){swMessage('Please select product.');errorSet=1;}
                    else if(netPayable<0){swMessage('Invalid discount.');errorSet=1;}


        if(errorSet==0){
            $('#return-product .pID').each(function(a,b){
                if(errorSet==0){
                    let tID=$(this).closest('tr').attr('id');
                    let product_id                 = parse_int($('#'+tID+' .pID').val());
                    let qty                 = parse_float($('#'+tID+' .qty').html());
                    let salePrice           = parse_float($('#'+tID+' .salePrice').html());

                    products[product_id]={
                        product_id:product_id,
                        qty:qty,
                        salePrice:salePrice,
                    }
                    count++;
                }
            });
            if(count==0){errorSet=1;swMessage('select a product');}
            else{

                let postData={
                    purchase_return_entry:1,
                    prodcut_type:prodcut_type,
                    supplyer_id:supplyer_id,
                    date:date,
                    products:products,
                    discount:discount,
                    note:note

                };



                $.ajax({
                    type:'post',
                    url:ajUrl,
                    data:postData,
                    success:function(data){
                        button_loading_destroy('return_entry','RETURN');   
                        if(typeof(data.status)  !== "undefined"){ 
                            if(data.status==1){
                                //$('#return-print-btn').html('<a href="<?=URL?>/?print=return&id='+data.return_id+'" target="_blank" class="mt-1 btn-lg btn-success pull-right">Print</a>');
                                $('#prodcut_type').val('');
                                $('#supplier_id').val('');
                                $('#return-discount').val('');
                                $('#return-product').html('');
                                $('#pay-type').val('');
                                $('#base_id').val('');
                                select2Call();
                                product_return_subtotal();

                            }
                            swMessageFromJs(data.m);
                        }
                        else{
                            swMessage(AJAX_ERROR_MESSAGE); 
                        }
                    },
                    error: function(data) { 
                        button_loading_destroy('return_entry','RETURN');   
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                });
            }
        }
        else{
            button_loading_destroy('return_entry','RETURN');   
        }
    }

</script>
<div class="col-sm-12">
    <div class="white-box border-box">
        <div><?php show_msg();?></div>
        <hr>
        <div class="row">
            <div class="col-xs-4 col-md-4 col-sm-4">
                <?php $general->inputBoxSelect($product_type,'Product type','prodcut_type','id','title');?>

            </div>
            <div class="col-xs-4 col-md-4 col-sm-4">
                <?php $general->inputBoxSelect([],'Supplyer','supplier_id','id','name');?>
            </div>
            <div class="col-xs-4 col-md-4 col-sm-4">
                <?php $general->inputBoxText('date','Date',$date,'','daterangepicker');?>
            </div>

        </div>
        <hr>
        <div class="row">
            <div class="col-xs-6 col-md-3 col-sm-6">
                <?php $general->inputBoxSelect([],'Product','product_id','id','t',script:'onchange="purchase_product_change(this.value)"');?>
            </div> 


            <div class="col-xs-6 col-md-3 col-sm-6">

                <div class="form-group row">
                    <label for="qty" class="col-md-4 col-form-label">Quantity</label>
                    <div class="col-md-8">
                        <div class="input-group qty-input-group">
                            <input class="form-control amount_td" value="" placeholder="Quantity" id="product_qty" type="text">
                            <div class="input-group-append">
                                <span class="input-group-text" id="qtyLabel">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-sm-6">
                <?php $general->inputBoxText('product_price','Return Price','','','amount_td');?>
            </div>
            <div class="col-xs-6 col-md-3 col-sm-6">
                <?php $general->inputBoxText('total','Total','','','amount_td','readonly');?>
            </div>

        </div>
        <div class="row">

            <div class="clearfix visible-xs"></div>

            <div class="col-sm-12">
                <button onclick="purchase_return_product_add_to_cart()" class="p-2 m-2 btn btn-info waves-effect waves-light pull-right m-t-10">Add to cart</button>
            </div>
        </div>
        <div class="row">
            <div style="display: none;">
                <table>
                    <tbody>
                        <tr id="return-products-tr">
                            <td class="autoSerial"></td>
                            <td>
                                <input type="hidden" class="pID" value="">
                                <span class="prodcut_title"></span>
                            </td>

                            <td class="unTitle"></td>
                            <td class="salePrice amount_td"></td>
                            <td class="qty amount_td"></td>
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

                        <th >Unit</th>
                        <th  class="amount_td ">TP</th>
                        <th class="amount_td">Qty</th>
                        <th style="width: 10%;" class="amount_td">Total</th>
                        <th style="width: 3%;" class="amount_td">X</th>
                    </tr>
                    <thead>
                    <tbody id="return-product">

                    </tbody>
                    <tfoot>

                        <tr>
                        <tr>
                            <td colspan="5" class="amount_td"><b>Total</b></td>
                            <td class="amount_td"><b id="subTotal"></b></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="amount_td"><b>Discount</b></td>
                            <td class="amount_td"><input type="text" class="form-control amount_td" value="" id="return-discount"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="amount_td"><b>Net Payable</b></td>
                            <td class="amount_td"><b id="netPayable"></b></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>                                          
                            <td colspan="5" class="amount_td">Note</td>
                            <td class="amount_td"><textarea type="text" class="form-control" value="" id="note"></textarea></td>
                            <td>&nbsp;</td>
                        </tr>


                        <tr>
                            <td colspan="7" class="amount_td">
                                <?php
                                    if($db->permission(4)){
                                    ?>
                                    <button  onclick="return_entry()" class="mt-1 btn-lg btn-success pull-right return_entry">RETURN</button>
                                    <?php
                                    }
                                ?>
                            </td>

                        </tr>
                        <tr>
                            <td colspan="7" id="return-print-btn" class="amount_td">

                            </td>

                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>
<?php 



?>
<script type="">



</script>
