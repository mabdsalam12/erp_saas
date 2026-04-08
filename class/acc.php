<?php

const DEBIT = 1;
const CREDIT = 2;

const V_T_OPENING = 1;
const V_T_PURCHASE = 2;
const V_T_SALE_CASH = 3;
const V_T_SALE_VAT = 4;
const V_T_SALE_RETURN = 5;
const V_T_CUSTOMER_COLLECTION_DISCOUNT = 6;
const V_T_CUSTOMER_YEARLY_DISCOUNT = 7;
const V_T_SALE_TAX = 8;
const V_T_SALE_VAN_CHART = 9;
const V_T_EMPLOYEE_PAY = 10;
const V_T_EMPLOYEE_SALARY = 11;
const V_T_EMPLOYEE_SALE_SALARY_PAY = 12;
const V_T_SUPPLIER_PAYMENT = 13;
const V_T_PURCHASE_RETURN = 14;
const V_T_SALE_DUE_COLLECT = 15;
const V_T_INCOME = 19;
const V_T_EXPENSE = 20;
const V_T_SALE_RETURN_PROCESS = 21;
const V_T_PRODUCT_REJECT = 22;

const V_T_INVEST = 37;
const V_T_LOAN_RETURN = 39;
const V_T_LOAN_PAY = 47;
const V_T_MAIN_BALANCE_SALARY_PAY = 56;
const V_T_SALE_CASH_CUSTOMER = 65;
const V_T_PAY_TO_CUSTOMER = 68;
const V_T_SALE_CASH_RECEIVE_FROM_CUSTOMER = 69;
const V_T_RECEIVE_FROM_CUSTOMER = 70;
const V_T_CUSTOMER_SALE_DUE_DISTRIBUTE = 72;
const V_T_PROFIT_WITHDRAWAL = 73;
const V_T_SUPPLIER_PAYMENT_COST = 74;
const V_T_PAY_TO_PERSON = 75;
const V_T_RECEIVE_FROM_PERSON = 76;
const V_T_SALE_RETURN_CASH_CUSTOMER = 78;
const V_T_SALE_RETURN_CASH_RECEIVE_FROM_CUSTOMER = 79;
const V_T_PURCHASE_RETURN_CASH_RECEIVE_FROM_SUPPLIER = 80;
const V_T_VAT_PURCHASE = 81;
const V_T_AIT_PURCHASE = 82;
const V_T_RECOVERABLE_ENTRY = 83;
const V_T_RECOVERABLE_COLLECTION = 84;
const V_T_MPO_BALANCE_IN = 86;
const V_T_MPO_BALANCE_OUT = 87;
const V_T_CUSTOMER_BAD_DEBT = 88;
const V_T_VOUCHER_ENTRY = 89;
const V_T_MPO_BALANCE_TRANSFER = 90;
const V_T_CONTRA = 91;
const V_T_CONTRA_TR_CHARGE = 92;
const V_T_RECEIVE_FROM_EMPLOYEE = 93;
const V_T_PRODUCT_STOCK_ENTRY = 94;
const V_T_FIXED_ASSETS_PURCHASE = 95;
const V_T_FIXED_ASSETS_DEPRECIATION = 96;
const V_T_NEW_RECOVERABLE_ENTRY = 97;
const V_T_NEW_RECOVERABLE_COLLECTION = 98;
const V_T_DOCTOR_HONORARIUM = 99;
const V_T_EMPLOYEE_SALARY_ALLOWANCE = 100;
//নতুন voucher type add করলে এই function এ update করে আসতে হবে all_voucher_type


const REMOVE_ABLE_VOUCHER_TYPE =[
	V_T_EXPENSE,
	V_T_INCOME,
	V_T_EMPLOYEE_PAY,
	V_T_RECEIVE_FROM_EMPLOYEE,
	V_T_VOUCHER_ENTRY,
	V_T_SUPPLIER_PAYMENT,
	V_T_PAY_TO_PERSON,
	V_T_RECEIVE_FROM_PERSON,
];



const AH_CASH 							= 1;
const AH_CAPITAL 						= 2;
const AH_PURCHASE 						= 3;
const AH_DEALER_SALE 					= 4;
const AH_CONTRA_TRANSACTION_CHARGE 		= 5;
const AH_DOCTOR_HONORARIUM 				= 6;
const AH_SALE_RETURN 					= 9;
const AH_SALE_DUE 						= 10;
const AH_EMP_SALARY 					= 11;
const AH_PUR_RETURN 					= 13;
const AH_PRODUCT_REJECT 				= 14;
const AH_PRODUCT_REJECT_PROCESS 		= 15;
const AH_VAT_SALE 						= 17;
const AH_SALE_RETURN_PROCESS 			= 20;
const AH_SALE_RETURN_PROCESS_GOOD 		= 21;
const AH_SALE_RETURN_PROCESS_EXPIRY 	= 22;
const AH_SALE_RETURN_PROCESS_DAMAGE 	= 23;
const AH_MAIN_BALANCE 					= 25;
const AH_MAIN_EXPENSE 					= 28;
const AH_MAIN_INCOME 					= 29;
const AH_CUSTOMER_COLLECTION_DISCOUNT 	= 31;
const AH_CUSTOMER_YEARLY_DISCOUNT 		= 32;
const AH_VAT_PURCHAS 					= 33;
const AH_AIT_PURCHAS 					= 34;
const AH_RECOVERABLE_COLLECTION 		= 35;
const BALANCE_TRANSFER 					= 36;
const AH_CUSTOMER_BAD_DEBT              = 37;
const AH_FIXED_ASSETS                   = 38;
const AH_FIXED_ASSETS_DEPRECIATION      = 39;

const H_TYPE_CUSTOM                    	= 1;
const H_TYPE_SUPPLIER                   = 2;
const H_TYPE_BANK                       = 3;
const H_TYPE_EMPLOYEE                   = 4;
const H_TYPE_AUTO_HEAD                  = 5;
const H_TYPE_USER                   	= 6;
const H_TYPE_CUSTOMER                   = 12;
const H_TYPE_PERSON                     = 13;

const AHC_EXPENSE                        = 1;  // Auto Head Chart Expense
const AUTO_HEAD_CHART_PAYABLE            = 7;  // Auto Head Chart Payable

const OPENING_VOUCHER_TYPE_SUPPLIER     = 1;
const OPENING_VOUCHER_TYPE_EMPLOYEE     = 2;
const OPENING_VOUCHER_TYPE_LEDGER     	= 3;
const OPENING_VOUCHER_TYPE_CUSTOMER     = 4;
const OPENING_VOUCHER_TYPE_PERSON       = 5;

	


