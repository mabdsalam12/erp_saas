<?php
const RUNNING_VERSION	= 39;
const DEMO_MODE			= false;
const SHOW_ERROR_LINE	= true;

define('TIME'                           ,time());
const SUPERADMIN_USER		= 1;
const MODULE_URL			='module';
define('SU_LOGIN_SESSION_NAME'          ,'tkdwam_d_'.PROJECT);
define('SU_LOGIN_COOKIE_NAME'           ,'sudels');
define('SU_LOGIN_SESSION_STRING'        ,'Tkowf8kdo_'.PROJECT);
define('APP_URL'                        ,URL.'/app');

define('ROOT_DIR'                       ,__DIR__);

//Gender Types
const GENDER_TYPE_MALE = 1;
const GENDER_TYPE_FEMALE = 2;
const GENDER_TYPE_COMMON = 3;

//Week Days
const WEEK_DAY_SUNDAY = 7;
const WEEK_DAY_MONDAY = 1;
const WEEK_DAY_TUESDAY = 2;
const WEEK_DAY_WEDNESDAY = 3;
const WEEK_DAY_THURSDAY = 4;
const WEEK_DAY_FRIDAY = 5;
const WEEK_DAY_SATURDAY = 6;

//Stock Change Types
const ST_CH_PURCHASE 				= 1;
const ST_CH_SALE 					= 2;
const ST_CH_SALE_RETURN 			= 3;
const ST_CH_PURCHASE_RETURN 		= 4;
const ST_CH_OPENING 				= 5;
const ST_CH_REJECT 					= 6;
const ST_CH_ADJUST 					= 7;
const ST_CH_ADJUST_CLEAR 			= 8;
const ST_CH_PRODUCTION 				= 9;
const ST_CH_PRODUCTION_SOURCE 		= 10;
const ST_CH_PRODUCTION_SOURCE_MAN 	= 11;
const ST_CH_DISTRIBUTE 				= 12;
const ST_CH_STOCK_ENTRY 			= 13;

//Discount Types
const PERCENT_TYPE = 1;
const FLAT_TYPE = 2;

	
const COA_ADMINISTRATIVE_OVERHEAD = 'administrative-overhead';
const COA_SALES_AND_MARKETING_EXPENSES = 'sales-and-Marketing-Expenses';
const COA_FACTORY_OVER_HEAD = 'factory-overhead';
const COA_COST_OF_PRODUCTION = 'cost-of-Production';
const COA_ADMINISTRATIVE_EXPENSES = 'administrative-expenses';
const COA_SELLING_AND_MARKETING_EXP = 'selling-and-marketing-exp';
const COA_DISTRIBUTION_EXPENSES = 'distribution-expenses';
const COA_FINANCIAL_EXPENSES = 'financial-expenses';
const COA_CASH_AND_CASH_EQUIVALENT = 'cash-and-cash-equivalent';
const COA_SALES_RECEIVABLE = 'sales-receivable';
const COA_EMPLOYEES_ADVANCE = 'employees-advance';
const COA_DIRECTOR_LOANS = 'director-loans';
const COA_ADVANCE_TO_SUPPLIERS  = 'advance-to-suppliers';
//const COA_INVENTORIES  = 'inventories';
const COA_PROPERTY_PLANT_AND_EQUIPMENT  = 'property-Plant-and-equipment';
const COA_FACTORY_CONSTRUCTION_AND_RENOVATION  = 'factory-construction-and-Renovation';
const COA_ACCOUNTS_PAYABLE = 'accounts-payable';
const COA_NON_CURRENT_LIABILITIES = 'non-current-liabilities';
const COA_PAID_UP_CAPITAL = 'paid-up-capital';
const COA_SHARE_MONEY_DEPOSIT = 'share-money-deposit';
const COA_RETAINED_EARNINGS = 'retained-earnings';

