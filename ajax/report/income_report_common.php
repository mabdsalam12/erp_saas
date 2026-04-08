<?php
function hasPositiveT($array) {
    if(!isset($_POST['withoutZero'])){return true;}
    if($_POST['withoutZero']!=1){return true;}
    foreach ($array as $item) {
        if (isset($item['t']) && floatval($item['t']) != 0) {
            return true; // Found a value > 0
        }
    }
    return false; // All 't' values are 0
}
function product_amount_2(DB $db,$product_type,$data,$price,$products,&$amount){
    if(!empty($data)){
        foreach($data as $d){
            $count =count($d);
            //if($count==1){continue;}
            $product_id = $d['product_id'];
            
            foreach($d as $key=>$l){
               if($key=='product_id'){continue;}
                if(!isset($amount[$key])){
                    $amount[$key]=0;
                }
                
                $price_data = $price[$key]??[];
                
                if(!empty($price_data)){
                    $price_set = 0;
                    foreach($price_data as  $k){
                        if($k['product_id']==$product_id){
                            $main_price = $k;
                            $price_set = 1;
                        }
                    }
                   
                    if(!$price_set){
                        $main_price = $products[$product_id];
                        $db->product_price_log($product_id,['sale_price'=>$main_price['sale_price'],'unit_cost'=>$main_price['unit_cost']]); 
                    }
                }
                if(empty($main_price)){ continue;}
                if($product_type==PRODUCT_TYPE_PACKAGING || $product_type==PRODUCT_TYPE_RAW){
                    //print_r($main_price['unit_cost']*$d[$key]);exit;
                    $amount[$key]+=$main_price['unit_cost']*$d[$key]; continue;
                }
                $amount[$key]+=$main_price['sale_price']*$d[$key]; continue;
            }
        }
    }
}


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
    $ledger_accounts = $db->get_company_data()['ledger_accounts']??[];
    $chart_accounts_data = $db->selectAll('a_charts_accounts','where id in('.implode(',',$chart_accounts).') order by code','id,title,code');
    ////$jArray[fl()]=$chart_accounts;
    $general->arrayIndexChange($chart_accounts_data);
    $chart_of_account_ids=[];
    $ledgers=[];
    ////$jArray[fl()]=$chart_accounts;
    $chart_of_account_wise_ledgers=[];
    if(!empty($chart_accounts) || !empty($ledger_accounts)){
        $q=[];
        if(!empty($chart_accounts)){
            foreach($chart_accounts as $ca){
                $chart_of_account_ids[] =$ca;
            }
            $q[]='charts_accounts_id  in('.implode(',',$chart_of_account_ids).')';
        }
        if(!empty($ledger_accounts)){
            $q[]='id in('.implode(',',$ledger_accounts).')';
        }
        $ledgers = $db->selectAll('a_ledgers','where '.implode(' or ',$q).'  order by code','id, charts_accounts_id ,title, code','array',$jArray);
        $general->arrayIndexChange($ledgers);
        foreach($ledgers as $l){
            $chart_of_account_wise_ledgers[$l['charts_accounts_id']][]=$l;
        }
    }
    
    $dates=[];
    $product_log_query=[];
    $opening_product_log_query=[];
    $closed_product_log_query=[];
    $unit_price_query_from = [];
    $unit_price_query_to = [];
    $head_middle_query =[];
    $big_date=0;
    $small_date=0;
    $s=1;
    
    foreach($date_range as $date){
        $general->getFromToFromString($date,$from,$to);
        $dates["Q$s"]= [$from,$to];
        $product_log_query[]="SUM(CASE WHEN time BETWEEN $from AND $to THEN quantity * unit_price ELSE 0 END) AS 'Q$s'";
        $opening_product_log_query[]= " SUM(CASE WHEN action_time BETWEEN 0 AND $from THEN quantity ELSE 0 END) AS 'Q$s'";
        $closed_product_log_query[]= " SUM(CASE WHEN action_time BETWEEN 0 AND $to THEN quantity ELSE 0 END) AS 'Q$s'";
        $head_middle_query[]="SUM(CASE WHEN time BETWEEN $from AND $to THEN debit ELSE 0 END) -SUM(CASE WHEN time BETWEEN $from AND $to THEN credit ELSE 0 END) AS 'Q$s'";
        $unit_price_query_from["Q$s"] = $db->get_product_price_log_price($from+1);
        $unit_price_query_to["Q$s"] = $db->get_product_price_log_price($to);
        $s++;
        if($small_date==0 || $from<$small_date){
            $small_date = $from;
        }
        if($to>$big_date){
            $big_date = $to;
        }
    }
    //$jArray[fl()]=$general->make_date($small_date,'i');
    //$jArray[fl()]=$general->make_date($big_date,'i');
    $row_product_amount = [];
    $row_opening_product_amount = [];
    $row_closed_product_amount = [];
    $head_opening_balance=[];
    $chart_of_account_wise_data=[];
    $chart_of_account_wise_total=[];
    if(!empty($row_product)){
        $row_product_ids=array_keys($row_product);
        //$row_query = "SELECT CASE $product_log_query ELSE 'out_of_range' END AS date_range,product_id, SUM(quantity*unit_price) AS price FROM purchase_details WHERE time BETWEEN $small_date AND $big_date AND  product_id IN (".implode(',',$row_product_ids).") GROUP BY product_id,date_range ORDER BY `date_range` ASC;";
        $row_query = "SELECT ".implode(',',$product_log_query)." FROM purchase_details WHERE time BETWEEN $small_date AND $big_date AND product_id IN (".implode(',',$row_product_ids).") ORDER BY  product_id ASC;";

        $row_opening_query = "SELECT  product_id, ".implode(',',$opening_product_log_query)." FROM product_stock_log WHERE action_time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$row_product_ids).") GROUP BY  product_id ORDER BY  product_id ASC;";
        $row_closed_query = "SELECT  product_id, ".implode(',',$closed_product_log_query)." FROM product_stock_log WHERE action_time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$row_product_ids).") GROUP BY  product_id ORDER BY  product_id ASC;";

        


        $jArray[fl()]=$row_query;
        // $jArray[fl()]=$row_opening_query;
        //$jArray[fl()]=$row_closed_query;
        $row_product_log = $db->fetchQuery($row_query);
        //$row_product_log_2 = $db->fetchQuery($row_query_2);
        if(!empty($row_product_log)){
            $row_product_amount=$row_product_log[0];
        }
        $row_opening_product_log = $db->fetchQuery($row_opening_query);
        $row_closed_product_log = $db->fetchQuery($row_closed_query);

        //$jArray[fl()]=$row_product_amount;
        //$jArray[fl()]=$row_product_log;
        //$jArray[fl()]=$row_opening_product_log;
        //$jArray[fl()]=$row_closed_product_log;
        //$jArray[fl()]=$unit_price_query_from;
        //$jArray[fl()]=$unit_price_query_to;

        product_amount_2(
            $db,
            PRODUCT_TYPE_RAW,
            $row_opening_product_log,
            $unit_price_query_from,
            $row_product,
            $row_opening_product_amount,
        );
        product_amount_2(
            $db,
            PRODUCT_TYPE_RAW,
            $row_closed_product_log,
            $unit_price_query_to,
            $row_product,
            $row_closed_product_amount,
        );
        
        //$jArray[fl()]=$row_opening_product_amount;
        //$jArray[fl()]=$row_product_amount;
        //$jArray[fl()]=$row_closed_product_amount;
    
    }
    if(!empty($ledgers)){
        $head_query = "SELECT ledger_id, ".implode(',',$head_middle_query)." FROM a_ledger_entry WHERE  time BETWEEN $small_date AND $big_date and ledger_id in(".implode(',',array_keys($ledgers)).") GROUP BY ledger_id ORDER BY ledger_id ASC;";    
        ////$jArray[fl()]=$head_query;
        $head_opening_balance = $db->fetchQuery($head_query);
        $general->arrayIndexChange($head_opening_balance,'ledger_id');
        foreach($head_opening_balance as $l){
            $ledger = $ledgers[$l['ledger_id']];
            $charts_accounts_id=$ledger['charts_accounts_id'];
            foreach($dates as $key=>$d){
                if(!isset($chart_of_account_wise_total[$charts_accounts_id][$key])){
                    $chart_of_account_wise_total[$charts_accounts_id][$key]=0; 
                }
                $chart_of_account_wise_total[$charts_accounts_id][$key]+=$l[$key];
            }
            
        }
    }
    ////$jArray[fl()]=$chart_of_account_wise_total;
    //$jArray[fl()]=$head_opening_balance;
    
    $package_product_amount = [];
    $package_opening_product_amount = [];
    $package_closed_product_amount = [];
    if(!empty($package_product)){
        $package_product_ids=array_keys($package_product);
        $package_query = "SELECT ".implode(',',$product_log_query)." FROM purchase_details WHERE time BETWEEN $small_date AND $big_date AND product_id IN (".implode(',',$package_product_ids).") ORDER BY  product_id ASC;";
        $package_opening_query = "SELECT  product_id, ".implode(',',$opening_product_log_query)." FROM product_stock_log WHERE action_time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$package_product_ids).") GROUP BY  product_id ORDER BY  product_id ASC;";
        $package_closed_query = "SELECT  product_id, ".implode(',',$closed_product_log_query)." FROM product_stock_log WHERE action_time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$package_product_ids).") GROUP BY  product_id ORDER BY  product_id ASC;";
        $jArray[fl()] = $package_closed_query;
        $package_product_log = $db->fetchQuery($package_query);
        if(!empty($package_product_log)){
            $package_product_amount=$package_product_log[0];
        }
        $package_opening_product_log = $db->fetchQuery($package_opening_query);
        $package_closed_product_log = $db->fetchQuery($package_closed_query);
        product_amount_2(
            $db,
            PRODUCT_TYPE_PACKAGING,
            $package_opening_product_log,
            $unit_price_query_from,
            $package_product,
            $package_opening_product_amount,
        );
        product_amount_2(
            $db,
            PRODUCT_TYPE_PACKAGING,
            $package_closed_product_log,
            $unit_price_query_to,
            $package_product,
            $package_closed_product_amount,
        );
    }

    $finished_product_amount = [];
    $finished_opening_product_amount = [];
    $finished_closed_product_amount = [];
    if(!empty($finished_product)){
        $finished_product_ids=array_keys($finished_product);
        $finished_query = "SELECT ".implode(',',$product_log_query)." FROM purchase_details WHERE time BETWEEN $small_date AND $big_date AND product_id IN (".implode(',',$finished_product_ids).") ORDER BY  product_id ASC;";
        $finished_opening_query = "SELECT  product_id, ".implode(',',$opening_product_log_query)." FROM product_stock_log WHERE action_time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$finished_product_ids).") GROUP BY  product_id ORDER BY  product_id ASC;";
        $finished_closed_query = "SELECT  product_id, ".implode(',',$closed_product_log_query)." FROM product_stock_log WHERE action_time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$finished_product_ids).") GROUP BY  product_id ORDER BY  product_id ASC;";
        $finished_product_log = $db->fetchQuery($finished_query);
        if(!empty($finished_product_log)){
            $finished_product_amount=$finished_product_log[0];
        }
        $finished_opening_product_log = $db->fetchQuery($finished_opening_query);
        $finished_closed_product_log = $db->fetchQuery($finished_closed_query);
        product_amount_2(
            $db,
            PRODUCT_TYPE_FINISHED,
            $finished_opening_product_log,
            $unit_price_query_from,
            $finished_product,
            $finished_opening_product_amount,
        );
        product_amount_2(
            $db,
            PRODUCT_TYPE_FINISHED,
            $finished_closed_product_log,
            $unit_price_query_to,
            $finished_product,
            $finished_closed_product_amount,
        );
    }
    $manufacturing_product_amount = [];
    $manufacturing_opening_product_amount = [];
    $manufacturing_closed_product_amount = [];
    if(!empty($manufacturing_product)){
        $manufacturing_product_ids=array_keys($manufacturing_product);
        $manufacturing_query = "SELECT ".implode(',',$product_log_query)." FROM purchase_details WHERE time BETWEEN $small_date AND $big_date AND product_id IN (".implode(',',$manufacturing_product_ids).") ORDER BY  product_id ASC;";
        $manufacturing_opening_query = "SELECT  product_id, ".implode(',',$opening_product_log_query)." FROM product_stock_log WHERE action_time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$manufacturing_product_ids).") GROUP BY  product_id ORDER BY  product_id ASC;";
        $manufacturing_closed_query = "SELECT  product_id, ".implode(',',$closed_product_log_query)." FROM product_stock_log WHERE action_time BETWEEN 0 AND $big_date AND product_id IN (".implode(',',$manufacturing_product_ids).") GROUP BY  product_id ORDER BY  product_id ASC;";
        $manufacturing_product_log = $db->fetchQuery($manufacturing_query);
        if(!empty($manufacturing_product_log)){
            $manufacturing_product_amount=$manufacturing_product_log[0];
        }
        $manufacturing_opening_product_log = $db->fetchQuery($manufacturing_opening_query);
        $manufacturing_closed_product_log = $db->fetchQuery($manufacturing_closed_query);
        product_amount_2(
            $db,
            PRODUCT_TYPE_MANUFACTURING,
            $manufacturing_opening_product_log,
            $unit_price_query_from,
            $manufacturing_product,
            $manufacturing_opening_product_amount,
        );
        product_amount_2(
            $db,
            PRODUCT_TYPE_MANUFACTURING,
            $manufacturing_closed_product_log,
            $unit_price_query_to,
            $manufacturing_product,
            $manufacturing_closed_product_amount,
        );
    }
}
$rData=[];
$title_data = [];
$row_materials_consumed_data=[];
$packing_materials_consumed_data=[];
$factory_Overhead_data=[];
$cost_of_production_data=[];
$row_consumed = [];
$row_op =[];
$row_purchased =[];
$row_cl =[];