class ACC{
	public DB $db;
	public General $general;
	public SMT $smt;
	private $allBank=[];
	private $systemHead=[];
	public function __construct(General $general,DB $db,SMT $smt){
		$this->db = $db;
		$this->general = $general;
		$this->smt = $smt;
	}
	/**
	 * /
	 * @return array{code: string, id: int, title: string[]}
	 */
	public function all_voucher_type(): array{
		// টাইপ যেমন এক হওয়া যাবেনা সেমনি কোড সেম হওয়া যাবে না 
		return [
			V_T_OPENING=>['id'=>V_T_OPENING,'title'=>'Opening','code'=>'OP'],
			V_T_PURCHASE=>['id'=>V_T_PURCHASE,'title'=>'Purchase','code'=>'PUR'],
			V_T_SALE_CASH=>['id'=>V_T_SALE_CASH,'title'=>'Sale cash','code'=>'SC'],
			V_T_SALE_VAT=>['id'=>V_T_SALE_VAT,'title'=>'Sale vat','code'=>'SV'],
			V_T_SALE_RETURN=>['id'=>V_T_SALE_RETURN,'title'=>'Sale return','code'=>'SR'],
			V_T_CUSTOMER_COLLECTION_DISCOUNT=>['id'=>V_T_CUSTOMER_COLLECTION_DISCOUNT,'title'=>'Customer collection discount','code'=>'CCD'],
			V_T_CUSTOMER_YEARLY_DISCOUNT=>['id'=>V_T_CUSTOMER_YEARLY_DISCOUNT,'title'=>'Customer yearly discount','code'=>'CYD'],
			V_T_SALE_TAX=>['id'=>V_T_SALE_TAX,'title'=>'Sale tax','code'=>'ST'],
			V_T_SALE_VAN_CHART=>['id'=>V_T_SALE_VAN_CHART,'title'=>'Sale van chart','code'=>'SVC'],
			V_T_EMPLOYEE_PAY=>['id'=>V_T_EMPLOYEE_PAY,'title'=>'Employee pay','code'=>'EP'],
			V_T_EMPLOYEE_SALARY=>['id'=>V_T_EMPLOYEE_SALARY,'title'=>'Employee salary','code'=>'ES'],
			V_T_EMPLOYEE_SALE_SALARY_PAY=>['id'=>V_T_EMPLOYEE_SALE_SALARY_PAY,'title'=>'Employee sale salary pay','code'=>'ESS'],
			V_T_SUPPLIER_PAYMENT=>['id'=>V_T_SUPPLIER_PAYMENT,'title'=>'Supplier payment','code'=>'SP'],
			V_T_PURCHASE_RETURN=>['id'=>V_T_PURCHASE_RETURN,'title'=>'Purchase return','code'=>'PR'],
			V_T_SALE_DUE_COLLECT=>['id'=>V_T_SALE_DUE_COLLECT,'title'=>'Sale due collect','code'=>'SDC'],
			V_T_INCOME=>['id'=>V_T_INCOME,'title'=>'Income','code'=>'INC'],
			V_T_EXPENSE=>['id'=>V_T_EXPENSE,'title'=>'Expense','code'=>'EXP'],
			V_T_SALE_RETURN_PROCESS=>['id'=>V_T_SALE_RETURN_PROCESS,'title'=>'Sale_return_process','code'=>'SRP'],
			V_T_PRODUCT_REJECT=>['id'=>V_T_PRODUCT_REJECT,'title'=>'Product_reject','code'=>'PR'],
			V_T_INVEST=>['id'=>V_T_INVEST,'title'=>'Invest','code'=>'INV'],
			V_T_LOAN_RETURN=>['id'=>V_T_LOAN_RETURN,'title'=>'Loan return','code'=>'LR'],
			V_T_LOAN_PAY=>['id'=>V_T_LOAN_PAY,'title'=>'Loan_pay','code'=>'LP'],
			V_T_MAIN_BALANCE_SALARY_PAY=>['id'=>V_T_MAIN_BALANCE_SALARY_PAY,'title'=>'Main balance salary pay','code'=>'BSP'],
			V_T_SALE_CASH_CUSTOMER=>['id'=>V_T_SALE_CASH_CUSTOMER,'title'=>'Sale cash customer','code'=>'SCC'],
			V_T_PAY_TO_CUSTOMER=>['id'=>V_T_PAY_TO_CUSTOMER,'title'=>'Pay to customer','code'=>'PC'],
			V_T_SALE_CASH_RECEIVE_FROM_CUSTOMER=>['id'=>V_T_SALE_CASH_RECEIVE_FROM_CUSTOMER,'title'=>'Sale cash receive from customer','code'=>'SRC'],
			V_T_RECEIVE_FROM_CUSTOMER=>['id'=>V_T_RECEIVE_FROM_CUSTOMER,'title'=>'Receive from customer','code'=>'RC'],
			V_T_CUSTOMER_SALE_DUE_DISTRIBUTE=>['id'=>V_T_CUSTOMER_SALE_DUE_DISTRIBUTE,'title'=>'Customer sale due distribute','code'=>'SDD'],
			V_T_PROFIT_WITHDRAWAL=>['id'=>V_T_PROFIT_WITHDRAWAL,'title'=>'Profit withdrawal','code'=>'PW'],
			V_T_SUPPLIER_PAYMENT_COST=>['id'=>V_T_SUPPLIER_PAYMENT_COST,'title'=>'Supplier payment cost','code'=>'SPC'],
			V_T_PAY_TO_PERSON=>['id'=>V_T_PAY_TO_PERSON,'title'=>'Pay to person','code'=>'PP'],
			V_T_RECEIVE_FROM_PERSON=>['id'=>V_T_RECEIVE_FROM_PERSON,'title'=>'Receive from person','code'=>'RP'],
			V_T_SALE_RETURN_CASH_CUSTOMER=>['id'=>V_T_SALE_RETURN_CASH_CUSTOMER,'title'=>'Sale return cash customer','code'=>'RCC'],
			V_T_SALE_RETURN_CASH_RECEIVE_FROM_CUSTOMER=>['id'=>V_T_SALE_RETURN_CASH_RECEIVE_FROM_CUSTOMER,'title'=>'Sale return cash receive from customer','code'=>'SRR'],
			V_T_PURCHASE_RETURN_CASH_RECEIVE_FROM_SUPPLIER=>['id'=>V_T_PURCHASE_RETURN_CASH_RECEIVE_FROM_SUPPLIER,'title'=>'Purchase return cash receive from supplier','code'=>'PRR'],
			V_T_VAT_PURCHASE=>['id'=>V_T_VAT_PURCHASE,'title'=>'Vat purchase','code'=>'VP'],
			V_T_AIT_PURCHASE=>['id'=>V_T_AIT_PURCHASE,'title'=>'Ait purchase','code'=>'AP'],
			V_T_RECOVERABLE_ENTRY=>['id'=>V_T_RECOVERABLE_ENTRY,'title'=>'Recoverable entry','code'=>'RE'],
			V_T_RECOVERABLE_COLLECTION=>['id'=>V_T_RECOVERABLE_COLLECTION,'title'=>'Recoverable collection','code'=>'RBC'],
			V_T_MPO_BALANCE_IN=>['id'=>V_T_MPO_BALANCE_IN,'title'=>'MPO balance in','code'=>'MBI'],
			V_T_MPO_BALANCE_OUT=>['id'=>V_T_MPO_BALANCE_OUT,'title'=>'MPO balance out','code'=>'MBO'],
			V_T_CUSTOMER_BAD_DEBT=>['id'=>V_T_CUSTOMER_BAD_DEBT,'title'=>'Customer bad DEBT','code'=>'CBD'],
			V_T_VOUCHER_ENTRY=>['id'=>V_T_VOUCHER_ENTRY,'title'=>'Voucher entry','code'=>'VE'],
			V_T_MPO_BALANCE_TRANSFER=>['id'=>V_T_MPO_BALANCE_TRANSFER,'title'=>'Balance transfer','code'=>'BT'],
			V_T_CONTRA=>['id'=>V_T_CONTRA,'title'=>'CONTRA','code'=>'CT'],
			V_T_CONTRA_TR_CHARGE=>['id'=>V_T_CONTRA_TR_CHARGE,'title'=>'CONTRA','code'=>'CT'],
			V_T_RECEIVE_FROM_EMPLOYEE=>['id'=>V_T_RECEIVE_FROM_EMPLOYEE,'title'=>'Employee receive','code'=>'ER'],
			V_T_PRODUCT_STOCK_ENTRY=>['id'=>V_T_PRODUCT_STOCK_ENTRY,'title'=>'Stock entry','code'=>'SE'],
			V_T_FIXED_ASSETS_PURCHASE=>['id'=>V_T_FIXED_ASSETS_PURCHASE,'title'=>'Fixed assets purchase','code'=>'AP'],
			V_T_FIXED_ASSETS_DEPRECIATION=>['id'=>V_T_FIXED_ASSETS_DEPRECIATION,'title'=>'Fixed assets depreciation','code'=>'AD'],
			V_T_NEW_RECOVERABLE_ENTRY=>['id'=>V_T_NEW_RECOVERABLE_ENTRY,'title'=>'Recoverable entry','code'=>'RE'],
			V_T_NEW_RECOVERABLE_COLLECTION=>['id'=>V_T_NEW_RECOVERABLE_COLLECTION,'title'=>'Recoverable collection','code'=>'RBC'],
			V_T_DOCTOR_HONORARIUM=>['id'=>V_T_DOCTOR_HONORARIUM,'title'=>'Doctor honorarium','code'=>'DH'],
			V_T_EMPLOYEE_SALARY_ALLOWANCE=>['id'=>V_T_EMPLOYEE_SALARY_ALLOWANCE,'title'=>'Employee salary allowance','code'=>'RBC'],
		];
	}
	public function get_voucher_type_data($type){
		$all_type = $this->all_voucher_type();
		return $all_type[$type]??false;
	}
	public function columnNameById($columnID){
		if($columnID==CREDIT){return 'Credit';}
		elseif($columnID==DEBIT){return 'Debit';}
		else{return 'Unknown';}
	}
	public function chartsAccountsInfoByID($caID){ return $this->db->get_rowData('a_charts_accounts','caID',$caID);}
	public function headInfoByID($hID){ return $this->db->get_rowData('a_ledgers','id',$hID);}
	
	public function get_head_type($type){
		if($type==H_TYPE_CUSTOMER){
			return 'Customer';
		}
		else if($type==H_TYPE_SUPPLIER){
			return 'Supplier';
		}
		else if($type==H_TYPE_AUTO_HEAD){
			return 'System';
		}
		else if($type==H_TYPE_CUSTOM){
			return 'Custom';
		}
		else{
			return "N/A $type";
		}
	}


