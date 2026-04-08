<?php
    $edit = intval($_GET['edit']);   
    $purchase = $db->get_rowData('purchase','id',$edit);
    if(empty($purchase)){$general->redirect($pUrl,37,$rModule['title']);}
    $purchase_details = $db->selectAll('purchase_details','where purchase_id='.$edit);
    $purchase['discount'] = ($purchase['discount']==0)?'':$general->numberFormatString($purchase['discount']);
    $purchase['VAT'] = ($purchase['VAT']==0)?'':$general->numberFormatString($purchase['VAT']);
    $purchase['AIT'] = ($purchase['AIT']==0)?'':$general->numberFormatString($purchase['AIT']);
    $general->pageHeader($rModule['title'],[$pUrl=>$rModule['title'],1=>'Update']);

    $products=[];

    $zero_price=$purchase['total']==0?1:0;
    $productData=$db->getProductData('',true);
    //$general->printArray(shell_exec('mysql -V'));
    $types=$smt->get_all_product_type();
?>
<script type="text/javascript">   
    <?php echo 'var productData='.json_encode($productData).';';?>
    <?php echo 'var supplyer_data='.json_encode($supplyer_data).';';?>
    var supID=<?=$purchase['supplier_id']?>;
    $(document).on('change','#product_type',function(){type_wise_supplyer_and_product(this.value)});
    $(document).ready(function(){
        purchaseSubTotal();
        type_wise_supplyer_and_product(<?=$purchase['type']?>);
    });

</script>

<div class="row">
<div class="col-sm-12">
    <div class="white-box border-box">
        <div><?php show_msg();?></div>
        <div class="row">
            <div class="col-md-12">
                <input type="hidden" id="purchase_id" value="<?=$edit?>">
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxSelect($types,'Type','product_type','id','title',$purchase['type']);

                ?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxSelect([],'Supplier Name','supplier_id','id','name');
                ?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxText('supplier-no','Bill No',$purchase['supplier_invoice_no']);
                ?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxText('bill-date','Bill Date',$general->make_date($purchase['date']),'','daterangepicker');
                ?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxText('challan-no','Challan No',$purchase['challan_no']);
                ?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxText('challan-date','Challan Date',$general->make_date($purchase['challan_date']),'','daterangepicker');
                ?>
            </div>

            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxText('po-no','PO No',$purchase['po_no']);
                ?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxText('mrr-code','MRR No',$purchase['mrr_code']);
                ?>
            </div>
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxText('mrr-date','MRR Date',$general->make_date($purchase['mrr_date']),'','daterangepicker');
                ?>
            </div>

        </div>
        <hr>
        <div class="row">
            <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                    $general->inputBoxSelect([],'Item','product_id','id','title',script:'onchange="purchaseProductChange(this.value)"');
                ?>

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
                <div class="form-group row mb-1">
                        <label for="zero_price_purchase" class="col-md-4 col-form-label">Zero price purchase</label>
                        <div class="col-md-8">
                            <?php
                            $checked='';
                            if($zero_price==1){
                                $checked='checked';
                            }
                            ?>
                                <input class="form-control amount_td" <?=$checked?> value="1" placeholder="Quantity" id="zero_price_purchase" disabled type="checkbox">
                        </div>
                    </div>
            </div>

            <div class="col-xs-6 col-md-4 col-sm-6">                                         
                <?php
                    $general->inputBoxText('unitPrice','Unit Price','','','amount_td');
                    $general->inputBoxText('total','Total','','','amount_td','readonly');
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <button onclick="purchaseAddToCart()" class="m-2 btn btn-info waves-effect waves-light pull-right m-t-10">Add to cart</button>
            </div>
        </div>
        <div class="row">
            <div style="display: none;">
                <table>
                    <tbody>
                        <tr id="purchaseProductsTr">
                            <td class="autoSerial"></td>
                            <td><input type="hidden" class="pID" value=""><span class="pTitle"></span></td>

                            <td class="unTitle"></td>
                            <td class="unitPrice amount_td"></td>
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
                        <th  style="width: 5%;">#</th>
                        <th>Product</th>
                        <th style="width: 10%;">Unit</th>
                        <th style="width: 10%;" class="amount_td">Unit price</th>
                        <th style="width: 10%;" class="amount_td">Qty</th>
                        <th style="width: 10%;" class="amount_td">Total</th>
                        <th style="width: 5%;" class="amount_td">X</th>
                    </tr>
                    <thead>
                    <tbody id="purchaseProducts">
                        <?php
                            $serial=1;
                            if(!empty($purchase_details)){
                                foreach($purchase_details as $pr){
                                    $pID = $pr['product_id'];
                                    $p=$productData[$purchase['type']][$pID];
                                    $id='pd_'.$pID.'_'.$serial;
                                ?>
                                <tr id="<?=$id?>">
                                    <td class="autoSerial"><?=$serial++?></td>
                                    <td>
                                        <input type="hidden" class="pID" value="<?= $pID?>">
                                        <span class="pTitle"><?= $p['t']?></span>
                                    </td>
                                    <td class="unTitle"><?= $p['u']?></td>
                                    <td class="unitPrice amount_td"><?=$general->numberFormatString($pr['unit_price'])?></td>
                                    <td class="qty amount_td"><?=$pr['quantity']?></td>
                                    <td class="total amount_td"><?=$general->numberFormatString($pr['unit_price']*$pr['quantity'])?></td>
                                    <td class="amount_td"><button class="btn btn-danger remove" onclick="remove_row_by_id('<?php echo $id?>');purchaseSubTotal()">X</button></td>
                                </tr>
                                <?php
                                }
                            }
                        ?>
                    </tbody>
                    <tfoot>
                        <?php
                            $col_minus=0;

                            if($use_product_category==0){
                                $col_minus=2;
                            }

                        ?>  
                        <tr>
                            <td colspan="5" class="amount_td"><b>Sub Total</b></td>
                            <td class="amount_td"><b id="subTotal">0.00</b></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="amount_td"><b>Discount</b></td>
                            <td class="amount_td"><input type="text" class="form-control amount_td" value="<?=$purchase['discount']?>" id="discount"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="amount_td"><b>VAT</b></td>
                            <td class="amount_td"><input type="text" class="form-control amount_td" value="<?=$purchase['VAT']?>" id="VAT"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="amount_td"><b>AIT</b></td>
                            <td class="amount_td"><input type="text" class="form-control amount_td" value="<?=$purchase['AIT']?>" id="AIT"></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="amount_td"><b>Net Payable</b></td>
                            <td class="amount_td"><b id="netPayable">0.00</b></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="amount_td"><b>Note</b></td>
                            <td class="amount_td"><textarea type="text" class="form-control" value="" id="note"><?=$purchase['remarks']?></textarea></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <!--<td colspan="7"><a href="javascript:void()" onclick="purchaseDraftAdd()" class="btn btn-lg btn-success pull-left">Draft</a></td>-->
                            <td colspan="8" class="amount_td">
                                <?php
                                    if($db->permission(4)){
                                    ?>
                                    <button onclick="purchase_update()" class="m-1 p-1 btn btn-lg btn-success pull-right purchase_update">Update</button>
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