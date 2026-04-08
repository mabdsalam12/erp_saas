<?php
class Voucher_list{
    private General $general;
    private DB $db;
    public function __construct(public SMT $smt,public ACC $acc){
        $this->general=$smt->general;
        $this->db=$smt->db;
    }
    public function get_list($request,&$jArray=[]){
        $general=$this->general;
        $db=$this->db;
        $acc=$this->acc;
        $date_type=$request['date_type']??0;
        if(!isset($request['dRange'])){
            return ['status'=>0,'msg'=>'Invalid date range'];
        }
        $dRange = $request['dRange'];
        $user_id=0;
        $ledger_id=0;
        $type=0;
        $ledger_id=0;
        $report_type=intval($request['report_type']??0);

        if(isset($request['ledger_id'])){
            $ledger_id=intval($request['ledger_id']);
        }
        if(isset($request['user_id'])){
            $user_id    = intval($request['user_id']);
        }
        if(isset($request['ledger_id'])){
            $ledger_id    = intval($request['ledger_id']);
        }
        if(isset($request['type'])){
            $type    = intval($request['type']);
        }
        $reportInfo=["Date $dRange"];
        
        
        $general->getFromToFromString($dRange,$from,$to);
        $q=[];
        if($date_type==0){
            $q[]="time between $from and $to";
        }
        else{
            $q[]="createdOn between $from and $to";
        }

        // $q[]="time between $from and $to";
        if($user_id>0){
            $q[]="user_id=$user_id";
        }
        if($ledger_id>0){
            $q[]="ledger_id=$ledger_id";
        }

        $dStatus=$db->permission(90);
        $transactions=$db->selectAll('a_ledger_entry','where '.implode(' and ',$q),'','array',$jArray);
        $rData=[];
        $sr=1;
        $total_debit = 0;
        $total_credit = 0;
     
        $typeWiseData = [];
         $ledger_id=[];
         $voucher_ids=[];
        if(!empty($transactions)){
            $all_voucher_type = $acc->all_voucher_type();
            $general->getIDsFromArray($transactions,'voucher_id,ledger_id',$voucher_ids,$ledger_id);
            $voucher_entry = $db->selectAll('a_voucher_entry','where id in('.implode(',',$voucher_ids).')');
            $general->arrayIndexChange($voucher_entry);
        }
        if($report_type== 1){
            if(!empty($transactions)){
                foreach($transactions as $t){
                    $v = $voucher_entry[$t['voucher_id']];
                    if($type>0&&$type!=$v['type']){
                            continue;
                        }
                    if(!isset($typeWiseData[$v['type']])){
                        $typeWiseData[$v['type']]=[
                            'credit'=>0,
                            'debit'=>0,
                        ];
                    }
                    $total_debit += $t['debit'];
                    $total_credit +=$t['credit'];
                        $typeWiseData[$v['type']]['credit']+=$t['credit'];
                        $typeWiseData[$v['type']]['debit']+=$t['debit'];
                }
                foreach($typeWiseData as $type=>$v){
                    $rData[]=[
                        's'=>$sr++,
                        'type'=>$all_voucher_type[$type]['title']??'',
                        'debit'=>$general->numberFormat($v['debit']),
                        'credit'=>$general->numberFormat($v['credit']),
                    ];
                }
                $rData[]= [
                    's'=>'',
                    'type'=>['t'=>'Total','b'=>1],
                    'debit'=>['t'=>$general->numberFormat($total_debit),'b'=>1],
                    'credit'=>['t'=>$general->numberFormat($total_credit),'b'=>1],
                ];
            }

        }
        else{
           
            
            $voucher_wise_data=[];
            if(!empty($transactions)){
                $ledgers=$db->selectAll('a_ledgers','where id in('.implode(',',$ledger_id).')');
                $general->arrayIndexChange($ledgers);
                $general->getIDsFromArray($voucher_entry,'createdBy',$user_ids);
                $users = $db->allUsers(' and id in('.implode(',',$user_ids).')');
                $base = $db->allBase_for_voucher();
                foreach($transactions as $t){
                    if(!isset($voucher_wise_data[$t['voucher_id']])){
                        $v = $voucher_entry[$t['voucher_id']];
                        if($type>0&&$type!=$v['type']){
                            continue;
                        }
                        
                        $u = $users[$t['createdBy']]??[];
                        $voucher_wise_data[$t['voucher_id']]=[
                            'credit'=>[],
                            'debit'=>[],
                            'ledger_ids'=>[],
                            'amount'=>0,
                            'base'=>$base[$t['base_id']]['title']??'',
                            'user'=>$u['name']??'',
                            'note'=>$t['note']??'',
                            'time'=>$v['time'],
                            'entry_time'=>$v['createdOn'],
                            'code'=>$v['code'],
                            'type'=>$v['type'],
                            'type_title'=>$all_voucher_type[$v['type']]['title']??'',
                        ];
                    }
                    $ledger = @$ledgers[$t['ledger_id']];
                    if($ledger){
                        $ledger['title']=$ledger['code'].' '.$ledger['title'];
                    }
                    $voucher_wise_data[$t['voucher_id']]['ledger_ids'][$t['ledger_id']]=$t['ledger_id'];
                    $voucher_wise_data[$t['voucher_id']]['amount']+=$t['debit'];
                    ($t['debit']>0)?@$voucher_wise_data[$t['voucher_id']]['debit'][]=$ledger['title']:@$voucher_wise_data[$t['voucher_id']]['credit'][]=$ledger['title'];
                }
            }
        
            $jArray[fl()]=$voucher_wise_data;
            $total_amount=0;
            foreach($voucher_wise_data as $id=>$v){
                if($ledger_id>0&&!in_array($ledger_id,$v['ledger_ids'])){
                    continue;
                }
                $total_amount+=$v['amount'];
                $delete_html='';
                if($dStatus){
                    if(in_array($v['type'],REMOVE_ABLE_VOUCHER_TYPE)){
                        $delete_html='<button class="btn btn-danger delete_voucher_' . $id . '" onclick="are_you_sure(1, \'Are you sure?\', ' . $id . ', delete_voucher)">Remove</button>';
                    }
                    else{
                        $delete_html=''.$v['type'];
                    }
                }
                $rData[]=[
                    's'=>$sr++,
                    'id'=>$id,
                    'c'=>$v['code'],
                    't'=>$v['type_title'],
                    'd'=>$general->make_date($v['time']),
                    'et'=>$general->make_date($v['entry_time']),
                    'ti'=>$general->getHourMint($v['time']),
                    'tm'=>$v['time'],
                    'b'=>$v['base'],
                    'u'=>$v['user'],
                    'n'=>$general->content_show($v['note']),
                    'dr'=>implode(' ,',$v['debit']),
                    'cr'=>implode(' ,',$v['credit']),
                    'a'=>$general->numberFormat($v['amount']),
                    'r'=>$delete_html,
                    'table_tr_id'=>"delete_voucher_$id",
                ];
            }
            $general->arraySortByColumn($rData,'tm',SORT_DESC);
            $rData[]=[
                    's'=>'',
                    'id'=>'',
                    'c'=>'',
                    't'=>'',
                    'd'=>'',
                    'ti'=>'',
                    'b'=>'',
                    'u'=>'',
                    'n'=>'',
                    'dr'=>['t'=>'Total','al'=>'r','b'=>1],
                    'cr'=>['t'=>$general->numberFormat($total_amount),'b'=>1,'col'=>2,'al'=>'r'],
                    'a'=>['t'=>false],
                    'r'=>'',
                    'table_tr_id'=>"delete_voucher",
            ];
        }
        
        $fileName='voucher_list_'.TIME.rand(0,999).'.txt';
        if($report_type== 1){
            $heads = [
                ['title'=>"SL"          ,'key'=>'s','hw'=>5],
                ['title'=>"Type"        ,'key'=>'type'],
                ['title'=>"Debit"       ,'key'=>'debit'],
                ['title'=>"Credit"      ,'key'=>'credit'],
            ];
        }
        else{
                
            $heads = [
                ['title'=>"SL"          ,'key'=>'s','hw'=>5],
                ['title'=>"Code"        ,'key'=>'c'],
                ['title'=>"Type"        ,'key'=>'t'],
                ['title'=>"Date"        ,'key'=>'d'],
                ['title'=>"Time"        ,'key'=>'ti'],
                ['title'=>"Entry date"  ,'key'=>'et'],
                ['title'=>"Base"        ,'key'=>'b'],
                ['title'=>"User"        ,'key'=>'u'],
                ['title'=>"Note"        ,'key'=>'n'],
                ['title'=>"Debit"       ,'key'=>'dr'],
                ['title'=>"Credit"      ,'key'=>'cr'],
                ['title'=>"Amount"      ,'key'=>'a','al'=>'r'],
                ['title'=>"Remove"      ,'key'=>'r','noForExcel'=>1],

            ];
        }
        $report_data=[
            'name'      => 'voucher_list',
            'title'     => 'Voucher list',
            'info'      => $reportInfo,
            'fileName'  => $fileName,
            'head'=>$heads,
            'data'=>$rData
        ];
        $gAr['report_data']= $report_data;
        textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
        $html     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml',$gAr);
        return [
            'status'=>1,
            'html'=>$html
        ];
    }
}