	/**
	* Set the block dimensions accounting for page breaks and page/column fitting
	* @param int Type of opening
	* @param int Which type of opening Balance reference customer supplier etc
	* @param int Generated head of opening Ref
	* @param float transaction amount
	* @param int transaction type(DEBIT / CREDIT)
	* @param int ইউজার আইডি
	* @param array ডিবাগ করার জন্য লাগে অপশনাল
	* @return int|bool
	*/
	public function opening_voucher_create($opening_type,$ref,$head,$amount,$transactionType,$user_id=0,&$jArray=[]){

		$opening_head=$this->getSystemHead(AH_CAPITAL);
		if($opening_head==false){return false;}

		if($transactionType==DEBIT){
			$debit  = $head;
			$credit = $opening_head;
		}
		else{
			$debit  = $opening_head;
			$credit = $head;
		}
		$time=strtotime('30-06-2024');
		return $this->voucher_create(V_T_OPENING,$amount,$debit,$credit,$time,'Opening',$opening_type.'_'.$ref,$user_id);
	}
	/**
	* Please do not use this function directly. Use voucher_create instead
	* @param (int) Section ID all time zero
	* @param (int) Voucher Type
	* @param (float) Voucher Amount
	* @param (int) Debit Head
	* @param (int) Credit Head
	* @param (int) voucher Time
	* @param (string) voucher Particular
	* @param (string) voucher Reference
	* @param (int) Integer Default current user id
	* @param (array) for data check
	* @return int or false
	*/
	public function newVoucher($scID,$vType,$amount,$headDebit,$headCredit,$veTime,$note='',$vTypeRef='',$user_id=0,&$jArray=[]){
		if($user_id==0){
			if(!defined('USER_ID')){define('USER_ID',0);}
			$user_id=USER_ID;
		}
		$voucher_data=[
			[
				'ledger_id'           => $headDebit,
				'debit'    => $amount,
				'credit'    => 0,
				'time'        => $veTime,
				'note' => $note,
				'user_id'           => $user_id
			],
			[
				'ledger_id'           => $headCredit,
				'debit'    => 0,
				'credit'    => $amount,
				'time'        => $veTime,
				'note' => $note,
				'user_id'           => $user_id
			]
		];
		$data=[
			'type'         => $vType,
			'reference'      => $vTypeRef,
			'time'        => $veTime,
			'user_id'           => $user_id
		];
		$this->db->arrayUserInfoAdd($data);
		$voucher_id=$this->db->insert('a_voucher_entry',$data,true,'array',$jArray);
		if($voucher_id!=false){
			foreach($voucher_data as $vd){
				$vd['voucher_id']     = $voucher_id;
				$this->db->arrayUserInfoAdd($vd);
				$insert=$this->db->insert('a_ledger_entry',$vd,false,'array',$jArray);
				if($insert==false){setMessage(66);$error=fl();}
			}
		}else{
			setMessage(66);$error=fl();
			$this->general->printArray('acc $jArray');
			$this->general->printArray($jArray);
		}
		if(isset($error)){if(SHOW_ERROR_LINE=='Yes'){setErrorMessage($error);}}
		if(!isset($error)){return $voucher_id;}else{return false;}//ব্যালেন্স ট্রান্সফারে $voucher_id লাগে
	}
	/**
	* New Voucher Create
	* @param int Voucher Type
	* @param int  Amount
	* @param int Debit Head
	* @param int Credit Head
	* @param int voucher Time
	* @param string voucher Particular
	* @param string voucher Reference
	* @param int Integer Default current user id
	* @param array for data check
	* @return int|bool
	*/
	public function voucher_create(int $type,float $amount,int $debit_head,int $credit_head,int $time,string $note='',string $ref='',int $user_id=0,$extraData=[],&$jArray=[]):bool|int
	{
		$base_id=0;
		if(isset($extraData['base_id'])){
			$base_id = intval($extraData['base_id']);
		}
		
		$voucher_data=[
			[
				'ledger_id'	=> $debit_head,
				'debit'    	=> $amount,
				'credit'    => 0,
				'time'      => $time,
				'note' 		=> $note,
				'user_id'   => $user_id,
				'base_id'   => $base_id,
			],
			[
				'ledger_id'   => $credit_head,
				'debit'    	=> 0,
				'credit'    => $amount,
				'time'      => $time,
				'note' 		=> $note,
				'user_id'   => $user_id,
				'base_id'   => $base_id,
			]
		];
		$data=[
			'type'      => $type,
			'reference' => $ref,
			'time'    	=> $time,
			'user_id'   => $user_id,
			'base_id'   => $base_id,
		];
		$this->db->arrayUserInfoAdd($data);
		$voucher_id=$this->db->insert('a_voucher_entry',$data,true,'array',$jArray);
		if($voucher_id!=false){
			$type_date = $this->get_voucher_type_data($type);
			if(!$type_date){$jArray[fl()]=1;$error=fl();setMessage(66);return false;}
			$voucher_code = $this->db->setAutoCode('voucher',$voucher_id,$type_date['code']);
			if(!$voucher_code){
				$jArray[fl()]=1;
				$error=fl();setMessage(66);return false;
			}
			foreach($voucher_data as $vd){
				$vd['voucher_id']     = $voucher_id;
				$this->db->arrayUserInfoAdd($vd);
				$insert=$this->db->insert('a_ledger_entry',$vd,false,'array',$jArray);
				if($insert==false){setMessage(66);$error=fl();}
			}
		}else{
			setMessage(66);$error=fl();
		}
		$jArray[fl()][]=1;
		if(isset($error)){
			$jArray[fl()][]=$error;
			if(SHOW_ERROR_LINE=='Yes'){setErrorMessage($error);}
		}

		if(!isset($error)){return $voucher_id;}else{
			$jArray[fl()]=$error;
			return false;
		}//ব্যালেন্স ট্রান্সফারে $voucher_id লাগে
	}
	public function user_balance(int $mpo_id,int $to=0){
		
		$cash_head=$this->getSystemHead(AH_CASH);
		return $this->headBalance($cash_head,$to,$mpo_id);
	}
	public function getSystemHead($ahID,&$jArray=[]){
		if(isset($this->systemHead[$ahID])){
			return $this->systemHead[$ahID];
		}
		else{
			$h=$this->db->getRowData('a_ledgers','where type='.H_TYPE_AUTO_HEAD.' and reference_id='.$ahID,'array',$jArray);
			if(empty($h)){
				$jArray[__LINE__][]=fl();
				$ah=$this->db->get_rowData('a_auto_ledger','id',$ahID,'array',$jArray);
				if(!empty($ah)){
					$jArray[fl()][]=fl();
					$ca=$this->db->getRowData('a_charts_accounts','where system_chart='.$ah['auto_head_chart_id']);
					if(!empty($ca)){
						$charts_accounts_id=$ca['id'];
						$jArray[fl()][]=fl();
					}
					else{
						$ahc=$this->db->get_rowData('a_auto_ledger_chart','id',$ah['auto_head_chart_id'],'array',$jArray);
						if(!empty($ahc)){
							$jArray[fl()][]=fl();
							$data=array(
								'master_account_id'          => $ahc['master_account_id'],
								'title'       => $ahc['title'],
								'code'       => '',
								'system_chart'      => $ahc['id']
							);
							$charts_accounts_id=$this->db->insert('a_charts_accounts',$data,'getId');
							if($charts_accounts_id==false){if(SHOW_ERROR_LINE==true){setMessage(133,'acc'.fl());}}
						}else{if(SHOW_ERROR_LINE==true){setMessage(133,'acc'.fl());}}
					}
					if($charts_accounts_id>0){
						$jArray[fl()][]=$charts_accounts_id;
						$data=array(
							'title'    => $ah['title'],
							'charts_accounts_id'      => $charts_accounts_id,
							'system_head'  => 1,
							'code'=>'auto-'.rand(111,9999999),
							'type'     => H_TYPE_AUTO_HEAD,
							'reference_id'      => $ah['id']
						);
						$this->db->arrayUserInfoAdd($data);
						$hID=$this->db->insert('a_ledgers',$data,'getId','array',$jArray);
						$jArray[fl()][]=$hID;
						if($hID==false){if(SHOW_ERROR_LINE==true){setMessage(133,'acc'.fl());}}
					}
				}else{if(SHOW_ERROR_LINE==true){setMessage(63,'Error Code acc '.fl());}}
			}
			else{
				$jArray[fl()][]=fl();
				$hID=$h['id'];
			}

			if(isset($hID)){
				$this->systemHead[$ahID]=$hID;
				return $hID;
			}
			else{
				$this->systemHead[$ahID]=false;
				return false;
			}
		}

	}
	public function cashFlowReport($from,$to,$ledger_id=0,$user_id=0,$vType=0,$vTypeRef=0,&$jArray=[]){
		$rData=[];
		$db=$this->db;
		$general=$this->general;

		$rData[fl()]=$this->general->make_date($from,'time');
		$rData[fl()]=$this->general->make_date($to,'time');
		$cash_ledger_id=$this->getSystemHead(AH_CASH);
		$rData[fl()]=$cash_ledger_id;
		if($cash_ledger_id!=false){
			$transactions=[];
			$q=[];
			$q[]="ledger_id=$cash_ledger_id";
			$q[]="time between $from and $to";
			if($user_id>0){
				$q[]="user_id=$user_id";
			}
			$cashTransactions=$db->selectAll('a_ledger_entry','where '.implode(' and ',$q),'','array',$rData);
			if(!empty($cashTransactions)){
				$rData[fl()]=$cashTransactions;
				$voucher_ids=[];
				$entry_ids=[];
				foreach($cashTransactions as $tr){
					$voucher_ids[$tr['voucher_id']]=$tr['voucher_id'];
					$entry_ids[$tr['id']]=$tr['id'];
				}
				$q=[];
				$q[]='voucher_id in('.implode(',',$voucher_ids).')';
				$q[]='id not in ('.implode(',',$entry_ids).')';
				$q[]='time between '.$from.' and '.$to;
				if($ledger_id>0){
					$q[]='ledger_id ='.$ledger_id;
				}
				if($vType>0||is_array($vType)){
					if(!is_array($vType)){
						$vq='and type='.$vType;
					}
					else{
						$vq='and type in('.implode(',',$vType).')';
					}
					$spvType=$db->selectAll('a_voucher_entry','where id in('.implode(',',$voucher_ids).') '.$vq,'id');
					if(!empty($spvType)){
						$general->arrayIndexChange($spvType,'id');
						$q[]='voucher_id in('.implode(',',array_keys($spvType)).')';
					}
					else{
						$q[]='voucher_id=0';
					}
				}
				if($vTypeRef>0||is_array($vTypeRef)){
					if(!is_array($vTypeRef)){
						$vq='and reference='.$vTypeRef;
					}
					else{
						$vq='and reference in('.implode(',',$vTypeRef).')';
					}
					$spvType=$db->selectAll('a_voucher_entry','where id in('.implode(',',$voucher_ids).') '.$vq,'id');
					if(!empty($spvType)){
						$general->arrayIndexChange($spvType,'id');
						$q[]='voucher_id in('.implode(',',array_keys($spvType)).')';
					}
					else{
						$q[]='voucher_id=0';
					}
				}

				$transactions=$db->selectAll('a_ledger_entry','where '.implode(' and ',$q).' order by time asc','','array',$rData);
			}




			if(!empty($transactions)){
				$hIDs=[];
				foreach($transactions as $k=>$tr){
					$dr=$tr['debit'];
					$cr=$tr['credit'];
					if(!isset($transactions[$k]['debit'])){
						$transactions[$k]['debit']=0;
						$transactions[$k]['credit']=0;
					}
					$transactions[$k]['debit']+=$cr;
					$transactions[$k]['credit']+=$dr;
					$hIDs[$tr['ledger_id']]=$tr['ledger_id'];
				}


				$heads=$db->selectAll('a_ledgers','where id in('.implode(',',$hIDs).')');

				$general->arrayIndexChange($heads,'id');
				foreach($transactions as $k=>$tr){
					$transactions[$k]['ve']=@$spvType[$tr['voucher_id']];
				}
				$rData['transactions']=$transactions;
				$rData['heads']=$heads;
			}
		}
		else{
			$jArray[fl()]=1;
		}
		return $rData;
	}
	public function employeeSalaryInfo($eID,$year,$month,$fromCache=true){
		$cacheKey='empSalary_'.$eID.'_'.$year.'_'.$month;
		$cache=$this->db->reportCacheGet($cacheKey);
		if($cache!=false&&!isset($_GET['flush'])&&$fromCache==true){
			$salary=$cache;
		}
		else{
			$e=$this->smt->employeeInfoByID($eID);
			$salaryDate=strtotime('01-'.$month.'-'.$year);
			//echo $salaryDate;echo '<br>';echo $this->general->make_date($salaryDate);echo '<br>';
			//echo $salaryDate;echo '<br>';echo $this->general->make_date($e['eDOJ']);echo '<br>';
			if($salaryDate<$e['date_of_join']){return false;}
			$currentSalary=$this->voucherDetails(V_T_EMPLOYEE_SALARY,$eID.'_'.$year.'_'.$month);
			//$line=__FILE__.' '.__LINE__;$this->general->printArray($line);
			//$this->general->varDump($currentSalary);
			if(empty($currentSalary)){
				return false;
			}
			else{
				//$this->general->printArray($currentSalary);
				$currentSalary=current($currentSalary);
				$salary     = $currentSalary['amount'];
			}
			$this->db->reportCacheSet($cacheKey,$salary,strtotime('+1 year'));
		}
		return (float)$salary;
	}
	public function getSystemChart($ahcID,&$jArray=[]){
		$chart=$this->db->getRowData('a_charts_accounts','where system_chart='.$ahcID);
		if(empty($chart)){
			$ahc=$this->db->get_rowData('a_auto_ledger_chart','id',$ahcID);
			if(!empty($ahc)){
				$data=array(
					'master_account_id' => $ahc['master_account_id'],
					'title'       		=> $ahc['title'],
					'system_chart'      => $ahc['id']
				);
				$caID=$this->db->insert('a_charts_accounts',$data,true,'array',$jArray);
				return $caID;
			}else{$error=fl();setMessage(66);}
		}
		else{
			return $chart['id'];
		}
		return false;
	}

