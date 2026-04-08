<?php 
$date_range = $_POST['date_range'];
if(!empty($date_range)){
    $row_product = $db->selectAll('products','where type = '.PRODUCT_TYPE_RAW,'id,sale_price,unit_cost');
    $package_product = $db->selectAll('products','where type = '.PRODUCT_TYPE_PACKAGING,'id,sale_price,unit_cost');
    $finished_product = $db->selectAll('products','where type = '.PRODUCT_TYPE_FINISHED,'id,sale_price,unit_cost');
    $manufacturing_product = $db->selectAll('products','where type = '.PRODUCT_TYPE_MANUFACTURING,'id,sale_price,unit_cost');
    $general->arrayIndexChange($row_product);
    $general->arrayIndexChange($package_product);
    $general->arrayIndexChange($finished_product);
    $general->arrayIndexChange($manufacturing_product);
    $chart_accounts = $db->get_company_data()['chart_of_account']??[];
    $chart_of_account_wise_ledgers=[];
    $chart_accounts_data=[];
    if(!empty($chart_accounts)){
        $chart_accounts_data = $db->selectAll('a_charts_accounts','where id in('.implode(',',$chart_accounts).') order by code','id,title,code');
        $general->arrayIndexChange($chart_accounts_data);
        $jArray[fl()]=$chart_accounts_data;
        foreach($chart_accounts as $ca){
            $chart_of_account_ids[] =$ca;
        }
        $ledgers = $db->selectAll('a_ledgers','where  charts_accounts_id  in('.implode(',',$chart_of_account_ids).') order by code','id,code, charts_accounts_id ,title');
        $general->arrayIndexChange($ledgers);
        foreach($ledgers as $k=> $l){
            $l['title']=$l['code'].' '.$l['title'];
            $chart_of_account_wise_ledgers[$l['charts_accounts_id']][]=$l;
        }
        
        
    }
    $sale_queys=[];
    $sale_return_queys=[];
    $product_log_query=[];
    $dates=[];
    $s=0;
    $small_date=0;
    $big_date=0;
    foreach($date_range as $date){
        $general->getFromToFromString($date,$from,$to);
        $dates["Q$s"]= [$from,$to];
        $product_log_query[]="SUM(CASE WHEN time BETWEEN $from AND $to THEN quantity * unit_price ELSE 0 END) AS 'Q$s'";
        $product_cl_log_query[]="SUM(CASE WHEN time BETWEEN 0 AND $to THEN quantity * unit_price ELSE 0 END) AS 'Q$s'";
        $sale_queys[]="SUM(CASE WHEN time BETWEEN $from AND $to THEN credit ELSE 0 END) AS 'Q$s'";
        $sale_return_queys[]="SUM(CASE WHEN time BETWEEN $from AND $to THEN debit ELSE 0 END) AS 'Q$s'";
        $head_middle_query[]="SUM(CASE WHEN time BETWEEN $from AND $to THEN debit ELSE 0 END) AS 'Q".$s."_debit',SUM(CASE WHEN time BETWEEN $from AND $to THEN credit ELSE 0 END) AS 'Q".$s."_credit'";
        $head_middle_query_closing[]="SUM(CASE WHEN time BETWEEN 0 AND $to THEN debit ELSE 0 END) AS 'Q".$s."_debit',SUM(CASE WHEN time BETWEEN 0 AND $to THEN credit ELSE 0 END) AS 'Q".$s."_credit'";
        $s++;
        if($small_date==0 || $from<$small_date){
            $small_date = $from;
        }
        if($to>$big_date){
            $big_date = $to;
        }
    }
    $row_product_amount=[];
    $row_cl_product_amount=[];
    if(!empty($row_product)){
        $row_product_ids=array_keys($row_product);
        $row_query = "SELECT ".implode(',',$product_log_query)." FROM purchase_details WHERE time BETWEEN $small_date AND $big_date AND product_id IN (".implode(',',$row_product_ids).") ORDER BY  product_id ASC;";
        $row_cl_query = "SELECT ".implode(',',$product_cl_log_query)." FROM purchase_details WHERE time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$row_product_ids).") ORDER BY  product_id ASC;";
        $jArray[fl()]=$row_query;
        $row_product_log = $db->fetchQuery($row_query);
        $row_cl_product_log = $db->fetchQuery($row_cl_query);
        
        $row_product_amount=$row_product_log[0]??[];
        $row_cl_product_amount=$row_cl_product_log[0]??[];
        
    }
    $jArray[fl()]=$row_product_amount;
    $package_product_amount=[];
    $package_cl_product_amount=[];
    if(!empty($package_product)){
        $package_product_ids=array_keys($package_product);
        $package_query = "SELECT ".implode(',',$product_log_query)." FROM purchase_details WHERE time BETWEEN $small_date AND $big_date AND product_id IN (".implode(',',$package_product_ids).") ORDER BY  product_id ASC;";
        $package_cl_query = "SELECT ".implode(',',$product_cl_log_query)." FROM purchase_details WHERE time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$package_product_ids).") ORDER BY  product_id ASC;";
        $package_product_log = $db->fetchQuery($package_query);
        $package_cl_product_log = $db->fetchQuery($package_cl_query);
        
        $package_product_amount=$package_product_log[0]??[];
        $package_cl_product_amount=$package_cl_product_log[0]??[];
        
    }
    $finished_cl_product_amount=[];
    if(!empty($finished_product)){
        $finished_product_ids=array_keys($finished_product);
        $finished_cl_query = "SELECT ".implode(',',$product_cl_log_query)." FROM purchase_details WHERE time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$finished_product_ids).") ORDER BY  product_id ASC;";
        $finished_cl_product_log = $db->fetchQuery($finished_cl_query);
        
        $finished_cl_product_amount=$finished_cl_product_log[0]??[];
        
    }
    $manufacturing_cl_product_amount=[];
    if(!empty($manufacturing_product)){
        $manufacturing_product_ids=array_keys($finished_product);
        $manufacturing_cl_query = "SELECT ".implode(',',$product_cl_log_query)." FROM purchase_details WHERE time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$manufacturing_product_ids).") ORDER BY  product_id ASC;";
        $finished_cl_product_log = $db->fetchQuery($manufacturing_cl_query);
        
        $manufacturing_cl_product_amount=$manufacturing_cl_product_log[0]??[];
        
    }
    $jArray[fl()]=$package_product_amount;
    $saleHead=$acc->getSystemHead(AH_DEALER_SALE);
    $saleReturnHead=$acc->getSystemHead(AH_SALE_RETURN);
    $sale_credit_query = "SELECT ".implode(',',$sale_queys)." FROM a_ledger_entry WHERE time BETWEEN $small_date AND $big_date and ledger_id=$saleHead ORDER BY  id ASC;";
    $sale_return_credit_query = "SELECT ".implode(',',$sale_queys)." FROM a_ledger_entry WHERE time BETWEEN $small_date AND $big_date and ledger_id=$saleReturnHead ORDER BY  id ASC;";
    $jArray[fl()]=$sale_credit_query;
    $sale_credit = $db->fetchQuery($sale_credit_query)[0]??[];
    $sale_return_credit = $db->fetchQuery($sale_return_credit_query)[0]??[];
    $jArray[fl()]=$sale_credit;
    $jArray[fl()]=$sale_return_credit;
    if(!empty($ledgers)){
        $head_query = "SELECT ledger_id, ".implode(',',$head_middle_query)." FROM a_ledger_entry WHERE  time BETWEEN $small_date AND $big_date and ledger_id in(".implode(',',array_keys($ledgers)).") GROUP BY ledger_id ORDER BY ledger_id ASC;";    
        $head_query_closing = "SELECT ledger_id, ".implode(',',$head_middle_query_closing)." FROM a_ledger_entry WHERE  time BETWEEN 0 AND $big_date and ledger_id in(".implode(',',array_keys($ledgers)).") GROUP BY ledger_id ORDER BY ledger_id ASC;";    
        $jArray[fl()]=$head_query;
        $head_debit_and_credit = $db->fetchQuery($head_query);
        $head_debit_and_credit_closing = $db->fetchQuery($head_query_closing);
        $general->arrayIndexChange($head_debit_and_credit,'ledger_id');
        $general->arrayIndexChange($head_debit_and_credit_closing,'ledger_id');
        //print_r($head_debit_and_credit);
    }
    $head=[
        ['title'=>'','key'=>'title'],
    ];
    $rData=[];
    $title_data=[];
    $debit_credit=[];
    $income_data=[];
    $sale_data=[];
    $sale_return_data=[];
    $wastages_sales_data=[];
    $interest_received_data=[];
    $row_amount=[];
    $package_amount=[];
    $expense_data=[];
    $total_debit = [];
    $total_credit = [];
    $space_data=[];
    $inventories_data=[];
    $row_product_closing_data=[];
    $package_product_closing_data=[];
    $manufacturing_product_closing_data=[];
    $finished_product_closing_data=[];
    function debit_credit_wise_data_load(General $general,&$array,$debit_key,$credit_key,$amount){
        if($amount>0){
            $debit=$amount;
            $credit=0;
        }
        elseif($amount==0){
            $debit=0;
            $credit=0;
        }
        else{
            $credit=-1*$amount;
            $debit=0;
        }
        $array[$debit_key]=['t'=>$general->numberFormat($debit)];
        $array[$credit_key]=['t'=>$general->numberFormat($credit)];
    }
    foreach($dates as $key=>$date){
        $debit_key = $key.'_debit';
        $credit_key = $key.'_credit';
        if(!isset($total_debit[$debit_key])){$total_debit[$debit_key]=0;}
        if(!isset($total_credit[$credit_key])){$total_credit[$credit_key]=0;}
        $head[]=['title'=>'','key'=>$debit_key,'al'=>'r'];
        $head[]=['title'=>'','key'=>$credit_key,'al'=>'r'];
        $title_data[$debit_key]=['t'=>$general->make_date($date[0]).' to '.$general->make_date($date[1]),'b'=>1,'col'=>2,'al'=>'c'];
        $title_data[$credit_key]=false;

        $debit_credit[$debit_key]=['t'=>'Debit','b'=>1];
        $debit_credit[$credit_key]=['t'=>'Credit','b'=>1];

        $income_data[$debit_key]=['t'=>''];
        $income_data[$credit_key]=['t'=>$general->numberFormat(floatval(@$sale_credit[$key])),'b'=>1];
        

        $sale_data[$debit_key]=['t'=>''];
        $sale_data[$credit_key]=['t'=>$general->numberFormat(floatval(@$sale_credit[$key]))];
        
        $total_debit[$debit_key]+=floatval(@$sale_credit[$key]);
        $sale_return_data[$debit_key]=['t'=>$general->numberFormat(floatval(@$sale_return_credit[$key]))];
        $sale_return_data[$credit_key]=['t'=>''];
        $total_credit[$credit_key]+=floatval(@$sale_return_credit[$key]);
        $wastages_sales_data[$debit_key]=['t'=>''];
        $wastages_sales_data[$credit_key]=['t'=>''];
        $interest_received_data[$debit_key]=['t'=>''];
        $interest_received_data[$credit_key]=['t'=>''];
        $expense_data[$debit_key]=['t'=>$general->numberFormat(floatval(@$package_product_amount[$key])+floatval($package_product_amount[$key])),'b'=>1];
        $expense_data[$credit_key]=['t'=>''];

        $space_data[$debit_key]=false;
        $space_data[$credit_key]=false;
        
        $row_amount[$debit_key]=['t'=>$general->numberFormat(floatval(@$package_product_amount[$key]))];
        $row_amount[$credit_key]=['t'=>''];
        $package_amount[$debit_key]=['t'=>$general->numberFormat(floatval($package_product_amount[$key]))];
        $package_amount[$credit_key]=['t'=>''];

        $row_cl_amount = floatval(@$row_cl_product_amount[$key]);
        $package_cl_amount = floatval(@$package_cl_product_amount[$key]);
        $manufacturing_cl_amount = floatval(@$manufacturing_cl_product_amount[$key]);
        $finished_cl_amount = floatval(@$finished_cl_product_amount[$key]);

        $all_product_cl_amount= $row_cl_amount+$package_cl_amount+$manufacturing_cl_amount+$finished_cl_amount;
        debit_credit_wise_data_load($general,$inventories_data,$debit_key,$credit_key,$all_product_cl_amount);
        debit_credit_wise_data_load($general,$row_product_closing_data,$debit_key,$credit_key,$row_cl_amount);
        debit_credit_wise_data_load($general,$package_product_closing_data,$debit_key,$credit_key,$package_cl_amount);
        debit_credit_wise_data_load($general,$manufacturing_product_closing_data,$debit_key,$credit_key,$manufacturing_cl_amount);
        debit_credit_wise_data_load($general,$finished_product_closing_data,$debit_key,$credit_key,$finished_cl_amount);

        //$inventories_data[$debit_key]=['t'=>$general->numberFormat($all_product_cl_amount),'b'=>1];
        // $row_product_closing_data[$debit_key]=['t'=>$general->numberFormat($row_cl_amount)];
        // $package_product_closing_data[$debit_key]=['t'=>$general->numberFormat($package_cl_amount)];
        // $manufacturing_product_closing_data[$debit_key]=['t'=>$general->numberFormat($manufacturing_cl_amount)];
        // $finished_product_closing_data[$debit_key]=['t'=>$general->numberFormat($finished_cl_amount)];


    }

    $colspan = count($dates)*2+1;
    $rData[]=['title'=>['t'=>'','b'=>1],...$title_data];
    $rData[]=['title'=>['t'=>'Head','b'=>1],...$debit_credit];
    $rData[]=['title'=>['t'=>'Income','b'=>1],...$income_data];
    $rData[]=['title'=>['t'=>'1001 Sales Revenue'],...$sale_data];
    $rData[]=['title'=>['t'=>'1002 Sales Return'],...$sale_return_data];
    $rData[]=['title'=>['t'=>'1003 Wastages Sales'],...$wastages_sales_data];
    $rData[]=['title'=>['t'=>'1004 Interest received'],...$interest_received_data];
    $rData[]=['title'=>['t'=>'Expense','b'=>1],...$expense_data];
    $rData[]=['title'=>['t'=>'2101 Raw Material- Purchase'],...$row_amount];
    $rData[]=['title'=>['t'=>'2102 Packaging Material- Purchase'],...$package_amount];



    $chart_of_accounts = $acc->chart_of_accounts();
    $chart_of_accounts_array=[
        COA_ADMINISTRATIVE_OVERHEAD=>$chart_of_accounts[COA_ADMINISTRATIVE_OVERHEAD],
        COA_SALES_AND_MARKETING_EXPENSES=>$chart_of_accounts[COA_SALES_AND_MARKETING_EXPENSES],
        COA_DISTRIBUTION_EXPENSES=>$chart_of_accounts[COA_DISTRIBUTION_EXPENSES],
        COA_FACTORY_OVER_HEAD=>$chart_of_accounts[COA_FACTORY_OVER_HEAD],
        COA_FINANCIAL_EXPENSES=>$chart_of_accounts[COA_FINANCIAL_EXPENSES],
    ];
    chart_of_account_wise_rData_load(
        $general,
        $chart_of_accounts_array,
        $chart_accounts,
        $chart_accounts_data,
        $head_debit_and_credit,
        $chart_of_account_wise_ledgers,
        $dates,
        $rData,
        $total_debit,
        $total_credit
    );
    $rData[]=['title'=>['t'=>'Assets','b'=>1,'col'=>$colspan],...$space_data];
    $rData[]=['title'=>['t'=>'Current Assets','b'=>1,'col'=>$colspan],...$space_data];
    $chart_of_accounts_array=[
        COA_CASH_AND_CASH_EQUIVALENT=>$chart_of_accounts[COA_CASH_AND_CASH_EQUIVALENT],
        //COA_SALES_RECEIVABLE=>$chart_of_accounts[COA_SALES_RECEIVABLE],
        COA_EMPLOYEES_ADVANCE=>$chart_of_accounts[COA_EMPLOYEES_ADVANCE],
        COA_DIRECTOR_LOANS=>$chart_of_accounts[COA_DIRECTOR_LOANS],
        COA_ADVANCE_TO_SUPPLIERS=>$chart_of_accounts[COA_ADVANCE_TO_SUPPLIERS],
        //COA_INVENTORIES=>$chart_of_accounts[COA_INVENTORIES],
    ];

    chart_of_account_wise_rData_load(
        $general,
        $chart_of_accounts_array,
        $chart_accounts,
        $chart_accounts_data,
        $head_debit_and_credit_closing,
        $chart_of_account_wise_ledgers,
        $dates,
        $rData,
        $total_debit,
        $total_credit
    );
    $rData[] = ['title'=>['t'=>'Inventories','b'=>1],...$inventories_data];
    $rData[] = ['title'=>['t'=>'Inventories - RM'],...$row_product_closing_data];
    $rData[] = ['title'=>['t'=>'Inventories - PM'],...$package_product_closing_data];
    $rData[] = ['title'=>['t'=>'Inventories - Working Process'],...$manufacturing_product_closing_data];
    $rData[] = ['title'=>['t'=>'Inventories - Finished Good'],...$finished_product_closing_data];

    

    $chart_of_accounts_array=[
        COA_PROPERTY_PLANT_AND_EQUIPMENT=>$chart_of_accounts[COA_PROPERTY_PLANT_AND_EQUIPMENT],
        COA_FACTORY_CONSTRUCTION_AND_RENOVATION=>$chart_of_accounts[COA_FACTORY_CONSTRUCTION_AND_RENOVATION],
        COA_ACCOUNTS_PAYABLE=>$chart_of_accounts[COA_ACCOUNTS_PAYABLE],
        COA_NON_CURRENT_LIABILITIES=>$chart_of_accounts[COA_NON_CURRENT_LIABILITIES],
    ];
    chart_of_account_wise_rData_load(
        $general,
        $chart_of_accounts_array,
        $chart_accounts,
        $chart_accounts_data,
        $head_debit_and_credit_closing,
        $chart_of_account_wise_ledgers,
        $dates,
        $rData,
        $total_debit,
        $total_credit
    );
    
    $chart_of_accounts_array=[
        COA_PROPERTY_PLANT_AND_EQUIPMENT=>$chart_of_accounts[COA_PROPERTY_PLANT_AND_EQUIPMENT],
        COA_FACTORY_CONSTRUCTION_AND_RENOVATION=>$chart_of_accounts[COA_FACTORY_CONSTRUCTION_AND_RENOVATION],
        COA_ACCOUNTS_PAYABLE=>$chart_of_accounts[COA_ACCOUNTS_PAYABLE],
        COA_NON_CURRENT_LIABILITIES=>$chart_of_accounts[COA_NON_CURRENT_LIABILITIES],
    ];
    chart_of_account_wise_rData_load(
        $general,
        $chart_of_accounts_array,
        $chart_accounts,
        $chart_accounts_data,
        $head_debit_and_credit_closing,
        $chart_of_account_wise_ledgers,
        $dates,
        $rData,
        $total_debit,
        $total_credit
    );
    $rData[]=['title'=>['t'=>'Non Current Assets','b'=>1,'col'=>$colspan],...$space_data];
    $rData[]=['title'=>['t'=>'8001 Long Term Loan'],...$wastages_sales_data];
    $rData[]=['title'=>['t'=>'Equity','b'=>1,'col'=>$colspan],...$space_data];
    $chart_of_accounts_array=[
        COA_PAID_UP_CAPITAL=>$chart_of_accounts[COA_PAID_UP_CAPITAL],
        COA_SHARE_MONEY_DEPOSIT=>$chart_of_accounts[COA_SHARE_MONEY_DEPOSIT],
        COA_RETAINED_EARNINGS=>$chart_of_accounts[COA_RETAINED_EARNINGS],
    ];
    chart_of_account_wise_rData_load(
        $general,
        $chart_of_accounts_array,
        $chart_accounts,
        $chart_accounts_data,
        $head_debit_and_credit_closing,
        $chart_of_account_wise_ledgers,
        $dates,
        $rData,
        $total_debit,
        $total_credit
    );


    $jArray[fl()]=$head;
    $jArray[fl()]=$rData;

    $fileName='item_wise_sale_'.TIME.rand(0,999).'.txt';
    $report_data=[
        'name'      => 'saleReport',
        'title'     => 'Sale List',
        'info'      => [],
        'fileName'  => $fileName,
        'head'=>$head,
        'data'=>$rData
    ];
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;

}
else{$error=fl();setMessage(1,'Data not found');}

