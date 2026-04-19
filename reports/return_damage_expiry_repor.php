<?php
    $general->pageHeader($rModule['name']);
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
                        <button class="btn btn-success" onclick="return_damage_expiry_repor()">Search</button>
                        <script type="text/javascript">
                            function return_damage_expiry_repor(){
                                let dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage);
                                const return_damage_expiry_repor_data={return_damage_expiry_repor:1,dRange:dRange};
                                localStorage.setItem('return_damage_expiry_repor_data', JSON.stringify(return_damage_expiry_repor_data));
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:return_damage_expiry_repor_data,
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
                                const report_data = JSON.parse(localStorage.getItem('return_damage_expiry_repor_data'));
                                if(report_data&&report_data.hasOwnProperty('dRange')){
                                    $('#dRange').val(report_data.dRange);
                                    select2Call();
                                }
                                return_damage_expiry_repor();
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
<?php
include_once ROOT_DIR.'/common/details_modal.php';