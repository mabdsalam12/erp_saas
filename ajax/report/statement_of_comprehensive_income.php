<?php
$date_range = $_POST['date_range'];
include_once 'income_report_common.php';
$total_sale_query = [];
$total_sale_return_query = [];
$s=1;
$rData=[];

if(!empty($date_range)){
    foreach($date_range as $date){
        $general->getFromToFromString($date,$from,$to);
        $total_sale_query[]="SUM(CASE WHEN date BETWEEN $from AND $to THEN total ELSE 0 END) AS 'Q$s'";
        $total_sale_return_query[]="SUM(CASE WHEN createdOn BETWEEN $from AND $to THEN return_amount ELSE 0 END) AS 'Q$s'";
        $s++;
    }
    $chart_of_accounts = $acc->chart_of_accounts();
    $chart_administrative_expenses = $chart_accounts[$chart_of_accounts[COA_ADMINISTRATIVE_EXPENSES]['id']]??0;
    $chart_distribution_expenses = $chart_accounts[$chart_of_accounts[COA_DISTRIBUTION_EXPENSES]['id']]??0;
    $chart_selling = $chart_accounts[$chart_of_accounts[COA_SELLING_AND_MARKETING_EXP]['id']]??0;
    $chart_finance_cost = $chart_accounts[$chart_of_accounts[COA_FINANCIAL_EXPENSES]['id']]??0;

    $total_sale_query = "SELECT ".implode(',',$total_sale_query)." FROM sale WHERE date BETWEEN $small_date AND $big_date";
    $total_sale_return_query = "SELECT ".implode(',',$total_sale_return_query)." FROM sale_return WHERE createdOn BETWEEN $small_date AND $big_date";
    $total_sale = $db->fetchQuery($total_sale_query)[0]??[];
    $total_sale_return = $db->fetchQuery($total_sale_return_query)[0]??[];
    $jArray[fl()]=$total_sale;
    $turnover_data = [];
    $gross_profit_data = [];
    $ad_amount = [];
    $total_expenses=[];
    $total_ad_expenses = [];
    $profit_from_operation_data = [];
    $finance_cost_data = [];
    $profit_loss_data = [];
    $other_revenue_data = [];
    $provision_for_income_tax_data = [];
    $empty_data = [];
    $net_profit_loss_before_tax_data = [];
    $net_profit_after_tax_data = [];
    $false_data=[];
    $other_revenue_head_account = $acc->getHeadAccountId(LEDGER_ACCOUNT_OTHER_REVENUE);
    $provision_for_income_tax_head_account = $acc->getHeadAccountId(LEDGER_ACCOUNT_PROVISION_FOR_INCOME_TAX);
    $jArray[fl()]=$other_revenue_head_account;
    foreach($dates as $key=>$date){
        $debit_key = "debit_$key";
        $credit_key = "credit_$key";
        $turnover = floatval($total_sale[$key]??0)-floatval($total_sale_return[$key]??0);
        $turnover_data[$debit_key]= ['t'=>$general->numberFormat($turnover)];
        $cost_of_goods_sold = floatval(str_replace(',', '', $cost_of_goods[$debit_key]['t']??0));
        $gross_profit = $turnover-$cost_of_goods_sold;
        $gross_profit_data[$debit_key]= ['t'=>$general->numberFormat($gross_profit)];
        $ad_of_ac_total = floatval(@$chart_of_account_wise_total[$chart_administrative_expenses][$key]);
        $distribution_expenses = floatval(@$chart_of_account_wise_total[$chart_distribution_expenses][$key]);
        $selling = floatval(@$chart_of_account_wise_total[$chart_selling][$key]);
        $finance_cost = floatval(@$chart_of_account_wise_total[$chart_finance_cost][$key]);
        $ad_amount[$debit_key]=['t'=>$general->numberFormat($ad_of_ac_total)];
        $total_expenses[$debit_key]=['t'=>$general->numberFormat($distribution_expenses+$selling)];
        $total_ad_expenses[$debit_key]=['t'=>$general->numberFormat($distribution_expenses+$selling+$ad_of_ac_total)];
        $profit_from_operation = $gross_profit-($distribution_expenses+$selling+$ad_of_ac_total);
        $profit_from_operation_data[$debit_key]=['t'=>$general->numberFormat($profit_from_operation)];

        $other_revenue = $head_opening_balance[$other_revenue_head_account][$key]??0;
        $provision_for_income_tax = $head_opening_balance[$provision_for_income_tax_head_account][$key]??0;
        $other_revenue_data[$debit_key]=['t'=>$general->numberFormat($other_revenue)];
        $provision_for_income_tax_data[$debit_key]=['t'=>$general->numberFormat($provision_for_income_tax)];
        $finance_cost_data[$debit_key]=['t'=>$general->numberFormat($finance_cost)];
        $profit_loss = $profit_from_operation-$finance_cost+$other_revenue;
        $profit_loss_data[$debit_key]=['t'=>$general->numberFormat($profit_loss)];
       
        $empty_data[$debit_key]=['t'=>''];
        $net_profit_loss_before_tax= $profit_loss+0+0;
        $net_profit_loss_before_tax_data[$debit_key]=['t'=>$general->numberFormat($net_profit_loss_before_tax)];
        $net_profit_after_tax = $net_profit_loss_before_tax-$profit_loss;
        $net_profit_after_tax_data[$debit_key]=['t'=>$general->numberFormat($net_profit_after_tax)];
        $false_data[$debit_key]=false;
    }
    $rData[]=['title'=>['t'=>'','b'=>1],...$title_data];
    $rData[]=['title'=>['t'=>'Turnover','b'=>1],...$turnover_data];
    $rData[]=['title'=>['t'=>'Cost of Goods Sales','b'=>1],...$cost_of_goods];
    $rData[]=['title'=>['t'=>'Gross Profit','b'=>1],...$gross_profit_data];
    $rData[]=['title'=>['t'=>'Administrative Expenses','b'=>1],...$ad_amount];
    $rData[]=['title'=>['t'=>'Selling & Distribution Exp.','b'=>1],...$total_expenses];
    $rData[]=['title'=>['t'=>' ','b'=>1],...$total_ad_expenses];
    $rData[]=['title'=>['t'=>'Profit from operation','b'=>1],...$profit_from_operation_data];
    $rData[]=['title'=>['t'=>'Finance cost','b'=>1],...$finance_cost_data];
    $rData[]=['title'=>['t'=>'Profit/Loss before tax','b'=>1],...$profit_loss_data];
    $rData[]=['title'=>['t'=>'Other Revenue','b'=>1],...$other_revenue_data];
    $rData[]=['title'=>['t'=>'Add: FDR Interest','b'=>1],...$provision_for_income_tax_data];
    $rData[]=['title'=>['t'=>'Net Profit/ Loss before Tax','b'=>1],...$net_profit_loss_before_tax_data];
    $rData[]=['title'=>['t'=>'Provision for Income Tax','b'=>1],...$empty_data];
    $rData[]=['title'=>['t'=>'Net profit after Tax during the year','b'=>1],...$net_profit_loss_before_tax_data];
    $rData[]=['title'=>['t'=>'Net profit after Tax during the year','b'=>1],...$net_profit_after_tax_data];
    $rData[]=['title'=>['t'=>'Basic Earnings Per Share (EPS)','b'=>1],...$empty_data];
    $rData[]=['title'=>['t'=>'The accompanying notes form an integral part of this Statement of Profit or Loss and Other Comprehensive Income.','b'=>1,'col'=>count($dates)+1],...$false_data];
    $rData[]=['title'=>['t'=>'This is the Statement of Profit or Loss and Other Comprehensive Income referred to in our separate report of even date.','b'=>1,'col'=>count($dates)+1],...$false_data];
        
    
}



$jArray[fl()]=$cost_of_goods;
$jArray[fl()]=$head;
$jArray[fl()]=$rData;


$fileName='statement_of_comprehensive_income'.TIME.rand(0,999).'.txt';
$report_data=[
    'name'      => 'statement_of_comprehensive_income',
    'title'     => 'Statement of comprehensive income',
    'info'      => [],
    'fileName'  => $fileName,
    'head'=>$head,
    'data'=>$rData
];
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;