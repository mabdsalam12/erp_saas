<?php
class Customer_closing_sms{
    private $db;
    private $general;
    private int $from=0;
    private int $to=0;
    private SMT $smt;
    public function __construct(private ACC $acc){
        $this->db = $acc->db;
        $this->general = $acc->general;
        $this->smt = $acc->smt;
    }
    private function get_all_customer_data(){
        $db=$this->db;
        $acc=$this->acc;
        $q=[];
        $q[]='isActive in(0,1)';
        $customers=$db->selectAll('customer','where '.implode(' and ',$q),'id,name,code,ledger_id');
        if(!empty($customers)){
            $ledger_ids=[];
            foreach($customers as $c){
                $ledger_id=$this->acc->getCustomerHead($c);
                $ledger_ids[$ledger_id]=$ledger_id;
                //$customer_data[$ledger_id]=$c;
            }
            $opening_balances=$acc->headBalance($ledger_ids,$this->from,0,['groupByHID'=>true]);
            $this->general->arrayIndexChange($opening_balances,'ledger_id');
            $closing_balances=$acc->headBalance($ledger_ids,$this->to,0,['groupByHID'=>true]);
            $this->general->arrayIndexChange($closing_balances,'ledger_id');

            $transactions=$acc->head_vouchers($ledger_ids,$this->from,$this->to,0,$jArray);
            // $this->general->printArray('$transactions');
            // $this->general->printArray($transactions);
            $transaction_by_head_type=[];
            if(!empty($transactions)){
                foreach($transactions as $ledger_id=>$tr){
                    foreach($tr as $s){
                        $target_head=0;
                        if(isset($ledger_ids[$s['debit_head']])){
                            $target_head=$s['debit_head'];
                        }
                        elseif(isset($ledger_ids[$s['credit_head']])){
                            $target_head=$s['credit_head'];
                        }
                        else{
                            $jArray[fl()][]=$s;
                        }
                        if(!isset($transaction_by_head_type[$target_head])){
                            $transaction_by_head_type[$target_head]=[];
                        }
                        if(!isset($transaction_by_head_type[$target_head][$s['type']])){
                            $transaction_by_head_type[$target_head][$s['type']]=0;
                        }
                        $transaction_by_head_type[$target_head][$s['type']]+=$s['amount'];
                    }
                }
            }
            // $this->general->printArray('$transaction_by_head_type');
            // $this->general->printArray($transaction_by_head_type);
            foreach($customers as $k=>$c){
                $ledger_id=$this->acc->getCustomerHead($c);
                $opening_balance=$opening_balances[$ledger_id]['balance'] ?? 0;
                $closing_balance=$closing_balances[$ledger_id]['balance'] ?? 0;
                $customers[$k]['opening_balance']=$opening_balance;
                $customers[$k]['closing_balance']=$closing_balance;
                $sale=0;
                $collection=0;
                $collection_discount=0;
                $return=0;
                $recoverable=0;
                $bad_debt=0;
                $yearly=0;
                if(isset($transaction_by_head_type[$ledger_id])){
                    if(isset($transaction_by_head_type[$ledger_id][V_T_SALE_CASH_CUSTOMER])){
                        $sale=$transaction_by_head_type[$ledger_id][V_T_SALE_CASH_CUSTOMER];
                    }
                    if(isset($transaction_by_head_type[$ledger_id][V_T_RECEIVE_FROM_CUSTOMER])){
                        $collection=$transaction_by_head_type[$ledger_id][V_T_RECEIVE_FROM_CUSTOMER];
                    }
                    if(isset($transaction_by_head_type[$ledger_id][V_T_CUSTOMER_YEARLY_DISCOUNT])){
                        $yearly=$transaction_by_head_type[$ledger_id][V_T_CUSTOMER_YEARLY_DISCOUNT];
                    }
                    if(isset($transaction_by_head_type[$ledger_id][V_T_RECOVERABLE_ENTRY])){
                        $recoverable=$transaction_by_head_type[$ledger_id][V_T_RECOVERABLE_ENTRY];
                    }
                    if(isset($transaction_by_head_type[$ledger_id][V_T_NEW_RECOVERABLE_ENTRY])){
                        $recoverable=$transaction_by_head_type[$ledger_id][V_T_NEW_RECOVERABLE_ENTRY];
                    }
                    if(isset($transaction_by_head_type[$ledger_id][V_T_CUSTOMER_BAD_DEBT])){
                        $bad_debt=$transaction_by_head_type[$ledger_id][V_T_CUSTOMER_BAD_DEBT];
                    }
                    if(isset($transaction_by_head_type[$ledger_id][V_T_CUSTOMER_COLLECTION_DISCOUNT])){
                        $collection_discount=$transaction_by_head_type[$ledger_id][V_T_CUSTOMER_COLLECTION_DISCOUNT];
                    }
                    if(isset($transaction_by_head_type[$ledger_id][V_T_SALE_RETURN])){
                        $return=$transaction_by_head_type[$ledger_id][V_T_SALE_RETURN];
                    }
                }
                $customers[$k]['sale']=$sale;
                $customers[$k]['collection']=$collection;
                $customers[$k]['collection_discount']=$collection_discount;
                $customers[$k]['return']=$return;
                $customers[$k]['recoverable']=$recoverable;
                $customers[$k]['bad_debt']=$bad_debt;
                $customers[$k]['yearly']=$yearly;
            }
            
            // $this->general->printArray('$customers');
            // $this->general->printArray($customers);
        }
        return $customers;
    }
    public function create($name,$from,$to){
        $from = strtotime($from);
        $to = strtotime($to);
        if($name==''){throw new Exception('Please provide name');}
        if(!$from||!$to){throw new Exception('Invalid date');}
        if($from>$to){throw new Exception('Invalid date range');}
        $this->from = $from;
        $this->to = $to;
        $customers=$this->get_all_customer_data();
        if(empty($customers)){
            throw new Exception('No customer data found for the given date range');
        }
        // $this->general->printArray($customers);
        $customerData=[];
        foreach($customers as $c){
            $data=['id'=>$c['id']];
            if($c['opening_balance']!=0){
                $data['o']= (float)$c['opening_balance'];
            }
            if($c['closing_balance']!=0){
                $data['c']= (float)$c['closing_balance'];
            }
            if($c['sale']!=0){
                $data['s']= (float)$c['sale'];
            }
            if($c['collection']!=0){
                $data['cl']= (float)$c['collection'];
            }
            if($c['collection_discount']!=0){
                $data['cld']= (float)$c['collection_discount'];
            }
            if($c['return']!=0){
                $data['rt']= (float)$c['return'];
            }
            $customerData[$c['id']]=$data;
        }
        // $this->general->printArray($customerData);exit;
        $data=[
            'name'          =>$name,
            'from'          =>$this->from,
            'to'            =>$this->to,
            'data'         =>json_encode($customerData),
        ];
        $this->db->arrayUserInfoAdd($data);
        $insert=$this->db->insert('customer_closing_sms',$data);
        if(!$insert){
            throw new Exception(m(66));
        }
    }
    public function details($id){
        $db=$this->db;
        $general=$this->general;
        $record=$db->get_rowData('customer_closing_sms','id',$id);
        if(empty($record)){
            throw new Exception('Customer closing SMS record not found');
        }
        $transactionDetails=json_decode($record['data'],true);
        $this->from=$record['from'];
        $this->to=$record['to'];
        $customerData=$general->getJsonFromString($record['data']);
        $customer_ids=array_keys($customerData);
        $all_customers=$db->selectAll('customer','where id in('.implode(',',$customer_ids).')','id,code,name,mobile,base_id,data');
        $general->arrayIndexChange($all_customers,'id');
        $details=[];
        foreach($transactionDetails as $cid=>$t){
                $customer=$all_customers[$cid];
                $data=$general->getJsonFromString($customer['data']);
                $getClosingSMS=1;
                if(isset($data['getClosingSMS'])){
                    $getClosingSMS=$data['getClosingSMS'];
                }
                $detail=[
                    'id'                    =>$customer['id'],
                    'code'                  =>$customer['code'],
                    'base_id'               =>$customer['base_id'],
                    'name'                  =>$customer['name'],
                    'mobile'                =>$customer['mobile'],
                    'getClosingSMS'         =>$getClosingSMS,
                    'opening_balance'       =>$t['o'] ?? 0,
                    'closing_balance'       =>$t['c'] ?? 0,
                    'sale'                  =>$t['s'] ?? 0,
                    'collection'            =>$t['cl'] ?? 0,
                    'collection_discount'   =>$t['cld'] ?? 0,
                    'return'                =>$t['rt'] ?? 0,
                ];
                $details[]=$detail;
        }
        return [
            'record'=>$record,
            'details'=>$details
        ];
    }
}