$package_consumed = [];
$package_op =[];
$package_purchased =[];
$manufacturing_purchased_add =[];
$manufacturing_purchased_less =[];
$calculation_data =[];
$cost_of_goods_sold_data =[];
$package_cl =[];

$finished_purchased_add =[];
$finished_purchased_less =[];

$cost_of_goods = [];

$chart_of_account_rData=[];
$inventories_data=[];
$row_product_closing_data=[];
$package_product_closing_data=[];
$manufacturing_product_closing_data=[];
$finished_product_closing_data=[];

$head=[
    ['title'=>' ','key'=>'title'],
];
$factory_overhead_account_ledger_id = $acc->get_chart_of_account(COA_FACTORY_OVER_HEAD);
if(!empty($dates)){
    foreach($dates as $key=>$date){
        $debit_key = "debit_$key";
        $credit_key = "credit_$key";
        // $head[]=['title'=>'Debit','key'=>$debit_key];
        // $head[]=['title'=>'Credit','key'=>$credit_key];

        $head[]=['title'=>'','key'=>$debit_key];
        $title_data[$debit_key]=['t'=>$general->make_date($date[0]).' to '.$general->make_date($date[1]),'key'=>$debit_key];

        $row_amount = $row_product_amount[$key]??0;
        $row_op_amount = $row_opening_product_amount[$key]??0;
        $row_cl_amount = $row_closed_product_amount[$key]??0;
        $row_materials_consumed = $row_amount+$row_op_amount-$row_cl_amount;

        $row_consumed[$debit_key]=['t'=>$general->numberFormat($row_materials_consumed)];
        //$row_consumed[$credit_key]=['t'=>'0.00'];

        $row_op[$debit_key]=['t'=>$general->numberFormat($row_op_amount)];
        //$row_op[$credit_key]=['t'=>'0.00'];

        //$row_purchased[$debit_key]=['t'=>'0.00'];
        $row_purchased[$debit_key]=['t'=>$general->numberFormat($row_amount)];

        $row_cl[$debit_key]=['t'=>$general->numberFormat($row_cl_amount)];
        //$row_cl[$credit_key]=['t'=>'0.00'];

        $package_amount = $package_product_amount[$key]??0;
        $package_op_amount = $package_opening_product_amount[$key]??0;
        $package_cl_amount = $package_closed_product_amount[$key]??0;
        $package_materials_consumed = $package_amount+$package_op_amount-$package_cl_amount;
        $jArray[fl()]=[
            'amount'=>$general->numberFormat($package_amount),
            'opening'=>$general->numberFormat($package_op_amount),
            'package_cl_amount'=>$general->numberFormat($package_cl_amount),
            'consume'=>$general->numberFormat($package_materials_consumed),
        ];
        $package_consumed[$debit_key]=['t'=>$general->numberFormat($package_materials_consumed)];
        //$package_consumed[$credit_key]=['t'=>'0.00'];

        $package_op[$debit_key]=['t'=>$general->numberFormat($package_op_amount)];
        //$package_op[$credit_key]=['t'=>'0.00'];
        //$package_purchased[$debit_key]=['t'=>'0.00'];
        $package_purchased[$debit_key]=['t'=>$general->numberFormat($package_amount)];
        //$jArray[fl()][$key]=$package_cl_amount;
        $package_cl[$debit_key]=['t'=>$general->numberFormat($package_cl_amount)];
        //$package_cl[$credit_key]=['t'=>'0.00'];
        

        $factory_overhead_amount = $chart_of_account_wise_total[$factory_overhead_account_ledger_id][$key]??0 ;
        $cost_of_goods_amount = $row_materials_consumed+$package_materials_consumed+$factory_overhead_amount;//এখানে একটা বাদ থাকলো
        //$jArray[fl()][$key]=[$row_materials_consumed,$package_materials_consumed];
        //$cost_of_goods[$debit_key]=['t'=>'0.00','key'=>$key];
        $cost_of_goods[$debit_key]=['t'=>$general->numberFormat($cost_of_goods_amount),'key'=>$key];

        $row_materials_consumed_data[$debit_key] = ['t'=>$general->numberFormat($row_materials_consumed),'key'=>$key];
        $packing_materials_consumed_data[$debit_key] = ['t'=>$general->numberFormat($package_materials_consumed),'key'=>$key];
        $factory_Overhead_data[$debit_key] = ['t'=>$general->numberFormat($factory_overhead_amount),'key'=>$key];
        $cost_of_production_amount = $package_materials_consumed+$row_materials_consumed+$factory_overhead_amount;
        $cost_of_production_data[$debit_key] = ['t'=>$general->numberFormat($cost_of_production_amount),'key'=>$key,'b'=>1];

        //$manufacturing_amount = $manufacturing_product_amount[$key]??0; // এটা চেঞ্জ করে দিলাম কিন্তু বুঝলাম না এটা এমন করা ছিলো কেন
        $manufacturing_amount = $manufacturing_opening_product_amount[$key]??0;
        $manufacturing_cl_amount = $manufacturing_closed_product_amount[$key]??0;
        $manufacturing_purchased_add[$debit_key] = ['t'=>$general->numberFormat($manufacturing_amount),'key'=>$key];
        $manufacturing_purchased_less[$debit_key] = ['t'=>$general->numberFormat($manufacturing_cl_amount),'key'=>$key];
        //$calculation_amount = $manufacturing_amount+$manufacturing_cl_amount-$cost_of_production_amount;
        $calculation_amount=$cost_of_production_amount+$manufacturing_amount-$manufacturing_cl_amount;
        $calculation_data[$debit_key] = ['t'=>$general->numberFormat($calculation_amount),'key'=>$key,'b'=>1];

        //$finished_amount = $finished_product_amount[$key]??0; // এটা চেঞ্জ করে দিলাম কিন্তু বুঝলাম না এটা এমন করা ছিলো কেন
        $finished_amount = $finished_opening_product_amount[$key]??0;
        $finished_cl_amount = $finished_closed_product_amount[$key]??0;
        $finished_purchased_add[$debit_key] = ['t'=>$general->numberFormat($finished_amount),'key'=>$key];
        $finished_purchased_less[$debit_key] = ['t'=>$general->numberFormat($finished_cl_amount),'key'=>$key];
        $cost_of_goods_sold_data[$debit_key] = ['t'=>$general->numberFormat($finished_amount+$finished_cl_amount-$calculation_amount),'key'=>$key,'b'=>1];

        $all_product_cl_amount= $row_cl_amount+$package_cl_amount+$manufacturing_cl_amount+$finished_cl_amount;
        $inventories_data[$debit_key]=['t'=>$general->numberFormat($all_product_cl_amount),'b'=>1];
        $row_product_closing_data[$debit_key]=['t'=>$general->numberFormat($row_cl_amount)];
        $package_product_closing_data[$debit_key]=['t'=>$general->numberFormat($package_cl_amount)];
        $manufacturing_product_closing_data[$debit_key]=['t'=>$general->numberFormat($manufacturing_cl_amount)];
        $finished_product_closing_data[$debit_key]=['t'=>$general->numberFormat($finished_cl_amount)];
    }
}