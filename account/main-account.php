<?php
    $types=[
       DEBIT=> [
            'id'=>DEBIT,
            'title'=>'Debit'
        ],
        CREDIT=>[
            'id'=>CREDIT,
            'title'=>'Credit'
        ]
    ];
    
    if(isset($_GET['add'])){
        $data = [$pUrl=>$rModule['name'],1=>'Add'];
        $general->pageHeader('Add '.$rModule['name'],$data);
        if(isset($_POST['add'])){
            $title  = $_POST["title"];
            $code  = $_POST["code"];
            $type   = intval($_POST["type"]);
            if(empty($title)){setMessage(36,'Title');$error=fl();}
            elseif(empty($code)){setMessage(36,'Code');$error=fl();}
            elseif(!isset($types[$type])){
                setMessage(63,'type');$error=fl();
            }
            if(!isset($error)){
                $data=[
                    'title' => $title,
                    'code'  => $code,
                    'type'  => $type
                ];
                $db->arrayUserInfoAdd($data);
                $insert=$db->insert('a_master_account',$data);
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
                                    <?php $general->inputBoxSelect($types,'Type','type','id','title',@$_POST['type'],'y');?>
                                    <?php echo $general->addBtn();?>
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
        // if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
        $id = intval($_GET['edit']);
        $c = $db->get_rowData('a_master_account','id',$id);
        if(empty($c)){$general->redirect($pUrl,37,$rModule['name']);}
        
        if(isset($_POST['edit'])){
            $title  = $_POST["title"];
            $code  = $_POST["code"];
            $type   = intval($_POST["type"]);
            if(empty($title)){setMessage(36,'Title');$error=fl();}
            elseif(empty($code)){setMessage(36,'Code');$error=fl();}
            elseif(!isset($types[$type])){
                setMessage(63,'type');$error=fl();
            }
            $data=[
                'title'  => $title,
                'code'  => $code,
                'type'   => $type
            ];
            $db->arrayUserInfoEdit($data);
            $update=$db->update('a_master_account',$data,['id'=>$id]);
            if($update){
                $general->redirect($pUrl,29,$rModule['name']);
            }
            else{$error=fl(); setMessage(66);}
        }
        $data = [$pUrl=>$rModule['name'],1=>'Edit'];
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
                                    <?php $general->inputBoxSelect($types,'Type','type','id','title',$c['type'],'y');?>
                                    <?php echo $general->editBtn();?>
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
        $data = [$pUrl=>$rModule['name']];
        $general->pageHeader($rModule['name'],$data,$general->addBtnHtml($pUrl));
        $master_account = $db->selectAll('a_master_account','order by code');
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
                            <th>Type</th>
                            <th>Edit</th>
                        </tr>
                    </thead> 
                    <?php
                        if(!empty($master_account)){
                    ?>
                    <tbody>
                        <?php
                        $sr=1;
                            foreach($master_account as $b){
                                ?>
                                <tr>
                                    <td><?=$sr++?></td>
                                    <td><?=$b['code']?></td>
                                    <td><?=$b['title']?></td>
                                    <td><?=$types[$b['type']]['title']?></td>
                                    <td><a href="<?=$pUrl?>&edit=<?=$b['id']?>" class="btn btn-info">Edit</a></td>
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

