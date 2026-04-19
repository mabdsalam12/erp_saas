<?php
    $general->pageHeader($rModule['name'],[$pUrl=>$rModule['name'],1=>'New']);
    $users = $db->selectAll('users','where type='.USER_TYPE_MPO.' and isActive=1','id,name');
    $base = $db->selectAll('base');
    $draftID=0;
    $supInvNo='';
    $purType='';
    $discount='';
    $date=date('d-m-Y');
    $product=[];
    $cID=0;
    $base_id = 0;
    $due_day='';
    $note='';
    $pay_type='';
    $customers = $db->selectAll('customer','where isActive=1','id,name,code,due_day,base_id');
    $general->arrayIndexChange($customers,'id');
    if(!empty($customers)){
        foreach($customers as $k=>$p){
            $customers[$k]['name']=$p['code'].' '.$p['name'];
        }
    }
    $use_product_category = $db->get_company_settings('use_product_category');


    $categoryData=$db->getCategoryData(); 
    $productData=$db->getProductData('and type in('.PRODUCT_TYPE_FINISHED.') and isActive=1');

    $base_customers = [];
    foreach($customers as $c){
        $base_customers[$c['base_id']][]=$c;
    }
?>
<script type="text/javascript">
    <?php  echo 'var cID='.$cID.';'; ?>
    <?php  echo 'var base_customers='.json_encode($base_customers).';'; ?>
    <?php echo 'var USE_PRODUCT_CATEGORY=0;';?>
    <?php echo 'var productData='.json_encode($productData).';';?>
    <?php echo 'var categoryData='.json_encode($categoryData).';';?>
    <?php echo 'var customers='.json_encode($customers).';';?>
    function get_customer_due_day(customer_id){  
        $('#due_day').val(parse_int(customers[customer_id].due_day))
    }
    $(document).on('change','#cID',function(){get_customer_due_day(this.value)});

    $(document).on('change','#base_id',function(){base_wise_customer(this.value,'cID')});
    $(document).ready(function(){
        base_wise_customer(<?=$base_id?>);    
    });

</script>
<div class="col-sm-12">
    <div class="white-box border-box">
        <div><?php show_msg();?></div>
        <div class="row">
            <div class="col-md-12">

                <input type="hidden" id="draftID" value="<?php echo $draftID;?>">

            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-xs-4 col-md-4 col-sm-4">
                <?php $general->inputBoxSelect($base,'MPO','base_id','id','title',$base_id);?>
                <?php $general->inputBoxText('invoice-date','Invoice date',$date,'','daterangepicker');?>
            </div>
            <div class="col-xs-4 col-md-4 col-sm-4">
                <?php $general->inputBoxSelect([],'Customer','cID','id','name',$cID);?>
                <?php $general->inputBoxText('approved-date','Approved date',$date,'','daterangepicker');?>
            </div>

            <div class="col-xs-4 col-md-4 col-sm-4">

                <?php $general->inputBoxSelect([['id'=>PAY_TYPE_CREDIT,'title'=>'Credit'],['id'=>PAY_TYPE_CASH,'title'=>'Cash']],'Pay type','pay-type','id','title',$pay_type);?>
            </div>
            <div class="col-xs-4 col-md-4 col-sm-4">

            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-xs-6 col-md-3 col-sm-6">
                <?php
                    if($use_product_category==1){
                    ?>
                    <?php $general->inputBoxSelect($categoryData,'Category','category','id','title');?>
                    <?php
                    }
                ?>
                <?php $general->inputBoxSelect($productData,'Product','pID','id','t',script:'onchange="saleProductChange(this.value)"');?>
            </div> 


            <div class="col-xs-6 col-md-3 col-sm-6">

                <div class="form-group row">
                    <label for="qty" class="col-md-4 col-form-label">Quantity</label>
                    <div class="col-md-8">
                        <div class="input-group qty-input-group">
                            <input class="form-control amount_td" value="" placeholder="Quantity" id="product_quantity" type="text">
                            <div class="input-group-append">
                                <span class="input-group-text" id="qtyLabel">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-3 col-sm-6">
                <?php $general->inputBoxText('product_sale_price','Sale Price','','','amount_td');?>
            </div>
            <div class="col-xs-6 col-md-3 col-sm-6">
                <?php $general->inputBoxText('total','Total','','','amount_td','readonly');?>
            </div>

        </div>
        <div class="row">

            <div class="clearfix visible-xs"></div>

            <div class="col-sm-12">
                <button onclick="return_product_add_to_cart()" class="p-2 m-2 btn btn-info waves-effect waves-light pull-right m-t-10">Add to cart</button>
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
                                <span class="pTitle"></span>
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

    function return_entry(){
        buttonLoading('return_entry');
        $('#return-print-btn').html('');
        let pay_type = parse_int($('#pay-type').val());
        let cID = parse_int($('#cID').val());
        let base_id = parse_int($('#base_id').val());
        let invoice_date = $('#invoice-date').val();
        let approved_date = $('#approved-date').val();
        let subTotal=parse_float($('#subTotal').html());
        let discount=parse_float($('#return-discount').val());
        let netPayable=parse_float($('#netPayable').html());
        let note = $('#note').val();



        let products= {};
        let count=0;
        errorSet=0;

        if(cID<=0){swMessage('Please select a customers.');errorSet=1;}
        else if(base_id<1){swMessage('Please select a base.');errorSet=1;}
            else if(pay_type<1){swMessage('Please select a pay type.');errorSet=1;}
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
                    sale_return_entry:1,
                    pay_type:pay_type,
                    cID:cID,
                    base_id:base_id,
                    invoice_date:invoice_date,
                    approved_date:approved_date,
                    products:products,
                    discount:discount,
                    note:note

                };



                $.ajax({
                    type:'post',
                    url:ajUrl,
                    data:postData,
                    success:function(data){
                        button_loading_destroy('return_entry','Return');   
                        if(typeof(data.status)  !== "undefined"){ 
                            if(data.status==1){
                                //$('#return-print-btn').html('<a href="<?=URL?>/?print=return&id='+data.return_id+'" target="_blank" class="mt-1 btn-lg btn-success pull-right">Print</a>');
                                $('#cID').val('');
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
                        button_loading_destroy('return_entry','Return');   
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                });
            }
        }
        if(errorSet==1){
            button_loading_destroy('return_entry','Sale');   
        }
    }

</script>
