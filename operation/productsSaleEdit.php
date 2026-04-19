<?php
    if(isset($_GET['edit'])){
        $use_product_category = $db->get_company_settings('use_product_category');
        $sID = intval($_GET['edit']);
        $s = $db->get_rowData('sale','id',$sID);
        if(empty($s)){$general->redirect($pUrl,37,'Sale');}
        $purDate = date('d-m-Y',$s['date']);
        $saleDetails = $db->selectAll('sale_products','where sale_id='.$sID);

        $data = array($pUrl=>$rModule['name'],'1'=>'Update');
        $general->pageHeader($rModule['name'],$data);
        $customers = $db->selectAll('customer','where isActive=1','id,name');
        $categories=$db->selectAll('product_category','where isActive=1');
        $general->arrayIndexChange($categories,'id');
        $categoryData=$db->getCategoryData();
        $productData=$db->getProductData();
        $products=[];
        if($use_product_category==0){
            $products = $db->selectAll('products','where type='.PRODUCT_TYPE_FINISHED.' and isActive=1','id as pID, title');
        }

    ?>
    <script type="text/javascript">
        <?php echo 'var USE_PRODUCT_CATEGORY='.$use_product_category.';';?>
        <?php echo 'var productData='.json_encode($productData).';';?>
        <?php echo 'var categoryData='.json_encode($categoryData).';';?>
    </script>
    <div class="col-sm-12">
        <div class="white-box border-box">
            <div><?php show_msg();?></div>
            <input type="hidden" id="sID" value="<?=$sID?>"> 
            <hr>
            <div class="row">
                <div class="col-xs-6 col-md-6 col-sm-6">
                    <?php $general->inputBoxSelect($customers,'Customer','cID','id','name',$s['customer_id'],'','form-control','disabled');?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-4">
                    <?php $general->inputBoxText('saleDate','Date',$purDate,'','daterangepicker','disabled');?>
                </div>
                <div class="col-xs-6 col-md-4 col-sm-4">

                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-xs-6 col-md-4 col-sm-6">
                <?php
                     if($use_product_category==1){
                 ?>
                    <?php $general->inputBoxSelect($categoryData,'Category','category','id','title');?>
                    <?php
                     }
                     ?>
                    <?php $general->inputBoxText('productSalePrice','Sale Price','','','amount_td');?>
                </div> 
                <div class="col-xs-6 col-md-4 col-sm-3">
                <?php
                     if($use_product_category==1){
                 ?>
                    <?php $general->inputBoxSelect([],'Sub Category','subCategory','id','title'); ?>
                    <?php
                     }
                     ?>
                    <?php $general->inputBoxText('total','Total','','','amount_td','readonly');?>
                </div>

                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php $general->inputBoxSelect($products,'Product','pID','pID','title',script:'onchange="saleProductChange(this.value)"');?>
                    <div class="form-group row">
                        <label for="qty" class="col-md-4 col-form-label">Quantity</label>
                        <div class="col-md-8">
                            <div class="input-group qty-input-group">
                                <input class="form-control amount_td" value="" placeholder="Quantity" id="productSaleqty" type="text">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="qtyLabel">--</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
                            <th style="width: 10%;" class="amount_td">Sale Price</th>
                            <th style="width: 10%;" class="amount_td">Qty</th>
                            <th style="width: 10%;" class="amount_td">Total</th>
                            <th style="width: 5%;" class="amount_td">X</th>
                        </tr>
                        <thead>
                        <tbody id="saleProducts">
                            <?php
                                $serial=1;
                                $subTotal = 0;
                                if(!empty($saleDetails)){
                                    foreach($saleDetails as $pr){
                                        $pID = $pr['product_id'];
                                        $p=$productData[$pID];
                                        $id='pd_'.$pID.'_'.$serial;
                                        if($use_product_category==1){
                                        $c  = $categories[$p['pc']];
                                        }
                                        $subTotal+= (float)($pr['unit_price']*$pr['sale_qty']);
                                    ?>
                                    <tr id="<?php echo $id?>">
                                        <td class="autoSerial"><?=$serial?></td>
                                        <td>
                                            <input type="hidden" class="pID" value="<?=$pID?>">
                                            <span class="pTitle"><?=$p['t']?></span>
                                        </td>
                                        <?php
                                             if($use_product_category==1){
                                         ?>
                                        <td class="category"><?=$categories[$c['parent']]['title']?></td>
                                        <td><input type="hidden" class="categoryID" value="<?=$p['pc']?>"><span class="subCategory"><?=$c['title']?></span></td>
                                        <?php
                                             }
                                         ?>
                                        <td class="unTitle"><?=$p['u']?></td>
                                        <td class="salePrice amount_td"><?= $pr['unit_price']?></td>
                                        <td class="qty amount_td"><?= $pr['sale_qty']?></td>
                                        <td class="total amount_td"><?= (float)($pr['unit_price']*$pr['sale_qty'])?></td>
                                        <td class="amount_td"><button class="btn btn-danger remove" onclick="remove_row_by_id('<?php echo $id?>');purchaseSubTotal()">X</button></td>
                                    </tr>
                                    <?php
                                    }
                                }
                                $payable =  $subTotal-$s['discount'];
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
                                <td colspan="<?=7-$col_minus?>" class="amount_td"><b>Sub Total</b></td>
                                <td class="amount_td"><b id="subTotal"><?=$subTotal?></b></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="<?=7-$col_minus?>" class="amount_td"><b>Discount</b></td>
                                <td class="amount_td"><input type="text" class="form-control amount_td" value="<?=$s['discount']?>" id="saleDiscount"></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="<?=7-$col_minus?>" class="amount_td"><b>Net Payable</b></td>
                                <td class="amount_td"><b id="netPayable"><?=$payable?></b></td>
                                <td>&nbsp;</td>
                            </tr>


                            <tr>
                                <td colspan="<?=9-$col_minus?>" class="amount_td">
                                    <button onclick="productsSaleEdit()" class="mt-1 btn-lg btn-success pull-right productsSaleEdit">Update</button>
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
    else{
        $general->pageHeader($rModule['name']);
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


    function productsSaleEdit(){
        buttonLoading('productsSaleEdit');
        let sID = parse_int($('#sID').val());
        let saleDate = $('#saleDate').val();
        let subTotal=parse_float($('#subTotal').html());
        let discount=parse_float($('#saleDiscount').val());
        let netPayable=parse_float($('#netPayable').html());
        let products= {};
        errorSet=0;
        if(subTotal<=0){swMessage('Please select product.');errorSet=1;}
        else if(netPayable<=0){swMessage('Invalid discount.');errorSet=1;}
        let count=0;

        if(errorSet==0){
            $('#saleProducts .pID').each(function(a,b){
                if(errorSet==0){
                    let tID=$(this).closest('tr').attr('id');
                    let pID         = parse_int($('#'+tID+' .pID').val());
                    let qty         = parse_float($('#'+tID+' .qty').html());
                    let salePrice   = parse_float($('#'+tID+' .salePrice').html());
                    products[pID]={
                        qty:qty,salePrice:salePrice
                    }
                    count++;
                }
            });
            if(count==0){errorSet=1;swMessage('select a product');}
            else{
                let postData={
                    productsSaleEdit:1,
                    sID:sID,
                    saleDate:saleDate,
                    products:products,
                    discount:discount,

                };
                console.log('postData');
                console.log(postData);
                $.ajax({
                    type:'post',
                    url:ajUrl,
                    data:postData,
                    success:function(data){
                        button_loading_destroy('productsSaleEdit','Update'); 
                        if(typeof(data.status)  !== "undefined"){ 
                            swMessageFromJs(data.m);
                            if(data.status==1){
                                setTimeout(function(){window.location = pUrl;},1000);
                            }

                        }
                        else{
                            swMessage(AJAX_ERROR_MESSAGE); 
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) { 
                        button_loading_destroy('productsSaleEdit','Update'); 
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                });
            }
        }
        if(errorSet==1){
            button_loading_destroy('productsSaleEdit','Update');  
        }
    }
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

