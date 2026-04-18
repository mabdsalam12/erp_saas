<?php
class SMT{
    public $allSection=[];
    public $allSup=[];
    public $allOwner=[];
    public $allDpsPackage=[];
    public $all_employee=[];
    public $allCustomer=[];
    private $all_user=[];
    public $allPerson=[];
    public $allMember=[];
    public $allProduct=[];
    public $allLoanProduct=[];
    public $allRoute=[];
    public DB $db;
    public General $general;
    public function __construct(General $general, DB $db){
        $this->db       = $db;
        $this->general  = $general;
    }

    public function productStockChangeLog($product_id,$qty,$type,$refID,$actionDate='',&$jArray=[]){
        if($actionDate==''){$actionDate=TIME;}
        if(
            $type==ST_CH_SALE
            ||$type==ST_CH_PURCHASE_RETURN
            ||$type==ST_CH_PRODUCTION_SOURCE
            ||$type==ST_CH_PRODUCTION_SOURCE_MAN
            ||$type==ST_CH_REJECT
            ||$type==ST_CH_DISTRIBUTE
            ){
            $qty=-$qty;
        }
        $data=[
            'product_id'    => $product_id,
            'change_type'   => $type,
            'reference_id'  => $refID,
            'quantity'      => $qty,
            'action_time'   => $actionDate,
            'entry_time'    => TIME,
        ];
        return $this->db->insert('product_stock_log',$data,jArray:$jArray);

    }
    
    function productInfoByID($pID,$fromCash=true){
        if(isset($this->allProduct[$pID])&&$fromCash==true){
            return $this->allProduct[$pID];
        }
        else{
            $b=$this->db->get_rowData('products','id',$pID);
            $this->allProduct[$pID]=$b;
            return $b;
        }
    }
    function saleInfoByID($sID){
        return $this->db->get_rowData('sale','id',$sID);
    }
    function supplierInfoByID($supplier_id){
        if(isset($this->allSup[$supplier_id])){
            return $this->allSup[$supplier_id];
        }
        else{
            $b=$this->db->get_rowData('suppliers','id',$supplier_id);
            $this->allSup[$supplier_id]=$b;
            return $b;
        }
    }
    function employeeInfoByID($id){
        if(isset($this->all_employee[$id])){
            return $this->all_employee[$id];
        }
        else{
            $b=$this->db->get_rowData('employees','id',$id);
            $this->all_employee[$id]=$b;
            return $b;
        }
    }
    function customerInfoByID($cID){
        if(isset($this->allCustomer[$cID])){
            return $this->allCustomer[$cID];
        }
        else{
            $b=$this->db->get_rowData('customer','id',$cID);
            $this->allCustomer[$cID]=$b;
            return $b;
        }
    }
    function userInfoByID($user_id){
        if(isset($this->all_user[$user_id])){
            return $this->all_user[$user_id];
        }
        else{
            $b=$this->db->get_rowData('users','id',$user_id);
            $this->all_user[$user_id]=$b;
            return $b;
        }
    }
    function personInfoByID($id){
        if(isset($this->allPerson[$id])){
            return $this->allPerson[$id];
        }
        else{
            $b=$this->db->get_rowData('person','id',$id);
            $this->allPerson[$id]=$b;
            return $b;
        }
    }

    function getAllUnit(){
        $heads=$this->db->selectAll('unit','where isActive=1 order by title asc');
        $this->general->arrayIndexChange($heads,'id');
        return $heads;
    }
    function productClosingStock($product_id,$to=0){
        if($to==0){$to=TIME;}
        $closing=$this->db->selectAll('product_stock_log','where product_id='.$product_id.' and action_time<='.$to,'sum(quantity) as stock');
        return intval($closing[0]['stock']);
    }
    function update_product_closing_stock($product_id,&$jArray=[]){
        $db=$this->db;
        $closing_stock=$this->productClosingStock($product_id,strtotime('+5 year'));
        $where=['id'=>$product_id];
        $update=$db->update('products',['stock'=>$closing_stock],$where,'array',$jArray);
        if($update==false){
            $jArray[fl()][]=1;
            return false;
        }
        return true;
    }
    
