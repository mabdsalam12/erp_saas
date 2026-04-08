<?php
    $aStatus      = true;
    $eStatus      = true;

    $tpID           = 'id';

    $districts=$db->getAllDistricts();

    if(isset($_GET['add'])){
        if(!$aStatus){$general->redirect($pUrl,146,'add Bank');}
        $data = array($pUrl=>$rModule['title'],1=>'Add');
        $general->pageHeader('Add '.$rModule['title'],$data);
        if(isset($_POST['add'])){
            $name                  = $_POST["name"];
            if(empty($name)){setMessage(36,'Name');$error=fl();}
            else{
                $data=[
                    'name'=>$name
                ];
                $db->arrayUserInfoAdd($data);
                $insert=$db->insert('bank',$data);
                if($insert){
                    $general->redirect($pUrl,29,'Bank');
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
                                    <?php $general->inputBoxText('name','Name',@$_POST['name'],'y');?>
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
        $c = $db->get_rowData('bank','id',$edit);
        if(empty($c)){$general->redirect($pUrl,37,$rModule['title']);}

        if(isset($_POST['edit'])){
            if(!$aStatus){$general->redirect($pUrl,146,'add Customer');}
            $data = array($pUrl=>$rModule['title'],1=>'Add');
            $general->pageHeader('Add '.$rModule['title'],$data);
            if(isset($_POST['edit'])){
                $name                  = $_POST["name"];



                if(empty($name)){setMessage(36,'Name');$error=fl();}
                else{
                    $data=['name'=>$name];
                    $where=['id'=>$edit];
                    $db->arrayUserInfoEdit($data);
                    $update = $db->update('bank',$data,$where);
                    if($update){
                        if($c['ledger_id']>0){
                            $data=[
                                'title'=>$name
                            ];
                            $where=['id'=>$c['ledger_id']];
                            $update=$db->update('a_ledgers',$data,$where);
                            if(!$update){$error=fl();setMessage(66);}
                        }


                        $general->redirect($pUrl,30,'Bank');
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
                                    <?php $general->inputBoxText('name','Name',$c['name'],'y');?>

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
        $banks = $db->selectAll('bank');
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
                            <th>Name</th>
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
                                 <td><?=$b['name']?></td>
                                 <?php
                                    if($eStatus){   
                                  ?>
                                  <td><a href="<?=$pUrl?>&edit=<?=$b['id']?>" class="btn btn-info">Edit</a></td>
                                 <td><?php $general->onclickChangeBTN($b['id'],$general->checked($b['status']));?></td>
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
        $general->onclickChangeJavaScript('bank','id');  
    }