	public function getPersonHead(&$person,&$jArray=[]){
		$hID=false;
		if($person['ledger_id']>0){
			return $person['ledger_id'];
		}
		if(!isset($person['name'])||!isset($person['id'])){
			$jArray[fl()]=1;
			return false;
		}
		$caID=$this->getSystemChart(AUTO_HEAD_CHART_PAYABLE);
		if($caID==false){$error=fl();setMessage(66);}
		if(!isset($error)){
			$data=[
				'type'     			=> H_TYPE_PERSON,
				'reference_id'      => $person['id'],
				'code'				=> 'PR-'.$person['id'],
				'title'    			=> $person['name'],
				'charts_accounts_id'=> $caID,
				'system_head'  		=> 1
			];
			$this->db->arrayUserInfoAdd($data);
			$hIDd=$this->db->insert('a_ledgers',$data,'getId','array',$jArray);
			if($hIDd==false){$error=fl();setMessage(66);}
			$person['ledger_id']=$hIDd;
			$data=['ledger_id'   => $hIDd];
			$where=['id' => $person['id']];
			$update=$this->db->update('person',$data,$where,'array',$jArray);
			if($update==false){$error=fl();setMessage(66);}
			if(!isset($error)){
				$hID=$hIDd;    
			}
		}
		
		return $hID;
	}
	public function getCustomerHead(&$c,&$jArray=[]){
		if(!isset($c['ledger_id'])&&!isset($c['name'])){
			$jArray[fl()]=1;
			return false;
		}
		$hID=false;
		if($c['ledger_id']>0){
			return $c['ledger_id'];
		}
		else{
			$caID=$this->getSystemChart(AUTO_HEAD_CHART_PAYABLE);
			if($caID==false){$error=fl();setMessage(66);}
			if(!isset($error)){
				$data=[
					'type'     => H_TYPE_CUSTOMER,
					'reference_id'      => $c['id'],
					'code'			=> 'CUS'.$c['id'],
					'title'    => $c['name'],
					'charts_accounts_id'      => $caID,
					'system_head'  => 1
				];
				$this->db->arrayUserInfoAdd($data);
				$hIDd=$this->db->insert('a_ledgers',$data,true,'array',$jArray);
				if($hIDd==false){$error=fl();setMessage(66);}
				$c['ledger_id']=$hIDd;
				$data=['ledger_id'   => $hIDd];
				$where=['id' => $c['id']];
				$update=$this->db->update('customer',$data,$where,'array',$jArray);
				if($update==false){$error=fl();setMessage(66);}
				if(!isset($error)){
					$hID=$hIDd;    
				}
			}
		}
		return $hID;
	}
	public function get_user_head(&$c,&$jArray=[]){
		$hID=false;
		if(!isset($c['ledger_id']))return false;
		if($c['ledger_id']>0){
			return $c['ledger_id'];
		}
		
		$caID=$this->getSystemChart(AUTO_HEAD_CHART_PAYABLE);
		if($caID==false){$error=fl();setMessage(66);}
		if(!isset($error)){
			$data=[
				'type'     			=> H_TYPE_USER,
				'code'				=> 'US-'.$c['id'],
				'reference_id'  	=> $c['id'],
				'title'    			=> $c['username'],
				'charts_accounts_id'=> $caID,
				'system_head'  		=> 1
			];
			$this->db->arrayUserInfoAdd($data);
			$hIDd=$this->db->insert('a_ledgers',$data,true,'array',$jArray);
			if($hIDd==false){$error=fl();setMessage(66);}
			$c['ledger_id']=$hIDd;
			$data=['ledger_id'   => $hIDd];
			$where=['id' => $c['id']];
			$update=$this->db->update('users',$data,$where,'array',$jArray);
			if($update==false){$error=fl();setMessage(66);}
			if(!isset($error)){
				$hID=$hIDd;    
			}
		}
		
		return $hID;
	}
	public function getSupplierHead(&$sup,&$jArray=[]){
		$hID=false;
		if($sup['ledger_id']>0){
			return $sup['ledger_id'];
		}
		if(!isset($sup['name'])||!isset($sup['id'])){
			$jArray[fl()]=1;
			return false;
		}
		$caID=$this->getSystemChart(AUTO_HEAD_CHART_PAYABLE);
		if($caID==false){$error=fl();setMessage(66);}
		if(!isset($error)){
			$data=array(
				'type'     => H_TYPE_SUPPLIER,
				'reference_id'      => $sup['id'],
				'code'				=> 'SP-'.$sup['id'],
				'title'    => $sup['name'],
				'charts_accounts_id'      => $caID,
				'system_head'  => 1
			);
			$this->db->arrayUserInfoAdd($data);
			$hIDd=$this->db->insert('a_ledgers',$data,'getId','array',$jArray);
			if($hIDd==false){$error=fl();setMessage(66);}
			$sup['ledger_id']=$hIDd;
			$data=array('ledger_id'   => $hIDd);
			$where=array('id' => $sup['id']);
			$update=$this->db->update('suppliers',$data,$where,'array',$jArray);
			if($update==false){$error=fl();setMessage(66);}
			if(!isset($error)){
				$hID=$hIDd;    
			}
		}
		
		return $hID;
	}
	public function get_all_cash_accounts(&$jArray=[]){
		$db=$this->db;
		$banks=$db->selectAll('bank','where status=1');
		$cash_ledger=$this->getSystemHead(AH_CASH);
		$source_ledgers=[];
		if($cash_ledger){
			$source_ledgers[$cash_ledger]=[
				'id'=>$cash_ledger,
				'title'=>'Cash'
			];
		}
		if(!empty($banks)){
			foreach($banks as $b){
				$ledger_id=$this->get_bank_head($b,$jArray);
				$source_ledgers[$ledger_id]=[
					'id'=>$ledger_id,
					'title'=>$b['name']
				];
			}
		}
		return $source_ledgers;
	}
	public function get_bank_head(&$bank,&$jArray=[]){
		$hID=false;
		if($bank['ledger_id']>0){
			return $bank['ledger_id'];
		}
		if(!isset($bank['name'])||!isset($bank['id'])){
			$jArray[fl()]=1;
			return false;
		}
		$caID=$this->getSystemChart(AUTO_HEAD_CHART_PAYABLE);
		if($caID==false){$error=fl();setMessage(66);}
		if(!isset($error)){
			$data=[
				'type'     			=> H_TYPE_BANK,
				'code'				=> 'BANK_'.$bank['id'],
				'reference_id'  	=> $bank['id'],
				'title'    			=> $bank['name'],
				'charts_accounts_id'=> $caID,
				'system_head'  		=> 1
			];
			$this->db->arrayUserInfoAdd($data);
			$hIDd=$this->db->insert('a_ledgers',$data,true,'array',$jArray);
			if($hIDd==false){$error=fl();setMessage(66);}
			$bank['ledger_id']=$hIDd;
			$data=['ledger_id'   => $hIDd];
			$where=['id' => $bank['id']];
			$update=$this->db->update('bank',$data,$where,'array',$jArray);
			if($update==false){$error=fl();setMessage(66);}
			if(!isset($error)){
				$hID=$hIDd;    
			}
		}
		
		return $hID;
	}
	public function headTranssaction($hID,$data=[],&$jArray=[]){
		if($hID<=0){
			return 0.00;
		}
		if(isset($data['from'])){$from=$data['from'];}else{$from=0;}
		if(isset($data['to'])){$to=$data['to'];}else{$to=0;}
		if(isset($data['scID'])){$scID=$data['scID'];}else{$scID=0;}
		if(isset($data['uID'])){$uID=$data['uID'];}else{$uID=0;}

		if($to>0){$to=strtotime('-1 second',$to);}
		else{$to=TIME;}
		$jArray[__LINE__][]=$this->general->make_date($to,'time');
		$q=[];
		if(!is_array($hID)){
			$q[]="hID=".$hID;    
		}
		else{
			$q[]="hID in(".implode(",",$hID).")";
		}
		if($from>0){
			$q[]="time>=".$from;
		}
		$q[]="time<=".$to;
		if($scID>0){
			$q[]='scID='.$scID;
		}
		if($uID>0){
			$q[]='uID='.$uID;
		}
		$query="select * from ".'a_ledger_entry'." where ".implode(' and ',$q);
		$transactions=$this->db->fetchQuery($query,'array',$jArray);
		if(!empty($transactions)){
			$veIDs=[];
			foreach($transactions as $t){
				$veIDs[$t['veID']]=$t['veID'];
			}
			$vouchers=$this->db->selectAll('a_voucher_entry','where veID in('.implode(',',$veIDs).')','veID,vType,vTypeRef');
			$this->general->arrayIndexChange($vouchers,'veID');
			foreach($transactions as $k=>$i){
				$v=$vouchers[$i['veID']];
				$transactions[$k]['vType']=$v['vType'];
				$transactions[$k]['vTypeRef']=$v['vTypeRef'];
			}
		}


		return $transactions;

	}
	
