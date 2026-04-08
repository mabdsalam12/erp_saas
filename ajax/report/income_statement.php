<?php 
$date_range = $_POST['date_range'];
include_once 'income_report_common.php';
// print_r($row_materials_consumed_data);
$jArray[fl()] = $cost_of_goods_sold_data;
$rData[]=['title'=>['t'=>'Particulars','b'=>1],...$title_data];
if(hasPositiveT($cost_of_goods)){
    $rData[] = ['title'=>['t'=>'13 Cost of Goods Sales','b'=>1],...$cost_of_goods];
}
if(hasPositiveT($row_materials_consumed_data)){
    $rData[] = ['title'=>['t'=>'13.1 Raw materials consumed'],...$row_materials_consumed_data];
}
if(hasPositiveT($packing_materials_consumed_data)){
    $rData[] = ['title'=>['t'=>'13.2 Packing Materials consumed'],...$packing_materials_consumed_data];
}
if(hasPositiveT($factory_Overhead_data)){
    $rData[] = ['title'=>['t'=>'13.3 Factory Overhead'],...$factory_Overhead_data];
}
if(hasPositiveT($cost_of_production_data)){
    $rData[] = ['title'=>['t'=>'Cost of Production','b'=>1],...$cost_of_production_data];
}
if(hasPositiveT($manufacturing_purchased_add)){
    $rData[] = ['title'=>['t'=>'Add: Opening Work in Process'],...$manufacturing_purchased_add];
}
if(hasPositiveT($manufacturing_purchased_less)){
    $rData[] = ['title'=>['t'=>'Less: Closing Work in Process'],...$manufacturing_purchased_less];
}
if(hasPositiveT($calculation_data)){
    $rData[] = ['title'=>['t'=>'','b'=>1],...$calculation_data];
}
if(hasPositiveT($finished_purchased_add)){
    $rData[] = ['title'=>['t'=>'Add: Opening Stock of Finished Goods'],...$finished_purchased_add];
}
if(hasPositiveT($finished_purchased_less)){
    $rData[] = ['title'=>['t'=>'Less : Closing Stock of Finished Goods'],...$finished_purchased_less];
}
if(hasPositiveT($cost_of_goods_sold_data)){
    $rData[] = ['title'=>['t'=>'Cost of goods sold','b'=>1],...$cost_of_goods_sold_data];
}
if(hasPositiveT($row_consumed)){
    $rData[] = ['title'=>['t'=>'13.1 Raw materials consumed','b'=>1],...$row_consumed];
}
if(hasPositiveT($row_op)){
    $rData[] = ['title'=>['t'=>'Opening Raw Materials'],...$row_op];
}
if(hasPositiveT($row_purchased)){
    $rData[] = ['title'=>['t'=>'Add: Purchased Raw Materials'],...$row_purchased];
}
if(hasPositiveT($row_cl)){
    $rData[] = ['title'=>['t'=>'Less: Closing Raw Materials'],...$row_cl];
}
if(hasPositiveT($package_consumed)){

    $rData[] = ['title'=>['t'=>'13.2 Packing Materials consumed','b'=>1],...$package_consumed];
}
if(hasPositiveT($package_op)){
    $rData[] = ['title'=>['t'=>'Opening Packing Materials'],...$package_op];
}
if(hasPositiveT($package_purchased)){
    $rData[] = ['title'=>['t'=>'Add: Purchased Packing Materials during the year'],...$package_purchased];
}
if(hasPositiveT($package_cl)){
    $rData[] = ['title'=>['t'=>'Less: Closing Packing Materials'],...$package_cl];
}
// if(hasPositiveT($package_cl)){
//     $rData[] = ['title'=>['t'=>'Less: Closing Packing Materials'],...$package_cl];
// }
// $rData[] = ['title'=>['t'=>'Inventories','b'=>1],...$inventories_data];
// $rData[] = ['title'=>['t'=>'Inventories - RM'],...$row_product_closing_data];
// $rData[] = ['title'=>['t'=>'Inventories - PM'],...$package_product_closing_data];
// $rData[] = ['title'=>['t'=>'Inventories - Working Process'],...$manufacturing_product_closing_data];
// $rData[] = ['title'=>['t'=>'Inventories - Finished Good'],...$finished_product_closing_data];
//$jArray[fl()]=$acc->chart_of_accounts();
//$jArray[fl()]=$chart_of_account_wise_ledgers;
//$jArray[fl()]=$chart_accounts;
$chart_of_accounts = $acc->chart_of_accounts();
$chart_of_accounts_array=[
    COA_FACTORY_OVER_HEAD=>[
        'title'=>'13.3 '.$chart_of_accounts[COA_FACTORY_OVER_HEAD]['title'],
        'id'=>$chart_of_accounts[COA_FACTORY_OVER_HEAD]['id']
    ],
    COA_ADMINISTRATIVE_EXPENSES=>['title'=>'14 '.$chart_of_accounts[COA_ADMINISTRATIVE_EXPENSES]['title'],'id'=>$chart_of_accounts[COA_ADMINISTRATIVE_EXPENSES]['id']],
    COA_SELLING_AND_MARKETING_EXP=>['title'=>'15 '.$chart_of_accounts[COA_SELLING_AND_MARKETING_EXP]['title'],'id'=>$chart_of_accounts[COA_SELLING_AND_MARKETING_EXP]['id']],
    COA_DISTRIBUTION_EXPENSES=>['title'=>'16 '.$chart_of_accounts[COA_DISTRIBUTION_EXPENSES]['title'],'id'=>$chart_of_accounts[COA_DISTRIBUTION_EXPENSES]['id']],
    COA_FINANCIAL_EXPENSES=>['title'=>'17 '.$chart_of_accounts[COA_FINANCIAL_EXPENSES]['title'],'id'=>$chart_of_accounts[COA_FINANCIAL_EXPENSES]['id']],
];
//$jArray[fl()]=$chart_of_accounts_array;
//$jArray[fl()]=$chart_accounts_data;
if(!empty($chart_accounts)){
    foreach($chart_of_accounts_array as $chart_key=>$ca){
        $chart_account = $chart_accounts[$ca['id']]??0;
        $ledger= $chart_of_account_wise_ledgers[$chart_account]??[];
        ////$jArray[fl()][]=$ledger;
        $ca_amount = [];
        $total_amount=[];
        foreach($dates as $key=>$date){
            $debit_key = "debit_$key";
            $credit_key = "credit_$key";
            $ch_of_ac_total = floatval(@$chart_of_account_wise_total[$chart_account][$key]) ;
            // if($ch_of_ac_total>0){
            //     $debit=$ch_of_ac_total;
            //     $credit=0;
            // }
            // else{
            //     $debit=0;
            //     $credit=-1*$ch_of_ac_total;
            // }
            $ca_amount[$debit_key]=['t'=>$general->numberFormat($ch_of_ac_total)];
            //$ca_amount[$credit_key]=['t'=>$general->numberFormat($debit)];

            $total_amount[$debit_key]=['t'=>$general->numberFormat($ch_of_ac_total),'key'=>$key];
            //$total_amount[$credit_key]=['t'=>$general->numberFormat($credit),'key'=>$key];
        }
        $code = $chart_accounts_data[$chart_account]['code']??'';
        if(hasPositiveT($ca_amount)){
            $rData[]=['title'=>['t'=>$code.' *'.$chart_key.'* ' .$ca['title'],'b'=>1],...$ca_amount];
        }
        //$rData[]=['title'=>['t'=>$code.' '.$chart_account.' *'.$chart_key.'* ' .$ca['title'],'b'=>1],...$ca_amount];
        
        foreach($ledger as $l){
            $l_amount = [];
            foreach($dates as $key=>$date){
                $debit_key = "debit_$key";
                $credit_key = "credit_$key";
                $ledger_amount = floatval(@$head_opening_balance[$l['id']][$key]);
                // if($ledger_amount>0){
                //     $debit=$ledger_amount;
                //     $credit=0;
                // }
                // else{
                //     $debit=0;
                //     $credit=-1*$ledger_amount;
                // }
                $l_amount[$debit_key]=['t'=>$general->numberFormat($ledger_amount),'key'=>$key];
                //$l_amount[$credit_key]=['t'=>$general->numberFormat($credit),'key'=>$key];
            }
            if(hasPositiveT($l_amount)){
                $rData[]=['title'=>['t'=>$l['code'].' '.$l['title']],...$l_amount];
            }
        }
        $code = $chart_accounts_data[$chart_account]['code']??'';
        //$rData[]=['title'=>['t'=>$code.' '.$ca['title'].' Distribution','b'=>1],...$total_amount];

    }
}

//$jArray[fl()]=$rData;


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