<?php

     $base = $db->selectAll('base');
        $general->arrayIndexChange($base,'id');
    if(isset($_GET['add'])){
        
        $data = array($pUrl=>$rModule['title'],1=>'Add');
        $general->pageHeader('Add '.$rModule['title'],$data);
        if(isset($_POST['add'])){
            $base_id = intval($_POST['base_id']);
            $title=$_POST['title'];

            if($base_id<1){$error=fl(); setMessage(36,'base');}
            else if(!isset($base[$base_id])){$eror=fl(); setMessage(63,'base');}
                else if(empty($title)){$eror=fl(); setMessage(36,'title');}
                    else{
                        $data=[
                            'base_id'=>$base_id,
                            'title'=>$title,
                        ];
                        $db->arrayUserInfoAdd($data);
                        $insert=$db->insert('bazar',$data);
                        if(!$insert){$error=fl(); setMessage(66);}
                        else{
                            $general->redirect($pUrl,29,'bazar');
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
                            <div class="col-xs-6 col-sm-4">
                                <?php $general->inputBoxSelect($base,'Base','base_id','id','title',@$_POST['base_id']);?>
                                <?php $general->inputBoxText('title','Title',@$_POST['title'],'y');?>
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
        $bazar = $db->get_rowData('bazar','id',$edit);
        if(empty($bazar)){$general->redirect($pUrl,37,$rModule['title']);}

        if(isset($_POST['edit'])){
            $base_id = intval($_POST['base_id']);
            $title=$_POST['title'];

            if($base_id<1){$error=fl(); setMessage(36,'base');}
            else if(!isset($base[$base_id])){$eror=fl(); setMessage(63,'base');}
                else if(empty($title)){$eror=fl(); setMessage(36,'title');}
                    else{
                        $data=[
                            'base_id'=>$base_id,
                            'title'=>$title,
                        ];
                        $where=['id'=>$edit];
                        $db->arrayUserInfoEdit($data);
                        $insert=$db->update('bazar',$data,$where);
                        if(!$insert){$error=fl(); setMessage(66);}
                        else{
                            $general->redirect($pUrl,29,'bazar');
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
                                    <?php $general->inputBoxSelect($base,'Base','base_id','id','title',$bazar['base_id']);?>
                                    <?php $general->inputBoxText('title','Title',$bazar['title'],'y');?>
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
    ?>
    <div class="row">
    <div class="col-sm-12">
        <div class="white-box border-box">
            <?php
                show_msg();


            ?>
            <div class="row">
                <div class="col-sm-12 col-lg-12 padding-left-0">


                    <?php $general->inputBoxSelectForReport($base,'Base','base_id','id','title'); ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <input type="submit" value="Search" class="btn btn-success" onclick="bazar_list()" name="s">

                    </div>

                </div>

                <div class="col-sm-12 col-lg-12" id="reportbazar">
                </div>

            </div>
        </div>
    </div>
    <?php
    }
?>
<script>
    $(document).ready(function(){
        bazar_list();
    });
    function bazar_list(){
        let base_id=$('#base_id').val();
        $('#reportbazar').html(loadingImage);
        $.ajax({
            type:'post',
            url:ajUrl,
            data:{bazar_list:1,base_id:base_id},
            success:function(data){
                if(typeof(data.status)!=='undefined'){
                    if(data.status==1){
                        $('#reportbazar').html(data.html);
                    }
                    swMessageFromJs(data.m);
                }
                else{
                    $('#reportbazar').html('');
                    swMessage(AJAX_ERROR_MESSAGE); 
                }
            },
            error:function(){
                $('#reportbazar').html('');
                swMessage(AJAX_ERROR_MESSAGE); 
            }


        });
    }
</script>