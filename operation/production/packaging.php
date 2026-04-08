<?php
    $pageTitle      = $rModule['title'];
    $purDate=date('d-m-Y');
    //$products_data = $db->selectAll('products','where type='.PRODUCT_TYPE_PACKAGING.' and isActive=1','id,type,title');

    $product_array=$db->selectAll('products','where isActive=1 order by title asc','id,title,code,type');
    $manufacturing_products=[];
    $packaging_product=[];
    $finish_products=[];
    if(!empty($product_array)){
        foreach($product_array as $p){
            $p['title']=$p['code'].' '.$p['title'];
            if($p['type']==PRODUCT_TYPE_FINISHED){
                $finish_products[$p['id']]=$p;
            }
            elseif($p['type']==PRODUCT_TYPE_PACKAGING){
                $packaging_product[$p['id']]=$p;
            }
            elseif($p['type']==PRODUCT_TYPE_MANUFACTURING){

                $manufacturing_products[$p['id']]=$p;
            }
        }
    }
    if($db->permission(3)){
        $can_show_price=1;
    }
    else{
        $can_show_price=0;
    }
    $units=$db->selectAll('unit','where isActive=1 order by title asc');
    $general->arrayIndexChange($units,'id');

    $draftID=0;
    $supInvNo='';
    $purType='';
    $discount='';
    $date=date('d-m-Y');
    $page = 'New';
    $page_title = $rModule['title'];
    $batch='';
    $manufacture_product_id='';
    $manufacture_product_quantity='';
    $targetQuantity='';
    $retention_sample='';
    $note='';
    $mpID='';
    $pmo_no='';
    $yield='';
    $edit=0;
    if(isset($_GET['edit'])){ 
        $id = intval($_GET['edit']);
        $production = $db->get_rowData('production_product','id',$id);
        if(!$production){
            $general->redirect(URL.'/?mdl=manufacture-list',63,'Manufacture');
        }
        $edit=1;
        $mpID = $production['product_id'];
        $manufacture_product_id = $production['manufacture_product_id'];
        $manufacture_product_quantity = $production['manufacture_product_quantity'];
        $extraCost = $production['extra_cost']>0? $general->numberFormatString($production['extra_cost']):'';
        $targetQuantity = $production['quantity']?:'';
        $yield = $production['yield']?:'';

        $pmo_no = $production['pmo_no'];
        $retention_sample = $production['retention_sample']?:'';
        $note = $production['note'];
        $production_product = $db->selectAll('production_product_source','where production_id='.$id);
        $date = date('d-m-Y',$production['date']);
        $page_title.=' Edit';
        $page = 'Edit';
    ?>
    <input type="hidden" id="manufacture_id" value="<?=$id?>">
    <?php 
    }
    $general->pageHeader($page_title,[$pUrl=>$rModule['title'],1=>$page]);
    $categoryData=$db->getCategoryData();
    $productData=$db->getProductData('and type='.PRODUCT_TYPE_PACKAGING);
    $manufacture_product_data=$db->getProductData('and type='.PRODUCT_TYPE_MANUFACTURING);
    $use_product_category = $db->get_company_settings('use_product_category');

?>
<script type="text/javascript">  
    <?php echo 'var can_show_price='.$can_show_price.';';?> 
    <?php echo 'var USE_PRODUCT_CATEGORY='.$use_product_category.';';?> 
    <?php echo 'var productData='.json_encode($productData).';';?>
    <?php echo 'var manufacture_product_data='.json_encode($manufacture_product_data).';';?>


    $(document).on('change','#extraCost',function(){
        productionSubTotal();
    });
    $(document).on('change','#subCategory',function(){
        purchaseSubCategoryChange();
    });
    <?php 
    if(!isset($re_packing)){
    ?>
    $(document).on('change','#manufacture_product_id',function(){
        production_manufacture_product_change(this.value);
    });
    <?php 
    }
    ?>  
    $(document).on('change','#pID',function(){
        production_product_change(this.value);
    });
    <?php 
        if($edit){
        ?>
        $(document).ready(function(){
            <?php 
            if(!isset($re_packing)){
                ?>
                production_manufacture_product_change(<?=$manufacture_product_id?>);
                <?php 
            }
            ?>  
            productionSubTotal();
        });
        <?php 
        }
    ?>