function chart_of_account_wise_rData_load(
    General $general, 
    array $chart_account,
    array $chart_accounts,
    array $chart_accounts_data,
    array $head_debit_and_credit,
    array $chart_of_account_wise_ledgers,
    array $dates ,
    array &$rData,
    &$total_debit,
    &$total_credit ): void{


    $colspan = count($dates)*2+1;
    if(!empty($chart_accounts)){
        foreach($chart_account as $ca){
            $chart_account = $chart_accounts[$ca['id']]??0;
            $ledger= $chart_of_account_wise_ledgers[$chart_account]??[];
            
            $r_data=[];
            $totalDebit = [];
            $totalCredit = [];
            foreach($ledger as $l){
                $l_amount = [];
                foreach($dates as $key=>$date){
                    $debit_key = $key.'_debit';
                    $credit_key = $key.'_credit';
                    $debit = floatval(@$head_debit_and_credit[$l['id']][$debit_key]);
                    $credit = floatval(@$head_debit_and_credit[$l['id']][$credit_key]);
                    $amount = $debit-$credit;
                    if(!isset($totalDebit[$debit_key])){$totalDebit[$debit_key]=0;}
                    if(!isset($totalCredit[$credit_key])){$totalCredit[$credit_key]=0;}
                    $totalDebit[$debit_key]+=$debit;
                    $totalCredit[$credit_key]+=$credit;
                    if($amount>0){
                        $debit=$general->numberFormat($amount);
                        $credit='';
                    }
                    elseif($amount==0){
                        $debit='0.00';
                        $credit='0.00';
                    }
                    else{
                        $credit=$general->numberFormat(-1*$amount);
                        $debit='';
                    }
                    
                    
                    $l_amount[$debit_key]=['t'=>$debit,'key'=>$debit_key];
                    $l_amount[$credit_key]=['t'=>$credit,'key'=>$credit_key];
                    $total_debit[$debit_key]+=floatval(@$head_debit_and_credit[$l['id']][$debit_key]);
                    $total_credit[$credit_key]+=floatval(@$head_debit_and_credit[$l['id']][$credit_key]);
                }
                $r_data[]=['title'=>['t'=>$l['title']],...$l_amount];
            }
            $ca_array = [];
            foreach($dates as $key=>$date){
                $debit_key = $key.'_debit';
                $credit_key = $key.'_credit';
                $debit = floatval(@$totalDebit[$debit_key]);
                $credit = floatval(@$totalCredit[$credit_key]);
                $amount = $debit-$credit;
                if($amount>0){
                    $debit=$general->numberFormat($amount);
                    $credit='';
                }
                elseif($amount==0){
                    $debit='0.00';
                    $credit='0.00';
                }
                else{
                    $credit=$general->numberFormat(-1*$amount);
                    $debit='';
                }
                $ca_array[$debit_key]=['t'=>$debit,'key'=>$debit_key,'b'=>1];
                $ca_array[$credit_key]=['t'=>$credit,'key'=>$credit_key,'b'=>1];
            }
            $code = $chart_accounts_data[$chart_account]['code']??'';
            $rData[]=['title'=>['t'=>$code.' '.$ca['title'],'b'=>1],...$ca_array];
            //$rData = [$rData,...$r_data];
            $rData = array_merge_recursive($rData, $r_data);
        }
    }
}