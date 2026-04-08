<?php
    $dRange=$_POST['dRange'];
    $reportInfo=['Date :'.$dRange];
    $general->getFromToFromStringt($dRange,$from,$to);
    $scID   = intval($_POST['scID']);
    $supID  = intval($_POST['supID']);
    $q=['isActive=1','scID='.$scID];
    if($supID>0){
        $q[]='supID='.$supID;
    }
    $suppliers=$db->selectAll($general->table(45),'where '.implode(' and ',$q));


    $rData=[];
    $serial=1;
    $tpaid=0;
    $tsale=0;
    $tbuy=0;
    $tprofit=0;
    $tcommission=0;
    $topeningStockPrice=0;
    $tclosingStockPrice=0;
    $topeningBalance=0;
    if(!empty($suppliers)){
        foreach($suppliers as $sup){
            $paid=0;
            $sale=0;
            $profit=0;
            $commission=0;
            $openingStockPrice=0;
            $closingStockPrice=0;
            $products=$db->selectAll($general->table(104),'where supID='.$sup['supID'],'pID,pBuyPrice');
            if(!empty($products)){
                $general->arrayIndexChange($products,'pID');
                $openingStock=$db->selectAll($general->table(2),'where pID in('.implode(',',array_keys($products)).') and actionDate<'.$from.' group by pID','pID,sum(changeStock) t');
                //$jArray[__LINE__][]=$openingStock;
                foreach($openingStock as $os){
                    $openingStockPrice+=$products[$os['pID']]['pBuyPrice']*$os['t'];
                }
                $closingStock=$db->selectAll($general->table(2),'where pID in('.implode(',',array_keys($products)).') and actionDate<='.$to.' group by pID','pID,sum(changeStock) t');
                //$jArray[__LINE__][]=$closingStock;
                foreach($closingStock as $os){
                    $closingStockPrice+=$products[$os['pID']]['pBuyPrice']*$os['t'];
                }
            }

            $paids=$acc->voucherDetails(V_T_SUPPLIER_PAYMENT,$sup['supID'],$from,$to);
            if(!empty($paids)){
                foreach($paids as $p){
                    $paid+=$p['amount'];
                }
            }
            $openingBalance=$acc->headBalance($sup['hID'],$from-1);
            $purchases=$db->selectAll($general->table(11),'where supID='.$sup['supID'].' and purDate between '.$from.' and '.$to,'sum(netTotal) as t','array',$jArray);
            $buy=$purchases[0]['t'];
            $sales=$db->selectAll($general->table(14),'where supID='.$sup['supID'].' and sDate between '.$from.' and '.$to,'sID,sTotal,sProfit','array',$jArray);
            if(!empty($sales)){
                $general->arrayIndexChange($sales,'sID');
                foreach($sales as $s){
                    $sale+=$s['sTotal'];
                    $profit+=$s['sProfit'];
                }
                $commissions=$db->selectAll($general->table(19),'where sID in('.implode(',',array_keys($sales)).') and socType='.OTHER_COST_COMMISSION,'sum(scAmount) as t');
                $commission=$commissions[0]['t'];

            }
            $tpaid              += $paid;
            $tsale              += $sale;
            $tprofit            += $profit;
            $tcommission        += $commission;
            $topeningStockPrice += $openingStockPrice;
            $tclosingStockPrice += $closingStockPrice;
            $topeningBalance    += $openingBalance;
            $tbuy               += $buy;
            $data=[
                's' =>$serial++,
                'n' =>$sup['supName'],
                'ob'=>$general->numberFormat($openingBalance),
                'os'=>$general->numberFormat($openingStockPrice),
                'b' =>$general->numberFormat($buy),
                'sl'=>$general->numberFormat($sale),
                'pr'=>$general->numberFormat($profit),
                'st'=>$general->numberFormat($closingStockPrice),
                'p' =>$general->numberFormat($paid),
                'c' =>$general->numberFormat($commission),
            ];
            $rData[]=$data;
        }
    }
    $rData[]=[
        's' =>'',
        'n' =>['t'=>'Total','b'=>1],
        'ob'=>['t'=>$general->numberFormat($topeningBalance),'b'=>1],
        'os'=>['t'=>$general->numberFormat($topeningStockPrice),'b'=>1],
        'b' =>['t'=>$general->numberFormat($tbuy),'b'=>1],
        'sl'=>['t'=>$general->numberFormat($tsale),'b'=>1],
        'pr'=>['t'=>$general->numberFormat($tprofit),'b'=>1],
        'st'=>['t'=>$general->numberFormat($tclosingStockPrice),'b'=>1],
        'p' =>['t'=>$general->numberFormat($tpaid),'b'=>1],
        'c' =>['t'=>$general->numberFormat($tcommission),'b'=>1],
    ];

    $fileName='supSummery_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'dueRep'.date('d_m_Y',$from).'_'.date('d_m_Y',$to),
        'title'     => 'Supplier Summery',
        'info'      => $reportInfo,
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"              ,'key'=>'s'     ,'hw'=>5),
            array('title'=>"Name"           ,'key'=>'n'),
            array('title'=>"Opening Balance",'key'=>'ob'    ,'al'=>'r'),
            array('title'=>"Opening Stock"  ,'key'=>'os'    ,'al'=>'r'),
            array('title'=>"Buy"            ,'key'=>'b'     ,'al'=>'r'),
            array('title'=>"Sale"           ,'key'=>'sl'    ,'al'=>'r'),
            array('title'=>"Paid"           ,'key'=>'p'     ,'al'=>'r'),
            array('title'=>"Profit"         ,'key'=>'pr'    ,'al'=>'r'),
            array('title'=>"Stock"          ,'key'=>'st'    ,'al'=>'r'),
            array('title'=>"Commission"     ,'key'=>'c'     ,'al'=>'r'),
            array('title'=>"Balance"        ,'key'=>'bl'    ,'al'=>'r'),
        ),
        'data'=>$rData
    );
    $jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;
?>
