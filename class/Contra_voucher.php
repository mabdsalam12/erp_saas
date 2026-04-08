<?php
include_once ROOT_DIR.'/class/Traits/Utility.php';
class Contra_voucher{
    use Utility;
    public function __construct(private General $general, private DB $db, private ACC $acc){}
    private function validation(array $data,$edit=false):array{
        $date               = strtotime($data['date']);
        $base_id            = intval($data['base_id']);
        $debit              = intval($data['debit']);
        $credit             = intval($data['credit']);
        $amount             = floatval($data['amount']);
        $reference          = $data['reference']??'';
        $transaction_charge = floatval($data['transaction_charge']);
        $note               = $data['note']??'';
        $source_ledgers=$this->acc->get_all_cash_accounts($this->logArray);
        $base = $this->db->selectAll('base','','id');
        $this->general->arrayIndexChange($base);
        $base=[0=>['id'=>0],...$base];
        if($date<strtotime('-1 year')){
            $this->logArray[fl()]=1;
           throw new Exception('Invalid date');
        }
        elseif(!isset($base[$base_id])){
            $this->logArray[fl()]=1;
            throw new Exception('Invalid base');
        }
        else if(!array_key_exists($debit,$source_ledgers)){
            $this->logArray[fl()]=1;
            throw new Exception('Invalid debit ledger');
        }
        else if(!array_key_exists($credit,$source_ledgers)){
            $this->logArray[fl()]=1;
            throw new Exception('Invalid credit ledger');
        }
        else if($amount<=0){
            $this->logArray[fl()]=1;
            throw new Exception('Invalid amount');
        }
        if($edit){
            $id = intval(@$data['id']);
            if($id<1){
                $this->logArray[fl()]=1;
                throw new Exception('Invalid contra voucher');
            }
            $contra=$this->db->get_rowData('contra_voucher','id',$id);
            if(!$contra){
                $this->logArray[fl()]=1;
                throw new Exception('Invalid contra voucher');
            }
        }
        if($date==TODAY_TIME){
            $date=TIME;
        }
        $data=[
            'debit'             => $debit,
            'credit'            => $credit,
            'base_id'           => $base_id,
            'amount'            => $amount,
            'reference'         => $reference,
            'transaction_charge'=> $transaction_charge,
            'time'              => $date,
            'note'              => $note,
        ];
        return $data;
    }

