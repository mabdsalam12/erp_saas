<?php
    $general->pageHeader($rModule['name']);
?>
<div class="row">
    <div class="col-sm-12" id="message_show_box"></div>
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">
                <div class="col-md-2">
                    <h5 class="box-title">Date</h5>
                    <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Status</h5>
                    <select id='status' class="form-control select2">
                        <option value="">All</option>
                        <option value="p">Pending</option>
                        <option value="a">Approved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <input type="submit" value="Search"class="btn btn-success" name="s" onclick="customer_collection_report();">
                </div>
                <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                <div class="col-sm-12 col-lg-12" id="reportArea"></div>
            </div>
        </div>
    </div>
</div>

<script>
    $( document ).ready(function() {
        customer_collection_report();
    });
    function customer_collection_report(){
        let status=$('#status').val();
        let dRange=$('#dRange').val();
        $('#reportArea').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{customer_collection_report:1,dRange:dRange,status:status},
            success:function(data){
                if(typeof(data.status)){
                    if(data.status==1){
                        $('#reportArea').html(data.html);
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    $('#reportArea').html('');
                    swMessage(AJAX_ERROR_MESSAGE); 
                }

            },
            error:function(){
                $('#reportArea').html('');
                swMessage(AJAX_ERROR_MESSAGE); 
            }
        });
    } 
    function customer_collection_action(id){
        
        if(confirm('Are you sure?')){
            $.ajax({
                type:'post',
                url:ajUrl,
                data:{customer_collection_action:1,id:id},
                success:function(data){
                    if(typeof(data.status)){
                        if(data.status==1){
                            customer_collection_report()
                        }
                        swMessageFromJs(data.m);
                    }
                    else{
                        swMessage(AJAX_ERROR_MESSAGE); 
                    }

                },
                error:function(){
                    button_loading_destroy('action_btn_'+type+'_'+id,title);
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            });  
        }  
    }  

</script>