const LEDGER_ACCOUNT_OTHER_REVENUE = 'other-revenue';
const LEDGER_ACCOUNT_PROVISION_FOR_INCOME_TAX = 'provision-for-income-tax';


	//Status Change
	const PER_CHANGE_PER_STATUS = 2;
	define('NUMERIC_INPUT'                  ,'onkeypress="return isNumberKey(event)"');
	define('BOOTSTRAP_REQUIRED'             ,'<span class="v-star" style="color:red">*</span>');
	define('INFO_SUPPLIER_BALANCE'           ,'<span style="color:red;">সাপ্লায়ার ব্যালেন্স মাইনাস মানে সাপ্লায়ার পাবে</span>');
    
	const PRODUCT_TYPE_FINISHED           = 0;
	const PRODUCT_TYPE_RAW                = 1;
	const PRODUCT_TYPE_GIFT_ITEM          = 2;
	const PRODUCT_TYPE_PACKAGING          = 3;
	const PRODUCT_TYPE_STATIONARY         = 4;
	const PRODUCT_TYPE_MANUFACTURING      = 5;
	const PRODUCT_TYPE_OFFER              = 6;
	const PRODUCT_TYPE_RE_PACKAGING       = 7;

	const USER_TYPE_COMMON                = 0;
	const USER_TYPE_MPO                   = 1;
	const USER_TYPE_MANAGER               = 2;
	const USER_TYPE_RSM                   = 3;

	const PAY_TYPE_CREDIT                 = 1;
	const PAY_TYPE_CASH                   = 2;
	const PAY_TYPE_CASH_ON_DELIVERY       = 3;

	const SALE_RETURN_PROCESS_TYPE_GOOD   = 1;
	const SALE_RETURN_PROCESS_TYPE_DAMAGE = 2;
	const SALE_RETURN_PROCESS_TYPE_EXPIRY = 3;
    

	$onlyDevleperMenu=array(1);
	$onlyDevleperPermission=array(2);

	$ttm    = strtotime('today');
	$ytm    = strtotime('yesterday');
	$tmtm   = strtotime('tomorrow');
	define('YESTERDAY_TIME',$ytm);
	define("TODAY_TIME",$ttm);
	define("TOMORROW_TIME",$tmtm);


	$reportBacgroundColor=array(
		'FFDF80','C5FF60','D076FF','4BFFFF'
	);
	if(isset($_SESSION[SU_LOGIN_SESSION_NAME])){
		$userPut    = $_SESSION[SU_LOGIN_SESSION_NAME];
	}
	elseif(isset($_GET['sessionString'])){
		$userPut    = $_GET['sessionString'];
	}
	if(isset($userPut)){
		$login_string   = base64_decode($userPut);
		$login_string   = explode(md5(SU_LOGIN_SESSION_STRING),$login_string);
		$login_string   = $login_string[1];
		$lData   = $db->get_rowData('user_login_session','login_string',$login_string);
		if(!empty($lData)){
			if($lData['isActive']==1){
				if($lData['validity']>=TIME){
					$userData = $db->userInfoByID($lData['user_id']);
					if(!empty($userData)){
						$loginUpTime = strtotime("-2 hour", $lData['validity']);
						if(TIME>$loginUpTime){
							$logoutTime = strtotime("+3 hour", TIME);
							$data   = ['validity'=>$logoutTime];
							$update = $db->update_by_id($lData['id'],'user_login_session',$data);
						}
						define('USER_ID'			, $userData['id']);
						define('GROUP_ID'			, $userData['group_id']);
						define('LOGIN_SESSION_ID'	, $lData['id']);
					}else{setMessage(63,'login');unset($_SESSION[SU_LOGIN_SESSION_NAME]);}
				}else{
					setMessage(48);
					unset($_SESSION[SU_LOGIN_SESSION_NAME]);
					$data = ['end'=>TIME];  // ,'ulsStatus'=>0 এটার জন্য error আসতেছিলো তাই বন্ধ করে দিলাম মইনুল 
					$update = $db->update_by_id($lData['id'],'user_login_session',$data);
				}
			}else{
				setMessage(47);unset($_SESSION[SU_LOGIN_SESSION_NAME]);
			}
		}else{setMessage(47);unset($_SESSION[SU_LOGIN_SESSION_NAME]);}
	}
	$smt->sms_cron();