	/**
	* Get head closing balance. Single head or head array balance return
	* @param (int|array) Head id
	* @param (int) timestamp
	* @param (int) user id
	* @param (array) array/anything
	* @param (array) for debug or logging
	* @return bool/int
	*/
	public function headBalance(int|array $hID,$to=0,$uID=0,$extraData=[],&$jArray=[]):array|float{
		if($hID<=0){
			return 0.00;
		}
		if($to==0){
			$to=TIME;
			//$jArray[fl()][$hID.'_'.$to]=date('i',$to).' '.date('s',$to);
		}
		
		$q=[];
		if(!is_array($hID)){
			$q[]="ledger_id=".$hID;    
		}
		else{
			$q[]="ledger_id in(".implode(",",$hID).")";
		}

		$q[]="time<$to";//ওপেনিং এই সময়ের আগের ট্রানজেকশনই হবে। এখন থেকে রানিং ট্রানজেকশন 
		$gb='';
		$col='(sum(debit)-sum(credit)) as balance';
		if(isset($extraData['groupByHID'])){
			$col.=',ledger_id';
			$gb=' group by ledger_id';
		}
		$query="select $col from a_ledger_entry where ".implode(' and ',$q).$gb;
		$jArray[fl()][]= $query;
		// if($uID>0){
		// 	$query.=" and user_id=$uID";
		// }
		//echo $query;
		$balance=$this->db->fetchQuery($query,'array',$jArray);
		if(!isset($extraData['groupByHID'])){
			$balance=floatval($balance[0]['balance']);
		}
		return $balance;
	}
	public function voucherPrint($veID,$ve=[]){

	}
	public function voucherTypeOption(){
?><option value="<?php echo V_T_RECEIVE_FROM_CUSTOMER;?>">Receive from customer</option><?php
?><option value="<?php echo V_T_EMPLOYEE_SALE_SALARY_PAY;?>">Employee salary pay</option><?php
?><option value="<?php echo V_T_EMPLOYEE_PAY;?>">Employee Pay</option><?php
	}
	public function getAllHead(){
		$heads=$this->db->selectAll('a_ledgers','where isActive=1 order by title asc');
		$this->general->arrayIndexChange($heads,'id');
		return $heads;
	}
	public function getEmployeeHead(array &$emp,array &$jArray=[]){
		if(empty($emp)){
			return false;
		}
		if(!isset($emp['ledger_id'])||!isset($emp['name'])||!isset($emp['id'])){
			$jArray[fl()]=1;
			return false;
		}
		$hID=false;
		if($emp['ledger_id']>0){
			return $emp['ledger_id'];
		}
		else{
			$chart=$this->db->getRowData('a_charts_accounts','where system_chart='.AHC_EXPENSE);//Employee এক্সপেন্স টাইপ হেড হবে
			if(empty($chart)){
				$ahc=$this->db->get_rowData('a_auto_ledger_chart','master_account_id',AHC_EXPENSE);
				if(!empty($ahc)){
					$data=[
						'master_account_id' => $ahc['master_account_id'],
						'title'       		=> $ahc['title'],
						'system_chart'      => $ahc['id']
					];
					$caID=$this->db->insert('a_charts_accounts',$data,true,'array',$jArray);
				}else{$error=fl();setMessage(66);}
			}
			else{
				$caID=$chart['id'];
			}
			if(!isset($error)){
				$data=[
					'type'     			=> H_TYPE_EMPLOYEE,
					'reference_id'      => $emp['id'],
					'title'				=> $emp['name'],
					'code'				=> 'EMP-'.$emp['id'],
					'charts_accounts_id'=> $caID,
					'system_head'  		=> 1
				];
				$this->db->arrayUserInfoAdd($data);
				$hIDd=$this->db->insert('a_ledgers',$data,true,'array',$jArray);
				if($hIDd==false){$error=fl();setMessage(66);}
				$emp['ledger_id']=$hIDd;
				$data   =array('ledger_id'   => $hIDd);
				$where  =array('id' => $emp['id']);
				$update=$this->db->update('employees',$data,$where,'array',$jArray);
				if($update==false){$error=fl();setMessage(66);}
				if(!isset($error)){
					$company_data = $this->db->get_company_data();
            		$for_voucher_entry_ledgers = $company_data['for_voucher_entry_ledgers']??[];
            		$for_voucher_entry_ledgers[$hIDd]=$hIDd;
					$company_data['for_voucher_entry_ledgers']=$for_voucher_entry_ledgers;
                    $update = $this->db->company_data_update($company_data);
					$hID=$hIDd;
				}
			}
		}
		return $hID;
	}
	public function head_vouchers(array $ledger_ids,$from=0,$to=0,$user_id=0,&$jArray=[]){
		$db=$this->db;
		$general=$this->general;
		$statement=[];
		if(empty($ledger_ids)){
			return $statement;
		}
		$q=[];
		//$q[]='createdOn between '.$from.' and '.$to;
		if($from>0&&$to>0){
			$q[]='time between '.$from.' and '.$to;
		}
		$request_ledger_ids=[];
		
		$q[]='ledger_id in('.implode(',',$ledger_ids).')';
		foreach($ledger_ids as $hh){
			$request_ledger_ids[$hh]=$hh;
		}
		if($user_id>0){
			$q[]='user_id='.$user_id;
		}
		$sq='where '.implode(' and ',$q);

		$query=" select id,voucher_id from a_ledger_entry ".$sq;
		$hEntry=$db->fetchQuery($query,'array',$jArray);
		$jArray[fl()]=$hEntry;
		if(!empty($hEntry)){
			$heIDs=[];
			$veIDs=[];
			foreach($hEntry as $st){
				$veIDs[$st['voucher_id']]=$st['voucher_id'];
				$heIDs[$st['id']]=$st['id'];
			}
			$vouchers=$db->selectAllByID('a_voucher_entry','id',$veIDs);
			
			$entry=$db->selectAllByID('a_ledger_entry','voucher_id',$veIDs,'order by time asc',false,'array',$jArray);
			
			$ledger_ids=[];
			$general->getIDFromVariable($entry,'ledger_id',$ledger_ids);
			$heads=$db->selectAllByID('a_ledgers','id',$ledger_ids);
			
			$voucher_details=[];
			$statement=[];
			foreach($entry as $e){
				$v=$vouchers[$e['voucher_id']];
				if(!isset($voucher_details[$e['voucher_id']])){
					$voucher_details[$e['voucher_id']]=[
						'voucher_id'=> $v['id'],
						'time'      => $v['time'],
						'type'      => $v['type'],
						'reference' => $v['reference'],
						'createdBy' => $v['createdBy'],
						'note'      => $e['note'],
						'debit_head'	=> 0,
						'credit_head'   => 0,
					];
				}
				//$jArray[fl()]=$request_ledger_ids;
				//$jArray[fl()]=$voucher_details;
				if($e['debit']>0){
					$voucher_details[$e['voucher_id']]['debit_head']=$e['ledger_id'];
					$voucher_details[$e['voucher_id']]['amount']=$e['debit'];
				}
				else{
					$voucher_details[$e['voucher_id']]['credit_head']=$e['ledger_id'];
					$voucher_details[$e['voucher_id']]['amount']=$e['credit'];
				}
			}
			foreach($voucher_details as $v){
				if(isset($request_ledger_ids[$v['debit_head']])){
					if(!isset($statement[$v['debit_head']])){
						$statement[$v['debit_head']]=[];
					}
					$statement[$v['debit_head']][]=$v;
				}
				if(isset($request_ledger_ids[$v['credit_head']])){
					if(!isset($statement[$v['credit_head']])){
						$statement[$v['credit_head']]=[];
					}
					$statement[$v['credit_head']][]=$v;
				}
			}
			
		}
		return $statement;
	}
	public function ledger_statement($ledger_id=0,$from=0,$to=0,&$jArray=[]){
		$db=$this->db;
		$general=$this->general;
		$statement=[];
		$q=[];
		if($from>0&&$to>0){
			$q[]='time between '.$from.' and '.$to;
		}
		
		if($ledger_id>0){
			$q[]='ledger_id='.$ledger_id;
		}
		else{
			return [];
		}
		$sq='where '.implode(' and ',$q);

		$query=" select * from a_ledger_entry ".$sq;
		$head_entry=$db->fetchQuery($query,'array',$jArray);
		// $jArray[fl()]=$hEntry;
		if(!empty($head_entry)){
			$ledger_ids=[];
			$veIDs=[];
			foreach($head_entry as $st){
				$veIDs[$st['voucher_id']]=$st['voucher_id'];
				$ledger_ids[$st['ledger_id']]=$st['ledger_id'];
			}
			$jArray[fl()]=$veIDs;
			$vouchers=$db->selectAllByID('a_voucher_entry','id',$veIDs);
			$heads=$db->selectAllByID('a_ledgers','id',$ledger_ids);
			$general->arrayIndexChange($vouchers,'id');
			$general->arrayIndexChange($heads,'id');
			$jArray[fl()]=$vouchers;
			
			foreach($head_entry as $e){
				$v=$vouchers[$e['voucher_id']];
				$type_title=$this->get_voucher_type_data($v['type']);
				if(!$type_title){
					$type_title=$v['type'];
				}
				else{
					$type_title=$type_title['title'];
				}
				$vData=[
					'voucher_id'=> $v['id'],
					'code'		=> $v['code'],
					'time'      => $v['time'],
					'type'      => $v['type'],
					'type_title'=> $type_title,
					'reference' => $v['reference'],
					'createdBy' => $v['createdBy'],
					'ledger_id'   => $e['ledger_id'],
					'head_title'=> @$heads[$e['ledger_id']]['title'],
					'note'      => $e['note'],
					'credit'    => $e['credit'],
					'debit'     => $e['debit'],
				];
				$statement[]=$vData;
			}

		}
		return $statement;
	}
	public function voucher_details($voucher_id){
		$db=$this->db;
		$general=$this->general;
		$v=$db->get_rowData('a_voucher_entry','id',$voucher_id);
		if(!empty($v)){
			$user=$this->db->userInfoByID($v['createdBy']);
			$v['user']=[
				'username'=>$user['username'],
				'name'=>$user['name']
			];
			$v['type']=$this->get_voucher_type_data($v['type']);
			$entrys=$db->selectAll('a_ledger_entry','where voucher_id='.$voucher_id);
			if(!empty($entrys)){
				$ledger_ids=[];
				$general->getIDFromVariable($entrys,'ledger_id',$ledger_ids);
				$heads=$db->selectAllByID('a_ledgers','id',$ledger_ids);
				$general->arrayIndexChange($heads,'id');
				foreach($entrys as $e){
					$vData=[
						'head'		=> $heads[$e['ledger_id']],
						'note'      => $e['note'],
						'credit'    => $e['credit'],
						'debit'     => $e['debit'],
					];
					$v['entrys'][]=$vData;
				}
			}
		}
		return $v;
	}
	public function head_statement($ledger_id=0,$from=0,$to=0,$user_id=0,&$jArray=[]){
		$db=$this->db;
		$general=$this->general;
		$statement=[];
		$q=[];
		//$q[]='createdOn between '.$from.' and '.$to;
		if($from>0&&$to>0){
			$q[]='time between '.$from.' and '.$to;
		}
		
		if(is_array($ledger_id)){
			$q[]='ledger_id in('.implode(',',$ledger_id).')';
		}
		else if($ledger_id>0){
			$q[]='ledger_id='.$ledger_id;
		}
		if($user_id>0){
			$q[]='user_id='.$user_id;
		}
		$sq='where '.implode(' and ',$q);

		$query=" select * from a_ledger_entry ".$sq;
		$hEntry=$db->fetchQuery($query,'array',$jArray);
		$jArray[fl()]=$hEntry;
		if(!empty($hEntry)){
			$heIDs=[];
			$veIDs=[];
			foreach($hEntry as $st){
				$veIDs[$st['voucher_id']]=$st['voucher_id'];
				$heIDs[$st['id']]=$st['id'];
			}
			$jArray[fl()]=$veIDs;
			$vouchers=$db->selectAllByID('a_voucher_entry','id',$veIDs);
			if($ledger_id==0){
				$entry=$db->selectAllByID('a_ledger_entry','voucher_id',$veIDs,' order by time asc',false,'array',$jArray);
			}
			else{
				if(!empty($heIDs)){
					$entry=$db->selectAllByID('a_ledger_entry','voucher_id',$veIDs,' and id not in('.implode(',',$heIDs).') order by time asc',false,'array',$jArray);
				}
				else{
					$entry=[];   
				}
			}
			$ledger_ids=[];
			$general->getIDFromVariable($entry,'ledger_id',$ledger_ids);
			$heads=$db->selectAllByID('a_ledgers','id',$ledger_ids);
			$general->arrayIndexChange($vouchers,'id');
			$jArray[fl()]=$vouchers;
			foreach($entry as $e){
				$v=$vouchers[$e['voucher_id']];
				$vData=[
					'voucher_id'=> $v['id'],
					'time'      => $v['time'],
					'type'      => $v['type'],
					'reference' => $v['reference'],
					'createdBy' => $v['createdBy'],
					'ledger_id'   => $e['ledger_id'],
					'head_title'=> $heads[$e['ledger_id']]['title'],
					'note'      => $e['note'],
					'in'        => $e['credit'],
					'out'       => $e['debit'],
				];
				$statement[]=$vData;
			}

		}
		return $statement;
	}
	public function headStatement($scID,$hID=0,$from=0,$to=0,&$jArray=[]){
		$db=$this->db;
		$general=$this->general;
		$statement=[];




		$q=[];
		//$q[]='createdOn between '.$from.' and '.$to;
		if($from>0&&$to>0){
			$q[]='time between '.$from.' and '.$to;
		}
		//$q[]='scID='.$scID;
		if($hID>0){
			$q[]='ledger_id='.$hID;
		}
		$sq='where '.implode(' and ',$q);

		$query=" select * from a_ledger_entry ".$sq;
		$hEntry=$db->fetchQuery($query,'array',$jArray);
		//$jArray[fl()]=$hEntry;
		if(!empty($hEntry)){
			$heIDs=[];
			$veIDs=[];
			foreach($hEntry as $st){
				$veIDs[$st['voucher_id']]=$st['voucher_id'];
				$heIDs[$st['id']]=$st['id'];
			}
			$vouchers=$db->selectAllByID('a_voucher_entry','id',$veIDs);
			if($hID==0){
				$entry=$db->selectAllByID('a_ledger_entry','voucher_id',$veIDs,' order by time asc',false,'array',$jArray);
			}
			else{
				if(!empty($heIDs)){
					$entry=$db->selectAllByID('a_ledger_entry','voucher_id',$veIDs,' and id not in('.implode(',',$heIDs).') order by time asc',false,'array',$jArray);
				}
				else{
					$entry=[];   
				}
			}

			$general->getIDFromVariable($entry,'ledger_id',$hIDs);
			$heads=$db->selectAllByID('a_ledgers','id',$hIDs);
			// $jArray[fl()]=$vouchers;
			// $jArray[fl()]=$entry;
			// $jArray[fl()]=$hIDs;

			foreach($entry as $e){
				$v=$vouchers[$e['voucher_id']];
				$vData=[
					'voucher_id'=> $v['id'],
					'time'      => $v['time'],
					'type'      => $v['type'],
					'reference' => $v['reference'],
					'createdBy' => $v['createdBy'],
					'user_id' 	=> $v['user_id'],
					'ledger_id'   => $e['ledger_id'],
					'head_title'=> $heads[$e['ledger_id']]['title']??'',
					'code'      => $v['code'],
					'note'      => $e['note'],
					'in'        => $e['credit'],
					'out'       => $e['debit'],
				];
				$statement[]=$vData;
			}

		}
		return $statement;
	}
	
