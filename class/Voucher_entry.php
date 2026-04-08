<?php 
include_once ROOT_DIR.'/class/Traits/Utility.php';
class Voucher_entry{
    private $entrees=[];
    private $date;
    private $base_id;
    private $ledgers;
    private ACC $acc;
    use Utility;
    public function __construct(private General $general,private DB $db, array $request){
        $this->date = $request['date']??false;
        $this->base_id = $request['base_id']??false;
        $this->entrees = $request['entrees']??[];
        $SMT = new SMT($general,$db);
        $this->acc = new ACC($this->general,$this->db,$SMT);

    }
    private function validation(): void{
        if(!$this->date){$this->logArray[fl()]=1; throw new Exception('Invalid date');}
        if($this->base_id===false){$this->logArray[fl()]=1; throw new Exception('Invalid base');}
        $this->date = strtotime($this->date);
        if(!$this->date){$this->logArray[fl()]=1;throw new Exception('Invalid date');}
        if(date('d-m-Y',$this->date)==date('d-m-Y'))$this->date = TIME;
        if(!$this->entrees){$this->logArray[fl()]=1;throw new Exception('Invalid ledger');}
        $base = $this->db->selectAll('base','','id');
        $this->general->arrayIndexChange($base);
        $base=[0=>['id'=>0],...$base];
        $this->logArray[fl()]=$this->base_id;
        if(!isset($base[$this->base_id])){
            $this->logArray[fl()]=1;
            $this->logArray[fl()]=$this->base_id;
            throw new Exception('Invalid base');
        }
        $chart_of_account_ids=[];
        $ledger_ids=[];
        $total_debit = 0;
        $total_credit = 0; 
        foreach($this->entrees as $k=>$entry){
            $type = intval($entry['type']);
            $ledger_id = intval($entry['ledger_id']);
            $chart_of_account_id = intval($entry['chart_of_account_id']);
            $amount = floatval($entry['amount']);
            $this->entrees[$k]['type']=$type;
            $this->entrees[$k]['ledger_id']=$ledger_id;
            $this->entrees[$k]['amount']=$amount;
            $this->entrees[$k]['note']=$entry['note']??'';
            if($type!=DEBIT&&$type!=2){$this->logArray[fl()]=1;throw new Exception('Invalid Type');}
            if($ledger_id<1){$this->logArray[fl()]=1;throw new Exception('Invalid ledger');}
            if($amount<=0){$this->logArray[fl()]=1;throw new Exception('Invalid amount');}
            $chart_of_account_ids[$chart_of_account_id]=$chart_of_account_id;
            $ledger_ids[$ledger_id]=$ledger_id;
            $type==DEBIT?$total_debit+=$amount:$total_credit+=$amount;
        }
        if($total_debit!=$total_credit){$this->logArray[fl()]=1;throw new Exception('Invalid amount');}
        
        $chart_of_accounts = $this->db->selectAll('a_charts_accounts','where isActive=1 and id in('.implode(',',$chart_of_account_ids).')','id');
        if(count($chart_of_account_ids)!=count($chart_of_accounts)){$this->logArray[fl()]=1;throw new Exception('Invalid chart account');}
        $company_data = $this->db->get_company_data();
        $for_voucher_entry_ledgers = $company_data['for_voucher_entry_ledgers']??[0=>0];
        $this->ledgers = $this->db->selectAll('a_ledgers','where id in('.implode(',',$ledger_ids).') and (id in('.implode(',',$for_voucher_entry_ledgers).') or type='.H_TYPE_CUSTOM.')','id,charts_accounts_id','array',$this->logArray);
        if(!$this->ledgers){$this->logArray[fl()]=1;throw new Exception('Invalid ledger');}
        foreach($this->ledgers as $l){
            if(!in_array($l['charts_accounts_id'],$chart_of_account_ids)){$this->logArray[fl()]=1;throw new Exception('Invalid ledger');}
        }
        if(count($this->ledgers)!=count($ledger_ids)){$this->logArray[fl()]=1;throw new Exception('Invalid ledger');}
        $this->general->arrayIndexChange($this->ledgers);
        
    }
    public function voucher_entry():void{
        try{
            $this->validation();
        }
        catch(Exception $e){
            $this->logArray[fl()]=[$e->getMessage().' '.$e->getFile().' '.$e->getLine()];
            throw new Exception($e->getMessage().' '.$e->getLine());
        }
        $this->db->transactionStart();
        $data=[
            'type'      => V_T_VOUCHER_ENTRY,
            'reference' => '',
            'time'    	=> $this->date,
            'user_id'   => 0,
            'base_id'   => $this->base_id
        ];
        $this->db->arrayUserInfoAdd($data);
        $voucher_id=$this->db->insert('a_voucher_entry',$data,true);
        if(!$voucher_id){$this->logArray[fl()]=1;throw new Exception($this->db->l('Some problem there. Please try again later'));}
        $type_date = $this->acc->get_voucher_type_data(V_T_VOUCHER_ENTRY);
        if(!$type_date){$this->logArray[fl()]=1;throw new Exception($this->db->l('Some problem there. Please try again later'));}
        $voucher_code = $this->db->setAutoCode('voucher',$voucher_id,$type_date['code']);
        $this->logArray[fl()]=$voucher_code;
        if(!$voucher_code){$this->logArray[fl()]=1;throw new Exception($this->db->l('Some problem there. Please try again later'));}
        foreach($this->entrees as $entry){
            $data = [
                'voucher_id'=> $voucher_id,
                'ledger_id'	=> $entry['ledger_id'],
                'time'      => $this->date,
                'note' 		=> $entry['note'],
                'user_id'   => 0,
                'base_id'   => $this->base_id
            ];
            ($entry['type']==1)?$data['debit']=$entry['amount']:$data['debit']=0;
            ($entry['type']!=1)?$data['credit']=$entry['amount']:$data['credit']=0;
            $this->db->arrayUserInfoAdd($data);
            $insert = $this->db->insert('a_ledger_entry',$data);
            if(!$insert){$this->logArray[fl()]=1;throw new Exception($this->db->l('Some problem there. Please try again later'));}
        }
        $this->db->transactionStop(true);
    }
    

}