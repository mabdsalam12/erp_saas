<?php
    $aStatus      = true;
    $eStatus      = true;

    $tpID           = 'id';

    $districts=$db->getAllDistricts();

    if(isset($_GET['add'])){
        $data = [$pUrl=>$rModule['name'],1=>'Add'];
        $general->pageHeader('Add '.$rModule['name'],$data);
        $bases = $db->allBase();
        $users = $db->allUsers();
        $months = $general->allMonth();
        $general->arrayIndexChange($months);
        $years = [];
        $s =date('Y')-2;
        $e =$s+10; 
        for($s;$s<$e;$s++){
            $years[$s]=['id'=>$s];
        }
        $base_wise_users=[];
        if(!empty($users)){
            foreach($users as $u){
                if($u['base_id']<1){continue;}
                $base_wise_users[$u['base_id']][]=$u;
            }
        }
        if(isset($_POST['add'])){
            $general->printArray($_POST);
            $base_id = intval($_POST['base_id']);
            $user_id = intval($_POST['user_id']);
            $year = intval($_POST['year']);
            $month = intval($_POST['month']);
            $sale_target = intval($_POST['sale_target']);
            $collection_target = intval($_POST['collection_target']);
            if(!isset($bases[$base_id])){setMessage(36,'base');$error=fl();}
            elseif(!isset($users[$user_id])){setMessage(36,'user');$error=fl();}
            elseif(!isset($years[$year])){setMessage(36,'year');$error=fl();}
            elseif(!isset($months[$month])){setMessage(36,'month');$error=fl();}
            elseif($sale_target<1){setMessage(36,'sale target');$error=fl();}
            elseif($collection_target<1){setMessage(36,'collection target');$error=fl();}
            else{
                $date = strtotime(date("t-$month-$year"));
                $data=[
                    'base_id'=>$base_id,
                    'user_id'=>$user_id,
                    'date'=>$date,
                    'sale_target'=>$sale_target,
                    'collection_target'=>$collection_target,
                ];
                $db->arrayUserInfoAdd($data);
                $insert=$db->insert('monthly_target',$data);
                if($insert){
                    $general->redirect($pUrl,29,'monthly target');
                }
                else{$error=fl(); setMessage(66);}
            }

        }
    ?>
    <script>
        <?php  echo 'var base_wise_users='.json_encode($base_wise_users).';'; ?>
        $(document).on('change','#base_id',function(){base_wise_user(this.value)});
    </script>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php 
                                    
                                    $general->inputBoxSelect($bases,'Base','base_id','id','title');
                                    $general->inputBoxSelect([],'User','user_id','id','name');
                                    $general->inputBoxSelect($years,'Year','year','id','id',date('Y'));
                                    $general->inputBoxSelect($months,'Month','month','id','name',date('m'));
                                    $general->inputBoxText('sale_target','Sale target',@$_POST['sale_target'],'y');
                                    $general->inputBoxText('collection_target','Collection target',@$_POST['collection_target'],'y');
                                    ?>
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
        $c = $db->get_rowData('monthly_target','id',$edit);
        if(empty($c)){$general->redirect($pUrl,37,$rModule['name']);}
        $bases = $db->allBase();
        $users = $db->allUsers();
        $months = $general->allMonth();
        $general->arrayIndexChange($months);
        $years = [];
        $s =date('Y')-2;
        $e =$s+10; 
        for($s;$s<$e;$s++){
            $years[$s]=['id'=>$s];
        }
        $base_wise_users=[];
        if(!empty($users)){
            foreach($users as $u){
                if($u['base_id']<1){continue;}
                $base_wise_users[$u['base_id']][]=$u;
            }
        }
        if(isset($_POST['edit'])){
            $general->printArray($_POST);
            $base_id = intval($_POST['base_id']);
            $user_id = intval($_POST['user_id']);
            $year = intval($_POST['year']);
            $month = intval($_POST['month']);
            $sale_target = intval($_POST['sale_target']);
            $collection_target = intval($_POST['collection_target']);
            if(!isset($bases[$base_id])){setMessage(36,'base');$error=fl();}
            elseif(!isset($users[$user_id])){setMessage(36,'user');$error=fl();}
            elseif(!isset($years[$year])){setMessage(36,'year');$error=fl();}
            elseif(!isset($months[$month])){setMessage(36,'month');$error=fl();}
            elseif($sale_target<1){setMessage(36,'sale target');$error=fl();}
            elseif($collection_target<1){setMessage(36,'collection target');$error=fl();}
            else{
                $date = strtotime(date("t-$month-$year"));
                $data=[
                    'base_id'=>$base_id,
                    'user_id'=>$user_id,
                    'date'=>$date,
                    'sale_target'=>$sale_target,
                    'collection_target'=>$collection_target,
                ];
                $db->arrayUserInfoEdit($data);
                $insert=$db->update('monthly_target',$data,['id'=>$edit]);
                if($insert){
                    $general->redirect($pUrl,30,'monthly target');
                }
                else{$error=fl(); setMessage(66);}
            }
        }
        $data = [$pUrl=>$rModule['name'],1=>'Edit'];
        $general->pageHeader('Edit '.$rModule['name'],$data);
    ?>
    <script>
        <?php  echo 'var base_wise_users='.json_encode($base_wise_users).';'; ?>
        $(document).on('change','#base_id',function(){base_wise_user(this.value)});
        $(document).ready(function(){base_wise_user(<?=$c['base_id']?>,'Select',<?=$c['user_id']?>)});
    </script>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box border-box">
                <div class="row">
                    <div class="col-md-12">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-xs-6 col-sm-4">
                                    <?php 
                                    $general->inputBoxSelect($bases,'Base','base_id','id','title',$c['base_id']);
                                    $general->inputBoxSelect([],'User','user_id','id','name');
                                    $general->inputBoxSelect($years,'Year','year','id','id',date('Y',$c['date']));
                                    $general->inputBoxSelect($months,'Month','month','id','name',date('m',$c['date']));
                                    $general->inputBoxText('sale_target','Sale target',$c['sale_target'],'y');
                                    $general->inputBoxText('collection_target','Collection target',$c['collection_target'],'y');
                                    ?>

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
    ?>
    <div class="row">
    <div class="col-sm-12">
        <div class="white-box border-box">
        <div class="row">
            <div class="col-sm-12 col-lg-12 padding-left-0">
                <?php
                    show_msg();
                   
                ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-lg-12 padding-left-0">
                    <?php
                     $bases = $db->allBase();
                     $users = $db->allUsers();
                     $general->inputBoxSelectForReport($bases,'Base','base_id','id','title');
                     $general->inputBoxSelectForReport($users,'User','user_id','id','name');
                    ?>
                    <div class="col-md-2">
                        <h5 class="box-title">Search</h5>
                        <button  class="btn btn-success" onclick="monthly_target_list()">Search</button>

                    </div>
                </div>

                <div class="col-sm-12 col-lg-12" id="reportArea">
                </div>

            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            monthly_target_list();
        });
        function monthly_target_list(){
            let base_id = parse_int($('#base_id').val());
            let user_id = parse_int($('#user_id').val());
            ajax_report_request({monthly_target_list:1,user_id:user_id,base_id:base_id})
        }
    </script>
    <?php
    }