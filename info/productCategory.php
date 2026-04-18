<?php
$aStatus      = true;
$eStatus      = true;
$pageTitle      = $rModule['title'];
$titleFieldName = 'Title';
$companyID=$cmp->getCurrentCompanyID();
if($companyID>0){
    $categorys=$db->selectAll('product_category','where company_Id='.$companyID.' order by name asc'); 
    if(isset($_GET['add'])){
        if(!$aStatus){$general->redirect($pUrl,146,'add '.$rModule['title']);}
        $data = array($pUrl=>$pageTitle,'1'=>'Add');
        $general->pageHeader('Add '.$rModule['title'],$data);

        if(isset($_POST['add'])){
            $name= $_POST["name"];
            $parent= intval($_POST["parent"]);
            if(empty($name)){setMessage(36,'Name');$error=1;}
            if(!isset($error)){
                $data = array(
                    'name'=> $name,
                    'parent'=> $parent,
                    'company_id'=> $companyID
                );
                $db->arrayUserInfoAdd($data);
                $insert = $db->insert('product_category',$data);
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
                                        $general->inputBoxText('name','Name',@$_POST['name'],'y');
                                        ?>
                                    </div>
                                    <div class="col-sm-4">
                                        <?php
                                        $general->inputBoxSelect($categorys,'Parent','parent','id','name',@$_POST['parent']);
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
        $s = $db->get_rowData('product_category','id',$edit);
        $general->arrayContentShow($s);
        if(empty($s)){$general->redirect($pUrl,array(37,$pageTitle));}
        if(isset($_POST['edit'])){
            $name= $_POST['name'];
            $parent= intval($_POST["parent"]);
            if(empty($name)){SetMessage(36,'Name');$error=1;}
            if(!isset($error)){ 
                $data = array(
                    'name'        => $name,
                    'parent'=> $parent
                );
                $where = array('id'=>$edit);
                $update = $db->update('product_category',$data,$where);  
                if($update){
                    $general->redirect($pUrl,30,$pageTitle);
                }
                else{$error=fl();SetMessage(66);}
            }
        }
        $data = array($pUrl=>$pageTitle,'javascript:void()'=>$s['name'],'1'=>'Edit');
        $general->pageHeader('Edit '.$rModule['title'],$data);
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
                                        $general->inputBoxText('name','Name',$s['name'],'y');
                                        ?>
                                    </div>
                                    <div class="col-sm-4">
                                        <?php
                                        $general->inputBoxSelect($categorys,'Parent','parent','id','name',$s['parent']);
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
        $general->pageHeader($rModule['title'],$data,$general->addBtnHtml($pUrl));
    }
    $parentCategorys =  $categorys;
    $general->arrayIndexChange($parentCategorys,'id');
    ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <?php
                show_msg();

                
                ?>
                <table class="table table-striped table-bordered table-hover only_show">
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th><?php echo $titleFieldName;?></th>
                            <th>Parent</th>
                            <?php
                            if($eStatus){
                            ?>
                                <th>Edit</th>
                                <th>Status</th>
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
                                <td><?=$c['name']?></td>
                                <td><?=@$parentCategorys[$c['parent']]['name']?></td>
                                <?php
                                if($eStatus){
                                ?>
                                    <td><a href="<?=$pUrl?>&edit=<?=$c['id']?>" class="btn btn-info">Edit </a>
                                    </td>
                                    <td><?php $general->onclickChangeBTN($c['id'],$general->checked($c['isActive']));?></td>
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
?>