<?php
$tPTbl          = 39;
$tpID           = 'agID';
$tpTitle        = 'title';
$pageTitle      = 'Admin Group';
$titleFieldName = 'Title';
$data = array(1=>$rModule['title']);
$general->pageHeader($rModule['title'],$data);

$aStatus    = $db->permission(25);
$eStatus    = $db->permission(26);
if(isset($_GET['edit'])){
    if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
    $edit = intval($_GET['edit']);
    $s = $db->get_rowData($general->table($tPTbl),$tpID,$edit);
    if(empty($s)){$general->redirect($pUrl,array(37,$pageTitle));}
    $general->arrayContentShow($s);
    
    if(isset($_POST['edit'])){
        $title= $_POST["title"]; 
        if(empty($title)){SetMessage(36,$titleFieldName);$error=fl();}
        
        if(!isset($error)){ 
            $data = array(
                $tpTitle        => $title
            );
            $db->arrayUserInfoEdit($data);
            $where = array($tpID=>$edit);
            $update = $db->update($general->table($tPTbl),$data,$where);  
            if($update){
                $general->redirect($pUrl,30,$pageTitle);
            }
            else{$error=fl();SetMessage(66);}
        }
    }
?>
    <div class="row">
        <div class="col-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box">
                            <?php show_msg();?>
                            <form method="post" action="">
                                <div class="form-group row">
                                    <label for="title" class="col-md-2 col-form-label">Title: <span>*</span></label>
                                    <div class="col-md-4">
                                        <input type="text" id="title" required="required" class="form-control" value="<?=$s[$tpTitle]?>" name="title">
                                    </div>
                                </div>
                                <div class="col-md-12 pro_inp">
                                    <label>&nbsp;</label>
                                    <div class="col-md-5"><input class="btn btn-success" type="submit" name="edit" value="<?php echo $db->l('save') ?> "></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
else{
    if($aStatus){
        if(isset($_POST['add'])){
            $title= $_POST["title"];
            if(empty($title)){SetMessage(36,$titleFieldName);$error=1;}




            if(!isset($error)){
                $data = array(
                    $tpTitle=> $title
                );
                $db->arrayUserInfoAdd($data);
                $insert = $db->insert('user_group',$data);
                if($insert){$general->redirect($pUrl,29,$pageTitle);}
                else{
                    $error=fl();SetMessage(66);
                }
            }
        }
    ?>
        <form method="post" action="">
            <div class="row">
                <div class="col-12">
                    <div class="white-box border-box">
                        <div><?php show_msg();?></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label for="title" class="col-2 col-form-label">Title</label>
                                    <div class="col-10">
                                        <input class="form-control" value="<?php echo @$_POST['title'];?>" id="title" type="text" name="title" required="required" placeholder="Group Title">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 ">
                                <div class="form-group m-b-0">
                                    <div class="col-md-12">
                                        <input type="submit" class="btn btn-info waves-effect waves-light pull-right" value="<?php echo $db->l('save') ?> " name="add">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
<?php
    }
}
?>
<?php show_msg();?>
<?php
$categorys=$db->allGroups('order by title asc');
?>

<div class="row">
    <div class="col-12">
        <div class="white-box border-box">
            <table class="table table-striped table-bordered table-hover only_show">
                <thead>
                    <tr>
                        <th><?php echo $db->l('sn') ?> </th>
                        <th>Title</th>
                        <?php
                        if($eStatus){
                        ?>
                            <th><?php echo $db->l('edit') ?> </th>
                            <th><?php echo $db->l('status') ?> </th>
                        <?php
                        }
                        ?>
                    </tr>
                </thead> 
                <tbody>
                    <?php
                    $total=1;
                    foreach($categorys as $c){
                    ?>
                        <tr>
                            <td><?=$total++?></td>
                            <td><?=$c[$tpTitle]?></td>
                            <?php
                            if($eStatus){
                                if($eStatus){
                            ?>
                                    <td><a href="<?=$pUrl?>&edit=<?=$c[$tpID]?>" class="btn btn-info"><?php echo $db->l('edit') ?> </a></td>
                                    <td><?php $general->onclickChangeBTN($c[$tpID],$general->checked($c['isActive']));?></td>
                                    <?php
                                }
                                else{
                                    ?>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                            <?php
                                }
                            }
                            ?>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$general->onclickChangeJavaScript($tPTbl,$tpID);
?>