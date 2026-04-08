<?php
    $aStatus      = true;
    $eStatus      = true;
    $tPTbl          = 104;
    $tpID           = 'pID';
    $tpTitle        = 'title';
    $pageTitle      = $rModule['title'];
    $titleFieldName = 'Product Title';

    $suppliers=$db->selectAll('suppliers','where isActive=1 order by name asc','id,name,product_type');
    $supplyer_data = [];
    if(!empty($suppliers)){
        foreach($suppliers as $s){
            $supplyer_data[$s['product_type']][] = $s;
        }

    } 
    $use_product_category = $db->get_company_settings('use_product_category');
    $units=$db->selectAll('unit','where isActive=1 order by title asc');
    $general->arrayIndexChange($units,'id');
?> 
<script type="text/javascript">   
    <?php echo 'var USE_PRODUCT_CATEGORY='.$use_product_category.';';?>

</script>

<?php 
    if(isset($_GET['edit'])){
        include(__DIR__."/update.php");
    }
    else{
        $draftID=0;
        $supInvNo='';
        $purType='';
        $discount='';
        $mrr_code='';
        $op_no='';
        $purDate=date('d-m-Y');
        if(isset($_GET['draftID'])){
            $draftID=intval($_GET['draftID']);
            $draft=$db->get_rowData('purchase_draft','purID',$draftID);
            //$general->printArray($draft);
            if(!empty($draft)){
                if($draft['supID']==$supID){
                    $drData = $general->getJsonFromString($draft['sData']);
                    //$general->printArray($drData);
                    if(isset($drData['supInvNo'])){$supInvNo=$drData['supInvNo'];}    
                    if(isset($drData['purType'])){$purType=$drData['purType'];}    
                    if(isset($drData['purDate'])){$purDate=$drData['purDate'];}    
                    if(isset($drData['mrr_code'])){$mrr_code=$drData['mrr_code'];}    
                    if(isset($drData['op_no'])){$op_no=$drData['op_no'];}    
                    if(isset($drData['discount'])){$discount=$drData['discount'];}	
                }else{$draftID=0;}
            }else{$draftID=0;}
        ?>
        <script type="text/javascript">
            $(document).ready(function(){
                purchaseSubTotal();
            });
        </script>
        <?php
        }
        $general->pageHeader($rModule['title'],[$pUrl=>$rModule['title'],1=>'New']);

        $products=[];
        if($use_product_category==0){
            $products = $db->selectAll('products','where isActive=1','id as pID, title,code');
            if(!empty($products)){
                foreach($products as $k=>$p){
                    $products[$k]['title']=$p['code'].' '.$p['title'];
                }
            }
        }

        $productData=$db->getProductData('',true);
        //$general->printArray(shell_exec('mysql -V'));

        if($use_product_category==1){
            $categoryData=$db->getCategoryData();
        }  
        $types=$smt->get_all_product_type();
    ?>
    <script type="text/javascript">   
        <?php echo 'var productData='.json_encode($productData).';';?>
        <?php echo 'var supplyer_data='.json_encode($supplyer_data).';';?>
        $(document).on('change','#product_type',function(){type_wise_supplyer_and_product(this.value)});

    </script>

    <div class="row">
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
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxSelect($types,'Type','product_type','id','title');
                    ?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxSelect([],'Supplier Name','supplier_id','id','name');
                    ?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxText('supplier-no','Bill No',$supInvNo);
                    ?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxText('bill-date','Bill Date',$purDate,'','daterangepicker');
                    ?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxText('challan-no','Challan No');
                    ?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxText('challan-date','Challan Date',$purDate,'','daterangepicker');
                    ?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxText('po-no','PO No',$op_no);
                    ?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxText('mrr-code','MRR No',$mrr_code);
                    ?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxText('mrr-date','MRR Date',$purDate,'','daterangepicker');
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
                                <input class="form-control amount_td" value="" placeholder="Quantity" id="zero_price_purchase" type="checkbox" onchange="purchaseSubTotal()">
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
                                <?php
                                    if($use_product_category==1){
                                    ?>
                                    <td class="category"></td>
                                    <td><input type="hidden" class="categoryID" value=""><span class="subCategory"></span></td>
                                    <?php
                                    }
                                ?>
                                <td class="unTitle"></td>
                                <td class="unitPrice amount_td"></td>
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
                            <th  style="width: 5%;">#</th>
                            <th>Product</th>
                            <?php
                                if($use_product_category==1){
                                ?>
                                <th>Category</th>
                                <th>Sub Category</th>
                                <?php
                                }
                            ?>
                            <th style="width: 10%;">Unit</th>
                            <th style="width: 10%;" class="amount_td">Unit price</th>
                            <th style="width: 10%;" class="amount_td">Sale Price</th>
                            <th style="width: 10%;" class="amount_td">Qty</th>
                            <th style="width: 10%;" class="amount_td">Total</th>
                            <th style="width: 5%;" class="amount_td">X</th>
                        </tr>
                        <thead>
                        <tbody id="purchaseProducts">
                            <?php
                                $serial=1;
                                if(isset($drData['products'])){
                                    foreach($drData['products'] as $pID=>$pr){
                                        $p=$productData[$pID];
                                        $id='pd_'.$pID.'_'.$serial;
                                    ?>
                                    <tr id="<?php echo $id?>">
                                        <td class="autoSerial"><?php echo $serial++?></td>
                                        <td>
                                            <input type="hidden" class="pID" value="<?php echo $pID?>">
                                            <span class="pTitle"><?php echo $p['t']?></span>
                                        </td>
                                        <td class="unTitle"><?php echo $p['u']?></td>
                                        <td class="unitPrice amount_td"><?php echo $pr['up']?></td>
                                        <td class="salePrice amount_td"><?php echo $pr['sp']?></td>
                                        <td class="qty amount_td"><?php echo $pr['q']?></td>
                                        <td class="total amount_td"><?php echo (float)($pr['up']*$pr['q'])?></td>
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
                                <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Sub Total</b></td>
                                <td class="amount_td"><b id="subTotal">0.00</b></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Discount</b></td>
                                <td class="amount_td"><input type="text" class="form-control amount_td" value="" id="discount"></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="<?=8-$col_minus?>" class="amount_td"><b>VAT</b></td>
                                <td class="amount_td"><input type="text" class="form-control amount_td" value="" id="VAT"></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="<?=8-$col_minus?>" class="amount_td"><b>AIT</b></td>
                                <td class="amount_td"><input type="text" class="form-control amount_td" value="" id="AIT"></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Net Payable</b></td>
                                <td class="amount_td"><b id="netPayable">0.00</b></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="<?=8-$col_minus?>" class="amount_td"><b>Note</b></td>
                                <td class="amount_td"><textarea type="text" class="form-control" value="" id="note"></textarea></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <!--<td colspan="7"><a href="javascript:void()" onclick="purchaseDraftAdd()" class="btn btn-lg btn-success pull-left">Draft</a></td>-->
                                <td colspan="<?=11-$col_minus?>" class="amount_td">
                                    <?php
                                        if($db->permission(4)){
                                        ?>
                                        <button onclick="purchaseAdd()" class="m-1 p-1 btn btn-lg btn-success pull-right purchaseAdd">Purchase</button>
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
    <?php
    }
?>
<script>
    $(document).ready(function(){
        $('#product-type').on('change',function(){
            let type = this.value;
            $('#purchaseProducts').html('');
            $('#pID').html('');
            $('#qty').val('');
            $('#total').val('');
            $('#unitPrice').val('');
            $('#salePrice').val('');
            $('#pID').html('<option value="">Select</option>');
            productData={};
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{typed_wise_product:1,type:type},
                success:function(data){
                    if(typeof(data.status)  !== "undefined"){ 
                        if(data.status==1){
                            productData=data.product_data;
                            $.each(data.product_data,function(a,b){
                                $('#pID').append('<option value="'+a+'">'+b.t+'</option>');
                            });
                            select2Call();
                            purchaseSubTotal();
                        }
                        swMessageFromJs(data.m);
                    }
                    else{
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) { 
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            });
        })
    })
</script>