    public function sms_cron(){
        if(defined('NO_SMS_SEND')){
            return;
        }
        $db     = $this->db;
        $general= $this->general;
        $s=$db->getRowData('queue_sms','where send_time=0 and is_pic=0 and total_try<2');
        if(!empty($s)){
            $general->createLog('SMS',$s);
            $data=[
                'is_pic'=>1
            ];
            $where=['id'=>$s['id']];
            $db->update('queue_sms',$data,$where);
            $response=$this->general->getJsonFromString($s['response'],true);
            $sms_data=$this->general->getJsonFromString($s['data'],true);
            $send_time=0;
            $response[fl()][]=$general->make_date(time(),'time');

            $text=$sms_data['text'];
            $text=str_ireplace("\r\n","\n",$text);
            $text=str_ireplace("\n","\n",$text);


            $url    = "https://sms-service.xylub.com/?api=send-single-message";
            $data   = [
                "api_key"   => SMS_API_KEY,
                "mobile"    => $s['mobile'],
                "text"      => $text
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $result = curl_exec($ch);
            if($result === false){
                $response[fl()][]=curl_error($ch);
            }
            else{
                $send_time=TIME;
                $response[fl()][]=$result;
            }
            curl_close($ch);
            $general->createLog('smsCroneResponse',$result);
            $total_try=$s['total_try']+1;

            $data=[
                'send_time'         => $send_time,
                'total_try'         => $total_try,
                'is_pic'             => 0,
                'response'  => json_encode($response)
            ];
            $where=['id'=>$s['id']];
            $db->update('queue_sms',$data,$where);
        }
    }
    private $base_data=[];
    public function base_info_by_id($id){
        if(isset($this->base_data[$id])){
            return $this->base_data[$id];
        }
        else{
            $base=$this->db->get_rowData('base','id',$id);
            $this->base_data[$id]=$base;
            return $base;
        }
    }
    function get_base_wise_all_customer($query=''){
        $db=$this->db;
        $general=$this->general;
        $customers = $db->selectAll('customer',"where isActive=1 $query",'id,name,code,due_day,base_id');
        $general->arrayIndexChange($customers,'id');
        $base_customers = [];
        if(!empty($customers)){
            foreach($customers as $k=>$p){
                $customers[$k]['name']=$p['code'].' '.$p['name'];
            }

            foreach($customers as $c){
                $base_customers[$c['base_id']][]=$c;
            }
        }
        return [
            'customers'=>$customers,
            'base_customers'=>$base_customers
        ];
    }
    
    public function get_all_product_type($filter=[]){
        $return=[];
        if($this->db->permission(10)){
            $return[PRODUCT_TYPE_RAW]= ['id'=>strval(PRODUCT_TYPE_RAW),'title'=>'Raw'];
        }
        if($this->db->permission(11)){
            $return[PRODUCT_TYPE_FINISHED]= ['id'=>strval(PRODUCT_TYPE_FINISHED),'title'=>'Finished'];
        }
        // if($this->db->permission(12)){
        //     $return[PRODUCT_TYPE_GIFT_ITEM]= ['id'=>strval(PRODUCT_TYPE_GIFT_ITEM),'title'=>'Gift item'];
        // }
        if($this->db->permission(13)){
            $return[PRODUCT_TYPE_PACKAGING]= ['id'=>strval(PRODUCT_TYPE_PACKAGING),'title'=>'Packaging'];
        }
        // if($this->db->permission(14)){
        //     $return[PRODUCT_TYPE_STATIONARY]= ['id'=>strval(PRODUCT_TYPE_STATIONARY),'title'=>'Stationary'];
        // }
        if($this->db->permission(14)){
            $return[PRODUCT_TYPE_MANUFACTURING]= ['id'=>strval(PRODUCT_TYPE_MANUFACTURING),'title'=>'Manufacturing'];
        }
        if(!empty($filter)){
            $return=array_filter($return,function($v) use ($filter){
                return in_array($v['id'],$filter);
            });
        }
        return $return;
    }
    public function generate_sms($event,$variables,$to,&$jArray=[]){
        $company_data = $this->db->get_company_data();
        $sms_event=[];
        if(isset($company_data['sms_event'])){
            $sms_event=$company_data['sms_event'];
        }
        else{
            $jArray[fl()]=1;
        }
        $jArray[fl()][]=$sms_event;
        $jArray[fl()][]=$event;
        if(isset($sms_event[$event])){
            //echo $sms_event[$event];exit;
            $template=$this->db->get_rowData('sms_template','id',$sms_event[$event],'array',$jArray); 
            if(!empty($template)){
                $variables['company_name']=COMPANY_NAME;
                $variables['company_mobile']=COMPANY_MOBILE;
                $text=$template['body'];
                foreach($variables as $k=>$v){
                    $text=str_ireplace('{{'.$k.'}}',$v,$text);
                }
                $smsData = [
					'text' => $text,
				];
				$data = [
					'data'      => json_encode($smsData),
					'mobile'    => $to,
					'add_time'  => TIME,
				];
				$jArray[fl()][]=$data;
				return $this->db->insert('queue_sms', $data,false,'array',$jArray);
            }
            else{
                $jArray[fl()][]=$sms_event;
            }
        }
        else{
            $jArray[fl()][]=$event;
        }
        return false;
    }
    public function doctor_get(int $doctor_id){
        return $this->db->get_rowData('doctor','id',$doctor_id);
    }
}