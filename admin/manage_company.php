<?php
$aStatus      = $db->permission(37);
$eStatus      = $db->permission(38);
if(isset($_GET['add'])){
    if(!$aStatus){$general->redirect($pUrl,146,'add employee department');}
    $data = array($pUrl=>$pageTitle,'1'=>'Add');
    $general->pageHeader('Add '.$rModule['name'],$data);

    if(isset($_POST['add'])){
        $title= $_POST["title"];
        if(empty($title)){setMessage(36,$titleFieldName);$error=1;}
        if(!isset($error)){
            $data = array(
                'name'=> $title
            );
            $db->arrayUserInfoAdd($data);
            $insert = $db->insert($tPTbl,$data);
            if($insert){$general->redirect($pUrl,29,$pageTitle);}
            else{
                $error=fl();setMessage(66);
            }
        }
    }
?>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-sm-4">
                                    <?php
                                    $general->inputBoxText('title','Title',@$_POST['title'],'y');
                                    ?>
                                </div>
                                <div class="col-sm-4">
                                <?php $general->addBtn();?>
                                </div>  

                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

}
elseif(isset($_GET['edit'])){
    if(!$eStatus){$general->redirect($pUrl,array(1,'You have no permission to edit.'));}
    $id = intval($_GET['edit']);
    $s = $cmp->getById($id);
    if(empty($s)){$general->redirect($pUrl,array(37,'company'));}
    if(isset($_POST['edit'])){
        $title= $_POST['name'];
        if(empty($title)){SetMessage(36,$titleFieldName);$error=1;}
        if(!isset($error)){ 
            $data = array(
                'name'        => $title,
            );
            $db->arrayUserInfoEdit($data);
            $where = array('id'=>$edit);
            $update = $db->update($tPTbl,$data,$where);  
            if($update){
                $general->redirect($pUrl,30,$pageTitle);
            }
            else{$error=fl();SetMessage(66);}
        }
    }
    $data = array($pUrl=>$rModule['name'],'javascript:void()'=>$s['name'],'1'=>'Edit');
    $general->pageHeader('Edit '.$rModule['name'],$data);
    ?>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-sm-4">
                                    <?php
                                    $general->inputBoxText('name','Title',$s['name'],'y');
                                    ?>
                                </div>
                                <div class="col-sm-4">
                                    <?php $general->editBtn();?>
                                </div> 

                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
else{
    $data = array($pUrl=>$rModule['name']);
    $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));
?>

<div class="row">
    <div class="col-sm-12">
        <div class="white-box border-box">
            <?php
            show_msg();

            $companyes=$db->selectAll('companys','order by name asc'); 
            ?>
            <table class="table table-striped table-bordered table-hover only_show">
                <thead>
                    <tr>
                        <th><?php echo l('sn') ?> </th>
                        <th>Name</th>
                        <?php
                        if($eStatus){
                        ?>
                            <th><?php echo l('edit') ?> </th>
                            <th><?php echo l('status') ?> </th>
                        <?php
                        }
                        ?>
                    </tr>
                </thead> 
                <tbody>
                    <?php
                    $total=1;
                    foreach($companyes as $c){
                    ?>
                        <tr>
                            <td><?=$total++?></td>
                            <td><?=$c['name']?></td>
                            <?php
                            if($eStatus){
                            ?>
                                <td><a href="<?=$pUrl?>&edit=<?=$c['id']?>" class="btn btn-info"><?php echo l('edit') ?> </a>
                                </td>
                                <td><?= $c['isActive'] ? l('active') : l('inactive') ?></td>
                            <?php
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
}