<div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title"><?php echo $rModule['name'];?></h4>
    </div>
    <?php
    $data = array($pUrl=>$rModule['name']);$general->breadcrumb($data);
    ?>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <?php show_msg();?>
            <div class="col-sm-2 small_image" id="pr_branch">
                <script type="text/javascript">
                    $("input[name=pr_f_group]:radio").change(function(){
                        $('#permissions_set').html('Click permission');
                    });
                    function set_permissions(roleId){
                            $('#permissions_set').html(loadingImage);
                            $.post(ajUrl,{get_premessions:roleId},function(data){
                                $('#permissions_set').html(data);
                            });
                    }
                </script>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th style="width: 30px;">Permission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                            $userGroup=$db->allGroups();

                            foreach($userGroup as $ug){
                            ?>
                                <tr>
                                    <td><?=$ug['name']?></td>
                                    <td>
                                        <a href="javascript:void()">
                                            <button class="btn btn-xs btn-info" onclick="set_permissions('<?=$ug['id']?>')">Permission</button>
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-5 small_image" id="permissions_set" style="overflow: auto;">Click permission</div>
            </div>
        </div>
    </div>
</div>