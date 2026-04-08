<?php
//$pay_types=[['id'=>PAY_TYPE_CREDIT,'title'=>'Credit'],['id'=>PAY_TYPE_CASH,'title'=>'Cash'],['id'=>PAY_TYPE_CASH_ON_DELIVERY,'title'=>'Cash on delivery']];
$pay_types=$db->selectAll('pay_types','where isActive=1');
$manage_order_number_and_date = $db->get_company_settings('manage_order_number_and_date');
?>
<script type="text/javascript">
    <?= 'var cID='.$cID.';'?>
    <?= 'var base_customers='.json_encode($base_customers).';' ?>
    <?= 'var USE_PRODUCT_CATEGORY='.$use_product_category.';'?>
    <?= 'var productData='.json_encode($productData).';'?>
    <?= 'var categoryData='.json_encode($categoryData).';'?>
    <?= 'var customers='.json_encode($customers).';'?>
    function get_customer_due_day(customer_id){  
        $('#due_day').val(parse_int(customers[customer_id].due_day))
    }
    $(document).on('change','#cID',function(){get_customer_due_day(this.value)});

    $(document).on('change','#base_id',function(){base_wise_customer(this.value,'cID')});
    $(document).ready(function(){
        base_wise_customer(<?=$base_id?>,'cID',<?=$cID?>);
    <?php 
        if($cID>0){
        ?>
            sale_customer_change(<?=$cID?>);
        <?php 
        }
    ?>
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
                <?php $general->inputBoxSelect($base,'Base','base_id','id','title',$base_id);?>
                <?php
                    if($db->permission(103)){
                    ?>
                    <div class="form-group row">
                        <label for="not_check_credit_limit" class="col-md-4 col-form-label ">Not check credit limit</label>
                        <div class="col-md-8">
                            <input class="form-check-input" value="0"  id="not_check_credit_limit" type="checkbox" name="not_check_credit_limit" >
                        </div>
                    </div>
                    <?php
                    }
                ?>
            </div>
            <div class="col-xs-4 col-md-4 col-sm-4">
                <div id="customer_input_area"><?php $general->inputBoxSelect([],'Customer','cID','id','name',$cID,script:'onchange="sale_customer_change(this.value)"');?></div>
                <?php $general->inputBoxText('due_day','Due day',$due_day);?>
                <?php
                if($manage_order_number_and_date){
                    $general->inputBoxText('order_no','Order No ',$order_no);
                }
                 ?>
            </div>

            <div class="col-xs-4 col-md-4 col-sm-4">
                <?php $general->inputBoxText('saleDate','Date',$purDate,'','daterangepicker');?>
                <?php $general->inputBoxSelect($pay_types,'Pay type','pay-type','id','name',$pay_type);?>
                <?php 
                if($manage_order_number_and_date){
                    $general->inputBoxText('order_date','Order Date',$order_date,'','daterangepicker');
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
                                <span class="input-group-text" id="quantity_label">--</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $general->inputBoxText('VAT','VAT','','','amount_td','readonly');?>
            </div>
            <div class="col-xs-6 col-md-3 col-sm-6">
                <?php $general->inputBoxText('product_sale_price','SP','','','amount_td');?>
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
                            <?php
                                if($use_product_category==1){
                                ?>
                                <td class="category"></td>
                                <td><input type="hidden" class="categoryID" value=""><span class="subCategory"></span></td>
                                <?php
                                }
                            ?>
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
            <div class="col-md-12" style="overflow:auto">
                <table class="table table-border">
                    <thead>
                    <tr>
                        <th  style="width: 2%;">#</th>
                        <th>Product</th>
                        <?php
                            if($use_product_category==1){
                            ?>
                            <th>Category</th>
                            <th>Sub Category</th>
                            <?php
                            }
                        ?>
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
                            $subTotal = 0;
                            $total_product_discount = 0;
                            if(!empty($product)){
                                foreach($product as $pID=>$pr){
                                    $p=$productData[$pID];
                                    $id='pd_'.$pID.'_'.$serial;
                                    if($use_product_category==1){
                                        $c = $categories[$p['pc']];
                                    }
                                    $subTotal+= (float)($p['s']*$pr['qty']);
                                ?>
                                <tr id="<?php echo $id?>">
                                    <td style="width: 5%;"><?php echo $serial++?></td>
                                    <td>
                                        <input type="hidden" class="pID" value="<?php echo $pID?>">
                                        <span class="pTitle"><?php echo $p['t']?></span>
                                    </td>
                                    <?php
                                        if($use_product_category==1){
                                        ?>
                                        <td><input type="hidden" class="categoryID" value="<?=$p['pc']?>"><span class="subCategory"><?=$categories[$c['parent']]['title']?></span></td>
                                        <td><?=$c['title']?></td>
                                        <?php
                                        }
                                    ?>
                                    <td class="unTitle"><?php echo $p['u']?></td>
                                    <td class="salePrice-tp amount_td"><?=floatval($p['s'])?></td>
                                    <td class="salePrice amount_td"><?=floatval(@$pr['salePrice'])?></td>
                                    <td class="qty amount_td"><?=intval(@$pr['qty'])?></td>
                                    <td class="free_qty amount_td"><?=intval(@ $pr['free_qty'])?></td>
                                    <td class="total_qty amount_td"><?=intval(@$pr['total_qty'])?></td>
                                    <td class="sub_total amount_td"><?=(floatval(@$p['s'])*intval(@$pr['qty']))?></td>
                                    <td class="discount amount_td"><?=floatval(@$pr['discount'])?></td>
                                    <td class="VAT amount_td"><?=floatval(@$pr['VAT'])?></td>
                                    <td class="total amount_td"><?=(floatval(@$p['s'])*intval(@$pr['qty']))-floatval(@$pr['discount'])?></td>
                                    <td class="amount_td"><button class="btn btn-danger remove" onclick="remove_row_by_id('<?php echo $id?>');productSaleSubTotal()">X</button></td>
                                </tr>
                                <?php
                                }
                            }
                            $netPayble = $subTotal;
                            if(!empty($discount)){
                                $netPayble-=$discount;
                            }
                        ?>
                    </tbody>
                    <tfoot>
                        <?php
                            $col_minus = 0;
                            if($use_product_category==0){
                                $col_minus = 2;
                            }


                        ?>
                        <tr>
                        <tr>
                            <td colspan="<?=11-$col_minus?>" class="amount_td"><b>Total</b></td>    
                            <td class="amount_td"><b id="total_product_discount"><?=$total_product_discount?></b></td>
                            <td class="amount_td"><b id="total_VAT">0.00</b></td>
                            <td class="amount_td"><b id="subTotal"><?=$subTotal?></b></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?=13-$col_minus?>" class="amount_td"><b>Discount</b></td>
                            <td class="amount_td"><input type="text" class="form-control amount_td" value="<?=$discount?>" id="saleDiscount"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?=13-$col_minus?>" class="amount_td"><b>Net Payable</b></td>
                            <td class="amount_td"><b id="netPayable"><?=$netPayble?></b></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?=13-$col_minus?>" class="amount_td"><b>Note</b></td>
                            <td class="amount_td"><textarea type="text" class="form-control" value="" id="note"><?=$note?></textarea></td>
                            <td>&nbsp;</td>
                        </tr>


                        <tr>
                            <td colspan="<?=14-$col_minus?>" class="amount_td"><button  onclick="productsSaleDraft()" class="mt-1 btn-lg btn-success pull-left productsDraft">Save a draft</button>
                                <button  onclick="productsSale()" class="mt-1 btn-lg btn-success pull-right productsSale">Sale</button>
                            </td>

                        </tr>
                        <tr>
                            <td colspan="<?=14-$col_minus?>" id="sale-print-btn" class="amount_td">

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
    $(document).on('keyup','#saleDiscount',function(){
        productSaleSubTotal();
    });
    
    
</script>