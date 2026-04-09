<?php
	
	if(isset($_GET['aaaaaa'])){echo __DIR__;exit;} 
	require_once __DIR__.'/../config/project.php'; 
	require_once __DIR__.'/../config/'.PROJECT.'.php';
	require_once AUTOLOAD_FILE_PATH;
	
	
	if(!isset($crn))session_start();//ক্রোনের ক্ষেত্রে সেশন স্টার্টের দরকার নাই

	date_default_timezone_set('Asia/Dhaka');
	
	header('Content-Type: text/html; charset=utf-8');
	// print_r($_SERVER);
	define('DB_SERVER', 'localhost');
    
    $localServers=[
        'erp_saas.as',
        'erp_saas.moi',
        'erp_saas.oo'
    ];
	if (isset($_SERVER['SERVER_NAME'])) {
        if (in_array($_SERVER['SERVER_NAME'],$localServers)) {
            define('LOCAL_SERVER_NAME', $_SERVER['SERVER_NAME']);
        } else {
            define('LOCAL_SERVER_NAME', 'localhost');
        }
    } else {
        define('LOCAL_SERVER_NAME', 'localhost');
    }
	$useWWW='';
	if(isset($_SERVER['SERVER_NAME'])){
		if(preg_match("/www/i", $_SERVER['SERVER_NAME'])){
			$useWWW='www.';
		}
	}
	
	
	//echo PROJECT;echo '<br>'; echo DB_DATABASE;exit();
	$GLOBALS['connection'] = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD,DB_DATABASE) or die('Oops connection error 1'.mysqli_connect_error());
	mysqli_set_charset($GLOBALS['connection'],'utf8mb4');


	/*$connection = mysql_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD) or die('Oops connection error 1');
	mysql_select_db(DB_DATABASE, $connection) or die('Oops connection error 2');
	mysql_set_charset('utf8', $connection);*/
	$_POST = sanitize($_POST);
	$_GET = sanitize($_GET);
	if(isset($SFU)){$SFU = sanitize($SFU);}
	function cleanInput($input){
		$search = array(
			'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
			/*'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags*/
			'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
		);

		$output = preg_replace($search, '', $input);
		return $output;
	}
	function sanitize($input) {
		$output = array();
		if (is_array($input)) {
			foreach($input as $var=>$val) {
				$output[$var] = sanitize($val);
			}
		}
		else {
			/*if(get_magic_quotes_gpc()) {
			$input = stripslashes($input);
			}*/
			$input  = cleanInput($input);
			$output = mysqli_real_escape_string($GLOBALS['connection'],trim(@$input));
			$output = preg_replace('/\s+/', ' ',$output);
		}
		return $output;
	}
	function textFileWrite($data,$fileName="error.txt"){
		$handle = fopen($fileName, 'a');
		if(is_array($data)){
			$data=json_encode($data);
		}
		//echo $fileName;echo '<br>';
		fwrite($handle, $data."\n");
		fclose($handle);
		return true;
	}
	function fl() {//file line
		$details=debug_backtrace();
		$file=basename($details[0]['file'],'.php');
		return 'EL#'.$details[0]['line'].'_'.$file;
	}
	function exception_error_return(Exception $e,object $object,string $fl,array &$jArray=[]): void{
        setMessage(1,$e->getMessage());
        $count=0;
		$jArray[$fl.'_'.$count++]=[$e->getMessage().' '.$e->getFile().' '.$e->getLine()];
        $jArray[$fl.'_'.$count++]=$e->getFile();
        $jArray[$fl.'_'.$count++]=$e->getCode();
        $jArray[$fl.'_'.$count++]=$e->getLine();
        $jArray[$fl.'_'.$count++]=$e->getPrevious();
        $jArray[$fl.'_'.$count++]=$e->getTrace();
        $jArray[$fl.'_'.$count++]=$e->getTraceAsString();
        if(method_exists($object,'getLog')){
            $log=$object->getLog();
            $jArray[$fl.'_'.$count++]=$log;
        }
    }
