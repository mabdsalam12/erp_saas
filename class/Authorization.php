<?php
class Authorization{
    private DB $db;
    private General $general;
    private Company $company;

    public function __construct(DB $db, General $general, Company $company){
        $this->db = $db;
        $this->general = $general;
        $this->company = $company;
    }
    public function login() {
        $db = $this->db;
        $general = $this->general;
        $captchaVerified = $this->verifyCaptcha();
        if($captchaVerified==true){
            $password   = $_POST['password'];
            $identity = trim((string)($_POST['email_or_mobile'] ?? ''));
            if(!empty($identity) && !empty($password)){
                if(filter_var($identity, FILTER_VALIDATE_EMAIL)){
                    $user = $db->get_rowData('users','email',$identity);
                }elseif($general->bangladeshiMobileCheck($identity)){
                    $user = $db->get_rowData('users','mobile',$identity);
                }else{
                    setMessage(63,'Email or Mobile');
                    $error = fl();
                }
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
    private function verifyCaptcha(){
        $general = $this->general;
        if($_ENV['CAPTCHA_ENABLE']==1){
            if(isset($_POST['g-recaptcha-response'])){
                $recaptchaResponse = $_POST['g-recaptcha-response'];
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
                $response = @file_get_contents($url, false, $context);
                if($response){
                    $result = $general->getJsonFromString($response);
                    if(isset($result['success']) && $result['success']==1){
                        return true;
                    }
                }
                setMessage(1,'Captcha not verified.'.fl());
                return false;
            }
            // no response posted
            setMessage(1,'Captcha not verified.'.fl());
            return false;
        }
        return true;
    }

    public function logOut($lData,$redirectUrl){
        $data = array('end'=>TIME,'start'=>0);
        $where = array('id'=> $lData['id']);
        $update = $this->db->update('user_login_session',$data,$where);
        unset($_SESSION[SU_LOGIN_SESSION_NAME]);
        if(isset($_SESSION['company_id'])){unset($_SESSION['company_id']);}
        $this->general->redirect($redirectUrl,49);
    }
}