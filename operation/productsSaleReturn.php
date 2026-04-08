<?php
    $general->pageHeader($rModule['title']);
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
                        <a href="javascript:void()" class="btn btn-success" onclick="saleReturnList()">Search</a>

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
<script type="">
    function saleReturn(){
        let sID = $('#sID').val();
        let cashPayment = parse_int($('#cashPayment').text());
        let returnRemarks = $('#returnRemarks').text();
        let totalAmount = 0;
        let returnData={};
        $('.saleReturnProduct').each(function(a,b){
            let tID=this.id;
            let pID=$(this).attr('data-pID');
            let amount =  parse_float($('#'+tID+' .amount').html());
            let returnQty = parse_int($('#'+tID+' .returnQty').text());
            let returnUp = parse_float($('#'+tID+' .return_up').text());
            returnData[pID]={
                returnQty:returnQty,  
                returnUp:returnUp  
            };
            totalAmount+=amount;
        });
        if(totalAmount<=0){swMessage('invalid submit.');}
        else if(cashPayment<0){swMessage('invalid cash payment amount.');}
        else{
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{saleReturn:1,sID:sID,returnData:returnData,cashPayment:cashPayment,returnRemarks:returnRemarks},
                success:function(data){
                    if(typeof(data.status)  !== "undefined"){ 
                        if(data.status==1){
                            $('#sID').val(0);
                            $('#productReturnModulBody').html('');
                            $(".close").click();
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
        }

    }
    function saleReturnCalculation(id){
        let avQty = parse_int($('#'+id+' .avQty').html());
        let returnQty = parse_int($('#'+id+' .returnQty').text());
        let sale_up = parse_float($('#'+id+' .sale_up').html());
        let returnUp = parse_float($('#'+id+' .return_up').text());
        if(returnQty>avQty){swMessage('invalid quantity.'); $('#'+id+' .returnQty').text('');}
        else if(returnUp>sale_up){swMessage('invalid unit price.'); $('#'+id+' .return_up').text('');}  

        returnQty = parse_int($('#'+id+' .returnQty').text());
        returnUp = parse_float($('#'+id+' .return_up').text());

        let amount = returnQty*returnUp;
        $('#'+id+' .amount').html(amount);    


        let totalAmount = 0;
        $('.saleReturnProduct').each(function(a,b){
            let tID=this.id;
            let amount =  parse_float($('#'+tID+' .amount').html());

            totalAmount+=amount;
        });
        let cashPayment = parse_float($('#cashPayment').text());
        let due = totalAmount-cashPayment;
        $('#returnDue').html(due);
        $('#returnAmout').html(totalAmount);

    } 
    function saleReturnDueCalclulation(){
        let totalAmount = parse_float($('#returnAmout').html()); 
        let cashPayment = parse_int($('#cashPayment').text());
        let due = totalAmount-cashPayment;
        $('#returnDue').html(due);
    } 
    function saleReturnInit(sID){
        $('#productReturnModulBody').html(loadingImage);
        $('#sID').val(0);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{saleReturnInit:1,sID:sID},
            success:function(data){
                $('#productReturnModulBody').html('');
                if(typeof(data.status)  !== "undefined"){ 
                    if(data.status==1){
                        $('#sID').val(sID);
                        $('#productReturnModulBody').html(data.html);
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    swMessage(AJAX_ERROR_MESSAGE); 
                }

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) { 
                $('#productReturnModulBody').html('');
                swMessage(AJAX_ERROR_MESSAGE); 
            }
        });
        $("#productReturnBtn").click();
    } 
    function saleReturnList(){
        var dRange  = $('#dRange').val();
        $('#reportArea').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{saleReturnList:1,dRange:dRange},
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
<div style="display: none;">
    <a href="javascript:void()" data-toggle="modal" data-target="#productReturnModul" id="productReturnBtn" href="javascript:void()">ddddddd</a>
</div>
<div class="modal fade bd-example-modal-lg" id="productReturnModul" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <input type="hidden" id="sID"> 
                <h5 class="modal-title d-inline-block">Product Return</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="productReturnModulBody">


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button updateBtn" onclick="saleReturn()" class="btn btn-info">Submit</button>
            </div>
        </div>
    </div>
</div>