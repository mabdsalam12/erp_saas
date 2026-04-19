<?php
$suppliers=$db->selectAll('suppliers','where isActive=1 order by name asc');    
$general->pageHeader($rModule['name']);
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
                        <select id='supID' class="form-control select2">
                            <option value="">All Supplier</option>
                            <?php
                            foreach($suppliers as $sup){
                            ?><option value="<?php echo $sup['id'];?>"><?php echo $sup['name'];?></option><?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Type</h5>
                        <select id='type' class="form-control">
                            <option value="0">All</option>
                            <option value="<?php echo PRODUCT_TYPE_GOOD;?>">Good product</option>
                            <option value="<?php echo PRODUCT_TYPE_DAMAGE;?>">Damage product</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <a href="javascript:void()" class="btn btn-success" onclick="productReturnList()">Search</a>
                        <script type="text/javascript">
                            $(document).ready(function(){
                                productReturnList();
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
<script type="text/javascript">
    var notPaidDetails=[];
    function damageReturnPayInit(id){
        if(notPaidDetails.hasOwnProperty(id)){
            var details=notPaidDetails[id];
            $('#paidID').val(id);
            $('#supplier').val(details.supName)
            $('#invoiceNo').val(details.supInvNo)
            $('#subtotal').val(details.subTotal)
            $('#discount').val(details.discount)
            $('#netTotal').val(details.netTotal)
            $('#purchaseReturnPayModalBtn').click();
        }
        else{
            swMessage('Invalid request');
        }

    }
    function productReturnList(){
        var supID   = parse_int($('#supID').val());
        var type    = parse_int($('#type').val());
        var dRange  = $('#dRange').val();
        $('#reportArea').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{productReturnList:1,dRange:dRange,supID:supID,type:type},
            success:function(data){
                if(data.status==1){
                    notPaidDetails=data.notPaidDetails;
                    $('#reportArea').html(data.html);
                }
                swMessageFromJs(data.m);
            }
        });
    }
    function productReturnPayCollect(){
        if(productHideBtn==0)return false;
        var id  = $('#paidID').val();
        var paid    = parse_int($('#paid').val());
        var note    = $('#payNote').val();
        errorSet=0;
        if(id<1){
            errorSet=1;
            swMessage('Invalid request');
        }
        else if(note==''){
            errorSet=1;
            swMessage('Please enter note');
        }
        if(errorSet==0){
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{productReturnPayCollect:1,id:id,paid:paid,note:note},
                success:function(data){
                    if(data.status==1){
                        $('#paidID').val('');
                        $('#payNote').val('');
                        $('#paid').val('');
                        productReturnList();
                        $('#purchaseReturnPayModal').modal('toggle');
                    }
                    swMessageFromJs(data.m);
                }
            });

        }

    }
</script>

<div style="display: none;">
    <a href="javascript:void()" data-toggle="modal" data-target="#purchaseReturnPayModal" id="purchaseReturnPayModalBtn"></a>
</div>
<div class="modal fade" id="purchaseReturnPayModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><span class="entryLock">Purchase return pay</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="receiveAmount">
                    <input type="hidden" value="" id="paidID">
                    <?php $general->inputBoxText('supplier','Supplier','','','','disabled');?>
                    <?php $general->inputBoxText('invoiceNo','Invoice no','','','','disabled');?>
                    <?php $general->inputBoxText('subtotal','Subtotal','','','amount_td','disabled');?>
                    <?php $general->inputBoxText('discount','Discount','','','amount_td','disabled');?>
                    <?php $general->inputBoxText('netTotal','Net total','','','amount_td','disabled');?>
                    <?php $general->inputBoxText('paid','Paid','','','amount_td');?>
                    <?php $general->inputBoxText('payNote','Note');?>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" onclick="productReturnPayCollect()" class="btn btn-info" id="salaryPayBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>