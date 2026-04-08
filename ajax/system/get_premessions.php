<?php
if($db->modulePermission(132)==true){
    $group_id   = intval($_POST['get_premessions']);
    $ug=$db->adminGroupInfoByID($group_id);
    if(empty($ug)){SetMessage(63,'Group');$error=fl();}
    if(!isset($error)){
        $pcStatus= $db->permission(PER_CHANGE_PER_STATUS);
        $q=array();$pq=array();
        if(GROUP_ID!=SUPERADMIN_USER){
            
            if(!empty($onlyDevleperMenu)){
                $q[]='id not in('.implode(',',$onlyDevleperMenu).') ';
            }
            if(!empty($onlyDevleperPermission)){
                $pq[]='id not in('.implode(',',$onlyDevleperPermission).') ';
            }
        }
        $groups=$db->allGroups();
?>
        <p>Permission <br>for <b><?=$ug['title']?></b> Group</p>
        <style type="text/css">.mback{background-color: #9AB8CF;}.mback:hover{background-color: #9AB8CF !important;}</style>
        <div class="report_table">
            <?php
            if(GROUP_ID==SUPERADMIN_USER){//যদি সবার জন্য দেই তাহলে অনেক ঝামেলা আছে। অনেক কাজ করা লাগবে
            ?>
                <div class="col-md-12">
                    Copy From
                    <select id="cugID">
                        <option value="">Copy From</option>
                        <?php
                        foreach($groups as $cg){
                        ?><option value="<?php echo $cg['id'];?>"><?php echo $cg['title'];?></option><?php
                        }
                        ?>
                    </select>
                    <a href="javascript:void();" class="btn btn-success" onclick="copyPermissionFrom()">Save</a>
                </div>
            <?php
            }
            ?>
            <table class="table table_fixed_header">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Module</th>
                        <th>Permission</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 1;
                    $query='';

                    $dq=$q;
                    $dq[]='parent=0';
                    $query='where '.implode(' and ',$dq);
                    $mod   = $db->selectAll('module',$query.' order by sequence asc');
                    foreach($mod as $m){
                        if(!$db->modulePermission($m['id'])){continue;}
                        $mq=$pq;
                        $mq[]='module_id='.$m['id'];
                        $query='where '.implode(' and ',$mq);

                        $pers = $db->selectAll('user_permissions',$query);
                        $d = $db->getRowData('module_permission','where module_id='.$m['id'].' and group_id='.$group_id);
                        if(empty($d)){$check=0;}else{$check=1;}
                    ?>
                        <tr class="mback">
                            <td><b><?=$total++?></b></td>
                            <td><?=$m['title']?>(<?=$m['id']?>)</td>
                            <td>&nbsp;</td>
                            <td>
                                <?php
                                if($pcStatus==true){

                                ?>
                                    <input type="checkbox" class="check_box" <?=$general->checked($check)?>
                                        onclick="change_permission('<?=$m['id']?>',this.checked,'m');"
                                        id="act_m_<?=$m['id']?>"
                                        name="act_m_<?=$m['id']?>">
                                    <label for="act_m_<?=$m['id']?>"></label>
                                <?php
                                }else{echo $check==1?'Active':'Deactive';}
                                ?>
                            </td>
                        </tr>
                        <?php
                        foreach($pers as $p){
                            if(!$db->permission($p['id'])){continue;}
                            if($pcStatus==true){
                                $d = $db->getRowData('user_permission_assign',' where permission_id='.$p['id'].' and group_id='.$group_id);
                                if(empty($d)){$check=0;}else{$check=1;}
                            }
                        ?>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td><?=$p['description']?>(<?=$p['id']?>)</td>
                                <td>
                                    <?php
                                    if($pcStatus==true){
                                    ?>
                                        <input type="checkbox" class="check_box" <?=$general->checked($check)?>
                                            onclick="change_permission('<?=$p['id']?>',this.checked,'p');"
                                            id="act_<?=$p['id']?>"
                                            name="act_<?=$p['id']?>">
                                        <label for="act_<?=$p['id']?>"></label>
                                    <?php
                                    }else{echo $check==1?'Active':'Deactive';}
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        $dq=$q;
                        $dq[]='parent='.$m['id'];
                        $query='where '.implode(' and ',$dq);
                        
                        $mod   = $db->selectAll('module',$query.' order by sequence asc');
                        if(!empty($mod)){
                            foreach($mod as $m){
                                if(!$db->modulePermission($m['id'])){continue;}
                                $mq=$pq;
                                $mq[]='module_id='.$m['id'];
                                $query='where '.implode(' and ',$mq);

                                $pers = $db->selectAll('user_permissions',$query);
                                $d = $db->getRowData('module_permission','where module_id='.$m['id'].' and group_id='.$group_id);
                                if(empty($d)){$check=0;}else{$check=1;}
                            ?>
                                <tr class="mback">
                                    <td><b><?=$total++?></b></td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;| - - <?=$m['title']?>(<?=$m['id']?>)</td>
                                    <td>&nbsp;</td>
                                    <td>
                                        <?php
                                        if($pcStatus==true){
                                        ?>
                                            <input type="checkbox" class="check_box" <?=$general->checked($check)?>
                                                onclick="change_permission('<?=$m['id']?>',this.checked,'m');"
                                                id="act_m_<?=$m['id']?>"
                                                name="act_m_<?=$m['id']?>">
                                            <label for="act_m_<?=$m['id']?>"></label>
                                        <?php
                                        }else{echo $check==1?'Active':'Deactive';}
                                        ?>
                                    </td>
                                </tr>
                                <?php
                                foreach($pers as $p){
                                    if(!$db->permission($p['id'])){continue;}
                                    if($pcStatus==true){
                                        $d = $db->getRowData('user_permission_assign',' where permission_id='.$p['id'].' and group_id='.$group_id);
                                        if(empty($d)){$check=0;}else{$check=1;}
                                    }
                                ?>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;&nbsp;&nbsp;&nbsp;| - - </td>
                                        <td><?=$p['description']?>(<?=$p['id']?>)</td>
                                        <td>
                                            <?php
                                            if($pcStatus==true){
                                            ?>
                                                <input type="checkbox" class="check_box" <?=$general->checked($check)?>
                                                    onclick="change_permission('<?=$p['id']?>',this.checked,'p');"
                                                    id="act_<?=$p['id']?>"
                                                    name="act_<?=$p['id']?>">
                                                <label for="act_<?=$p['id']?>"></label>
                                            <?php
                                            }else{echo $check==1?'Active':'Deactive';}
                                            ?>
                                        </td>
                                    </tr>
                    <?php
                                }
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <script type="text/javascript">
            function change_permission(permission_id,perStatus,type){
                if(perStatus==true){var ch=1;}else{var ch=0;}
                $.post(ajUrl,{change_premessions:'<?=$group_id?>',per:permission_id,st:ch,type:type},function(data){

                });
            }
            function copyPermissionFrom(){
                var group_id=parse_int($('#cugID').val());
                var sbID=parse_int($('#sbID').val());
                if(group_id>0){
                    if(confirm('Are you sure to copy permission?')){
                        $.post(ajUrl,{copyPermissionFrom:'<?php echo $group_id;?>',source:group_id,sbID:sbID},function(data){
                            if(data.status==1){
                                $('#permissions_set').html('All permission coppied.');
                            }
                        });
                    }
                }else{alert('Invalid source');}
            }
        </script>
<?php
    }
}else{
    SetMessage(52,'Privilege');
}