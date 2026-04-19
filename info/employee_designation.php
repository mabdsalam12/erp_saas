<?php
$aStatus      = $db->permission(37);
$eStatus      = $db->permission(38);
$tPTbl          = 'employee_designation';
$tpID           = 'id';
$tpTitle        = 'title';
$pageTitle      = 'Employee Designation';
$titleFieldName = 'Designation Title';
if(isset($_GET['add'])){
    if(!$aStatus){$general->redirect($pUrl,146,'add employee designation');}
    $data = array($pUrl=>$pageTitle,'1'=>'Add');
    $general->pageHeader('Add '.$rModule['name'],$data);

    if(isset($_POST['add'])){
        $title= $_POST["title"];
        if(empty($title)){setMessage(36,$titleFieldName);$error=1;}
        if(!isset($error)){
            $data = array(
                $tpTitle=> $title
            );
            $db->arrayUserInfoAdd($data);
            $insert = $db->insert('employee_designation',$data);
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
    $edit = intval($_GET['edit']);
    $s = $db->get_rowData('employee_designation',$tpID,$edit);
    $general->arrayContentShow($s);
    if(empty($s)){$general->redirect($pUrl,array(37,$pageTitle));}
    if(isset($_POST['edit'])){
        $title= $_POST[$tpTitle];
        if(empty($title)){SetMessage(36,$titleFieldName);$error=1;}
        if(!isset($error)){ 
            $data = array(
                $tpTitle        => $title,
            );
            $db->arrayUserInfoEdit($data);
            $where = array($tpID=>$edit);
            $update = $db->update('employee_designation',$data,$where);  
            if($update){
                $general->redirect($pUrl,30,$pageTitle);
            }
            else{$error=fl();SetMessage(66);}
        }
    }
    $data = array($pUrl=>$pageTitle,'javascript:void()'=>$s[$tpTitle],'1'=>'Edit');
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
                                    $general->inputBoxText($tpTitle,'Title',$s[$tpTitle],'y');
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
    $data = array($pUrl=>$pageTitle);
    $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));
}
?>

<div class="row">
    <div class="col-sm-12">
        <div class="white-box border-box">
            <?php
            show_msg();

            $categorys=$db->selectAll('employee_designation','order by '.$tpTitle.' asc'); 
            ?>
            <table class="table table-striped table-bordered table-hover only_show">
                <thead>
                    <tr>
                        <th><?php echo $db->l('sn') ?> </th>
                        <th><?php echo $titleFieldName;?></th>
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
                            ?>
                                <td><a href="<?=$pUrl?>&edit=<?=$c[$tpID]?>" class="btn btn-info"><?php echo $db->l('edit') ?> </a>
                                </td>
                                <td><?php $general->onclickChangeBTN($c[$tpID],$general->checked($c['isActive']));?></td>
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
$general->onclickChangeJavaScript($tPTbl,$tpID);

?>