	public function voucher_delete($voucher_id,$extraData=[],&$jArray=[]){
		if(is_array($voucher_id)){
			$delete=$this->db->runQuery('delete from a_ledger_entry where voucher_id in('.implode(',',$voucher_id).')');
			if(!$delete){return false;}
			$delete=$this->db->runQuery('delete from a_voucher_entry where id in('.implode(',',$voucher_id).')');
			if(!$delete){return false;}
			return true;
		}
		else{
			$delete=$this->db->runQuery('delete from a_ledger_entry where voucher_id ='.$voucher_id);
			if(!$delete){return false;}
			$delete=$this->db->runQuery('delete from a_voucher_entry where id ='.$voucher_id);
			if(!$delete){return false;}
			return true; 
		}
	}
	public function voucherDetails($vType=0,$vTypeRef='',$from=0,$to=0,$data=[],&$jArray=[]){
		$db=$this->db;
		$general=$this->general;
		$q=[];
		if(is_array($vType)){
			$q[]='type in('.implode(',',$vType).')';
		}
		else{
			if($vType>0){   
				$q[]='type='.$vType;
			}
			else if($vType==-1){   
				$q[]='type=0';
			}
		}
		if(isset($data['veID'])){
			$q[]='id='.$data['veID'];
		}
		if(isset($data['base_id'])&&$data['base_id']>0){
			$q[]='base_id='.$data['base_id'];
		}
		if(is_array($vTypeRef)){
			if(!empty($vTypeRef)){
				$q[]="reference in('".implode("','",$vTypeRef)."')";
			}
			else{
				$q[]='id=0';
			}
		}
		else{
			if($vTypeRef!=''){
				$q[]="reference='".$vTypeRef."'";
			}
		}
		if($from>0){
			$q[]='time>='.$from;
		}
		if($to>0){
			$q[]='time<='.$to;
		}
		$sq='';
		if(!empty($q)){
			$sq='where '.implode(' and ',$q);
		}

		if(!empty($q)){
			$vouchers=$db->selectAll('a_voucher_entry',$sq,'','array',$jArray);
			if(!empty($vouchers)){
				$general->arrayIndexChange($vouchers,'id');
				$entry=$db->selectAll('a_ledger_entry','where voucher_id in('.implode(',',array_keys($vouchers)).')','','array',$jArray);

				foreach($entry as $e){
					if($e['debit']>0){
						//$general->printArray($e);
						$vouchers[$e['voucher_id']]['amount']       = $e['debit'];
						$vouchers[$e['voucher_id']]['note']         = $e['note'];
						$vouchers[$e['voucher_id']]['debit']        = $e['ledger_id'];
					}
					else{
						$vouchers[$e['voucher_id']]['credit']      = $e['ledger_id'];
					}
					if(!isset($vouchers[$e['voucher_id']]['amount'])){
						$vouchers[$e['voucher_id']]['amount']     = 0;
						$vouchers[$e['voucher_id']]['note'] = '';
					}
				}

			}
			else{
				$jArray[fl()]=1;
			}
			return $vouchers;
		}
		else{
			return []; 
		}

	}
	public function voucherEdit($voucher_id,$amount,$note='',$debitHID=0,$creditHID=0,$time=0,$extraData=[],&$jArray=[]){
		
		$base_id = $extraData['base_id']??false;
		$db=$this->db;
		//$general->printArray($creditHID);
		$ve=$db->get_rowData('a_voucher_entry','id',$voucher_id);
		if(!empty($ve)){
			$details=$db->selectAll('a_ledger_entry','where voucher_id='.$voucher_id);
			$debit_entry=[];
			$credit_entry=[];
			foreach($details as $d){
				if($base_id!==false){
					$d['base_id']=$base_id;
				}
				if($time>0){
					$d['time']=$time;
				}
				if($note!=''){
					$d['note']  = $note; 
				}
				if($d['debit']>0){
					$d['debit']=$amount;
					if($debitHID>0){
						$d['ledger_id']=$debitHID;
					}
					$debit_entry=$d;
				}
				else{
					$d['credit']=$amount;
					if($creditHID>0){
						$d['ledger_id']=$creditHID;
					}
					$credit_entry=$d;
				}
			}
			// if(empty($debit_entry)||empty($credit_entry)){
			// 	foreach($details as $d){
			// 		if(empty($debit_entry)){
			// 			$debit_entry=$d;
			// 		}
			// 		else{
			// 			$credit_entry=$d;
			// 		}
			// 	}
			// }
			$jArray[fl()]=$voucher_id;
			$jArray[fl()]=$credit_entry;
			// echo '<pre>';
			// var_dump($debitHID);
			// var_dump($creditHID);
			// print_r($debit_entry);
			// print_r($credit_entry);
			// print_r($details);
			// echo '</pre>';
			$update=$db->update('a_ledger_entry',$debit_entry,['id'=>$debit_entry['id']],'array',$jArray);
			if($update==false){
				$jArray[fl()]=1;
				if($update==false){return false;}
			}
			$update=$db->update('a_ledger_entry',$credit_entry,['id'=>$credit_entry['id']],'array',$jArray);
			if($update==false){
				$jArray[fl()]=1;
				if($update==false){return false;}
			}
			$data=[];
			if($time>0){
				$data['time']=$time;
			}
			if($base_id!==false){
				$data['base_id']=$base_id;
			}
			if(!empty($data)){
				$where=['id'=>$voucher_id];
				$update=$db->update('a_voucher_entry',$data,$where,'array',$jArray);
				if($update==false){
					$jArray[fl()]=1;
					return false;
				}
			}
			
		}else{return false;}
		return true;
	}
	public function customer_due_details($customer_id){
		$general=$this->general;
		$customer = $this->smt->customerInfoByID($customer_id);
		$ledger_id = $this->getCustomerHead($customer);
		$balance = $this->headBalance($ledger_id);
		$due_data=[];
		$due_date = 0;
		if($balance>0){
			$calculation_balance=0;
			$break=true;
			$count_sale = 0;
			$sale_ids=[];
			while($break){
				$query = '';
				if($count_sale>0){
					$query= "OFFSET $count_sale";
				}
				$q=['customer_id='.$customer_id];
				if(!empty($sale_ids)){
					$q[]='id not in('.implode(',',$sale_ids).')';
				}
				$data = $this->db->selectAll('sale','where '.implode(' and ',$q).' ORDER BY id DESC LIMIT 10 '.$query);
				if(empty($data)){$break=false;break;}
				else{
					foreach($data as $d){
						$sale_ids[$d['id']]=$d['id'];
						$sale_data=$general->getJsonFromString($d['data']);
						if(isset($sale_data['paid'])){
							$d['total']-=$sale_data['paid'];
							if($d['total']<=0)continue;
						}
						$calculation_balance+=$d['total'];
						if($due_date==0||$due_date>$d['collection_date']){
							$due_date = $d['collection_date'];
						}
						if($calculation_balance>=$balance){
							$due = $d['total']-($calculation_balance-$balance);
							$d['due']=$due;
							$due_data[]=$d;
							
							$break=false;
							break;
						}
						else{
							$d['due']=$d['total'];
							$due_data[]=$d;
						}
					}
				}
			}
		}
		$balance = -$balance;
		
		return ['customer_balance'=>$balance,'due_data'=>$due_data,'due_date'=>$due_date];
	}
	/**
	 * Summary of confirmCustomerDeposit
	 * @param mixed $id
	 * @param mixed $jArray
	 * @return array
	 */
	public function confirmCustomerDeposit($id,&$jArray=[]): array {
		$db = $this->db;
		
		$smt = $this->smt;
		$id = intval($id);
		$old = $db->get_rowData('customer_amount_receive', 'id', $id);
		// Check if the record exists and its status is 0
		if (empty($old) || $old['status'] != 0) {
			return ['status' => 0, 'message' => 'Invalid or already confirmed deposit'];
		}
		$data = [
			'confirm_by' => USER_ID,
			'confirm_time' => TIME,
			'status' => 1,
		];
		$where = ['id' => $id];
		// Update the customer amount receive record
		$update = $db->update('customer_amount_receive', $data, $where);
		if (!$update) {
			return ['status' => 0, 'message' => 'Failed to confirm deposit'];
		}
	
		$customer = $smt->customerInfoByID($old['customer_id']);
		$jArray[fl()][]=$customer;
		if (!empty($customer['mobile'])) {
			$veriables=[
				'amount'=>$old['amount']
			];

			$sms=$this->smt->generate_sms('money_receive_form_customer',$veriables,$customer['mobile'],$jArray);
			if (!$sms) {
				return ['status' => 0, 'message' => 'Deposit confirmed, but SMS sending failed'];
			}
		}
		return ['status' => 1, 'message' => 'Deposit confirmed successfully'];
	}
	public function get_chart_of_account(string $key){
		return $this->db->get_company_data()['chart_of_account'][$key]??0;
	}
	public function chart_of_accounts(): array{
		return [
			COA_ADMINISTRATIVE_OVERHEAD=>['title'=>'Administrative Overhead','id'=>COA_ADMINISTRATIVE_OVERHEAD],
			COA_SALES_AND_MARKETING_EXPENSES=>['title'=>'Sales and Marketing Expenses','id'=>COA_SALES_AND_MARKETING_EXPENSES],
			COA_FACTORY_OVER_HEAD=>['title'=>'Factory Overhead','id'=>COA_FACTORY_OVER_HEAD],
			COA_COST_OF_PRODUCTION=>['title'=>'Cost of Production','id'=>COA_COST_OF_PRODUCTION],
			COA_ADMINISTRATIVE_EXPENSES=>['title'=>'Administrative Expenses','id'=>COA_ADMINISTRATIVE_EXPENSES],
			COA_SELLING_AND_MARKETING_EXP=>['title'=>'Selling & Marketing Exp','id'=>COA_SELLING_AND_MARKETING_EXP],
			COA_DISTRIBUTION_EXPENSES=>['title'=>'Distribution Expenses','id'=>COA_DISTRIBUTION_EXPENSES],
			COA_FINANCIAL_EXPENSES=>['title'=>'Financial Expenses','id'=>COA_FINANCIAL_EXPENSES],
			COA_CASH_AND_CASH_EQUIVALENT=>['title'=>'Cash & Cash Equivalent','id'=>COA_CASH_AND_CASH_EQUIVALENT],
			COA_SALES_RECEIVABLE=>['title'=>'Sales Receivable','id'=>COA_SALES_RECEIVABLE],
			COA_EMPLOYEES_ADVANCE=>['title'=>'Employees Advance','id'=>COA_EMPLOYEES_ADVANCE],
			COA_DIRECTOR_LOANS=>['title'=>'Director Loans','id'=>COA_DIRECTOR_LOANS],
			COA_ADVANCE_TO_SUPPLIERS=>['title'=>'Advance to Suppliers','id'=>COA_ADVANCE_TO_SUPPLIERS],
			COA_PROPERTY_PLANT_AND_EQUIPMENT=>['title'=>'Property, Plant & Equipment','id'=>COA_PROPERTY_PLANT_AND_EQUIPMENT],
			COA_FACTORY_CONSTRUCTION_AND_RENOVATION=>['title'=>'Factory Construction & Renovation','id'=>COA_FACTORY_CONSTRUCTION_AND_RENOVATION],
			COA_ACCOUNTS_PAYABLE=>['title'=>'Accounts Payable','id'=>COA_ACCOUNTS_PAYABLE],
			COA_NON_CURRENT_LIABILITIES=>['title'=>'Non-Current Liabilities','id'=>COA_NON_CURRENT_LIABILITIES],
			COA_PAID_UP_CAPITAL=>['title'=>'Paid Up Capital','id'=>COA_PAID_UP_CAPITAL],
			COA_SHARE_MONEY_DEPOSIT=>['title'=>'Share Money Deposit','id'=>COA_SHARE_MONEY_DEPOSIT],
			COA_RETAINED_EARNINGS=>['title'=>'retained-earnings','id'=>COA_RETAINED_EARNINGS],
		];
	}
	public function get_ledger_types(){
		return [
			H_TYPE_CUSTOM=>['id'=>H_TYPE_CUSTOM,'title'=>'Custom'],
			H_TYPE_SUPPLIER=>['id'=>H_TYPE_SUPPLIER,'title'=>'Supplier'],
			H_TYPE_CUSTOMER=>['id'=>H_TYPE_CUSTOMER,'title'=>'Customer'],
			//H_TYPE_AUTO_HEAD=>['id'=>H_TYPE_AUTO_HEAD,'title'=>'Asset'],
			H_TYPE_AUTO_HEAD=>['id'=>H_TYPE_AUTO_HEAD,'title'=>'System head'],//এখানে একটা ঝামেলা আছে মইনুল কি ঝামেলা  সালাম
		];
	}
	public function ledger_accounts(): array{
		return [
			LEDGER_ACCOUNT_OTHER_REVENUE=>['title'=>'Other Revenue','id'=>LEDGER_ACCOUNT_OTHER_REVENUE],
			LEDGER_ACCOUNT_PROVISION_FOR_INCOME_TAX=>['title'=>'Provision for Income Tax','id'=>LEDGER_ACCOUNT_PROVISION_FOR_INCOME_TAX],
		];
	}
	public function getHeadAccountId(string $id){
		$ledgerAccounts = $this->ledger_accounts();
		if (!array_key_exists($id, $ledgerAccounts)) {
			throw new InvalidArgumentException("Invalid ledger account ID: {$id}");
		}
		$company_data = $this->db->get_company_data();
		return $company_data['ledger_accounts'][$id]??0;
	}
	public function voucher_details_html(){
		echo $this->general->fileToVariable(ROOT_DIR.'/ajax/account/voucher/voucher_details_init.phtml');
	}
}