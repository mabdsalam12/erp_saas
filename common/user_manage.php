<?php
$aStatus      = $db->permission(64);
$eStatus      = $db->permission(65);
$general->pageHeader($rModule['title']);
$groups=$db->allGroups('order by title asc');
$general->arrayIndexChange($groups,'id');
$employees=$db->selectAll('employees','where isActive in(0,1) order by name asc');
$general->arrayIndexChange($employees,'id');
$user_types=$general->get_all_user_type();
$base = $db->selectAll('base');
$general->arrayIndexChange($base,'id');


$q=[];

$q[]='group_id!='.SUPERADMIN_USER;
$q[]='type in('.USER_TYPE_MANAGER.','.USER_TYPE_RSM.')';
$sq='where '.implode(' and ',$q);
$users   = $db->selectAll('users',$sq,'',$general->showQuery());
$total = 1;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <?php show_msg();?>
            <?php
            $uData=[];
            foreach($users as $u){
                $base_title = $base[$u['base_id']]['title']??'';
                $st=$u['isActive']==1?'Active':'Inactive';
                $eName='';
                if(isset($employees[$u['employee_id']])){
                    $eName=$employees[$u['employee_id']]['name'];
                }
                $a=[
                    $u['name']
                    ,$eName
                    ,$base_title
                    ,$u['mobile']
                    ,$groups[$u['group_id']]['title']
                    ,$user_types[$u['type']]['title']
                    ,$u['username']
                    ,$u['id']
                ];
                if($eStatus){
                    $a[]=$u['id'];
                }
                $uData[]=$a;
            }
            ?>
            <div class="table-responsive">
                <table id="dataTable" class="display"></table>
            </div>
            <script type="text/javascript">
                <?php
                echo 'var dataSet='.json_encode($uData).';';
                ?>
                var daTableOption;
                $(document).ready(function(){
                    if(daTableOption!=undefined){
                        daTableOption.destroy();
                    }
                    daTableOption=$('#dataTable').DataTable( {
                        data: dataSet,
                        "lengthMenu": [[100,500,1000],[100,500,1000]],
                        columns: [
                            { title: "Name"},
                            { title: "Employee"},
                            { title: "Base"},
                            { title: "Mobile"},
                            { title: "Group"},
                            { title: "Type"},
                            { title: "Username"},
                            { title: "Assign"}
                        ],
                        "createdRow": function ( row, data, index ) {
                            $('td', row).eq(7).html('<button onclick="user_assign_modal('+data[8]+')" class="btn btn-info user_assign_modal_'+data[8]+'" >Assign</button>');

                        }
                    });
                    //t(daTableOption)
                });
            </script>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="user_assign_modal" tabindex="-1" role="dialog" aria-labelledby="bigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="user_info">Base Assign</h4>
            </div>
            <div class="modal-body">
            <input type="hidden" id="assign_user_id" value="">
                <div id="assign_modal_body">
                    <!-- Modal content -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary user_manage_submit" onclick="user_manage_submit()">Submit</button>
            </div>
        </div>
    </div>
</div>


<style type="text/css">
    .modal-dialog {
        margin: 30px auto; /* Default Bootstrap margin */
        position: relative;
        top: 25%;
        transform: translateY(-50%);
    }
    .modal-body {
        max-height: calc(100vh - 200px); /* Adjust height as needed */
        overflow-y: auto;
    }

</style>
<script type="text/javascript">
    $(document).on('change','#type',function(){
        if(this.value==<?=USER_TYPE_MPO?>){
            $('#base-div').show();
        }
        else{
            $('#base-div').hide();
        }

    });   

    function user_manage_submit(){
        const user_id=$('#assign_user_id').val();
        const selected_base = $('.base-checkbox:checked').map(function() {
            return this.value;
        }).get()??[];
        buttonLoading(`user_manage_submit`);
        $.ajax({
            type: 'post',
            url: ajUrl,
            data:{base_assign:1,user_id:user_id,selected_base:selected_base},
            dataType: 'json',   // expecting JSON response
            timeout: 10000      // 10 seconds timeout
        })
        .done(function(data) {
            if (typeof data.status !== "undefined") {
                
                swMessageFromJs(data.m);
            } else {
                swMessage(AJAX_ERROR_MESSAGE);
            }
        })
        .fail(function(jqXHR, textStatus) {
            if (textStatus === "timeout") {
                swMessage("Request timed out. Please try again.");
            } else {
                swMessage(AJAX_ERROR_MESSAGE);
            }
        })
        .always(function() {
            // Runs on both success & fail
            button_loading_destroy(`user_manage_submit`,"Submit");
        });
    }

    function user_assign_modal(user_id){
        buttonLoading(`user_assign_modal_${user_id}`);
        $.ajax({
            type: 'post',
            url: ajUrl,
            data: { user_assign_data: 1, user_id: user_id },
            dataType: 'json',   // expecting JSON response
            timeout: 10000      // 10 seconds timeout
        })
        .done(function(data) {
            if (typeof data.status !== "undefined") {
                if (data.status == 1) {
                    $('#user_assign_modal').modal('show');
                    $('#assign_modal_body').html(data.html);
                    $('#assign_user_id').val(user_id);
                } else {
                    $('#assign_modal_body').html('');
                }
                swMessageFromJs(data.m);
            } else {
                $('#assign_modal_body').html('');
                swMessage(AJAX_ERROR_MESSAGE);
            }
        })
        .fail(function(jqXHR, textStatus) {
            $('#assign_modal_body').html('');
            if (textStatus === "timeout") {
                swMessage("Request timed out. Please try again.");
            } else {
                swMessage(AJAX_ERROR_MESSAGE);
            }
        })
        .always(function() {
            // Runs on both success & fail
            button_loading_destroy(`user_assign_modal_${user_id}`,"Assign");
        });
    } 
</script>
<?php

$general->onclickChangeJavaScript('users','uID');

?>
