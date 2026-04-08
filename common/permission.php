<?php
    if(GROUP_ID!=SUPERADMIN_USER){$general->redirect(URL);}

    if(isset($_GET['add'])){
        $cmID=intval($_GET['add']);
        $cm=$db->get_rowData($general->table(1),'cmID',$cmID);
        if(empty($cm)){$general->redirect($pUrl,63,'Request');}
        if(isset($_POST['add'])){
            $title=$_POST['title'];
            if(empty($title)){$error=fl();setMessage(36,'Title');}
            if(!isset($error)){
                $data=array(
                    'cmID'      => $cmID,
                    'perDesc'   => $title
                );
                $insert=$db->insert($general->table(40),$data);
                if($insert){
                    $general->redirect($pUrl,29,'Permission');
                }
                else{
                    $error=fl();setMessage(66);
                }
            }
        }
    ?>
    <h2>Add Permission for <?php echo $cm['title'];?></h2>
    <?php
        show_msg();
    ?>
    <form action="" method="POST">
        <table>
            <tr>
                <td>Title</td><td><input type="text" name="title" value="<?php echo @$_POST['title'];?>"></td>
            </tr>
            <tr>
                <td>&nbsp;</td><td><input type="submit" value="Save" name="add"></td>
            </tr>
        </table>
    </form>
    <?php
    }
    elseif(isset($_GET['edit'])){
        $perID=intval($_GET['edit']);
        $per=$db->get_rowData($general->table(40),'perID',$perID);
        if(empty($per)){$general->redirect($pUrl,63,'Request');}
        $cm=$db->get_rowData($general->table(1),'cmID',$per['cmID']);
        if(isset($_POST['edit'])){
            $title=$_POST['title'];
            if(empty($title)){$error=fl();setMessage(36,'Title');}
            if(!isset($error)){
                $data=array(
                    'perDesc'   => $title
                );
                $where=array('perID'=>$perID);
                $update=$db->update($general->table(40),$data,$where);
                if($update){
                    $general->redirect($pUrl,30,'Permission');
                }
                else{
                    $error=fl();setMessage(66);
                }
            }
        }
    ?>
    <h2>Edit Permission for <i><?php echo $cm['title'];?> -> <?php echo $per['perDesc'];?></i></h2>
    <?php
        show_msg();
    ?>
    <form action="" method="POST">
        <table>
            <tr>
                <td>Title</td><td><input type="text" name="title" value="<?php echo $per['perDesc'];?>"></td>
            </tr>
            <tr>
                <td>&nbsp;</td><td><input type="submit" value="Save" name="edit"></td>
            </tr>
        </table>
    </form>
    <?php
    }
    else{
        $mod   = $db->selectAll($general->table(1),'order by title asc');
        show_msg();
    ?>

    <div class="report_table">
        <table class="table table_fixed_header">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Module</th>
                    <th>Permission</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $total = 1;
                    foreach($mod as $m){
                        $pers = $db->selectAll($general->table(40),'where cmID='.$m['cmID']);
                    ?>
                    <tr class="mback">
                        <td><b><?=$total++?></b></td>
                        <td><?=$m['title']?> (<?=$m['cmID']?>)</td>
                        <td><a href="<?php echo $pUrl;?>&add=<?php echo $m['cmID'];?>">Add Permission</a></td>
                    </tr>
                    <?php
                        foreach($pers as $p){
                            //$general->printArray($p);
                        ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <a href="<?php echo $pUrl;?>&edit=<?php echo $p['perID'];?>"><?php echo $p['perDesc'];?> (<?php echo $p['perID']?>)</a>
                            </td>
                        </tr>
                        <?php
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>
    <?php
    }
?>
