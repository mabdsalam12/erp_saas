<?php
    $aStatus      = true;
    $eStatus      = true;

    $tpID           = 'id';

    $master_account=$db->selectAll('a_master_account');
    $general->arrayIndexChange($master_account,'id');

    if(isset($_GET['add'])){
        if(!$aStatus){$general->redirect($pUrl,146,'add Bank');}
        $data = [$pUrl=>$rModule['name'],1=>'Add'];
        $general->pageHeader('Add '.$rModule['name'],$data);
        if(isset($_POST['add'])){
            $title                  = $_POST["title"];
            $code  = $_POST["code"];
            $master_account_id      = intval($_POST["master_account_id"]);
            if(empty($title)){setMessage(36,'Title');$error=fl();}
            elseif(!isset($master_account[$master_account_id])){
                setMessage(63,'Master account');$error=fl();
            }
            else{
                $data=[
                    'title'=>$title,
                    'code'  => $code,
                    'master_account_id'=>$master_account_id
                ];
                $db->arrayUserInfoAdd($data);
                $insert=$db->insert('a_charts_accounts',$data);
                if($insert){
                    $general->redirect($pUrl,29,$rModule['name']);
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
                                    <?php $general->inputBoxText('code','Code',@$_POST['code'],'y');?>
                                    <?php $general->inputBoxText('title','Title',@$_POST['title'],'y');?>
                                    <?php $general->inputBoxSelect($master_account,'Master account','master_account_id','id','title',@$_POST['title'],'y');?>

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
        $c = $db->get_rowData('a_charts_accounts','id',$edit);
        if(empty($c)){$general->redirect($pUrl,37,$rModule['name']);}

        
        if(isset($_POST['edit'])){
            $title  = $_POST["title"];
            $code  = $_POST["code"];
            $master_account_id  = intval($_POST["master_account_id"]);
            if(empty($title)){setMessage(36,'Title');$error=fl();}
            else if(empty($code)){setMessage(36,'Code');$error=fl();}
            elseif(!isset($master_account[$master_account_id])){
                setMessage(63,'Master account');$error=fl();
            }
            if(!isset($error)){
                $data=[
                    'title'  => $title,
                    'code'  => $code,
                    'master_account_id'  => $master_account_id,
                ];
                $where=['id'=>$edit];
                $db->arrayUserInfoEdit($data);
                $update = $db->update('a_charts_accounts',$data,$where);
                if($update){
                    $general->redirect($pUrl,30,$rModule['name']);
                }
                else{$error=fl(); setMessage(66);}
            }
            
        }
        
        $data = array($pUrl=>$rModule['name'],1=>'Edit');
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
                                <div class="col-xs-6 col-sm-4">
                                    <?php $general->inputBoxText('code','Code',$c['code'],'y');?>
                                    <?php $general->inputBoxText('title','Title',$c['title'],'y');?>
                                    <?php $general->inputBoxSelect($master_account,'Master account','master_account_id','id','title',$c['master_account_id'],'y');?>
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
        $data = [$pUrl=>$rModule['name']];
        $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));
        $chart_accounts = $db->selectAll('a_charts_accounts','order by CAST(code AS UNSIGNED)');
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
                            <th>Code</th>
                            <th>Title</th>
                            <th>Master account</th>
                            <th>Master account code</th>
                            <th>Type</th>
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
                        if(!empty($chart_accounts)){
                    ?>
                    <tbody>
                        <?php
                        $sr=1;
                            foreach($chart_accounts as $b){
                                $m=$master_account[$b['master_account_id']];
                                ?>
                                <tr>
                                <td><?=$sr++?></td>
                                <td><?=$b['code']?></td>
                                <td><?=$b['title']?></td>
                                <td><?=$m['title']?></td>
                                <td><?=$m['code']?></td>
                                <td><?=$m['type']==DEBIT?'Debit':'Credit'?></td>
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
        $general->onclickChangeJavaScript('bank','id');  
    }

