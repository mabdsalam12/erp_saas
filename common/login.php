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
                                <input class="form-control" type="text" required placeholder="Email or Mobile" name="email_or_mobile" value="<?php echo @$_POST['email_or_mobile'];?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control" type="password" required placeholder="<?=l('password')?>" name="password" value="">
                            </div>
                        </div>
                        <?php if(isset($_ENV['GRECAPTCHA_SITE_KEY'])){?>
                        <button class="g-recaptcha btn d-block w-100 login_btn_afri" 
                            data-sitekey="<?php echo $_ENV['GRECAPTCHA_SITE_KEY'];?>" 
                            data-callback='onSubmit' style="color: white;background-color: green;padding: 13px;font-size: 31px;"
                            data-action='submit'><?=l('login')?></button>
                        <?php }else{?>
                        <div class="form-group text-center m-t-20">
                            <div class="col-xs-12">
                                <button class="btn d-block w-100 login_btn_afri" type="submit" style="color: white;background-color: green;padding: 13px;font-size: 31px;"><?=l('login')?></button>
                            </div>
                        </div>
                        <?php }?>
                        <div class="text-center m-t-20">
                            <a href="<?=URL?>/?register=1">Create a new account</a>
                        </div>
                            

                         <!-- <button class="g-recaptcha btn d-block w-100 login_btn_afri" 
                             style="color: white;background-color: green;padding: 13px;font-size: 31px;"
                            data-action='submit'></button>   -->
                    </form>
                </div>
            </div>
        </section>
        <?php if(isset($_ENV['GRECAPTCHA_SITE_KEY'])){?>
        <script type="text/javascript">function onSubmit(token) {document.getElementById("loginform").submit();}</script>
        <script src="https://www.google.com/recaptcha/api.js"></script>
        <?php }?>
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
