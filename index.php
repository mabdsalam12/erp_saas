<?php
	if(isset($_GET['showe'])){
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}
	ob_start();
	
// define('K_PATH_FONTS', 'C:\Users\mabds\OneDrive\Desktop\X\tcpdf\fonts');
	include "class/general.php";
	include "class/db.php";
	include "class/somiti.php";
	include "class/acc.php";
	include "class/messages.php";
	include "class/LanguageManager.php";
	include "class/Language_call.php";
	$general= new General();
	$db     = new DB($general);
	$smt    = new SMT($general,$db);
	$acc    = new ACC($general,$db,$smt);
	
	LanguageManager::getInstance()->setLang();
	include "init.php";

	if(isset($_GET['dev'])){
		include 'dev.php';
		exit;
	}
	// print_r($_SESSION);
    if(isset($_SESSION['billSleepTime']) && $_SESSION['billSleepTime']>0){
		//sleep($_SESSION['billSleepTime']);
		sleep(5);
		setMessage(1,'দয়া করে বাকি বিল পরিশোধ করুন। এছাড়া সিস্টেমে ধীরগতি হতে পারে। '.$_SESSION['billSleepTime']);
	}
	$thisPageTitle = l('dashboard');
	$pSlug='';
	if(defined('LOGIN_SESSION_ID')&&$userData['type']==USER_TYPE_MPO){
		$general->redirect(APP_URL);
	}
	if(!defined('LOGIN_SESSION_ID')){    
		$thisPageTitle='Log in';   
		include("common/login.php");
	}
	elseif(isset($_GET['rFlush'])){
		$a=$db->runQuery('delete from report_cache');
		$data=array('dData'=>'');
		$general->redirect(URL,1,'Cache Cleare');
	}
	elseif(isset($_GET['logout'])){ 
		if(defined('LOGIN_SESSION_ID')){ 
			$db->logOut($lData,URL);
		}
		else{$general->redirect(URL);}
	}
	elseif(isset($_GET['ajax'])){include 'ajax/ajax_rq.php';}
	elseif(isset($_GET['srsd'])){include 'routeDetails.php';}
	elseif(isset($_GET['print'])){
		
		include ROOT_DIR.'/print/print.php';
	}
	elseif(isset($_GET['dev'])){
		include 'dev.php';
	}
	else{
		$pUrl='';
		$include1="common/dashboard.php";
		if(isset($_GET[MODULE_URL])){
			$rModule = $db->get_rowData('module','slug',$_GET[MODULE_URL]);
			if(!empty($rModule)){
				if($rModule['isActive']==1){
					if($db->modulePermission($rModule['id'])){
						$pUrl = '?'.MODULE_URL.'='.$rModule['slug'];
						$pSlug = $rModule['slug'];
						$thisPageTitle = $rModule['title'];
						$include1 = $rModule['folder'].'/'.$rModule['page_name'];
					}
				}
				else{
					setMessage(131);
				}
			}
		}
		$thisPageTitle=PAGE_TITLE_PREFIX.$thisPageTitle;	
		include_once("common/header.php");
		include($include1);
		include_once("common/footer.php");
	}
	mysqli_close($GLOBALS['connection']);
	ob_flush();