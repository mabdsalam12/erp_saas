<?php
$c=$tkt->superadminAdd($pUrl);$coID=$c['coID'];
?>
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
                    function brandGetPermission(bID){
                        $('#permissions_set').html(loadingImage);
                        $.post(ajUrl,{brandGetPermission:1,bID:bID},function(data){
                            $('#permissions_set').html(data);
                        });

                    }
                </script>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Brand</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $brands=$db->selectAll($general->table(6),'where coID='.$coID.' order by bTitle asc');
                            foreach($brands as $b){
                            ?>
                                <tr>
                                    <td>
                                        <a href="javascript:void()">
                                            <button class="btn btn-xs btn-info" onclick="brandGetPermission('<?=$b['bID']?>')"><?php echo $b['bTitle'];?></button>
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-5 small_image" id="permissions_set" style="overflow: auto;">Click brand</div>
            </div>
        </div>
    </div>
</div>