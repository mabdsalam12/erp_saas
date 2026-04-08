<?php
$manage_order_number_and_date = $db->get_company_settings('manage_order_number_and_date');
    if(isset($_GET['edit'])){ 
        $edit = intval($_GET['edit']);   
        $sale = $db->get_rowData('sale','id',$edit);
        if(empty($sale)){$general->redirect($pUrl,37,$rModule['title']);}
        $sale_products = $db->selectAll('sale_products','where sale_id='.$edit);

        $data = array($pUrl=>$rModule['title'],'1'=>'Update');
        $general->pageHeader($rModule['title'],$data);

        $users = $db->selectAll('users','where type='.USER_TYPE_MPO.' and isActive=1','id,name');
        $base = $db->selectAll('base');
        $general->arraySortByColumn($base,'title');
        $draftID=0;
        $supInvNo='';
        $purType='';
        $discount='';
        $purDate=date('d-m-Y');
        $product=[];
        $cID=0;
        $base_id = 0;
        $due_day='';
        $note='';
        $pay_type='';

        $customer_data=$smt->get_base_wise_all_customer();
        $base_customers=$customer_data['base_customers'];
        $customers=$customer_data['customers'];

        $use_product_category = $db->get_company_settings('use_product_category');

        $cID = $sale['customer_id'];
        $base_id = $sale['base_id'];
        $due_day = $general->get_time_difference($sale['date'],$sale['collection_date'],'d');
        $purDate = $general->make_date($sale['date']);
        $product = $sale_products ;
        $discount = $sale['discount'] ?? '';
        $pay_type = $sale['pay_type'] ?? '';
        $note = $sale['note'] ?? '';
        $categories=$db->selectAll('product_category','where isActive=1');
        $general->arrayIndexChange($categories,'id');
        $order_date = '';
        if($sale['order_date']>0){
            $order_date = $general->make_date($sale['order_date']);
        }
        $order_no = $sale['order_no']??'';


        $categoryData=$db->getCategoryData(); 
        if($sale['product_type']==PRODUCT_TYPE_MANUFACTURING){
            $productData=$db->getProductData('and type in('.PRODUCT_TYPE_MANUFACTURING.','.PRODUCT_TYPE_PACKAGING.') and isActive=1');
        }
        else{
            $productData=$db->getProductData('and type in('.PRODUCT_TYPE_FINISHED.','.PRODUCT_TYPE_GIFT_ITEM.') and isActive=1');
        }
        
        if(!empty($sale_products)){
            foreach($sale_products as $sp){
                $productData[$sp['product_id']]['st']+=$sp['total_qty'];
            }
        }

    ?>
    <script type="text/javascript">
        <?php  echo 'var cID='.$cID.';'; ?>
        <?php  echo 'var base_customers='.json_encode($base_customers).';'; ?>
        <?php echo 'var USE_PRODUCT_CATEGORY='.$use_product_category.';';?>
        <?php echo 'var productData='.json_encode($productData).';';?>
        <?php echo 'var categoryData='.json_encode($categoryData).';';?>
        <?php echo 'var customers='.json_encode($customers).';';?>
        function get_customer_due_day(customer_id){  
            $('#due_day').val(parse_int(customers[customer_id].due_day))
        }
        $(document).on('change','#cID',function(){get_customer_due_day(this.value)});

        $(document).on('change','#base_id',function(){base_wise_customer(this.value,'cID')});
        $(document).ready(function(){
            base_wise_customer(<?=$base_id?>,'cID',<?=$cID?>); 
        });



    </script>
    <div class="col-sm-12">
        <div class="white-box border-box">
            <div><?php show_msg();?></div>
            <div class="row">
                <div class="col-md-12">

                    <input type="hidden" id="sale_id" value="<?=$edit?>">

                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-xs-4 col-md-4 col-sm-4">
                    <?php $general->inputBoxSelect($base,'MPO','base_id','id','title',$base_id);?>
                    <?php
                        if($db->permission(103)){
                        ?>
                        <div class="form-group row">
                            <label for="not_check_cedit_limit" class="col-md-4 col-form-label ">Not check cedit limit</label>
                            <div class="col-md-8">
                                <input class="form-check-input" value="0"  id="not_check_cedit_limit" type="checkbox" name="not_check_cedit_limit" >
                            </div>
                        </div>
                        <?php
                        }
                    ?>
                </div>
                <div class="col-xs-4 col-md-4 col-sm-4">
                    <?php $general->inputBoxSelect([],'Customer','cID','id','name',$cID);?>
                    <?php $general->inputBoxText('due_day','Due day',$due_day);?>
                    <?php
                if($manage_order_number_and_date){
                    $general->inputBoxText('order_no','Order No ',$order_no);
                }
                 ?>
                </div>

                <div class="col-xs-4 col-md-4 col-sm-4">
                    <?php $general->inputBoxText('saleDate','Date',$purDate,'','daterangepicker');?>
                    <?php $general->inputBoxSelect([['id'=>PAY_TYPE_CREDIT,'title'=>'Credit'],['id'=>PAY_TYPE_CASH,'title'=>'Cash']],'Pay type','pay-type','id','title',$pay_type);?>
                    <?php 
                if($manage_order_number_and_date){
                    $general->inputBoxText('order_date','Date',$order_date,'','daterangepicker');
                }
                ?>
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
                    <?php $general->inputBoxText('freeQTY','Free QTY','','','amount_td');?>
                </div> 
                <div class="col-xs-6 col-md-3 col-sm-6">
                    <?php $general->inputBoxText('product-tp','TP','','','amount_td','readonly');?>
                    <?php $general->inputBoxText('product-discount','Discount','','','amount_td','readonly');?>

                    <?php
                        if($use_product_category==1){
                        ?>
                        <?php $general->inputBoxSelect([],'Sub Category','subCategory','id','title'); ?>
                        <?php
                        }
                    ?>

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
                    <?php $general->inputBoxText('VAT','VAT','','','amount_td','readonly');?>
                </div>
                <div class="col-xs-6 col-md-3 col-sm-6">
                    <?php $general->inputBoxText('product_sale_price','TP','','','amount_td');?>
                    <?php $general->inputBoxText('total','Total','','','amount_td','readonly');?>
                </div>

            </div>
            <div class="row">

                <div class="clearfix visible-xs"></div>

                <div class="col-sm-12">
                    <button onclick="productSaleAddToCart()" class="p-2 m-2 btn btn-info waves-effect waves-light pull-right m-t-10">Add to cart</button>
                </div>
            </div>
            <div class="row">
                <div style="display: none;">
                    <table>
                        <tbody>
                            <tr id="saleProductsTr">
                                <td class="autoSerial"></td>
                                <td>
                                    <input type="hidden" class="pID" value="">
                                    <span class="pTitle"></span>
                                </td>
                                <td class="unTitle"></td>
                                <td class="salePrice-tp amount_td"></td>
                                <td class="salePrice amount_td"></td>
                                <td class="qty amount_td"></td>
                                <td class="free_qty amount_td"></td>
                                <td class="total_qty amount_td"></td>
                                <td class="sub_total amount_td"></td>
                                <td class="discount amount_td"></td>
                                <td class="VAT amount_td"></td>
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
                            <th  class="amount_td">Sale Price</th>
                            <th class="amount_td">Qty</th>
                            <th  class="amount_td">Free Qty</th>
                            <th  class="amount_td">Total Qty</th>
                            <th style="width: 10%;" class="amount_td">Sub total</th>
                            <th style="width: 10%;" class="amount_td">Discount</th>
                            <th style="width: 10%;" class="amount_td">VAT</th>
                            <th style="width: 10%;" class="amount_td">Total</th>
                            <th style="width: 3%;" class="amount_td">X</th>
                        </tr>
                        <thead>
                        <tbody id="saleProducts">
                            <?php
                                $serial=1;
                                $total_product_discount = 0;
                                if(!empty($product)){
                                    foreach($product as $pr){
                                        $pID = $pr['product_id'];
                                        $p=$productData[$pID];
                                        $id='pd_'.$pID.'_'.$serial;

                                    ?>
                                    <tr id="<?php echo $id?>">
                                        <td style="width: 5%;"><?php echo $serial++?></td>
                                        <td>
                                            <input type="hidden" class="pID" value="<?php echo $pID?>">
                                            <span class="pTitle"><?php echo $p['t']?></span>
                                        </td>

                                        <td class="unTitle"><?php echo $p['u']?></td>
                                        <td class="salePrice-tp amount_td"><?=$general->numberFormatString($pr['unit_price'])?></td>
                                        <?php
                                           $pr['unit_price']=$pr['unit_price']-($pr['discount']/$pr['sale_qty'])+$pr['VAT'];  
                                         ?>
                                        <td class="salePrice amount_td"><?=$general->numberFormatString($pr['unit_price'])?></td>
                                        <td class="qty amount_td"><?=intval($pr['sale_qty'])?></td>
                                        <td class="free_qty amount_td"><?=intval($pr['free_qty'])?></td>
                                        <td class="total_qty amount_td"><?=intval($pr['total_qty'])?></td> 
                                        
                                        <td class="sub_total amount_td"><?=$general->numberFormatString($pr['unit_price']*intval($pr['sale_qty']))?></td>
                                        <td class="discount amount_td"><?=$general->numberFormatString($pr['discount'])?></td>
                                        <td class="VAT amount_td"><?=$general->numberFormatString($pr['VAT'])?></td>

                                        <td class="total amount_td"><?=$general->numberFormatString($pr['total'])?></td>
                                        <td class="amount_td"><button class="btn btn-danger remove" onclick="remove_row_by_id('<?php echo $id?>');productSaleSubTotal()">X</button></td>
                                    </tr>
                                    <?php
                                    }
                                }

                            ?>
                        </tbody>
                        <tfoot>

                            <tr>
                            <tr>
                                <td colspan="9" class="amount_td"><b>Total</b></td>
                                <td class="amount_td"><b id="total_product_discount"><?=$general->numberFormatString($sale['discount'])?></b></td>
                                <td class="amount_td"><b id="total_VAT"><?=$general->numberFormatString($sale['VAT'])?></b></td>
                                <td class="amount_td"><b id="subTotal"><?=$general->numberFormatString($sale['sub_total'])?></b></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="11" class="amount_td"><b>Discount</b></td>
                                <td class="amount_td"><input type="text" class="form-control amount_td" value="<?=$general->numberFormatString($sale['discount'])?>" id="product_discount_footer" disabled></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="11" class="amount_td"><b>Extra discount</b></td>
                                <td class="amount_td"><input type="text" class="form-control amount_td" value="<?=$general->numberFormatString($sale['extra_discount'])?>" id="saleDiscount"></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="11" class="amount_td"><b>Net Payable</b></td>
                                <td class="amount_td"><b id="netPayable"><?=$general->numberFormatString($sale['total'])?></b></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="11" class="amount_td"><b>Note</b></td>
                                <td class="amount_td"><textarea type="text" class="form-control" value="" id="note"><?=$sale['note']?></textarea></td>
                                <td>&nbsp;</td>
                            </tr>


                            <tr>
                                <td colspan="12" class="amount_td">
                                    <?php
                                        if($db->permission(17)){
                                        ?>
                                        <button  onclick="products_sale_update()" class="mt-1 btn-lg btn-success pull-right products_sale_update">Update</button>
                                        <?php
                                        }
                                    ?>
                                </td>

                            </tr>
                            <tr>
                                <td colspan="12" id="sale-print-btn" class="amount_td">

                                </td>

                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <script type="">
        $(document).on('keyup','#saleDiscount',function(){
            productSaleSubTotal();
        });
        function products_sale_update(){
            buttonLoading('products_sale_update');
            $('#sale-print-btn').html('');
            let sale_id = parse_int($('#sale_id').val());
            let pay_type = parse_int($('#pay-type').val());
            let cID = parse_int($('#cID').val());
            let due_day = parse_int($('#due_day').val());
            let base_id = parse_int($('#base_id').val());
            let saleDate = $('#saleDate').val();
            let subTotal=parse_float($('#subTotal').html());
            let discount=parse_float($('#saleDiscount').val());
            let netPayable=parse_float($('#netPayable').html());
            let note = $('#note').val();
            let not_check_cedit_limit = 0;
            let order_no = $('#order_no').val();
            let order_date = $('#order_date').val();
            if($('#not_check_cedit_limit').is(':checked')){
                not_check_cedit_limit=1; 
            }


            let products= {};
            let count=0;
            errorSet=0;

            if(cID<=0){swMessage('Please select a customers.');errorSet=1;}
            else if(base_id<1){swMessage('Please select a base.');errorSet=1;}
                else if(due_day<0){swMessage('Invalid due day.');errorSet=1;}
                    else if(pay_type<1){swMessage('Please select a pay type.');errorSet=1;}
                        else if(subTotal<0){swMessage('Please select product.');errorSet=1;}
                            else if(netPayable<0){
                                console.log('netPayable',netPayable)
                                swMessage('Invalid discount.');errorSet=1;

                            }


            if(errorSet==0){
                $('#saleProducts .pID').each(function(a,b){
                    if(errorSet==0){
                        let tID=$(this).closest('tr').attr('id');
                        let pID                 = parse_int($('#'+tID+' .pID').val());
                        let qty                 = parse_float($('#'+tID+' .qty').html());
                        let free_qty            = parse_float($('#'+tID+' .free_qty').html());
                        let total_qty           = parse_float($('#'+tID+' .total_qty').html());
                        let salePrice_tp        = parse_float($('#'+tID+' .salePrice-tp').html());
                        let salePrice           = parse_float($('#'+tID+' .salePrice').html());
                        let product_discount    = parse_float($('#'+tID+' .discount').html());
                        let sub_total           = parse_float($('#'+tID+' .sub_total').html());
                        let VAT                 = parse_float($('#'+tID+' .VAT').html());
                        //if(product_discount>sub_total){
                          //  console.log('product_discount>sub_total',product_discount,sub_total)
                        //    swMessage('Invalid discount.');errorSet=1;
                        //}
                        if(!products.hasOwnProperty(pID)){
                            products[pID]={
                                qty:qty,
                                free_qty:free_qty,
                                total_qty:total_qty,
                                salePrice_tp:salePrice_tp,
                                salePrice:salePrice,
                                discount:product_discount,
                                VAT:VAT
                            }
                            count++;
                        }
                        else{
                            swMessage('Some product you select multiple time. Please remove one.');errorSet=1;
                        }
                    }
                });
                if(count==0){errorSet=1;swMessage('select a product');}
                if(errorSet==0){
                    let postData={
                        id:sale_id,
                        products_sale_update:1,
                        not_check_cedit_limit:not_check_cedit_limit,
                        pay_type:pay_type,
                        cID:cID,
                        base_id:base_id,
                        due_day:due_day,
                        saleDate:saleDate,
                        products:products,
                        discount:discount,
                        order_no:order_no,
                        order_date:order_date,
                        note:note,

                    };
                    console.log('post_data',postData);

                    $.ajax({
                        type:'post',
                        url:ajUrl,
                        data:postData,
                        success:function(data){
                            if(typeof(data.status)  !== "undefined"){ 
                                if(data.status==1){
                                    $('#sale-print-btn').html('<a href="<?=URL?>/?print=sale&id='+data.sale_id+'" target="_blank" class="mt-1 btn-lg btn-success pull-right">Print</a>');
                                    $('#cID').val('');
                                    $('#saleDiscount').val('');
                                    $('#saleProducts').html('');
                                    $('#pay-type').val('');
                                    $('#due_day').val('');
                                    $('#base_id').html('');
                                    select2Call();
                                    productSaleSubTotal();

                                }
                                swMessageFromJs(data.m);
                            }
                            else{
                                swMessage(AJAX_ERROR_MESSAGE); 
                            }
                            console.log('dkdfd');
                            button_loading_destroy('products_sale_update','Update');
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) { 
                            button_loading_destroy('products_sale_update','Update');   
                            swMessage(AJAX_ERROR_MESSAGE); 
                        }
                    });

                }
            }
            if(errorSet==1){
                button_loading_destroy('products_sale_update','Update');   
            }
        }
    </script>
    <?php 





    }   
    else{
        $general->pageHeader($rModule['title']);
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-sm-12 col-lg-12">
                        <?php
                            show_msg();
                        ?>
                    </div>
                    <div class="col-sm-12 col-lg-12">   
                        <div class="col-md-2">
                            <h5 class="box-title">Date</h5>
                            <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                        </div>               



                        <div class="col-md-2">
                            <h5 class="box-title">Search</h5>
                            <button class="btn btn-success" onclick="saleEditList()">Search</button>

                        </div>
                    </div>

                    <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
?>
<script type="">



    function saleEditList(){
        let dRange= $('#dRange').val();
        $('#reportArea').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{saleEditList:1,dRange:dRange},
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