    public function add(array $data): void {
        try {
            // Validate the input data
            $data = $this->validation($data);
    
            // Start a database transaction
            $this->db->transactionStart();
            $this->db->arrayUserInfoAdd($data);
            // Insert the data into the 'contra_voucher' table
            $contra_id = $this->db->insert('contra_voucher', $data, true, 'array', $jArray);
            
            if ($contra_id == false) { 
                // Log the error if the insert operation failed
                $this->logArray[fl()] = 1;
                throw new Exception('Some problem there. Please try again later');
            }
            else{
                $contra_id = intval($contra_id); // Convert the ID to an integer for safety
                
                // Create the main voucher
                $voucher = $this->acc->voucher_create(
                    V_T_CONTRA,
                    $data['amount'],
                    $data['debit'],
                    $data['credit'],
                    $data['time'],
                    $data['note'],
                    $contra_id,
                    0,
                    ['base_id'=>$data['base_id']]
                );
    
                if (!$voucher) { // Check if voucher creation failed
                    $this->logArray[fl()] = 1; // Log the error
                    throw new Exception('Some problem there. Please try again later');
                }
    
                // Handle transaction charges, if applicable
                if ($data['transaction_charge'] > 0) {
                    $charge_head = $this->acc->getSystemHead(AH_CONTRA_TRANSACTION_CHARGE); // Get the account head for transaction charges
                    if(!$charge_head){
                        $this->logArray[fl()]=1;
                        throw new Exception('Some problem there. Please try again later');
                    }
                    
                    // Create a voucher for transaction charges
                    $voucher = $this->acc->voucher_create(
                        V_T_CONTRA_TR_CHARGE,
                        $data['transaction_charge'],
                        $charge_head,
                        $data['credit'],
                        $data['time'],
                        'MFS charge',
                        $contra_id,
                        0,
                        ['base_id'=>$data['base_id']]
                    );
    
                    if (!$voucher) { // Check if voucher creation failed
                        $this->logArray[fl()] = 1; // Log the error
                        throw new Exception('Some problem there. Please try again later');
                    }
                }
            } 
    
            // Commit the transaction if all operations succeeded
            $this->db->transactionStop(true);
    
        } catch (Exception $e) {
            // Log the error and roll back the transaction on failure
            $this->logArray[fl()] = 1;
    
            // Re-throw the exception with its original message
            throw new Exception($e->getMessage());
        }
    }
    public function update(array $request){
        try {
            // Validate the input data
            $data = $this->validation($request,true);

            $id = intval($request['id']);
            // Start a database transaction
            $this->db->transactionStart();
            $this->db->arrayUserInfoEdit($data);
            
            $voucher = $this->acc->voucherDetails(V_T_CONTRA,$id,jArray:$this->logArray);
            if(!$voucher){
                $this->logArray[fl()]=1;
                throw new Exception('Invalid contra voucher');
            }
            $charge_voucher = $this->acc->voucherDetails(V_T_CONTRA_TR_CHARGE,$id);
            
            // Update the 'contra_voucher' table
            $contra_voucher_update = $this->db->update('contra_voucher', $data, ['id' => $id], 'array', $this->logArray);
            if(!$contra_voucher_update){
                $this->logArray[fl()]=1;
                throw new Exception('Some problem there. Please try again later');
            }
            $voucher = $voucher[array_key_first($voucher)];
            // Update the main voucher
            $voucher = $this->acc->voucherEdit(
                $voucher['id'],
                $data['amount'],
                extraData:['base_id'=>$data['base_id']],
                jArray:$this->logArray,
            );
            if(!$voucher){
                $this->logArray[fl()]=1;
                throw new Exception('Some problem there. Please try again later');
            }
            if($data['transaction_charge']>0){
                $charge_head = $this->acc->getSystemHead(AH_CONTRA_TRANSACTION_CHARGE);
                if(!$charge_head){
                    $this->logArray[fl()]=1;
                    throw new Exception('Some problem there. Please try again later');
                }
                if($charge_voucher){
                    $charge_voucher = $charge_voucher[array_key_first($charge_voucher)];
                    $voucher = $this->acc->voucherEdit(
                        $charge_voucher['id'],
                        $data['transaction_charge'],
                        extraData:['base_id'=>$data['base_id']],
                        jArray:$this->logArray
                    );
                }else{
                    $voucher = $this->acc->voucher_create(
                        V_T_CONTRA_TR_CHARGE,
                        $data['transaction_charge'],
                        $charge_head,
                        $data['credit'],
                        $data['time'],
                        $data['note'],
                        $id,
                        0,
                        ['base_id'=>$data['base_id']],
                    );
                }
                if(!$voucher){
                    $this->logArray[fl()]=1;
                    throw new Exception('Some problem there. Please try again later');
                }
            }
            else{
                if($charge_voucher){
                    $charge_voucher = $charge_voucher[array_key_first($charge_voucher)];
                    $charge_voucher_delete = $this->acc->voucher_delete($charge_voucher['id']);
                    if(!$charge_voucher_delete){
                        $this->logArray=fl();
                        throw new Exception('Some problem there. Please try again later');
                    }
                }
            }
            $this->db->transactionStop(true);
        }
        catch(Exception $e){
            $this->logArray[fl()]=1;
            throw new Exception($e->getMessage());
        }
    }
    public function delete(int $id): void{
        if($id<1){
            $this->logArray[fl()]=1;
            throw new Exception('Invalid contra voucher');
        }
        $contra=$this->db->get_rowData('contra_voucher','id',$id);
        if(!$contra){
            $this->logArray[fl()]=1;
            throw new Exception('Invalid contra voucher');
        }
        $this->db->transactionStart();
        // Fetch and delete the main voucher
        $voucher = $this->acc->voucherDetails(V_T_CONTRA,$id,jArray:$this->logArray);
        $this->logArray[fl()]=$voucher;
        if(!$voucher){
            $this->logArray[fl()]=1;
            throw new Exception('Some problem there. Please try again later');
        }
       
        $voucher = $voucher[array_key_first($voucher)];
        $voucher_delete = $this->acc->voucher_delete($voucher['id']);
        if(!$voucher_delete){
            $this->logArray[fl()]=1;
            throw new Exception('Some problem there. Please try again later');
        }

        // If transaction charges exist, fetch and delete their voucher
        if($contra['transaction_charge']>0){
            $charge_voucher = $this->acc->voucherDetails(V_T_CONTRA_TR_CHARGE,$id);
            if(!$charge_voucher){
                $this->logArray[fl()]=1;
                throw new Exception('Some problem there. Please try again later');
            }
            $charge_voucher = $charge_voucher[array_key_first($charge_voucher)];
            $charge_voucher_delete = $this->acc->voucher_delete($charge_voucher['id']);
            if(!$charge_voucher_delete){
                $this->logArray[fl()]=1;
                throw new Exception('Some problem there. Please try again later');
            }
        }

        // Delete the contra voucher
        $contra_delete = $this->db->delete('contra_voucher',['id'=>$id],jArray: $this->logArray);
        if(!$contra_delete){
            $this->logArray[fl()]=1;
            throw new Exception('Some problem there. Please try again later');
        }
        $this->db->transactionStop(true);
        
    }
    
}