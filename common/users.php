<?php
    $aStatus      = $db->permission(64);
    $eStatus      = $db->permission(65);
    $general->pageHeader($rModule['title']);
    $groups=$db->allGroups('order by title asc');
    $general->arrayIndexChange($groups,'id');
    $employees=$db->selectAll('employees','where isActive in(0,1) order by name asc');
    $general->arrayIndexChange($employees,'id');
    $user_types=$general->get_all_user_type();
    $base = $db->selectAll('base');
    $general->arrayIndexChange($base,'id');
    if(isset($_GET['edit'])){
        if($eStatus==false){$general->redirect($pUrl,146,'Edit');}
        $uID = intval($_GET['edit']);
        $u = $db->userInfoByID($uID);
        if(empty($u)){$general->redirect($pUrl,37,'user');}

        if(isset($_POST['edit'])){
            $name  = $_POST['name'];
            $username   = $_POST['username'];
            $mobile    = $_POST['mobile'];
            $group_id       = intval($_POST['group']);
            $type       = ($u['type']==USER_TYPE_MPO)?USER_TYPE_MPO:intval($_POST['type']);
            $employee_id        = intval($_POST['employee_id']);
            $isActive   = intval($_POST['isActive'])==1?1:0;
            if(empty($username)){SetMessage(36,'Username');$error=1;}
            else{
                $check = $db->check_available('users'," where username = '".$username."' and id!=".$uID);
                if($check==false){SetMessage(127,'Username');$error=1;}
            }
            if($employee_id>0){
                if(!array_key_exists($employee_id,$employees)){
                    $error=fl();setMessage(63,'Employee');
                }
                else{
                    $check = $db->check_available('users'," where employee_id='".$employee_id."'");
                    if($check==false && $employee_id!=$u['employee_id'] ){SetMessage(1,'This employee already assigned to other user');$error=1;}
                }
            }
            if(!isset($error)&&$u['type']==USER_TYPE_MPO){
                $base_id = intval($_POST['base_id']);
                if($base_id<1){$error=fl();setMessage(36,'Base');}
                elseif(!isset($base[$base_id])){$error=fl();setMessage(63,'Base');}
            }
            if(!isset($error)){
                if(!isset($groups[$group_id])){$error=fl();SetMessage(63,'Group');}
                if(isset($_POST['chp'])){
                    $password   = $_POST['password'];
                    $re_pass    = $_POST['re_password'];   
                    if(empty($password)){SetMessage(36,'Password');$error=1;}
                    elseif($password!=$re_pass){SetMessage(54);$error=1;}
                    $encPas = md5($password.$u['password_salt']);
                }
                else{
                    $encPas = $u['password'];
                }
            }
            $db->transactionStart();
            if(!isset($error) && $u['ledger_id']>0){
                $data = ['title'=>$username];
                $where = ['id'=>$u['ledger_id']];
                $update = $db->update('a_ledgers',$data,$where);
                if(!$update){$error=fl();setMessage(66);}
            }
            if(!isset($error)){
                $data = array(
                    'name'=> $name,
                    'username'=> $username,
                    'mobile'   => $mobile,
                    'group_id'      => $group_id,
                    //  'type'      => $type,
                    'password' => $encPas,
                    'isActive'  => $isActive
                );

                $data['employee_id']=$employee_id;

                if($u['type']==USER_TYPE_MPO){
                    $data['base_id']  = $base_id;
                }
                $db->arrayUserInfoEdit($data);
                $where=array('id'=>$uID);
                $update=$db->update('users',$data,$where);
                if(!$update){
                    $error=fl();setMessage(66);
                    
                }
            }
            $ac=!isset($error);
            $db->transactionStop($ac);
            if(!isset($error)){
                $general->redirect($pUrl,30,'User');
            }
        }
    ?>
    <form method="post" action="">
        <div class="row">
            <div class="col-12">
                <div class="white-box border-box">
                    <div><?php show_msg();?></div>
                    <div class="row">
                        <div class="col-xs-6 col-sm-4">

                            <div class="form-group row">
                                <div class="col-md-12 col-sm-4">
                                    <?php 
                                        $general->inputBoxText('name','Full Nmae',$u['name']);
                                        $general->inputBoxSelect($employees,'Employee','employee_id','id','name',$u['employee_id']);
                                    ?>
                                </div>
                                <label class="col-md-4 col-form-label" for="group">Group </label>
                                <div class="col-md-8">
                                    <select name="group" id="group" class="form-control">
                                        <option value="">Select Group</option>
                                        <?php
                                            foreach($groups as $g){
                                            ?>
                                            <option <?php echo $general->selected($g['id'],$u['group_id']);?> value="<?php echo $g['id'];?>">
                                                <?php
                                                    echo $g['title'];
                                                ?>
                                            </option>
                                            <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="isActive">Status</label>
                                <div class="col-md-8">
                                    <select name="isActive" id="isActive" class="form-control">
                                        <option value="1" <?php $general->checked(1,$u['isActive']);?>>Active</option>
                                        <option value="0"<?php $general->checked(0,$u['isActive']);?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-4">
                            <?php 
                                $general->inputBoxText('username', 'Username',$u['username'],'y');
                                $general->inputBoxText('mobile', 'Mobile',$u['mobile']);
                                if($u['type']==USER_TYPE_MPO){
                                    $general->inputBoxSelect($base,'Base','base_id','id','title',$u['base_id']);
                                } 
                            ?>
                        </div>
                        <div class="clearfix visible-xs"></div>
                        <div class="col-xs-6 col-sm-4">

                            <div class="form-group row">
                                <label for="chp" class="col-md-4 col-form-label">Change Password</label>
                                <div class="col-md-8">
                                    <input type="checkbox" id="chp" name="chp" value="1">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label">Password</label>
                                <div class="col-md-8">
                                    <input class="form-control" value="" placeholder="Password" id="password" type="password" name="password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="re_password" class="col-md-4 col-form-label">Confirm password</label>
                                <div class="col-md-8">
                                    <input class="form-control" value="" placeholder="Confirm Password" id="re_password" type="password" name="re_password">
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-12">
                            <input type="submit" class="btn btn-info waves-effect waves-light pull-right m-t-10" value="Submit" name="edit">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
    <?php
    }
    else{
        if($aStatus){

            if(isset($_POST['add'])){
                $user_data=[];
                $name  = $_POST['name'];
                $mobile    = $_POST['mobile'];
                $username   = $_POST['username'];
                $password   = $_POST['password'];
                $re_pass    = $_POST['re_password'];
                $group_id       = intval($_POST['group']);
                $type       = intval($_POST['type']);
                $base_id       = intval($_POST['base_id']);
                $employee_id        = intval($_POST['employee_id']);
                if(empty($name)){SetMessage(36,'Name');$error=fl();}
                elseif(empty($username)){SetMessage(36,'Username');$error=fl();}
                elseif($type==USER_TYPE_MPO && $base_id<1){SetMessage(36,'Base');$error=fl();}
                elseif($type==USER_TYPE_MPO && !isset($base[$base_id])){SetMessage(63,'Base');$error=fl();}
                else{
                    $check = $db->check_available('users'," where username = '".$username."'");
                    if($check==false){SetMessage(55);$error=fl();}
                }

                if(!isset($groups[$group_id])){$error=fl();SetMessage(63,'Group');}
                elseif(empty($password)){SetMessage(36,'Password');$error=fl();}
                elseif($password!=$re_pass){SetMessage(54);$error=fl();}
                else{
                    if($employee_id>0){
                        if(!array_key_exists($employee_id,$employees)){
                            $error=fl();setMessage(63,'Employee');
                        }
                        else{
                            $check = $db->check_available('users'," where employee_id='".$employee_id."'");
                            if($check==false){SetMessage(1,'This employee already assigned to other user');$error=1;}
                        }
                    }
                }

                if(!isset($error)){

                    $salt = md5(rand(0,9).'t'.rand(0,9).'a@'.rand(0,9).'Q'.rand(0,9).'u'.rand(0,9).'W');
                    $encPas = md5($password.$salt);
                    $data = array(
                        'name'      => $name,
                        'mobile'    => $mobile,
                        'username'  => $username,
                        'group_id'  => $group_id,
                        'type'      => $type,
                        'data'      => json_encode($user_data),
                        'password'  => $encPas,
                        'password_salt' => $salt
                    );
                    $data['employee_id']=$employee_id;
                    if($type==USER_TYPE_MPO){
                        $data['base_id'] = $base_id;
                    }
                    $db->arrayUserInfoAdd($data);
                    $insert = $db->insert('users',$data);
                    if($insert){
                        $general->redirect($pUrl,29,'User');
                    }
                    else{SetMessage(66);}
                }
            }
        ?>
        <form method="post" action="">
            <div class="row">
                <div class="col-12">
                    <div class="white-box border-box">
                        <div><?php show_msg();?></div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-4">
                                <?php 
                                    $general->inputBoxText('name'   ,'Full Name',@$_POST['name']);
                                    $general->inputBoxText('mobile' , 'Mobile',@$_POST['mobile']);
                                    $general->inputBoxSelect($employees,'Employee','employee_id','id','name',@$_POST['employee_id']);
                                ?>
                                <div class="form-group row">
                                    <label class="col-md-4 col-form-label" for="group">Group </label>
                                    <div class="col-md-8">
                                        <select name="group" id="group" class="form-control">
                                            <option value="">Select Group</option>
                                            <?php
                                                foreach($groups as $g){
                                                ?>
                                                <option <?php echo $general->selected($g['id'],@$_POST['group']);?> value="<?php echo $g['id'];?>">
                                                    <?php
                                                        echo $g['title'];
                                                    ?>
                                                </option>
                                                <?php
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-4">
                                <?php 
                                    $general->inputBoxText('username', 'Username',@$_POST['username']);
                                    $general->inputBoxSelect($user_types,'Type', 'type',currentValue:@$_POST['type']);
                                ?>
                                <div id="base-div" style="display: none;">

                                    <?php $general->inputBoxSelect($base,'Base','base_id','id','title',@$_POST['base_id']);?>
                                </div>

                            </div>
                            <div class="clearfix visible-xs"></div>
                            <div class="col-xs-6 col-sm-4">

                                <div class="form-group row">
                                    <label for="password" class="col-md-4 col-form-label">Password</label>
                                    <div class="col-md-8">
                                        <input class="form-control" value="" placeholder="Password" id="password" type="password" name="password" required="required">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="re_password" class="col-md-4 col-form-label">Confirm password </label>
                                    <div class="col-md-8">
                                        <input class="form-control" value="" placeholder="Confirm Password" id="re_password" type="password" name="re_password" required="required">
                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-12">
                                <input type="submit" class="btn btn-info waves-effect waves-light pull-right m-t-10" value="Add" name="add">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
        <?php
        }
    }

    $q=[];

    $q[]='group_id!='.SUPERADMIN_USER;
    $sq='where '.implode(' and ',$q);
    $users   = $db->selectAll('users',$sq,'',$general->showQuery());
    $total = 1;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <?php show_msg();?>
            <?php
                $uData=[];
                foreach($users as $u){
                    $base_title = $base[$u['base_id']]['title']??'';
                    $st=$u['isActive']==1?'Active':'Inactive';
                    $eName='';
                    if(isset($employees[$u['employee_id']])){
                        $eName=$employees[$u['employee_id']]['name'];
                    }
                    $a=[
                        $u['name']
                        ,$eName
                        ,$base_title
                        ,$u['mobile']
                        ,$groups[$u['group_id']]['title']
                        ,$user_types[$u['type']]['title']
                        ,$u['username']
                        ,$st
                        ,$u['id']
                    ];
                    if($eStatus){
                        $a[]=$u['id'];
                    }
                    $uData[]=$a;
                }
            ?>
            <div class="table-responsive">
                <table id="dataTable" class="display"></table>
            </div>
            <script type="text/javascript">
                <?php
                    echo 'var dataSet='.json_encode($uData).';';
                ?>
                var daTableOption;
                $(document).ready(function(){
                    if(daTableOption!=undefined){
                        daTableOption.destroy();
                    }
                    daTableOption=$('#dataTable').DataTable( {
                        data: dataSet,
                        "lengthMenu": [[100,500,1000],[100,500,1000]],
                        columns: [
                            { title: "Name"},
                            { title: "Employee"},
                            { title: "Base"},
                            { title: "Mobile"},
                            { title: "Group"},
                            { title: "Type"},
                            { title: "Username"},
                            { title: "Status"},
                            { title: "Edit"}
                        ],
                        "createdRow": function ( row, data, index ) {
                            $('td', row).eq(7).html('<a href="<?php echo $pUrl;?>&edit='+data[9]+'" class="btn btn-info" ">Edit</a>');

                        }
                    });
                    //t(daTableOption)
                });
            </script>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('change','#type',function(){
        if(this.value==<?=USER_TYPE_MPO?>){
            $('#base-div').show();
        }
        else{
            $('#base-div').hide();
        }

    });    
</script>
<?php

    $general->onclickChangeJavaScript('users','uID');

?>
