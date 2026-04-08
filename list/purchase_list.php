<?php
$suppliers=$db->selectAll('suppliers','where  isActive=1 order by name asc');
$general->pageHeader($rModule['title']);
$types=$smt->get_all_product_type();
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-sm-12 col-lg-12">
                    <div class="col-md-2">
                        <h5 class="box-title">Date</h5>

                        <input type="text" id="dRange" class="daterangepickerMulti form-control" value="<?php echo date('d-m-Y').' to '.date('d-m-Y');?>">
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Supplier</h5>
                        <select id='supplier_id' class="form-control select2">
                            <option value="0">All Supplier</option>
                            <?php
                            foreach($suppliers as $sup){
                                ?><option value="<?php echo $sup['id'];?>"><?php echo $sup['name'];?></option><?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Type</h5>
                        <select id='type' class="form-control select2">
                            <option value="0">All Type</option>
                            <?php
                            foreach($types as $type){
                                ?><option value="<?php echo $type['id'];?>"><?php echo $type['title'];?></option><?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button id="purchase" class="btn btn-success" >Search</button>
                        <script type="text/javascript">
                            $(document).ready(function(){
                                const report_data = JSON.parse(localStorage.getItem('purchase_report_data'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    $('#supplier_id').val(report_data.supplier_id);
                                    $('#type').val(report_data.type);
                                    select2Call();
                                }
                                purchaseReport();
                            })
                            $(document).on("click",'#purchase', function(){
                                purchaseReport();
                            })

                        </script>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12">
                    <?php
                    show_msg();
                    ?>
                </div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;"></div>
            </div>
        </div>
    </div>
</div>
<script>
    function purchase_details_view(id){
        $('#loading_area').html(loadingImage);
        $('#details_area').hide();
        $('#loading_area').show();

        $.ajax({
            type:'post',
            url:ajUrl,
            data:{purchase_details_view:1,id:id},
            success:function(data){
                if(data.status==1){
                    let i=data.info;
                    $('#details_top .supplier').html(i.supplier);
                    $('#details_top .invoice_no').html(i.invoice_no);
                    $('#details_top .date').html(i.date);
                    $('#details_top .challan_date').html(i.challan_date);
                    $('#details_top .mrr_date').html(i.mrr_date);
                    $('#details_top .mrr_no').html(i.mrr_no);
                    $('#details_top .remarks').html(i.remarks);
                    $('#details_top .create_date').html(i.createdOn	);
                    let details_top=$('#details_top').html();
                    $('#details_area .details_top').html(details_top);
                    let serial=1;
                    $('#details_area .details_products').html('');
                    $.each(data.products,function(a,b){
                        $('#details_products .s').html(serial++);
                        $('#details_products .title').html(b.title);
                        $('#details_products .unit').html(b.unit);
                        $('#details_products .quantity').html(b.quantity);
                        $('#details_products .unit_price').html(b.unit_price);
                        $('#details_products .total').html(b.total);
                        let details_products=$('#details_products').html();
                        console.log('details_products',details_products)
                        $('#details_area .details_products').append(details_products);
                    });
                    
                    $("#pur_action").html('<a href="<?=URL?>/?mdl=purchase&edit='+id+'" class="btn btn-info">Edit</a> <button onclick="are_you_sure(1,\'Are you sure you want to delete the purchase?\','+id+',purchase_delete)" class="btn btn-danger purchase_delete'+id+'">Delete</button>');
                    
                
                    $('#details_footer .sub_total').html(i.sub_total);
                    $('#details_footer .discount').html(i.discount);
                    $('#details_footer .VAT').html(i.VAT);
                    $('#details_footer .AIT').html(i.AIT);
                    $('#details_footer .total').html(i.total);

                    $('#loading_area').hide();
                    $('#details_area').show();
                }
                else{
                    $('#loading_area').hide();
                    $('#details_area').show();
                }
                swMessageFromJs(data.m);
            }
        });
        $('#details_modal_btn').click();
    }
    function purchase_delete(id){
        if(id>0){
            buttonLoading('purchase_delete'+id);
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{purchase_delete:1,id:id},
                success:function(data){
                    console.log('done');
                    swMessage('done');
                    if(typeof(data.status)!=='undefined'){
                        swMessageFromJs(data.m);
                        if(data.status==1){
                            purchaseReport();
                        }
                    }
                    else{
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }
                    button_loading_destroy('purchase_delete'+id,'Delete');
                },
                error:function(){   
                    button_loading_destroy('purchase_delete'+id,'Delete');
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            });
        }
        else{
            button_loading_destroy('purchase_delete'+id,'Delete');
            swMessage(AJAX_ERROR_MESSAGE); 
        }
    }
</script>
<div style="display: none;">
    <button data-toggle="modal" data-target="#details_modal" id="details_modal_btn"></button>
    <div id="details_top">
        <table class="table table-borderd">
            <tr><td>Invoice no</td><td class="invoice_no"></td></tr>
            <tr><td>Supplier</td><td class="supplier"></td></tr>
            <tr><td>Bill date</td><td class="date"></td></tr>
            <tr><td>Challan date</td><td class="challan_date"></td></tr>
            <tr><td>Challan no</td><td class="challan_no"></td></tr>
            <tr><td>MRR date</td><td class="mrr_date"></td></tr>
            <tr><td>MRR no</td><td class="mrr_no"></td></tr>
            <tr><td>Remarks</td><td class="remarks"></td></tr>
            <tr><td>Create Date</td><td class="create_date"></td></tr>
        </table>
    </div>

    <table>
        <tbody id="details_products">
            <tr>
                <td class="s"></td>
                <td class="title"></td>
                <td class="unit"></td>
                <td class="quantity amount_td"></td>
                <td class="unit_price amount_td"></td>
                <td class="total amount_td"></td>
            </tr>
        </tbody>
    </table>
</div>
<div class="modal fade" id="details_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Purchase details</h5>   
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="pur_action">
                    
                </div>
            </div>
            <div class="modal-body">
                <div id="loading_area"></div>
                <div id="details_area" style="display:none">
                    <div class="details_top"></div>
                    <div>
                        <table class="table table-borderd">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <td>Product</td>
                                    <td>Unit</td>
                                    <td>Quantity</td>
                                    <td>Unit price</td>
                                    <td>Total</td>
                                </tr>
                            </thead>
                            <tbody class="details_products">

                            </tbody>
                            <tfoot id="details_footer">
                                <tr><td colspan="4"></td><td>Subtotal</td><td class="sub_total amount_td"></td></tr>
                                <tr><td colspan="4"></td><td>Discount</td><td class="discount amount_td"></td></tr>
                                <tr><td colspan="4"></td><td>VAT</td><td class="VAT amount_td"></td></tr>
                                <tr><td colspan="4"></td><td>AIT</td><td class="AIT amount_td"></td></tr>
                                <tr><td colspan="4"></td><td>Total</td><td class="total amount_td"></td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>