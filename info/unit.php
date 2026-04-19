<?php
$companyID=$cmp->getCurrentCompanyID();
if($companyID>0){
    //        $shIn           = $db->permission(62,'all');
    $aStatus      = $db->permission(37);
    $eStatus      = $db->permission(38);
    $pageTitle      = $rModule['name'];
    
    if(isset($_GET['edit'])){
        $edit = intval($_GET['edit']);
        $u = $db->get_rowData('unit','id',$edit);
        $general->arrayContentShow($u);
        if(empty($u)){$general->redirect($pUrl,array(37,$pageTitle));}
        if(isset($_POST['edit'])){
            $name= $_POST["name"]; 
            if(empty($name)){SetMessage(36,$titleFieldName);$error=1;}
                if(!isset($error)){ 
                $data = [
                    'name'        => $name
                ];
                $db->arrayUserInfoEdit($data);
                $where = array('id'=>$edit);
                $update = $db->update('unit',$data,$where);  
                if($update){
                    $general->redirect($pUrl,30,$pageTitle);
                }
                else{$error=fl();SetMessage(66);}
            }
        }
        
        $data = array($pUrl=>$pageTitle,'javascript:void()'=>$u['name'],'1'=>'Edit');
        $general->pageHeader('Edit '.$rModule['name'],$data);
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                   <?php $general->inputBoxText('name',$pageTitle,$u['name'],'y','','');?>
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
            $name= $_POST['name'];
            if(empty($name)){SetMessage(36,$titleFieldName);$error=1;}
            
            if(!isset($error)){
                $data = [
                    'name'    => $name,
                    'company_id'    => $companyID
                ];
                $db->arrayUserInfoAdd($data);
                $insert = $db->insert('unit',$data);
                if($insert){$general->redirect($pUrl,29,$pageTitle);}
                else{
                    $error=fl();SetMessage(66);
                }
            }
        }
        $data = array($pUrl=>$pageTitle);
        $general->pageHeader($rModule['name'],$data);
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-lg-12"><?php show_msg();?></div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <form method="post" action="">
                            <div class="col-xs-6 col-sm-4 col-md-4">
                                   <?php $general->inputBoxText('name',$pageTitle,@$_POST['name'],'y','','');?>  
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
               
                    $units=$db->selectAll('unit','where company_id='.$companyID.' order by name asc');  
            ?>
            <div class="col-md-5">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Edit</th>
                    </tr>
                </thead> 
                <tbody>
                    <?php
                        $total=1;;
                        foreach($units as $u){
                        ?>
                        <tr>
                            <td><?=$total++?></td>
                            <td><?=$u['name']?></td>
                            <td><a href="<?=$pUrl?>&edit=<?=$u['id']?>" class="btn btn-info">Edit</a></td>
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
}
?>