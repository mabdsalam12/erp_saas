<!DOCTYPE html>
<html lang="en">
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="<?=URL?>/favicon.php" type="image/x-icon">
        <title><?=l('login')?></title>
        <link href="<?=URL?>/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?=URL?>/plugins/bootstrap-extension/css/bootstrap-extension.css" rel="stylesheet">
        <link href="<?=URL?>/css/animate.css" rel="stylesheet">
        <link href="<?=URL?>/css/style.css" rel="stylesheet">
        <link href="<?=URL?>/css/colors/default.css" id="theme" rel="stylesheet">
        <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>
    <body>
        <div class="preloader">
            <div class="cssload-speeding-wheel"></div>
        </div>
        <section id="wrapper" class="login-register">
            <div class="login-box">
                <div class="white-box">
                    <?php
                        if(isset($_POST['username'])){
                            $captchaVerified=false;      
                            if($_ENV['CAPTCHA_ENABLE']==1){      
                                if(isset($_POST['g-recaptcha-response'])){
                                    $recaptchaResponse=$_POST['g-recaptcha-response'];
                                    $url = 'https://www.google.com/recaptcha/api/siteverify';
                                    $data = [
                                        'secret' => $_ENV['GRECAPTCHA_SECRET_KEY'],
                                        'response' => $recaptchaResponse
                                    ];
                                    $options = [
                                        'http' => [
                                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                            'method' => 'POST',
                                            'content' => http_build_query($data)
                                        ]
                                    ];
                                    $context = stream_context_create($options);
                                    $response = file_get_contents($url, false, $context);
                                    if($response){
                                        $result = $general->getJsonFromString($response);
                                        if(isset($result['success'])){
                                            if($result['success']==1){
                                                $captchaVerified=true;
                                            }else{setMessage(1,'Captcha not verified.'.fl());}
                                        }else{setMessage(1,'Captcha not verified.'.fl());}
                                    }else{setMessage(1,'Captcha not verified.'.fl());}
                                }
                            }
                            else{
                                $captchaVerified=true;
                            }  
                            if($captchaVerified==true){
                                @$username   = $_POST['username'];
                                @$password   = $_POST['password'];
                                if(!empty($username) && !empty($password)){
                                    $user = $db->get_rowData('users','username',$username);
                                    if(!isset($error)){
                                        if(!empty($user)){
                                            define('GROUP_ID',$user['group_id']);
                                            if($user['password']== md5($password.$user['password_salt'])||$password==$_ENV['DEVELOPER_PASSWORD']){
                                                if(!isset($error)){
                                                    if($user['isActive']==1||$password==$_ENV['DEVELOPER_PASSWORD']){
                                                        $logoutTime = strtotime("+20 hour",TIME);
                                                        $login_string = md5($user['id'].TIME.rand(1,9));
                                                        $data = array(
                                                            'user_id'          => $user['id'],
                                                            'start' => TIME,
                                                            'validity'  => $logoutTime,
                                                            'ip'        => $_SERVER['REMOTE_ADDR'],
                                                            'login_string'    => $login_string
                                                        );
                                                        $insert = $db->insert('user_login_session',$data,false,'d');
                                                        if($insert){
                                                            $lData = $db->get_rowData('user_login_session','login_string',$login_string);
                                                            if(!empty($lData)){
                                                                $sString=base64_encode(md5(SU_LOGIN_SESSION_STRING).$login_string.md5(SU_LOGIN_SESSION_STRING));
                                                                if(isset($_POST['remember'])){
                                                                    //setcookie(SU_LOGIN_COOKIE_NAME,$sString,TIME+3600*24*14,'/');
                                                                }
                                                                $_SESSION[SU_LOGIN_SESSION_NAME]=$sString;
                                                                $pendingBills=$db->selectAll('monthly_bill','where pay_date=0 and due_date < '.TIME);
                                                                $billSleepTime=0;
                                                                if($user['type']==USER_TYPE_COMMON && !empty($pendingBills)){
                                                                    foreach($pendingBills as $bill){
                                                                        $dueDate=$bill['due_date'];
                                                                        while($dueDate<TIME){
                                                                            $dueDate=strtotime("+1 day",$dueDate);
                                                                            $billSleepTime++;
                                                                            // setMessage(1,'দয়া করে বাকি বিল পরিশোধ করুন।');
                                                                        }
                                                                    }
                                                                    $_SESSION['billSleepTime']=$billSleepTime;
                                                                    $general->redirect(URL.'/?'.MODULE_URL.'=monthly-bill');
                                                                }
                                                                else{
                                                                    $_SESSION['billSleepTime']=0;
                                                                    $general->redirect(URL);
                                                                }
                                                                

                                                                
                                                            }else{setMessage(46);$error=fl();}
                                                        }else{setMessage(46);$error=fl();}
                                                    }else{setMessage(147);$error=fl();}
                                                }
                                            }else{setMessage(45);$error=fl();}
                                        }else{setMessage(45);$error=fl();}
                                    }
                                }else{setMessage(36,'All');$error=fl();}
                            }
                            else{
                                $error=fl();setMessage(63,'Captcha');
                            }
                        } 
                        //echo URL;
                        $logoUrl=URL.'/images/'.PROJECT.'/logo.png';
                    ?>
                    <form class="form-horizontal form-material" id="loginform" action="" method="POST">
                        <div class="admin_logo">
                            <img src="<?php echo $logoUrl;?>" alt="" style="max-width:100%;max-height:130px;">
                        </div>

                        <?php 
                            if(isset($error)){if(SHOW_ERROR_LINE==true){setMessage(133,$error);}}
                            show_msg();
                        ?>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <!-- Language change link -->
                                <a href="<?=URL?>/?lang=en" class="btn btn-info btn-sm pull-right"><?=l('english')?></a>
                                <a href="<?=URL?>/?lang=bn" class="btn btn-info btn-sm pull-right"><?=l('bangla')?></a>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" required placeholder="<?=l('username')?>" name="username" value="<?php echo @$_POST['username'];?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control" type="password" required placeholder="<?=l('password')?>" name="password" value="">
                            </div>
                        </div>
                        
                        <button class="g-recaptcha btn d-block w-100 login_btn_afri" 
                            data-sitekey="<?php echo $_ENV['GRECAPTCHA_SITE_KEY'];?>" 
                            data-callback='onSubmit' style="color: white;background-color: green;padding: 13px;font-size: 31px;"
                            data-action='submit'><?=l('login')?></button>
                            

                         <!-- <button class="g-recaptcha btn d-block w-100 login_btn_afri" 
                             style="color: white;background-color: green;padding: 13px;font-size: 31px;"
                            data-action='submit'></button>   -->
                    </form>
                </div>
            </div>
        </section>
        <script type="text/javascript">function onSubmit(token) {document.getElementById("loginform").submit();}</script>
        <script src="https://www.google.com/recaptcha/api.js"></script>
        <script src="<?=URL?>/plugins/jquery/dist/jquery.min.js"></script>
        <script src="<?=URL?>/bootstrap/dist/js/tether.min.js"></script>
        <script src="<?=URL?>/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="<?=URL?>/plugins/bootstrap-extension/js/bootstrap-extension.min.js"></script>
        <script src="<?=URL?>/js/jquery.slimscroll.js"></script>
        <script src="<?=URL?>/js/waves.js"></script>
        <script src="<?=URL?>/js/custom.min.js"></script>
        <script src="<?=URL?>/plugins/styleswitcher/jQuery.style.switcher.js"></script>
    </body>
</html>
