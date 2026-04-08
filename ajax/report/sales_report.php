<?php
$can_see_unit_cost=$db->permission(104);
    $dRange=$_POST['dRange'];
    $with_tp=$_POST['with_tp']==2?2:1;
    $toll_sale_type=$_POST['toll_sale_type']==1?1:0;
    $toll_base_type=$_POST['toll_base_type']==1?1:0;
    
    $reportInfo=["Date :$dRange"];
    $general->getFromToFromString($dRange,$from,$to);

    $type='a';
    
    

    $base_opening=[
        'opening'               => 0,
        'closing'               => 0,
        'sales_tp'              => 0,
        'sales_sp'              => 0,
        'cost'                  => 0,
        'profit'                => 0,
        'sales_inv'             => 0,
        'collection'            => 0,
        'return'                => 0,
        'bad_dept'              => 0,
        'collection_discount'   => 0,
        'yearly_discount'       => 0,
    ];
    
    $q=['status=1'];
    $sq="where ".implode(" and ",$q);
    $base = $db->selectAll('base',"$sq order by title asc",'id,title,data');

    $customers=$db->selectAll('customer','','id,ledger_id,base_id');
    $customer_ledger_ids=[];
    foreach($customers as $c){
        $ledger_id=$acc->getCustomerHead($c);
        $customer_ledger_ids[]=$ledger_id;
    }
    $opening_due=$acc->headBalance($customer_ledger_ids,$from-1,0,['groupByHID'=>1]);
    $closing=$acc->headBalance($customer_ledger_ids,$to,0,['groupByHID'=>1]);
    if(!empty($opening_due)){
        $general->arrayIndexChange($opening_due,'ledger_id');
    }
    if(!empty($closing)){
        $general->arrayIndexChange($closing,'ledger_id');
    }
    $company_data = $db->get_company_data();
    $toll_product_base=$company_data['toll_product_base'];

    $base_report=[];
    $head_transactions=[];

    $types  = [
        V_T_CUSTOMER_YEARLY_DISCOUNT,
        V_T_PAY_TO_CUSTOMER,
        V_T_RECEIVE_FROM_CUSTOMER,
        V_T_RECOVERABLE_ENTRY,
        V_T_NEW_RECOVERABLE_ENTRY,
        V_T_CUSTOMER_BAD_DEBT,
        V_T_CUSTOMER_COLLECTION_DISCOUNT,
        V_T_SALE_RETURN
    ];
    $vouchers = $acc->voucherDetails($types,'',$from,$to,[],$jArray);
    if(!empty($vouchers)){
        $ledger_ids=[];
        $user_ids=[];
        foreach($vouchers as $d){
            $ledger_ids[$d['debit']] = $d['debit'];
            $ledger_ids[$d['credit']] = $d['credit'];
            $user_ids[$d['createdBy']] = $d['createdBy'];
        }
        foreach($vouchers as $tr){
            $in = 0;
            $out = 0;
            $type='';
            if($tr['type']==V_T_RECEIVE_FROM_CUSTOMER){
                $ledger_id = $tr['credit'];
                $type='collection';
            }
            else if($tr['type']==V_T_RECOVERABLE_ENTRY){
                
                $ledger_id = $tr['credit'];
            }
            else if($tr['type']==V_T_CUSTOMER_BAD_DEBT){
                $type='bad_dept';
                $ledger_id = $tr['credit'];
            }else if($tr['type']==V_T_CUSTOMER_COLLECTION_DISCOUNT){
                $type='collection_discount';
                $ledger_id = $tr['credit'];
            }else if($tr['type']==V_T_CUSTOMER_YEARLY_DISCOUNT){
                $type='yearly_discount';
                $ledger_id = $tr['credit'];
            }else if($tr['type']==V_T_SALE_RETURN){
                $type='return';
                $jArray[fl()][]=$tr;
                $ledger_id = $tr['credit'];
            }
            else{
                $jArray[fl()][]=$tr;
            }
            if($type!=''){
                if(!isset($head_transactions[$ledger_id])){
                    $head_transactions[$ledger_id]=[
                        'collection'=>0,
                        'collection_discount'=>0,
                        'yearly_discount'=>0,
                        'return'=>0,
                        'bad_dept'=>0,
                    ];
                }
                $head_transactions[$ledger_id][$type]+=$tr['amount'];
            }
            
        }
    }


    foreach($customers as $c){
        if(!isset($base_report[$c['base_id']])){
            $base_report[$c['base_id']]=$base_opening;
        }
        if(isset($opening_due[$c['ledger_id']])){
            $base_report[$c['base_id']]['opening']+=$opening_due[$c['ledger_id']]['balance'];
        }
        if(isset($closing[$c['ledger_id']])){
            $base_report[$c['base_id']]['closing']+=$closing[$c['ledger_id']]['balance'];
        }
        if(isset($head_transactions[$c['ledger_id']])){
            $base_report[$c['base_id']]['collection']+=$head_transactions[$c['ledger_id']]['collection'];
            $base_report[$c['base_id']]['return']+=$head_transactions[$c['ledger_id']]['return'];
            $base_report[$c['base_id']]['bad_dept']+=$head_transactions[$c['ledger_id']]['bad_dept'];
            $base_report[$c['base_id']]['collection_discount']+=$head_transactions[$c['ledger_id']]['collection_discount'];
            $base_report[$c['base_id']]['yearly_discount']+=$head_transactions[$c['ledger_id']]['yearly_discount'];
        }
        
    }

    $q=[];
    $q[]="date between $from and $to";
    
    $rData=[];
    $sales=$db->selectAll('sale','where '.implode(' and ',$q).' order by date asc','','array',$jArray);
    $general->arrayIndexChange($sales,'id');
    if(!empty($sales)){
        $sale_ids=[];
        foreach($sales as $s){
            $sale_ids[$s['id']]=$s['id'];
            if(!isset($base_report[$s['base_id']])){
                $base_report[$s['base_id']]=$base_opening;
            }
            $profit=$s['total']-$s['cost'];
            $base_report[$s['base_id']]['sales_inv']+=$s['total'];
            $base_report[$s['base_id']]['cost']+=$s['cost'];
            $base_report[$s['base_id']]['profit']+=$profit;
        }
        
        $sale_products = $db->selectAll('sale_products','where sale_id in('.implode(',',$sale_ids).')','','array',$jArray);
        $sale_wise_products=[];
        $sale_details=[];
        $product_ids=[];
        foreach($sale_products as $sp){
            $s=$sales[$sp['sale_id']];
            if(!isset($base_report[$s['base_id']])){
                $base_report[$s['base_id']]=$base_opening;
            }
            $base_report[$s['base_id']]['sales_tp']+=$sp['sub_total'];
            $base_report[$s['base_id']]['sales_sp']+=$sp['total'];
        }
    }
    $jArray[fl()]=$base_report;
    $serial=1;
    $total=[
        'o'=>0,
        'tp'=>0,
        'sp'=>0,
        'cs'=>0,
        'p'=>0,
        'tc'=>0,
        'it'=>0,
        'tr'=>0,
        'bd'=>0,
        'c'=>0,
        'y'=>0,
        'cd'=>0,

    ];
    foreach($base as $b){
        $data=[
            's'=>$serial++,
            'b'=>$b['title'],
        ];
        if(isset($base_report[$b['id']])){
            $base_data=$general->getJsonFromString($b['data']);
            $base_type=$base_data['base_type']??'';

            if($toll_base_type==0&&$base_type=='toll'){
                continue;
            }


            if(PROJECT=='project_1'){
                $sp=$base_report[$b['id']]['sales_inv'];
            }
            else{
                $sp=$base_report[$b['id']]['sales_sp'];
            }
            $total['o']+=$base_report[$b['id']]['opening'];
            $total['tp']+=$base_report[$b['id']]['sales_tp'];
            $total['sp']+=$sp;
            $total['it']+=$base_report[$b['id']]['sales_inv'];
            $total['cs']+=$base_report[$b['id']]['cost'];
            $total['p']+=$base_report[$b['id']]['profit'];
            $total['cd']+=$base_report[$b['id']]['closing'];
            $total['tc']+=$base_report[$b['id']]['collection'];
            $total['tr']+=$base_report[$b['id']]['return'];
            $total['bd']+=$base_report[$b['id']]['bad_dept'];
            $total['c']+=$base_report[$b['id']]['collection_discount'];
            $total['y']+=$base_report[$b['id']]['yearly_discount'];
            

            $data['o']=$general->numberFormat($base_report[$b['id']]['opening']);
            $data['tp']=$general->numberFormat($base_report[$b['id']]['sales_tp']);
            $data['sp']=$general->numberFormat($sp);
            $data['it']=$general->numberFormat($base_report[$b['id']]['sales_inv']);
            $data['cs']=$general->numberFormat($base_report[$b['id']]['cost']);
            $data['p']=$general->numberFormat($base_report[$b['id']]['profit']);
            $data['tc']=$general->numberFormat($base_report[$b['id']]['collection']);
            $data['tr']=$general->numberFormat($base_report[$b['id']]['return']);
            $data['bd']=$general->numberFormat($base_report[$b['id']]['bad_dept']);
            $data['c']=$general->numberFormat($base_report[$b['id']]['collection_discount']);
            $data['y']=$general->numberFormat($base_report[$b['id']]['yearly_discount']);

            $data['cd']=$general->numberFormat($base_report[$b['id']]['closing']);
        }
        else{
            $data['o']=0;
            $data['tp']=0;
            $data['sp']=0;
            $data['tc']=0;
            $data['it']=0;
            $data['cs']=0;
            $data['p']=0;
            $data['tr']=0;
            $data['bd']=0;
            $data['c']=0;
            $data['y']=0;
            
            $data['cd']=0;
        }
        $rData[]=$data;
    }
    $rData[]=[
        's'=>'',
        'b'=>['t'=>'Total','b'=>1],
        'o'=>['t'=>$general->numberFormat($total['o']),'b'=>1],
        'tp'=>['t'=>$general->numberFormat($total['tp']),'b'=>1],
        'sp'=>['t'=>$general->numberFormat($total['sp']),'b'=>1],
        'it'=>['t'=>$general->numberFormat($total['it']),'b'=>1],
        'cs'=>['t'=>$general->numberFormat($total['cs']),'b'=>1],
        'p'=>['t'=>$general->numberFormat($total['p']),'b'=>1],
        'cd'=>['t'=>$general->numberFormat($total['cd']),'b'=>1],
        'tc'=>['t'=>$general->numberFormat($total['tc']),'b'=>1],
        'bd'=>['t'=>$general->numberFormat($total['bd']),'b'=>1],
        'c'=>['t'=>$general->numberFormat($total['c']),'b'=>1],
        'y'=>['t'=>$general->numberFormat($total['y']),'b'=>1],
        'tr'=>['t'=>$general->numberFormat($total['tr']),'b'=>1],
    ];
    $head=[
        ['title'=>'SL'                      ,'key'=>'s','hw'=>5],
        ['title'=>'Base'                    ,'key'=>'b','hw'=>5],
        ['title'=>'Opening dues balance'    ,'key'=>'o' ,'al'=>'r'],
    ];
    if($with_tp==1){
        $head[]=['title'=>'Total Sales (TP)'        ,'key'=>'tp' ,'al'=>'r'];
    }
    $head[]=['title'=>'Total Sales(SP)'         ,'key'=>'sp' ,'al'=>'r'];
    if(PROJECT!='project_1'){
        $head[]=['title'=>'Invoice total'           ,'key'=>'it' ,'al'=>'r'];
    }
    if($can_see_unit_cost&&PROJECT!='project_1'){
        $head[]=['title'=>'Cost'                ,'key'=>'cs' ,'al'=>'r'];
        $head[]=['title'=>'Profit'              ,'key'=>'p' ,'al'=>'r'];
    }
    $head[]=['title'=>'Total Collection'        ,'key'=>'tc' ,'al'=>'r'];
    $head[]=['title'=>'Total Return'            ,'key'=>'tr' ,'al'=>'r'];
    $head[]=['title'=>'Bad Debt discount'       ,'key'=>'bd' ,'al'=>'r'];
    $head[]=['title'=>'Collection Discount'     ,'key'=>'c' ,'al'=>'r'];
    $head[]=['title'=>'Yearly Discount'         ,'key'=>'y' ,'al'=>'r'];
    $head[]=['title'=>'Closing Dues balance'    ,'key'=>'cd' ,'al'=>'r'];
    
    $fileName='item_wise_sale_'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'saleReport',
        'title'     => 'Sales report',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
