<?php
    $users = $db->selectAll('users','where type='.USER_TYPE_MPO.' and isActive=1 ORDER BY id DESC','id,name');
    $general->pageHeader($rModule['name']);
    $products=$db->getProductData('and isActive in(0,1)');
    $base = $db->selectAll('base');
    $list_types=[
        [
            'id'=>'invoice',
            'title'=>'Invoice'
        ],
        [
            'id'=>'item',
            'title'=>'Item'
        ],
        [
            'id'=>'base',
            'title'=>'Base'
        ]
    ];
    $product_types=[
        [
            'id'=>-1,
            'title'=>'All Products'
        ],
        [
            'id'=>PRODUCT_TYPE_GIFT_ITEM,
            'title'=>'Gift'
        ],
        [
            'id'=>PRODUCT_TYPE_FINISHED,
            'title'=>'Finished'
        ]
    ];
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
                    <?php
                        $general->inputBoxSelectForReport($base,'Base','base_id','id','title');
                        $general->inputBoxSelectForReport($users,'User','user_id','id','name');
                        $general->inputBoxSelectForReport($list_types,'List type','list_type','id','title','','','',false);
                        $general->inputBoxSelectForReport($product_types,'Product type','product_type','id','title','','','',false);
                        $general->inputBoxSelectForReport($products,'Product','product_id','id','t');
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button class="btn btn-success" onclick="gift_distribute_list()">Search</button>
                        <script type="text/javascript">
                            function gift_distribute_list(){
                                let user_id   = parse_int($('#user_id').val());
                                let product_id   = parse_int($('#product_id').val());
                                let base_id   = parse_int($('#base_id').val());
                                let list_type   = $('#list_type').val();
                                let product_type = parse_int($('#product_type').val());
                                let dRange  = $('#dRange').val();
                                $('#reportArea').html(loadingImage);
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{gift_distribute_list:1,base_id:base_id,user_id:user_id,product_id:product_id,list_type:list_type,product_type:product_type,dRange:dRange},
                                    success:function(data){
                                        $('#reportArea').html('');
                                        if(data.status==1){
                                            $('#reportArea').html(data.html);
                                        }
                                        swMessageFromJs(data.m);
                                    },
                                    error:function(data){
                                        $('#reportArea').html('');
                                        swMessage(AJAX_ERROR_MESSAGE); 
                                    }
                                });
                            }
                            function gift_distribute_details_view(id){
                                $('#details-body').html(loadingImage);
                                $('#details-modal-title').html('Gift distribute details');
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{gift_distribute_details_view:1,id:id},
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
                            function gift_distribute_remove(id){
                                if(confirm('Are you sure to remove this?')){
                                $.ajax({
                                    type:'post',
                                    url:ajUrl,
                                    data:{gift_distribute_remove:1,id:id},
                                    success:function(data){
                                        $('#details-body').html('');
                                        if(typeof(data.status)!=='undefined'){
                                            if(data.status==1){
                                                gift_distribute_list();
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
                                
                            }
                            }
                            $(document).ready(function(){
                                gift_distribute_list();
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
 ?>