</script>
<div class="row">
<div class="col-sm-12">
    <div class="white-box border-box">
        <div><?php show_msg();?></div>
        <div class="row">
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php $general->inputBoxSelect($finish_products,'Target Product','mpID','id','title',$mpID);?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php $general->inputBoxText('date','Date',$date,'','daterangepicker');?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">      
                <?php if(!$edit){ $general->inputBoxText('batch','Batch',$batch);}?>
                <?php $general->inputBoxText('pmo_no','PMO no',$pmo_no);?>
            </div>
        </div>
        <?php 
        if(!isset($re_packing)){
        ?>
        <div class="row">
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php $general->inputBoxSelect($manufacturing_products,'Manufacturing product','manufacture_product_id','id','title',$manufacture_product_id);?>
                <input type="hidden" id="manufacture_unit_price" value="">
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <div class="form-group row mb-1">
                    <label for="manufacture_qty" class="col-md-4 col-form-label">Available stock</label>
                    <div class="col-md-8">
                        <div class="input-group qty-input-group">
                            <input class="form-control amount_td" value="" placeholder="Available stock" id="manufacture_available_qty" type="text" readonly>
                            <div class="input-group-append">
                                <span class="input-group-text manufacture_qty_label">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <div class="form-group row mb-1">
                    <label for="manufacture_qty" class="col-md-4 col-form-label">Spend quantity</label>
                    <div class="col-md-8">
                        <div class="input-group qty-input-group">
                            <input class="form-control amount_td" value="<?=$manufacture_product_quantity?>" placeholder="Quantity" id="spend_qty" type="text">
                            <div class="input-group-append">
                                <span class="input-group-text manufacture_qty_label">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <?php
        }
        ?>
        <hr>
        <div class="row">
            <div class="col-xs-6 col-md-4 col-sm-6">   
                <?php $general->inputBoxSelect($packaging_product,'Item','pID','id','title'); ?>

            </div> 
            <div class="col-xs-6 col-md-4 col-sm-3">
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
                    if($can_show_price==1){
                        $general->inputBoxText('unitPrice','Unit cost','','','amount_td','readonly');
                    }
                ?> 
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php $general->inputBoxText('stock','Stock','','','amount_td','readonly');?> 
                <?php
                    if($can_show_price==1){
                        $general->inputBoxText('extra_cost','Extra cost','','','amount_td',);
                        $general->inputBoxText('total','Total','','','amount_td','readonly');
                    }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <button onclick="productionAddToCart()" class="m-2 btn btn-info waves-effect waves-light pull-right m-t-10">Add to cart</button>
            </div>
        </div>
        <div class="row">
            <div style="display: none;">
                <table>
                    <tbody>
                        <tr id="productionProductsTr">
                            <td class="autoSerial"></td>
                            <td><input type="hidden" class="pID" value=""><span class="pTitle"></span></td>
                            <?php
                                if($use_product_category==1){
                                ?>
                                <td class="category"></td>
                                <td><input type="hidden" class="categoryID" value=""><span class="subCategory"></span></td>
                                <?php
                                }
                            ?>
                            <td class="unTitle"></td>
                            <td class="qty amount_td"></td>
                            <?php
                                if($can_show_price==1){
                                ?>
                                <td class="unitPrice amount_td"></td>
                                <td class="extra_cost amount_td"></td>
                                <td class="total amount_td"></td>
                                <?php
                                }
                            ?>
                            <td><button class="btn btn-danger remove">X</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-12">
                <table class="table table-border">
                    <thead>
                    <tr>
                        <th  style="width: 5%;">#</th>
                        <th>Product</th>

                        <th style="width: 10%;">Unit</th>
                        <th style="width: 10%;" class="amount_td">Qty</th>
                        <?php
                            if($can_show_price==1){
                            ?>
                            <th style="width: 10%;" class="amount_td">Unit cost</th>
                            <th style="width: 10%;" class="amount_td">Extra cost</th>
                            <th style="width: 10%;" class="amount_td">Total cost</th>
                            <?php
                            }
                        ?>
                        <th style="width: 5%;" class="amount_td">X</th>
                    </tr>
                    <thead>
                    <tbody id="productionProducts">
                        <?php
                            if($edit){
                                $sr=1;
                                foreach($production_product as $pp){
                                    $tr_id = uniqid('tr_id');
                                    if(!$productData[$pp['product_id']]){continue;}
                                    $p =  $productData[$pp['product_id']];
                                ?>
                                <tr id="<?=$tr_id?>">
                                    <td class="autoSerial"><?=$sr++?></td>
                                    <td><input type="hidden" class="pID" value="<?=$p['id']?>"><span class="pTitle"><?=$p['t']?></span></td>
                                    <td class="unTitle"><?=$p['u']?></td>
                                    <td class="qty amount_td"><?=$pp['quantity']?></td>
                                    <?php
                                        if($can_show_price==1){
                                        ?>
                                        <td class="unitPrice amount_td"><?=$general->numberFormatString($pp['unit_cost'])?></td>
                                        <td class="extra_cost amount_td"><?=$general->numberFormatString($pp['extra_cost'])?></td>
                                        <td class="total amount_td"><?=$general->numberFormatString($pp['total_cost'])?></td>
                                        <?php
                                        }
                                    ?>
                                    <td class="amount_td"><button onclick="remove_row_by_id('<?=$tr_id?>');productionSubTotal()" class="btn btn-danger remove">X</button></td>
                                </tr>
                                <?php 
                                }
                            }
                        ?>
                    </tbody>
                    <tfoot>
                        <?php
                            $col_minus = 2;

                            if($can_show_price==1){

                            ?>
                            <tr>
                                <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Total cost</b></td>
                                <td class="amount_td"><b id="subTotal">0.00</b></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Extra cost</b></td>
                                <td class="amount_td"><input onkeyup="productionSubTotal()" type="text" class="form-control amount_td" value="" id="extraCost"></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Net cost</b></td>
                                <td class="amount_td"><b id="netPayable">0.00</b></td>
                                <td>&nbsp;</td>
                            </tr>
                            <?php
                            }
                        ?>
                        <tr>
                            <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Target Quantity</b></td>
                            <td class="amount_td"><input type="text" class="form-control" value="<?=$targetQuantity?>" id="targetQuantity"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Yield</b></td>
                            <td class="amount_td"><input type="text" class="form-control" value="<?=$yield?>" id="yield"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Percentage</b></td>
                            <td class="amount_td"><b id="percentage">0.00</b></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Retention sample</b></td>
                            <td class="amount_td"><input type="text" class="form-control" value="<?=$retention_sample?>" id="retention-sample"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Note</b></td>
                            <td class="amount_td"><textarea type="text" class="form-control" value="<?=$note?>" id="note"></textarea></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="<?=10-$col_minus?>" class="amount_td">
                                <?php
                                    if($edit){
                                    ?>
                                    <button  class="m-1 p-1 btn btn-lg btn-success pull-right production_packaging_edit">Production update</button>
                                    <?php 
                                    }
                                    else{
                                    ?>
                                    <button  class="m-1 p-1 btn btn-lg btn-success pull-right production_packaging_add">Production</button>
                                    <?php
                                    }
                                ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
    </div>
