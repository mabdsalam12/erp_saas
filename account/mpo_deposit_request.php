<?php
    $data = array($pUrl=>$rModule['title']);
    $general->pageHeader($rModule['title'],$data);
    $user = $db->selectAll('users','where type='.USER_TYPE_MPO,'name,id');
    $base = $db->selectAll('base');
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <div class="row">

                <div class="col-md-3">
                    <h5 class="box-title">Date </h5>
                    <input type="text" name="dRange" id="dRange" class="daterangepickerMulti form-control" value="">
                </div>
                <?php $general->inputBoxSelectForReport($base,'Base','base_id','id','title'); ?>
                <div class="col-md-2">
                    <h5 class="box-title">MPO</h5>
                    <select id='user_id' class="form-control select2">
                        <option value="">All</option>
                        <?php
                            foreach($user as $c){
                            ?>
                            <option value="<?php echo $c['id'];?>"><?php echo $c['name'];?></option>
                            <?php
                            }
                        ?>
                    </select>
                </div> 
                <div class="col-md-2">
                    <h5 class="box-title">Status</h5>
                    <select id='status' class="form-control select2">
                        <option value="-1">All</option>
                        <option value="1">Pending</option>
                        <option value="2">Confirm</option>
                        <option value="0">Cancel</option>
                        
                    </select>
                </div> 

                <div class="col-md-2">
                    <h5 class="box-title">Search</h5>
                    <input type="submit" value="Search"class="btn btn-success" name="s" onclick="mpo_deposit_request();">

                </div>
                <div class="col-sm-12 col-lg-12"><?php show_msg();?></div>
                <div class="col-sm-12 col-lg-12" id="reportArea" style="overflow: auto;"></div>
            </div>
        </div>
    </div>
</div>
<script>
    function mpo_deposit_request(){
        let user_id=$('#user_id').val();
        let base_id=$('#base_id').val();
        let status=$('#status').val();
        ajax_report_request({mpo_deposit_request:1,user_id:user_id,base_id:base_id,status:status});
    } 
    $(document).ready(function(){
        const report_data = JSON.parse(localStorage.getItem('mpo_deposit_request'));
        if(report_data){
            if(report_data.hasOwnProperty('dRange')){
                $('#dRange').val(report_data.dRange);
                if(report_data.user_id>0){
                    $('#user_id').val(report_data.user_id);
                }
                if(report_data.base_id>0){
                    $('#base_id').val(report_data.base_id);
                }
                
                if(report_data.status!=-1){
                    $('#status').val(report_data.status);
                }
                select2Call();
            }
        }
        mpo_deposit_request();
    });

   


    function mpo_deposit_action({id,type}){
        const buttonId = `action_btn_${type}_${id}`;
        console.log(buttonId);
        const title = type === 0 ? 'Cancel' : 'Accept';
        buttonLoading(buttonId);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{mpo_deposit_action:1,id:id,type:type},
            success:function(data){
                button_loading_destroy(buttonId,title);
                if(typeof(data.status)){
                    if(data.status==1){
                        mpo_deposit_request()
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    swMessage(AJAX_ERROR_MESSAGE); 
                }

            },
            error:function(data){
                button_loading_destroy(buttonId,title);
                swMessage(AJAX_ERROR_MESSAGE); 
            }
        });

    }  

</script>