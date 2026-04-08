<?php
    $dRange = $_POST['dRange'];
    $general->getFromToFromString($dRange,$from,$to);
    $reportInfo=['Date: '.$dRange];
    $types = $smt->get_all_product_type();
    $product_id = intval($_POST['product_id']);
    $can_show_price=$db->permission(104);
    $type = intval($_POST['type']);
    $zero_column = intval($_POST['zero_column'])==2?2:1;//2=with zero
    $zero_row = intval($_POST['zero_row'])==2?2:1;//2=with zero

    $q=['isActive in(1,0)']; 
    if($type>=0){
        $q[]='type='.$type;
        $reportInfo[]='Type: '.$types[$type]['title']??'';
    }
    if($product_id>0){
        $q[]='id='.$product_id;
    }
    $products = $db->selectAll('products','where '.implode(' and ',$q).' order by code','id,code,title,type,unit_id,sale_price,unit_cost','array',$jArray);//sale_price,unit_cost সিলেক্ট করা হয় যেন লগ না থাকলে লগ জেনারেট করে
    $rData=[];
    $sr=1;
    if(!empty($products)){
        $opening_tp=$db->get_product_price_log_price($from+1);
        $closing_tp=$db->get_product_price_log_price($to,$jArray);
        $general->arrayIndexChange($opening_tp,'product_id');
        $general->arrayIndexChange($closing_tp,'product_id');
        $jArray[fl()]=$opening_tp;
        $general->arrayIndexChange($products);
        $openings = $db->selectAll('product_stock_log','where product_id in('.implode(',',array_keys($products)).') and action_time < '.$from.' GROUP by product_id','SUM(quantity) as total_quantity, product_id','array',$jArray);
        $jArray[fl()] = $openings;
        $general->arrayIndexChange($openings,'product_id');
        $stock_data = $db->selectAll('product_stock_log','where product_id in('.implode(',',array_keys($products)).') and action_time between '.$from.' and '.$to,'','array',$jArray);
        $product_data=[];
        if(!empty($stock_data)){
            foreach($stock_data as $p){
                $product_id=$p['product_id'];
                if(!isset($product_data[$product_id])){
                    $product_data[$product_id]=[
                        'purchase'=>0,
                        'stock_entry'=>0,
                        'purchase_return'=>0,
                        'sale'=>0,
                        'sale_return'=>0,
                        'production'=>0,
                        'production_source'=>0,
                        'production_source_main'=>0,
                        'distribute'=>0,
                        'reject'=>0,
                    ];
                }
                if($p['change_type']==ST_CH_PURCHASE){
                    $product_data[$product_id]['purchase']+=$p['quantity'];
                }
                elseif($p['change_type']==ST_CH_PURCHASE_RETURN){
                    $product_data[$product_id]['purchase_return']+=-$p['quantity'];
                }
                elseif($p['change_type']==ST_CH_STOCK_ENTRY){
                    $product_data[$product_id]['stock_entry']+=$p['quantity'];
                }
                elseif($p['change_type']==ST_CH_SALE ){
                    $product_data[$product_id]['sale']+=-$p['quantity'];
                }
                elseif($p['change_type']==ST_CH_SALE_RETURN){
                    $product_data[$product_id]['sale_return']+=$p['quantity'];
                }
                elseif($p['change_type']==ST_CH_PRODUCTION){
                    $product_data[$product_id]['production']+=$p['quantity'];
                }
                elseif($p['change_type']==ST_CH_PRODUCTION_SOURCE){
                    $product_data[$product_id]['production_source']+=-$p['quantity'];
                }
                elseif($p['change_type']==ST_CH_PRODUCTION_SOURCE_MAN){
                    $product_data[$product_id]['production_source_main']+=-$p['quantity'];
                }
                elseif($p['change_type']==ST_CH_DISTRIBUTE){
                    $product_data[$product_id]['distribute']+=-$p['quantity'];
                }
                elseif($p['change_type']==ST_CH_REJECT){
                    $product_data[$product_id]['reject']+=-$p['quantity'];
                }
                else{
                    $jArray[fl()][]=$p['change_type'];
                }
            }
        }
        $jArray[fl()] = $zero_row;

        //$production_costs = $db->selectAll('production_product','where product_id in('.implode(',',array_keys($products)).') and date between '.$from.' and '.$to.' group by product_id','sum(total_cost) as total_cost, product_id');
        //$general->arrayIndexChange($production_costs,'product_id');
        $units=$db->selectAll('unit','where isActive=1');
        $general->arrayIndexChange($units);
        $zero_purchase=true;
        $zero_stock_entry=true;
        $zero_purchase_return=true;
        $zero_sale=true;
        $zero_sale_return=true;
        $zero_production=true;
        $zero_production_source=true;
        $zero_production_source_main=true;
        $zero_distribute=true;
        $zero_reject=true;
        $total_ov=0;
        $total_cv=0;
        foreach($products as $p){
            $is_zero_row=true;
            $opening                = intval(@$openings[$p['id']]['total_quantity']);
            $st_data                = $product_data[$p['id']]??[];
            $purchase               = intval($st_data['purchase']??0);
            $purchase_return        = intval($st_data['purchase_return']??0);
            $sale                   = intval($st_data['sale']??0);
            $sale_return            = intval($st_data['sale_return']??0);
            $production             = intval($st_data['production']??0);
            $production_source      = intval($st_data['production_source']??0);
            $production_source_main = intval($st_data['production_source_main']??0);
            $distribute             = intval($st_data['distribute']??0);
            $reject                 = intval($st_data['reject']??0);
            $stock_entry            = intval($st_data['stock_entry']??0);
            $otp=0;
            $ctp=0;
            $price_label='sale_price';
            if($p['type']==PRODUCT_TYPE_PACKAGING || $p['type']==PRODUCT_TYPE_RAW){
                $price_label = 'unit_cost';
            }
            if(isset($opening_tp[$p['id']])){
                $otp=$opening_tp[$p['id']][$price_label];
            }
            else{
                $productPriceLog=$db->fetchQuery("SELECT * FROM product_price_log WHERE product_id=".$p['id']);
                if(empty($productPriceLog)){
                   $jArray[fl()][]=$p['id'];
                   $product_data=[

                   ];
                   $db->product_price_log($p['id'],$p,$jArray);
                }
            }
            if(isset($closing_tp[$p['id']])){
                $ctp=$closing_tp[$p['id']][$price_label];
            }

            if($purchase>0){$zero_purchase=false;$is_zero_row=false;}
            if($stock_entry>0){$zero_stock_entry=false;$is_zero_row=false;}
            if($purchase_return>0){$zero_purchase_return=false;$is_zero_row=false;}
            if($sale>0){$zero_sale=false;$is_zero_row=false;}
            if($sale_return>0){$zero_sale_return=false;$is_zero_row=false;}
            if($production>0){$zero_production=false;$is_zero_row=false;}
            if($production_source>0){$zero_production_source=false;$is_zero_row=false;}
            if($production_source_main>0){$zero_production_source_main=false;$is_zero_row=false;}
            if($distribute>0){$zero_distribute=false;$is_zero_row=false;}
            if($reject>0){$zero_reject=false;$is_zero_row=false;}
            
            if($zero_row==1&&$is_zero_row==true){//1=without zero
                continue;
            }
            $stock = $opening
            +$purchase
            +$stock_entry
            -$purchase_return
            -$sale
            +$sale_return
            +$production
            -$production_source
            -$production_source_main
            -$reject
            -$distribute;

            $ov=0;
            $cv=0;
            if($opening>0&&$otp>0){
                $ov=$opening*$otp;
            }
            if($stock>0&&$ctp>0){
                $cv=$stock*$ctp;
            }
            $total_ov+=$ov;
            $total_cv+=$cv;
            $rData[]=[
                's'     => $sr++,
                't'     => $p['code'].' - '.$p['title'],
                'ty'    => $types[$p['type']]['title']??'',
                'u'     => $units[$p['unit_id']]['title']??'',
                'op'    => $opening,
                'ov'    => $ov,
                'otp'   => $otp,
                'pr'    => $purchase,
                'se'    => $stock_entry,
                'prr'   => $purchase_return,
                'sl'    => $sale,
                'slr'   => $sale_return,
                'p'     => $production,
                'ps'    => $production_source,
                'psm'   => $production_source_main,
                'dst'   => $distribute,
                'rj'    => $reject,
                'st'    => $stock,
                'ctp'   => $ctp,
                'cv'    => $cv,
                //'pc'    => $general->numberFormat($pr_cost),
            ];
        }
        $rData[]=[
            's'     => '',
            't'     => '',
            'ty'    => '',
            'u'     => '',
            'op'    => '',
            'ov'    => ['t'=>$total_ov,'b'=>1],
            'otp'   => '',
            'pr'    => '',
            'prr'   => '',
            'sl'    => '',
            'slr'   => '',
            'p'     => '',
            'ps'    => '',
            'psm'   => '',
            'dst'   => '',
            'rj'    => '',
            'st'    => '',
            'ctp'   => '',
            'cv'    => ['t'=>$total_cv,'b'=>1],
        ];
        $head=[
            ['title'=>"#"                  ,'key'=>'s','hw'=>5],
            ['title'=>"Product"            ,'key'=>'t'],
            //['title'=>"Type"               ,'key'=>'ty'],
            ['title'=>"Unit"               ,'key'=>'u'],
            ['title'=>"Opening QTY"            ,'key'=>'op','al'=>'r'],
        ];
        if($can_show_price){
            $head[]=['title'=>"TP"           ,'key'=>'otp','al'=>'r'];
            $head[]=['title'=>"Opening Value"        ,'key'=>'ov','al'=>'r'];
        }
        if($zero_column==2||$zero_purchase==false){
            $head[]=['title'=>"Purchase"           ,'key'=>'pr','al'=>'r'];
        }
        if($zero_column==2||$zero_stock_entry==false){
            $head[]=['title'=>"Stock entry"           ,'key'=>'se','al'=>'r'];
        }
        if($zero_column==2||$zero_purchase_return==false){
            $head[]=['title'=>"Purchase Return"    ,'key'=>'prr','al'=>'r'];
        }
        if($zero_column==2||$zero_sale==false){
            $head[]=['title'=>"Sale Qty"               ,'key'=>'sl','al'=>'r'];
        }
        if($zero_column==2||$zero_sale_return==false){
            $head[]=['title'=>"Sale Return"        ,'key'=>'slr','al'=>'r'];
        }
        if($zero_column==2||$zero_production==false){
            $head[]=['title'=>"Production Qty"         ,'key'=>'p','al'=>'r'];
        }
        if($zero_column==2||$zero_production_source==false){
            $head[]=['title'=>"Production Source"  ,'key'=>'ps','al'=>'r'];
        }
        if($zero_column==2||$zero_production_source_main==false){
            $head[]=['title'=>"Production Main"    ,'key'=>'psm','al'=>'r'];
        }
        if($zero_column==2||$zero_distribute==false){
            $head[]=['title'=>"Distribute"    ,'key'=>'dst','al'=>'r'];
        }
        if($zero_column==2||$zero_reject==false){
            $head[]=['title'=>"Reject"    ,'key'=>'rj','al'=>'r'];
        }
        
        $head[]=['title'=>"Closing Qty"              ,'key'=>'st','al'=>'r'];
        if($can_show_price){
            $head[]=['title'=>"Closing TP"           ,'key'=>'ctp','al'=>'r'];
            $head[]=['title'=>"Closing Value"        ,'key'=>'cv','al'=>'r'];
        }
        $fileName='product_report'.TIME.rand(0,999).'.txt';
        $report_data=array(
            'name'      => 'product_report',
            'title'     => 'Product Report',
            'info'      => $reportInfo,
            'fileName'  => $fileName,
            'head'=>$head,
            'data'=>$rData
        );
        $gAr['report_data']= $report_data;
        textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
        $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
        $jArray['status']=1;
}