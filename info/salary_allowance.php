<?php
$aStatus      = $db->permission(37);
$eStatus      = $db->permission(38);
$tPTbl          = 'salary_allowance';
$tpID           = 'id';
$tpTitle        = 'title';
$pageTitle      = 'Salary Allowance';
$titleFieldName = 'Allowance Title';
$heads=$db->selectAll('a_ledgers', 'where system_head =0 order by code asc','id,title,code',$general->showQuery()); 
$general->arrayIndexChange($heads);
if(isset($_GET['add'])){
    if(!$aStatus){$general->redirect($pUrl,146,'add salary allowance');}
    $data = array($pUrl=>$pageTitle,'1'=>'Add');
    $general->pageHeader('Add '.$rModule['name'],$data);

    if(isset($_POST['add'])){
        $title= $_POST["title"];
        $ledger_id = intval($_POST['ledger_id']);
        if(empty($title)){setMessage(36,$titleFieldName);$error=1;}
        if($ledger_id>0 && !isset($heads[$ledger_id])){
            $error=fl();
            setMessage(1,'Invalid Ledger');
        }
        if(!isset($error)){
            $data = array(
                $tpTitle=> $title,
                'ledger_id'=>$ledger_id
            );
            $db->arrayUserInfoAdd($data);
            $insert = $db->insert("salary_allowance",$data);
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
                                    $general->inputBoxSelect($heads,'Ledger','ledger_id','id','title');
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
    $s = $db->get_rowData("salary_allowance",$tpID,$edit);
    $general->arrayContentShow($s);
    if(empty($s)){$general->redirect($pUrl,array(37,$pageTitle));}
    if(isset($_POST['edit'])){
        $title= $_POST[$tpTitle];
        $ledger_id = intval($_POST['ledger_id']);
        if(empty($title)){SetMessage(36,$titleFieldName);$error=1;}
        if($ledger_id>0 && !isset($heads[$ledger_id])){
            $error=fl();
            setMessage(1,'Invalid Ledger');
        }
        if(!isset($error)){ 
            $data = array(
                $tpTitle        => $title,
                'ledger_id'=>$ledger_id
            );
            $db->arrayUserInfoEdit($data);
            $where = array($tpID=>$edit);
            $update = $db->update("salary_allowance",$data,$where);  
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
                                    $general->inputBoxSelect($heads,'Ledger','ledger_id','id','title',$s['ledger_id']);
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

?>

<div class="row">
    <div class="col-sm-12">
        <div class="white-box border-box">
            <?php
            show_msg();

            $category=$db->selectAll("salary_allowance",'order by '.$tpTitle.' asc'); 
            ?>
            <table class="table table-striped table-bordered table-hover only_show">
                <thead>
                    <tr>
                        <th><?= $db->l('sn') ?> </th>
                        <th><?= $titleFieldName;?></th>
                        <th><?= $db->l('ledger');?></th>
                        <?php
                        if($eStatus){
                        ?>
                            <th><?= $db->l('edit') ?> </th>
                            <th><?= $db->l('status') ?> </th>
                        <?php
                        }
                        ?>
                    </tr>
                </thead> 
                <tbody>
                    <?php
                    $total=1;
                    foreach($category as $c){
                    ?>
                        <tr>
                            <td><?=$total++?></td>
                            <td><?=$c[$tpTitle]?></td>
                            <td><?=$heads[$c['ledger_id']]['title']??''?></td>
                            <?php
                            if($eStatus){
                            ?>
                                <td><a href="<?=$pUrl?>&edit=<?=$c[$tpID]?>" class="btn btn-info"><?= $db->l('edit') ?> </a>
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
}
$general->onclickChangeJavaScript("salary_allowance",$tpID);

?>