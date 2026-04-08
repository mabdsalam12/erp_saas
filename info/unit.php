<?php
    //        $shIn           = $db->permission(62,'all');
    $aStatus      = $db->permission(37);
    $eStatus      = $db->permission(38);
    $tPTbl          = 56;
    $tpID           = 'id';
    $tpTitle        = 'title';
    $pageTitle      = 'Units';
    $titleFieldName = 'Unit Title';
    
    if(isset($_GET['edit'])){
        $edit = intval($_GET['edit']);
        $u = $db->get_rowData('unit',$tpID,$edit);
        $general->arrayContentShow($u);
        if(empty($u)){$general->redirect($pUrl,array(37,$pageTitle));}
        if(isset($_POST['edit'])){
            $unTitle= $_POST["unTitle"]; 
            if(empty($unTitle)){SetMessage(36,$titleFieldName);$error=1;}
                if(!isset($error)){ 
                $data = array(
                    $tpTitle        => $unTitle
                );
                $db->arrayUserInfoEdit($data);
                $where = array($tpID=>$edit);
                $update = $db->update('unit',$data,$where);  
                if($update){
                    $general->redirect($pUrl,30,$pageTitle);
                }
                else{$error=fl();SetMessage(66);}
            }
        }
        
        $data = array($pUrl=>$pageTitle,'javascript:void()'=>$u[$tpTitle],'1'=>'Edit');
        $general->pageHeader('Edit '.$rModule['title'],$data);
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                   <?php $general->inputBoxText('unTitle',$pageTitle,$u[$tpTitle],'y','','');?>
                            </div>
                            <div class="clearfix visible-xs"></div>
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                <div class="form-group m-b-0">
                                    <div class="pull-right m-t-5">
                                        <input type="submit" name="edit" value="Update" class="btn btn-info waves-effect waves-light">
                                    </div>
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
        if(isset($_POST['add'])){
            $unTitle= $_POST['unTitle'];
            if(empty($unTitle)){SetMessage(36,$titleFieldName);$error=1;}
            
            if(!isset($error)){
                $data = array(
                    $tpTitle    => $unTitle
                );
                $db->arrayUserInfoAdd($data);
                $insert = $db->insert('unit',$data);
                if($insert){$general->redirect($pUrl,29,$pageTitle);}
                else{
                    $error=fl();SetMessage(66);
                }
            }
        }
        $data = array($pUrl=>$pageTitle);
        $general->pageHeader($rModule['title'],$data);
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                   <?php $general->inputBoxText('unTitle',$pageTitle,@$_POST['unTitle'],'y','','');?>  
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                  
                            </div>
                            
                            <div class="clearfix visible-xs"></div>
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                <div class="form-group m-b-0">
                                    <div class="pull-right m-t-5">
                                        <input type="submit" name="add" value="Add" class="btn btn-info waves-effect waves-light">
                                    </div>
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
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <?php
                show_msg();
               
                    $units=$db->selectAll('unit','order by '.$tpTitle.' asc');  
            ?>
            <div class="col-md-5">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Edit</th>
                        <th>Status</th>
                    </tr>
                </thead> 
                <tbody>
                    <?php
                        $total=1;;
                        foreach($units as $u){
                        ?>
                        <tr>
                            <td><?=$total++?></td>
                            <td><?=$u[$tpTitle]?></td>
                            <td><a href="<?=$pUrl?>&edit=<?=$u[$tpID]?>" class="btn btn-info">Edit</a>
                            </td>
                            <td><?php $general->onclickChangeBTN($u[$tpID],$general->checked($u['isActive']));?></td>
                        </tr>
                        <?php
                        }
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<?php
    $general->onclickChangeJavaScript('unit',$tpID);
?>