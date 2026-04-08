

<?php 
    if(isset($_GET['add'])){


        $general->pageHeader($rModule['title'],[$pUrl=>$rModule['title'],1=>'New']);



        //$general->printArray(shell_exec('mysql -V'));
        $date=date('d-m-Y');
        $productData=$db->getProductData();
        //$general->printArray($productData);
    ?>


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
                        $general->inputBoxText('date','Date',$date,'','daterangepicker');
                    ?>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-xs-6 col-md-4 col-sm-6">
                    <?php
                        $general->inputBoxSelect($productData,'Product','product-id','id','t',script:'onchange="saleProductChange(this.value)"');
                    ?>

                    <div class="form-group row mb-1">
                        <label for="qty" class="col-md-4 col-form-label">Quantity</label>
                        <div class="col-md-8">
                            <div class="input-group qty-input-group">
                                <input class="form-control amount_td" value="" placeholder="Quantity" id="quantity" type="text">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="qtyLabel">--</span>
                                </div>
                            </div>
                        </div>
                    </div>


                </div> 

            </div>
            <div class="row">
                <div class="col-sm-12">
                    <button onclick="purchase_requisition_add_to_cart()" class="m-2 btn btn-info waves-effect waves-light pull-right m-t-10">Add to cart</button>
                </div>
            </div>
            <div class="row">
                <div style="display: none;">
                    <table>
                        <tbody>
                            <tr id="purchase-requisition-details-tr">
                                <td class="autoSerial"></td>
                                <td><input type="hidden" class="product-id" value=""><span class="product-title"></span></td>
                                <td class="unit-title"></td>
                                <td class="quantity amount_td"></td>
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
                            <th>Unit</th>
                            <th style="width: 10%;" class="amount_td">Qty</th>
                            <th style="width: 5%;" class="amount_td">X</th>
                        </tr>
                        <thead>
                        <tbody id="purchase-requisition-details">

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="amount_td"><b>Note</b></td>
                                <td class="amount_td"><textarea type="text" class="form-control" value="" id="note"></textarea></td>
                                <td>&nbsp;</td>
                            </tr>

                            <tr>
                                <td colspan="4" class="amount_td">

                                    <button onclick="purchase_requisition_add()" class="m-1 p-1 btn btn-lg btn-success pull-right">Submit</button>

                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        <?php echo 'var productData='.json_encode($productData).';'; ?>
        function purchase_requisition_add_to_cart(){

            //    t('pID')
            //    t(pID)
            errorSet=0;
            let quantity         = parse_int($('#quantity').val());
            let product_id  = parse_float($('#product-id').val());

            if(product_id<=0){swMessage('Please select a product');errorSet=1;}
            else if(quantity<=0){swMessage('Please enter a valid Quantity');errorSet=1;}
            if(errorSet==0){
                let product=productData[product_id];

                let id='pd_'+product_id+'_'+autoInc;autoInc++;

                $('#purchase-requisition-details-tr .product-id').val(product_id)

                $('#purchase-requisition-details-tr .product-title').html(product.t)
                $('#purchase-requisition-details-tr .unit-title').html(product.u)

                $('#purchase-requisition-details-tr .quantity').html(quantity)
                $('#purchase-requisition-details-tr .remove').attr('onclick','remove_row_by_id(\''+id+'\');')
                let purchase_requisition_details=$('#purchase-requisition-details-tr').html();
                $('#purchase-requisition-details').append('<tr id="'+id+'">'+purchase_requisition_details+'</tr>');
                $('#product-id').val('');
                $('#quantity').val('');
                select2Call();
                let tr_sl_start=1;$('#purchase-requisition-details .autoSerial').each(function(){$(this).html(tr_sl_start);tr_sl_start++;});
            }
        }
        function purchase_requisition_add(){
            let date = $('#date').val();
            let note= $('#note').val();
            //var draftID= parse_int($('#draftID').val());
            let products= {};
            errorSet=0;


            $('#purchase-requisition-details .product-id').each(function(a,b){
                if(errorSet==0){
                    let tID=$(this).closest('tr').attr('id');
                    let product_id         = parse_int($('#'+tID+' .product-id').val());
                    let quantity         = parse_float($('#'+tID+' .quantity').html());
                    if(quantity<=0){swMessage('Invalid Quantity');errorSet=1;}
                    products[product_id]={
                        quantity:quantity
                    }
                }
            });
            if(errorSet==0){
                let postData={
                    purchase_requisition_add:1,
                    date:date,
                    products:products,
                    note:note,
                    //draftID:draftID,
                };
                $.ajax({
                    type:'post',
                    url:ajUrl,
                    data:postData,
                    success:function(data){
                        if(typeof(data.status)!=='undefined'){
                            if(data.status==1){
                                $('#note').val('');
                                $('#purchase-requisition-details').html('');
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
    <?php
    }
    else{
        $data =[$pUrl=>$rModule['title']];
        $general->pageHeader($rModule['title'],$data,$general->addBtnHtml($pUrl));
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-sm-12 col-lg-12">   
                        <div class="col-md-2">
                            <h5 class="box-title">Date</h5>
                            <input type="text" id="dRange" class="daterangepickerMulti form-control" value="">
                        </div>               




                        <div class="col-md-2">
                            <h5 class="box-title">Search</h5>
                            <button class="btn btn-success" onclick="purchase_requisition_list()">Search</button>
                            <script type="text/javascript">
                                function purchase_requisition_list(){
                                    let dRange  = $('#dRange').val();
                                    $('#reportArea').html(loadingImage);
                                    $.ajax({
                                        type:'post',
                                        url:ajUrl,
                                        data:{purchase_requisition_list:1,dRange:dRange},
                                        success:function(data){
                                            $('#reportArea').html('');
                                            if(typeof(data.status)!=='undefined'){
                                                if(data.status==1){
                                                    $('#reportArea').html(data.html);
                                                }
                                                swMessageFromJs(data.m);
                                            }
                                            else{
                                                swMessage(AJAX_ERROR_MESSAGE); 
                                            }
                                        },
                                        error:function(){
                                            $('#reportArea').html('');
                                            swMessage(AJAX_ERROR_MESSAGE); 
                                        }
                                    });
                                }

                                $(document).ready(function(){
                                    purchase_requisition_list();
                                });
                            </script>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-12">
                        <?php
                            show_msg();
                        ?>
                    </div>
                    <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function purchase_requisition_details_view(id){
            $('#details-body').html(loadingImage);
            $('#modal_title').html('Purchase requisition details');
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{purchase_requisition_details_view:1,id:id},
                success:function(data){
                    $('#details-body').html('');
                    if(typeof(data.status)!=='undefined'){
                        if(data.status==1){
                            $('#details-body').html(data.html);
                        }

                        swMessageFromJs(data.m);
                    }
                    else{
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                },
                error:function(){
                    $('#details-body').html('');
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            });
            $('#details-modal-btn').click();
        }

    </script>
    <?php  
        include_once ROOT_DIR.'/common/details_modal.php';
    }
?>