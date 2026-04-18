<!DOCTYPE html>
<html lang="en">
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="<?=URL?>/favicon.php" type="image/x-icon">
        <title>Register</title>
        <link href="<?=URL?>/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?=URL?>/plugins/bootstrap-extension/css/bootstrap-extension.css" rel="stylesheet">
        <link href="<?=URL?>/css/animate.css" rel="stylesheet">
        <link href="<?=URL?>/css/style.css" rel="stylesheet">
        <link href="<?=URL?>/css/colors/default.css" id="theme" rel="stylesheet">
    </head>
    <body>
        <div class="preloader">
            <div class="cssload-speeding-wheel"></div>
        </div>
        <section id="wrapper" class="login-register">
            <div class="login-box">
                <div class="white-box">
                    <?php
                        if(isset($_POST['company_name'])){
                            $insert = $cmp->add($_POST);
                            if($insert){
                                $general->redirect(URL,2,'Registration completed successfully. Please login.');
                            }
                            else{
                                $error = $cmp->lastErrorLine ?: fl();
                            }
                        }
                        $logoUrl=URL.'/images/'.PROJECT.'/logo.png';
                    ?>
                    <form class="form-horizontal form-material" id="registerform" action="" method="POST">
                        <div class="admin_logo">
                            <img src="<?php echo $logoUrl;?>" alt="" style="max-width:100%;max-height:130px;">
                        </div>

                        <h3 class="text-center m-b-20">Register</h3>

                        <?php
                            if(isset($error)){if(SHOW_ERROR_LINE==true){setMessage(133,$error);}}
                            show_msg();
                        ?>

                        <div class="form-group ">
                            <div class="col-xs-12">
                                <a href="<?=URL?>/?lang=en&register=1" class="btn btn-info btn-sm pull-right"><?=l('english')?></a>
                                <a href="<?=URL?>/?lang=bn&register=1" class="btn btn-info btn-sm pull-right"><?=l('bangla')?></a>
                            </div>
                        </div>

                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" required placeholder="Company Name" name="company_name" value="<?php echo htmlspecialchars((string)@$_POST['company_name']); ?>">
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" required placeholder="Contact Person" name="contact_person" value="<?php echo htmlspecialchars((string)@$_POST['contact_person']); ?>">
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" placeholder="Mobile" name="mobile" value="<?php echo htmlspecialchars((string)@$_POST['mobile']); ?>">
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars((string)@$_POST['email']); ?>">
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="password" required placeholder="Password" name="password" value="">
                            </div>
                        </div>

                        <button type="submit" class="btn d-block w-100 login_btn_afri" style="color: white;background-color: green;padding: 13px;font-size: 31px;">Register</button>

                        <div class="text-center m-t-20">
                            <a href="<?=URL?>">Already have an account? Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <script src="<?=URL?>/plugins/jquery/dist/jquery.min.js"></script>
        <script src="<?=URL?>/bootstrap/dist/js/tether.min.js"></script>
        <script src="<?=URL?>/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="<?=URL?>/plugins/bootstrap-extension/js/bootstrap-extension.min.js"></script>
        <script src="<?=URL?>/js/jquery.slimscroll.js"></script>
        <script src="<?=URL?>/js/waves.js"></script>
        <script src="<?=URL?>/js/custom.min.js"></script>
    </body>
</html>
