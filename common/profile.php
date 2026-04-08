<?php
    $data = array($pUrl=>$rModule['title']);
    $general->pageHeader($rModule['title'],$data);
?>

<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <?php show_msg();?>
            <div class="col-lg-4 pull-left">
                <table class="only_show table table-bordered">
                    <tr><td>Name</td>           <td><?=$userData['uFullName']?></td></tr>
                    <tr><td>Username</td>       <td><?=$userData['username']?></td></tr>
                    <tr><td>Email</td>          <td><?=$userData['uEmail']?></td></tr>
                </table>
            </div>
            <div class="col-lg-7 pull-right">
                <script type="text/javascript">
                    function showPassForm(){
                        $('#pass_form').toggle();
                    }
                </script>
                <?php
                    if (isset($_POST['change'])){
                        $oldPass=$_POST['oldPass'];
                        $newPass=$_POST['newPass'];
                        $conPass=$_POST['conPass'];
                        if(empty($oldPass)){
                            SetMessage(36,'Password');$error=fl();
                        }else{
                            $oPassword=md5($oldPass.$userData['password_salt']);
                            if($oPassword==$userData['uPassword']){
                                if(empty($newPass)){SetMessage(36,'New Password');$error=fl();}
                                elseif($newPass!=$conPass){SetMessage(54);$error=fl();}
                                else{
                                    $nPassword=md5($newPass.$userData['password_salt']);
                                    $data = array(
                                        'uPassword' => $nPassword,
                                        'modifiedBy'=> UID,
                                        'modifiedOn'=> TIME
                                    );
                                    $where=array('uID'=>$userData['uID']);
                                    $update=$db->update($general->table(17),$data,$where);
                                    if($update){
                                        SetMessage(225);
                                    }else{SetMessage(66);}    
                                }
                            }else{SetMessage(63,'Password');$error=fl();}
                        }
                    }
                ?>
                <?php show_msg();?>
                <div class="col-lg-12"><a href="javascript:void()" onclick="showPassForm()" class="btn btn-large btn-success">Change Password</a></div>
                <div class="col-lg-11" id="pass_form" style="display: none; padding: 5px; margin: 5px;">
                    <?php show_msg();?>
                    <form action="" method="POST">
                        <table class="only_show table table-bordered">
                            <tr>
                                <td>Old Password</td>
                                <td><input type="password" name="oldPass" required="required" value=""></td>
                            </tr>
                            <tr>
                                <td>New Password</td>
                                <td><input type="password" name="newPass" required="required" value=""></td>
                            </tr>
                            <tr>
                                <td>Confirm Password</td>
                                <td><input type="password" name="conPass" required="required" value=""></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td><input type="submit" name="change" value="Submit" class="btn btn-success"></td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
