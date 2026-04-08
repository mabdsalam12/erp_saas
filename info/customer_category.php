<?php
    $aStatus      = true;
    $eStatus      = true;

    $tpID           = 'id';

    $districts=$db->getAllDistricts();

    if(isset($_GET['add'])){
        if(!$aStatus){$general->redirect($pUrl,146,'add customer category');}
        $data = array($pUrl=>$rModule['title'],1=>'Add');
        $general->pageHeader('Add '.$rModule['title'],$data);
        if(isset($_POST['add'])){
            $title                  = $_POST["title"];
            if(empty($title)){setMessage(36,'title');$error=fl();}
            else{
                $data=[
                    'title'=>$title
                ];
                $db->arrayUserInfoAdd($data);
                $insert=$db->insert('customer_category',$data);
                if($insert){
                    $general->redirect($pUrl,29,'Customer category');
                }
                else{$error=fl(); setMessage(66);}
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
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('title','title',@$_POST['title'],'y');?>
                                </div>

                            </div>
                            <div class="row">
                                <?php echo $general->addBtn();?>
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
        // if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
        $edit = intval($_GET['edit']);
        $c = $db->get_rowData('customer_category','id',$edit);
        if(empty($c)){$general->redirect($pUrl,37,$rModule['title']);}

        if(isset($_POST['edit'])){
            if(!$aStatus){$general->redirect($pUrl,146,'add Customer');}
            $data = array($pUrl=>$rModule['title'],1=>'Add');
            $general->pageHeader('Add '.$rModule['title'],$data);
            if(isset($_POST['edit'])){
                $title                  = $_POST["title"];
                if(empty($title)){setMessage(36,'title');$error=fl();}
                else{
                    $data=['title'=>$title];
                    $where=['id'=>$edit];
                    $db->arrayUserInfoEdit($data);
                    $update = $db->update('customer_category',$data,$where);
                    if($update){
                        $general->redirect($pUrl,30,'Customer category');
                    }
                    else{$error=fl(); setMessage(66);}
                }
            }
        }
        $data = array($pUrl=>$rModule['title'],1=>'Edit');
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
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('title','title',$c['title'],'y');?>

                                </div>

                            </div>
                            <div class="row">
                                <?php echo $general->editBtn();?>
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
        $data = array($pUrl=>$rModule['title']);
        $general->pageHeader($rModule['title'],$data,$general->addBtnHtml($pUrl));
        $banks = $db->selectAll('customer_category');
    ?>
    <div class="row">
    <div class="col-sm-12">
        <div class="white-box border-box">
            <?php
                show_msg();
            ?>
            <div class="row">
                <div class="col-sm-12 col-lg-12" id="reportArea">
                <table class="table table-striped table-bordered table-hover only_show">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>title</th>
                            <?php
                                if($eStatus){
                            ?>
                                <th>Edit</th>
                            <?php
                                }
                            ?>
                        </tr>
                    </thead> 
                    <?php
                         if(!empty($banks)){
                     ?>
                    <tbody>
                         <?php
                            $sr=1;
                             foreach($banks as $b){
                                 ?>
                                 <tr>
                                 <td><?=$sr++?></td>
                                 <td><?=$b['title']?></td>
                                 <?php
                                    if($eStatus){   
                                  ?>
                                  <td><a href="<?=$pUrl?>&edit=<?=$b['id']?>" class="btn btn-info">Edit</a></td>
                                 <?php
                                    }
                                  ?>
                                 </tr>
                                 <?php 
                             }
                         ?>
   
                    </tbody>
                    <?php
                         }
                     ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    }

